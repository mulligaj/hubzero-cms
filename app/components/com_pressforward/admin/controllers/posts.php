<?php
namespace Components\PressForward\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\PressForward\Models\Post;
use Components\PressForward\Models\Postmeta;
use Components\PressForward\Models\Folder;
use Components\PressForward\Models\Relationship;
use Request;
use Notify;
use Route;
use User;
use Lang;
use Date;
use App;

/**
 * PressForward controller for posts
 */
class Posts extends AdminController
{
	/**
	 * Execute a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Adding/Editing/Saving
		$this->registerTask('add', 'edit');
		$this->registerTask('apply', 'save');

		// State changes
		$this->registerTask('trash', 'state');
		$this->registerTask('publish', 'state');
		$this->registerTask('draft', 'state');

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
			'status' => urldecode(Request::getState(
				$this->_option . '.' . $this->_controller . '.status',
				'status',
				''
			)),
			'display' => urldecode(Request::getState(
				$this->_option . '.' . $this->_controller . '.display',
				'display',
				'gogrid'
			)),
			'folder' => Request::getState(
				$this->_option . '.' . $this->_controller . '.folder',
				'folder',
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
				'post_date_gmt'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sortdir',
				'filter_order_Dir',
				'DESC'
			)
		);

		// Get our model
		$p = Post::blank()->getTableName();
		$r = Relationship::blank()->getTableName();

		$record = Post::all()
			->select($p . '.*')
			->whereEquals('post_type', Post::$post_draft);

		if ($filters['status'])
		{
			$record->whereEquals('post_status', $filters['status']);
		}

		if ($filters['folder'])
		{
			$tr = \Components\PressForward\Models\Folder\Relationship::blank()->getTableName();
			$t  = \Components\PressForward\Models\Folder\Taxonomy::blank()->getTableName();

			$record
				->joinRaw($tr, $tr . '.object_id IN (' . $p . '.ID, ' . $p . '.post_parent)', 'inner')
				->join($t, $t . '.term_taxonomy_id', $tr . '.term_taxonomy_id', 'inner')
				->whereEquals($t . '.term_id', $filters['folder']);
		}

		if ($search = $filters['search'])
		{
			$record->whereLike($p . '.post_title', $search);
		}

		if ($filters['sort'] == 'dateofitem' || $filters['sort'] == 'reset')
		{
			$filters['sort'] = 'post_date_gmt';
		}
		if ($filters['sort'] == 'dateretrieved')
		{
			$filters['sort'] = 'post_date';
		}

		$rows = $record
			->order($p . '.' . $filters['sort'], $filters['sort_Dir'])
			->paginated('limitstart', 'limit')
			->rows();

		// Get the folders
		$folders = Folder::listing();

		// Output the view
		$this->view
			->set('rows', $rows)
			->set('filters', $filters)
			->set('folders', $folders)
			->set('config', $this->config)
			->display();
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

			// Load the record
			$row = Post::oneOrNew($id);
		}

		if ($row->isNew())
		{
			$row->set('post_author', User::get('id'));
			$row->set('post_date_gmt', Date::toSql());
			$row->set('post_date', Date::toSql());
			$row->set('post_status', 'draft');
			$row->set('visibility', 'public');
		}

		// Get the folders
		$folders = Folder::tree();

		// Output the HTML
		$this->view
			->set('row', $row)
			->set('folders', $folders)
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
		$fields = Request::getVar('fields', array(), 'post', 'none', 2);

		if ($fields['visibility'] == 'private')
		{
			$fields['post_status'] = 'private';
		}
		unset($fields['visibility']);

		// Initiate extended database class
		$row = Post::oneOrNew($fields['ID'])->set($fields);
		$row->set('post_type', Post::$post_draft);

		// Store new content
		if (!$row->save())
		{
			Notify::error($row->getError());
			return $this->editTask($row);
		}

		// Process tags
		$row->tag(trim(Request::getVar('tags', '')));

		// Save categories
		$tax_input = Request::getVar('tax_input', array(), 'post', 'none', 2);

		$added = array();
		if (isset($tax_input['pf_feed_category']))
		{
			foreach ($tax_input['pf_feed_category'] as $i => $tax)
			{
				if (!$tax)
				{
					continue;
				}

				$rel = Folder\Relationship::oneByObjectAndTerm($row->get('ID'), $tax);

				if (!$rel->get('object_id'))
				{
					$rel->set('object_id', $row->get('ID'));
					$rel->set('term_taxonomy_id', $tax);

					if (!$rel->save())
					{
						Notify::error($meta->getError());
						return $this->editTask($row);
					}

					// We need to update the usage count
					$taxonomy = Folder\Taxonomy::oneOrFail($tax);
					$taxonomy->set('count', $taxonomy->relationships()->total());
					$taxonomy->save();
				}

				$added[] = $rel->get('term_taxonomy_id');
			}
		}

		foreach ($row->folders()->rows() as $folder)
		{
			if (!in_array($folder->get('term_taxonomy_id'), $added))
			{
				$tax = $folder->get('term_taxonomy_id');

				if (!$folder->destroy())
				{
					Notify::error($folder->getError());
					return $this->editTask($row);
				}

				// We need to update the usage count
				$taxonomy = Folder\Taxonomy::oneOrFail($tax);
				$taxonomy->set('count', $taxonomy->relationships()->total());
				$taxonomy->save();
			}
		}

		// Notify user
		Notify::success(Lang::txt('PF_FEED_SAVED'));

		// Fall back to the edit form if needed
		if ($this->getTask() == 'apply')
		{
			return $this->editTask($row);
		}

		// Set the redirect
		$this->cancelTask();
	}

	/**
	 * Set a state value
	 *
	 * @return  void
	 */
	public function stateTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		if (!User::authorise('core.edit.state', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$ids = Request::getVar('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		// Loop through all the IDs
		foreach ($ids as $id)
		{
			$entry = Post::oneOrFail(intval($id));

			if ($entry->isNew())
			{
				Notify::error('Post not found');
				continue;
			}

			$entry->set('post_status', $this->getTask());

			if (!$entry->save())
			{
				Notify::error($entry->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			Notify::success(Lang::txt('PF_SUCCESS_ITEM_' . strtoupper($this->getTask())));
		}

		// Set the redirect
		$this->cancelTask();
	}

	/**
	 * Delete one or more entries
	 *
	 * @return  void
	 */
	public function deleteTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		if (!User::authorise('core.delete', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$ids = Request::getVar('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$removed = 0;

		// Loop through all the IDs
		foreach ($ids as $id)
		{
			$entry = Post::oneOrFail(intval($id));

			if (!$entry->destroy())
			{
				Notify::error($entry->getError());
				continue;
			}

			$removed++;
		}

		if ($removed)
		{
			Notify::success(Lang::txt('PF_ITEM_DELETED'));
		}

		// Set the redirect
		$this->cancelTask();
	}
}
