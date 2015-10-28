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
 * @author    Alissa Nedossekina <alisa@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Answers\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\Answers\Models\Question;
use Components\Answers\Models\Response;
use Components\Answers\Tables;
use Exception;
use Request;
use Notify;
use Config;
use Route;
use Lang;
use App;

/**
 * Controller class for question responses
 */
class Answers extends AdminController
{
	/**
	 * Execute a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->banking = \Component::params('com_members')->get('bankAccounts');

		$this->registerTask('add', 'edit');
		$this->registerTask('apply', 'save');
		$this->registerTask('reject', 'accept');

		parent::execute();
	}

	/**
	 * Display all responses for a given question
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Filters
		$this->view->filters = array(
			'filterby' => Request::getState(
				$this->_option . '.' . $this->_controller . '.filterby',
				'filterby',
				'all'
			),
			'question_id' => Request::getState(
				$this->_option . '.' . $this->_controller . '.qid',
				'qid',
				0,
				'int'
			),
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
			// Sorting
			'sortby' => '',
			'sort' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sort',
				'filter_order',
				'created'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sortdir',
				'filter_order_Dir',
				'DESC'
			)
		);

		$this->view->question = new Question($this->view->filters['question_id']);

		$ar = new Tables\Response($this->database);

		// Get a record count
		$this->view->total   = $ar->find('count', $this->view->filters);

		// Get records
		$this->view->results = $ar->find('list', $this->view->filters);

		// Did we get any results?
		if ($this->view->results)
		{
			foreach ($this->view->results as $key => $result)
			{
				$this->view->results[$key] = new Response($result);
			}
		}

		// Output the HTML
		$this->view->display();
	}

	/**
	 * Displays a question response for editing
	 *
	 * @param   object  $row
	 * @return  void
	 */
	public function editTask($row=null)
	{
		Request::setVar('hidemainmenu', 1);

		// Incoming
		$qid = Request::getInt('qid', 0);

		if (!is_object($row))
		{
			$id = Request::getVar('id', array(0));
			$id = (is_array($id) && !empty($id)) ? $id[0] : $id;

			$row = new Response($id);
		}

		$qid = $qid ?: $row->get('question_id');

		$this->view->set('question', new Question($qid));

		// Output the HTML
		$this->view
			->set('row', $row)
			->setLayout('edit')
			->display();
	}

	/**
	 * Save a response
	 *
	 * @return  void
	 */
	public function saveTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$answer = Request::getVar('answer', array(), 'post', 'none', 2);

		// Initiate extended database class
		$row = new Response(intval($answer['id']));
		if (!$row->bind($answer))
		{
			Notify::error($row->getError());
			return $this->editTask($row);
		}

		// Code cleaner
		$row->set('state', (isset($answer['state']) ? 1 : 0));
		$row->set('anonymous', (isset($answer['anonymous']) ? 1 : 0));

		// Store content
		if (!$row->store(true))
		{
			Notify::error($row->getError());
			return $this->editTask($row);
		}

		Notify::success(Lang::txt('COM_ANSWERS_ANSWER_SAVED'));

		if ($this->getTask() == 'apply')
		{
			return $this->editTask($row);
		}

		// Redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false)
		);
	}

	/**
	 * Removes one or more entries and associated data
	 *
	 * @return  void
	 */
	public function removeTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$ids = Request::getVar('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Do we have any IDs?
		if (count($ids) > 0)
		{
			// Loop through each ID
			foreach ($ids as $id)
			{
				$ar = new Response(intval($id));
				if (!$ar->delete())
				{
					throw new Exception($ar->getError(), 500);
				}
			}
		}

		// Redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller . '&qid=' . Request::getInt('qid', 0), false)
		);
	}

	/**
	 * Mark an entry as "accepted" and unmark any previously accepted entry
	 *
	 * @return  void
	 */
	public function acceptTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		// Incoming
		$qid = Request::getInt('qid', 0);
		$id  = Request::getVar('id', array(0));

		if (!is_array($id))
		{
			$id = array($id);
		}

		$publish = ($this->getTask() == 'accept') ? 1 : 0;

		// Check for an ID
		if (count($id) < 1)
		{
			$action = ($publish == 1) ? 'accept' : 'reject';

			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
				Lang::txt('COM_ANSWERS_ERROR_SELECT_ANSWER_TO', $action),
				'error'
			);
			return;
		}
		else if (count($id) > 1)
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
				Lang::txt('COM_ANSWERS_ERROR_ONLY_ONE_ACCEPTED_ANSWER'),
				'error'
			);
			return;
		}

		$ar = new Response($id[0]);
		if (!$ar->exists())
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false)
			);
			return;
		}

		if ($publish == 1)
		{
			// Unmark all other entries
			$tbl = new Tables\Response($this->database);
			if ($results = $tbl->find('list', array('question_id' => $ar->get('question_id'))))
			{
				foreach ($results as $result)
				{
					$result = new Response($result);
					if ($result->get('state') != 0 && $result->get('state') != 1)
					{
						continue;
					}
					$result->set('state', 0);
					$result->store(false);
				}
			}
		}

		// Mark this entry
		$ar->set('state', $publish);
		if (!$ar->store(false))
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
				$ar->getError(),
				'error'
			);
			return;
		}

		// Set message
		if ($publish == '1')
		{
			$message = Lang::txt('COM_ANSWERS_ANSWER_ACCEPTED');
		}
		else if ($publish == '0')
		{
			$message = Lang::txt('COM_ANSWERS_ANSWER_REJECTED');
		}

		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
			$message
		);
	}

	/**
	 * Reset the vote count for an entry
	 *
	 * @return  void
	 */
	public function resetTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$answer = Request::getVar('answer', array());

		// Reset some values
		$model = new Response(intval($answer['id']));

		if (!$model->reset())
		{
			throw new Exception($ar->getError(), 500);
		}

		// Redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
			Lang::txt('COM_ANSWERS_VOTE_LOG_RESET')
		);
	}
}

