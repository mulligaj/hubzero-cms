<?php
namespace Components\Contracts\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\Contracts\Models\Agreement;
use Components\Contracts\Models\Contract;
use Request;
use Config;
use Notify;
use Route;
use User;
use Lang;
use Date;
use App;

class Agreements extends AdminController
{
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
			'contract' => urldecode(Request::getState(
				$this->_option . '.' . $this->_controller . '.contract',
				'contract_id',
				0
			)),
			'sort' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sort',
				'filter_order',
				'lastname'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sortdir',
				'filter_order_Dir',
				'ASC'
			),
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
			)
		);

		$agreements = Agreement::all();

		if ($filters['contract'] > 0)
		{
			$agreements->whereEquals('contract_id', $filters['contract']);
		}
		
		$searchableFields = array(
			'firstname',
			'lastname',
			'email',
			'organization_name',
			'organization_address'
		);

		if (!empty($filters['search']))
		{
			$firstValue = array_shift($searchableFields);
			$agreements->whereLike($firstValue, $filters['search'], 1);
			foreach ($searchableFields as $field)
			{
				$agreements->orWhereLike($field, $filters['search'], 1);
			}
			$agreements->resetDepth();
		}

		$rows = $agreements
			->ordered('filter_order', 'filter_order_Dir')
			->paginated()
			->rows();

		$contracts = Contract::all()->order('title', 'ASC')->rows();

		// Output the view
		$this->view
			->set('filters', $filters)
			->set('rows', $rows)
			->set('contracts', $contracts)
			->set('total', count($agreements))
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
			$row = Agreement::oneOrNew($id);
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

		// Incoming
		$fields = Request::getVar('fields', array(), 'post', 'none', 2);

		// Initiate model and bind the incoming data to it
		$row = Agreement::oneOrNew($fields['id'])->set($fields);

		if (!$row->save())
		{
			foreach ($row->getErrors() as $error)
			{
				Notify::error($error);
			}

			return $this->editTask($row);
		}

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
		if (!User::authorise('core.delete', $this->_option))
        {
            App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
        }
		Request::checkToken();

		$ids = Request::getVar('id', array(), 'post');

		// Do we actually have any entries?
		if (count($ids) > 0)
		{
			$removed = 0;

			// Loop through all the IDs
			foreach ($ids as $id)
			{
				// Get the model for this entry
				$entry = Agreement::one(intval($id));

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

		// Set the redirect URL to the main entries listing.
		$this->cancelTask();
	}

	/**
	 * Sets the state of one or more entries
	 *
	 * @return  void
	 */
	public function stateTask()
	{
		Request::checkToken(['get', 'post']);

		$state = $this->getTask() == 'publish' ? 1 : 0;

		$ids = Request::getVar('id', array(0));
		$ids = (!is_array($ids) ? array($ids) : $ids);

		if (count($ids) < 1)
		{
			Notify::warning(Lang::txt('COM_DRWHO_SELECT_ENTRY_TO', $this->_task));

			return $this->cancelTask();
		}

		// Loop through all the IDs
		$success = 0;
		foreach ($ids as $id)
		{
			// Load the entry and set its state
			$row = Contract::oneOrNew(intval($id))->set(array('state' => $state));

			// Store new content
			if (!$row->save())
			{
				Notify::error($row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			switch ($this->_task)
			{
				case 'publish':
					$message = Lang::txt('COM_DRWHO_ITEMS_PUBLISHED', $success);
				break;
				case 'unpublish':
					$message = Lang::txt('COM_DRWHO_ITEMS_UNPUBLISHED', $success);
				break;
				case 'archive':
					$message = Lang::txt('COM_DRWHO_ITEMS_ARCHIVED', $success);
				break;
			}

			Notify::success($message);
		}
		$this->cancelTask();
	}
}
