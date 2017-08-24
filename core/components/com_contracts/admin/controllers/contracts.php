<?php
namespace Components\Contracts\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\Contracts\Models\Contract;
use Request;
use Config;
use Notify;
use Route;
use User;
use Lang;
use Date;
use App;

class Contracts extends AdminController
{
	/**
	 * Execute a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->registerTask('add', 'edit');
		$this->registerTask('apply', 'save');
		$this->registerTask('publish', 'state');
		$this->registerTask('unpublish', 'state');

		parent::execute();
	}

	/**
	 * Display a list of entries
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		$filters = array(
			'search' => urldecode(Request::getState(
				$this->_option . '.' . $this->_controller . '.search',
				'search',
				''
			)),
			'sort' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sort',
				'filter_order',
				'title'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sortdir',
				'filter_order_Dir',
				'ASC'
			)
		);

		$record = Contract::all()
					->select('#__contracts.*')
					->select('#__contract_agreements.id', 'submissions_count', true) 
					->join('#__contract_agreements', '#__contracts.id', 'contract_id', 'left')
					->group('#__contracts.id');

		if (!empty($filters['search']))
		{
			$record->whereLike('title', $filters['search']);
		}

		$rows = $record
			->ordered('filter_order', 'filter_order_Dir')
			->paginated()
			->rows();

		// Output the view
		$this->view
			->set('filters', $filters)
			->set('rows', $rows)
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
			$id = Request::getVar('id', array(0));
			if (is_array($id) && !empty($id))
			{
				$id = $id[0];
			}

			// Load the article
			$row = Contract::oneOrNew($id);
		}

		$this->view
			->set('row', $row)
			->setLayout('edit')
			->display();
	}

	/**
	 * Save changes to an entry
	 *
	 * @return  void
	 */
	public function saveTask()
	{
		Request::checkToken();

		if (!User::authorise('core.edit', $this->_option)
         && !User::authorise('core.create', $this->_option))
        {
            App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
        }

		$fields = Request::getVar('fields', array(), 'post', 'none', 2);
		$contacts = Request::getVar('contacts', array());
		$contacts = explode(',', $contacts);

		$row = Contract::oneOrNew($fields['id'])->set($fields);

		if (!$row->save())
		{
			foreach ($row->getErrors() as $error)
			{
				Notify::error($error);
			}

			return $this->editTask($row);
		}

		$row->contacts()->sync($contacts);

		// Notify the user that the entry was saved
		Notify::success(Lang::txt('COM_DRWHO_ENTRY_SAVED'));

		if ($this->getTask() == 'apply')
		{
			// Display the edit form. This will happen if the user clicked
			// the "save" or "apply" button.
			return $this->editTask($row);
		}

		// Are we redirecting?
		// This will happen if a user clicks the "save & close" button.
		$this->cancelTask();
	}

	/**
	 * Delete one or more entries
	 *
	 * @return  void
	 */
	public function removeTask()
	{
		Request::checkToken();

		if (!User::authorise('core.delete', $this->_option))
        {
            App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
        }

		$ids = Request::getVar('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Do we actually have any entries?
		if (count($ids) > 0)
		{
			$removed = 0;

			// Loop through all the IDs
			foreach ($ids as $id)
			{
				// Get the model for this entry
				$entry = Contract::oneOrFail(intval($id));

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
			Notify::success(Lang::txt('COM_DRWHO_ENTRIES_DELETED'));
		}

		$this->cancelTask();
	}

}
