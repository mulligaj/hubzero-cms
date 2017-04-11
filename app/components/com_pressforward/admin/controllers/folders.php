<?php
namespace Components\PressForward\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\PressForward\Models\Folder;
use Components\PressForward\Models\Folder\Taxonomy;
use Request;
use Notify;
use Route;
use Lang;
use App;

/**
 * PressForward controller for folders
 */
class Folders extends AdminController
{
	/**
	 * Execute a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Here we're aliasing the task 'add' to 'edit'. When examing
		// this controller, you should not find any method called 'addTask'.
		// Instead, we're telling the controller to execute the 'edit' task
		// whenever a task of 'add' is called.
		$this->registerTask('add', 'edit');
		$this->registerTask('apply', 'save');

		// Call the parent execute() method. Important! Otherwise, the
		// controller will never actually execute anything.
		parent::execute();
	}

	/**
	 * Display a list of entries
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Get some incoming filters to apply to the entries list
		$filters = array(
			'search' => urldecode(Request::getState(
				$this->_option . '.' . $this->_controller . '.search',
				'search',
				''
			)),
			// Paging
			'limit' => Request::getState(
				$this->_option . '.' . $this->_controller . '.limit',
				'limit',
				Config::get('list_limit'),
				'int'
			),
			'start' => Request::getState(
				$this->_option . '.' . $this->_controller . '.limitstart',
				'limitstart',
				0,
				'int'
			),
			// Get sorting variables
			//
			// "sort" is the name of the table column to sort by
			// "sort_Dir" is the direction to sort by [ASC, DESC]
			'sort' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sort',
				'filter_order',
				'name'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sortdir',
				'filter_order_Dir',
				'ASC'
			)
		);

		// Get our model
		// This is the entry point to the database and the 
		// table of entries we'll be retrieving data from
		$record = Folder::all();

		$a = $record->getTableName();
		$b = Taxonomy::blank()->getTableName();

		$record
			->select($a . '.*,' . $b . '.*')
			->join($b, $b . '.term_id', $a . '.term_id', 'left')
			->whereEquals($b . '.taxonomy', Taxonomy::$term_type);

		if ($search = $filters['search'])
		{
			$record->whereLike('name', $search, 1)
				->orWhereLike('description', $search, 1)
				->resetDepth();
		}

		$rows = $record
			->order($filters['sort'], $filters['sort_Dir'])
			//->paginated('limitstart', 'limit')
			->rows();

		$levellimit = ($filters['limit'] == 0) ? 500 : $filters['limit'];
		$list       = array();
		$children   = array();

		$total = $rows->count();

		if ($total > 0)
		{
			$children = array(
				0 => array()
			);

			foreach ($rows as $row)
			{
				$pt   = $row->get('parent');
				$list = @$children[$pt] ? $children[$pt] : array();

				array_push($list, $row);

				$children[$pt] = $list;
			}

			$list = $this->treeRecurse(0, '', array(), $children, max(0, $levellimit-1));
		}

		// Output the view
		$this->view
			->set('total', $total)
			->set('rows', array_slice($list, $filters['start'], $filters['limit']))
			->set('filters', $filters)
			->display();
	}

	/**
	 * Recursive function to build tree
	 *
	 * @param   integer  $id        Parent ID
	 * @param   string   $indent    Indent text
	 * @param   array    $list      List of records
	 * @param   array    $children  Container for parent/children mapping
	 * @param   integer  $maxlevel  Maximum levels to descend
	 * @param   integer  $level     Indention level
	 * @param   integer  $type      Indention type
	 * @return  void
	 */
	protected function treeRecurse($id, $indent, $list, $children, $maxlevel=9999, $level=0, $type=1)
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $v)
			{
				$id = $v->get('term_taxonomy_id');

				if ($type)
				{
					$pre    = '<span class="gi treenode">|—</span>&nbsp;'; //&#x2517
					$spacer = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}
				else
				{
					$pre    = '- ';
					$spacer = '&nbsp;&nbsp;';
				}

				if ($v->get('parent') == 0)
				{
					$txt = '';
				}
				else
				{
					$txt = $pre;
				}
				$pt = $v->get('parent');

				$list[$id] = $v;
				$list[$id]->set('treename', "$indent$txt");
				$list[$id]->set('children', count(@$children[$id]));
				$list = $this->treeRecurse($id, $indent . $spacer, $list, $children, $maxlevel, $level+1);
			}
		}
		return $list;
	}

	/**
	 * Show a form for editing an entry
	 *
	 * @param   object  $row
	 * @return  void
	 */
	public function editTask($row=null)
	{
		if (!User::authorise('core.edit', $this->_option)
		 && !User::authorise('core.create', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		Request::setVar('hidemainmenu', 1);

		if (!is_object($row))
		{
			// Incoming
			$id = Request::getVar('id', array(0));
			if (is_array($id) && !empty($id))
			{
				$id = $id[0];
			}

			// Load the article
			$row = Taxonomy::oneOrNew($id);
		}

		$record = Folder::all();

		$a = $record->getTableName();
		$b = Taxonomy::blank()->getTableName();

		$rows = $record
			->select($a . '.*,' . $b . '.*')
			->join($b, $b . '.term_id', $a . '.term_id', 'left')
			->whereEquals($b . '.taxonomy', Taxonomy::$term_type)
			->order('name', 'asc')
			->rows();

		$levellimit = 500;
		$list       = array();
		$children   = array();

		if ($rows->count() > 0)
		{
			$children = array(
				0 => array()
			);

			foreach ($rows as $r)
			{
				$pt   = $r->get('parent');
				$list = @$children[$pt] ? $children[$pt] : array();

				array_push($list, $r);

				$children[$pt] = $list;
			}

			$list = $this->treeRecurse(0, '', array(), $children, max(0, $levellimit-1), 0, 0);
		}

		foreach ($this->getErrors() as $err)
		{
			Notify::error($err);
		}

		// Output the HTML
		$this->view
			->set('row', $row)
			->set('folders', $list)
			->setLayout('edit')
			->display();
	}

	/**
	 * Save an entry
	 *
	 * @return  void
	 */
	public function saveTask()
	{
		// Check for request forgeries
		Request::checkToken();

		if (!User::authorise('core.edit', $this->_option)
		 && !User::authorise('core.create', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$raw = Request::getInt('no_html', 0);
		$dir = Request::getVar('folder', array(), 'post', 'none', 2);
		$tax = Request::getVar('taxonomy', array(), 'post', 'none', 2);
		$tax['taxonomy'] = Taxonomy::$term_type;

		// Save term data
		$folder   = Folder::oneOrNew($dir['term_id'])->set($dir);
		$taxonomy = Taxonomy::oneOrNew($tax['term_taxonomy_id'])->set($tax);
		$taxonomy->folder->set($folder);

		if (!$folder->save())
		{
			$this->setError($folder->getError());
			return ($raw ? $this->rawTask() : $this->editTask($taxonomy));
		}

		// Save taxonomy data
		$taxonomy->set('term_id', $folder->get('term_id'));
		$taxonomy->set('count', $taxonomy->relationships()->total());

		if (!$taxonomy->save())
		{
			$this->setError($taxonomy->getError());
			return ($raw ? $this->rawTask() : $this->editTask($taxonomy));
		}

		// Display success
		if ($raw)
		{
			$this->view
				->setName('feeds')
				->setLayout('_folder')
				->set('prfx', 'all')
				->set('folder', $folder)
				->set('depth', 1)
				->display();
			return;
		}

		Notify::success(Lang::txt('PF_FOLDER_SAVED'));

		if ($this->getTask() == 'apply')
		{
			return $this->editTask($taxonomy);
		}

		// Set the redirect
		$this->cancelTask();
	}

	/**
	 * Output a response as JSON
	 *
	 * @param   string  $message
	 * @return  void
	 */
	public function rawTask($message = null)
	{
		$response = new \stdClass;
		$response->success = true;
		$response->message = $message;

		if ($err = $this->getError())
		{
			$response->success = false;
			$response->message = $err;
		}

		echo json_encode($response);
		die();
	}

	/**
	 * Delete one or more entries
	 *
	 * @return  void
	 */
	public function deleteTask()
	{
		// Check for request forgeries
		Request::checkToken();

		if (!User::authorise('core.delete', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$ids = Request::getVar('id', array());

		if (count($ids) > 0)
		{
			$removed = 0;

			// Loop through all the IDs
			foreach ($ids as $id)
			{
				$entry = Taxonomy::oneOrFail(intval($id));

				// Delete the entry
				if (!$entry->destroy())
				{
					Notify::error($entry->getError());
					continue;
				}

				$removed++;
			}
		}

		if ($removed)
		{
			Notify::success(Lang::txt('PF_FOLDER_DELETED'));
		}

		// Set the redirect
		$this->cancelTask();
	}
}
