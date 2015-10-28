<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Blog\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\Blog\Models\Archive;
use Components\Blog\Models\Entry;
use Request;
use Config;
use Notify;
use Route;
use User;
use Lang;
use Date;
use App;

/**
 * Blog controller class for entries
 */
class Entries extends AdminController
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
	 * Display a list of blog entries
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		$this->view->filters = array(
			'scope' => Request::getState(
				$this->_option . '.' . $this->_controller . '.scope',
				'scope',
				'site'
			),
			'search' => urldecode(Request::getState(
				$this->_option . '.' . $this->_controller . '.search',
				'search',
				''
			)),
			'state' => urldecode(Request::getState(
				$this->_option . '.' . $this->_controller . '.state',
				'state',
				''
			)),
			// Get sorting variables
			'sort' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sort',
				'filter_order',
				'title'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sortdir',
				'filter_order_Dir',
				'ASC'
			),
			// Get paging variables
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

		if ($this->view->filters['scope'] == 'group')
		{
			$this->view->filters['scope_id'] = Request::getState(
				$this->_option . '.' . $this->_controller . '.scope_id',
				'scope_id',
				0,
				'int'
			);
		}
		$this->view->filters['order'] = $this->view->filters['sort'] . ' ' . $this->view->filters['sort_Dir'];

		// Instantiate our HelloEntry object
		$obj = new Archive();

		// Get record count
		$this->view->total = $obj->entries('count', $this->view->filters);

		// Get records
		$this->view->rows  = $obj->entries('list', $this->view->filters);

		// Output the HTML
		$this->view->display();
	}

	/**
	 * Show a form for editing an entry
	 *
	 * @param   object  $row  BlogEntry
	 * @return  void
	 */
	public function editTask($row=null)
	{
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
			$row = new Entry($id);
		}

		$this->view->row = $row;

		if (!$this->view->row->exists())
		{
			$this->view->row->set('created_by', User::get('id'));
			$this->view->row->set('created', Date::toSql());
			$this->view->row->set('publish_up', Date::toSql());
		}

		// Output the HTML
		$this->view
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

		// Incoming
		$fields = Request::getVar('fields', array(), 'post', 'none', 2);

		if (isset($fields['publish_up']) && $fields['publish_up'] != '')
		{
			$fields['publish_up']   = Date::of($fields['publish_up'], Config::get('offset'))->toSql();
		}
		if (isset($fields['publish_down']) && $fields['publish_down'] != '')
		{
			$fields['publish_down'] = Date::of($fields['publish_down'], Config::get('offset'))->toSql();
		}

		// Initiate extended database class
		$row = new Entry($fields['id']);
		if (!$row->bind($fields))
		{
			Notify::error($row->getError());
			return $this->editTask($row);
		}

		// Store new content
		if (!$row->store(true))
		{
			Notify::error($row->getError());
			return $this->editTask($row);
		}

		// Process tags
		$row->tag(trim(Request::getVar('tags', '')));

		Notify::success(Lang::txt('COM_BLOG_ENTRY_SAVED'));

		if ($this->_task == 'apply')
		{
			return $this->editTask($row);
		}

		// Set the redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false)
		);
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

		// Incoming
		$ids = Request::getVar('id', array());

		if (count($ids) > 0)
		{
			$removed = 0;

			// Loop through all the IDs
			foreach ($ids as $id)
			{
				$entry = new Entry(intval($id));

				// Delete the entry
				if (!$entry->delete())
				{
					Notify::error($entry->getError());
					continue;
				}

				$removed++;
			}
		}

		// Set the redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
			($removed ? Lang::txt('COM_BLOG_ENTRIES_DELETED') : null)
		);
	}

	/**
	 * Sets the state of one or more entries
	 *
	 * @return  void
	 */
	public function stateTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		$state = $this->_task == 'publish' ? 1 : 0;

		// Incoming
		$ids = Request::getVar('id', array(0));
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for a resource
		if (count($ids) < 1)
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
				Lang::txt('COM_BLOG_SELECT_ENTRY_TO', $this->_task),
				'error'
			);
			return;
		}

		// Loop through all the IDs
		$success = 0;
		foreach ($ids as $id)
		{
			// Load the article
			$row = new Entry(intval($id));
			$row->set('state', $state);

			// Store new content
			if (!$row->store())
			{
				Notify::error($row->getError());
				continue;
			}
			$success++;
		}

		switch ($this->_task)
		{
			case 'publish':
				$message = Lang::txt('COM_BLOG_ITEMS_PUBLISHED', $success);
			break;
			case 'unpublish':
				$message = Lang::txt('COM_BLOG_ITEMS_UNPUBLISHED', $success);
			break;
			case 'archive':
				$message = Lang::txt('COM_BLOG_ITEMS_ARCHIVED', $success);
			break;
		}

		// Set the redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
			$message
		);
	}

	/**
	 * Turn comments on/off
	 *
	 * @return  void
	 */
	public function setcommentsTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		// Incoming
		$ids = Request::getVar('id', array(0));
		$ids = (!is_array($ids) ? array($ids) : $ids);
		$state = Request::getInt('state', 0);

		// Check for a resource
		if (count($ids) < 1)
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
				Lang::txt('COM_BLOG_SELECT_ENTRY_TO_COMMENTS', $this->_task),
				'error'
			);
			return;
		}

		// Loop through all the IDs
		foreach ($ids as $id)
		{
			// Load the article
			$row = new Entry(intval($id));
			$row->set('allow_comments', $state);

			// Store new content
			if (!$row->store())
			{
				App::redirect(
					Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
					$row->getError(),
					'error'
				);
				return;
			}
		}

		switch ($state)
		{
			case 1:
				$message = Lang::txt('COM_BLOG_ITEMS_COMMENTS_ENABLED', count($ids));
			break;
			case 0:
			default:
				$message = Lang::txt('COM_BLOG_ITEMS_COMMENTS_DISABLED', count($ids));
			break;
		}

		// Set the redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
			$message
		);
	}
}

