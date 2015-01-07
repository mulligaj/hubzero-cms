<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Controller class for wiki pages
 */
class WikiControllerPages extends \Hubzero\Component\AdminController
{
	/**
	 * Execute a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		define('WIKI_SUBPAGE_SEPARATOR', $this->config->get('subpage_separator', '/'));
		define('WIKI_MAX_PAGENAME_LENGTH', $this->config->get('max_pagename_length', 100));

		parent::execute();
	}

	/**
	 * Display all pages in the wiki(s)
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Get configuration
		$app = JFactory::getApplication();
		$config = JFactory::getConfig();

		$this->view->filters = array(
			'authorized' => true
		);
		// Paging
		$this->view->filters['limit']    = $app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.limit',
			'limit',
			$config->getValue('config.list_limit'),
			'int'
		);
		$this->view->filters['start']    = $app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.limitstart',
			'limitstart',
			0,
			'int'
		);
		// Sorting
		$this->view->filters['sort']     = trim($app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.sort',
			'filter_order',
			'id'
		));
		$this->view->filters['sort_Dir'] = trim($app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.sortdir',
			'filter_order_Dir',
			'ASC'
		));
		$this->view->filters['sortby'] = $this->view->filters['sort'] . ' ' . $this->view->filters['sort_Dir'];

		// Filters
		$this->view->filters['search'] = trim($app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.search',
			'search',
			''
		));
		$this->view->filters['namespace'] = trim($app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.namespace',
			'namespace',
			''
		));
		$this->view->filters['group'] = trim($app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.group',
			'group',
			''
		));
		$this->view->filters['state'] = array(0, 1, 2);

		// In case limit has been changed, adjust limitstart accordingly
		$this->view->filters['start'] = ($this->view->filters['limit'] != 0 ? (floor($this->view->filters['start'] / $this->view->filters['limit']) * $this->view->filters['limit']) : 0);

		$p = new WikiModelBook();

		// Get record count
		$this->view->total = $p->pages('count', $this->view->filters);

		// Get records
		$this->view->rows  = $p->pages('list', $this->view->filters);

		$this->view->groups = $p->groups();

		// Initiate paging
		jimport('joomla.html.pagination');
		$this->view->pageNav = new JPagination(
			$this->view->total,
			$this->view->filters['start'],
			$this->view->filters['limit']
		);

		// Set any errors
		foreach ($this->getErrors() as $error)
		{
			$this->view->setError($error);
		}

		// Output the HTML
		$this->view->display();
	}

	/**
	 * Create a new entry
	 *
	 * @return  void
	 */
	public function addTask()
	{
		$this->editTask();
	}

	/**
	 * Edit an entry
	 *
	 * @return  void
	 */
	public function editTask($row = null)
	{
		JRequest::setVar('hidemainmenu', 1);

		if (is_object($row))
		{
			$this->view->row = $row;
		}
		else
		{
			// Incoming
			$id = JRequest::getVar('id', array(0));
			if (is_array($id) && !empty($id))
			{
				$id = $id[0];
			}

			// Load the article
			$this->view->row = new WikiModelPage(intval($id));
		}

		if (!$this->view->row->exists())
		{
			// Creating new
			$this->view->row->set('created_by', $this->juser->get('id'));
		}

		// Set any errors
		foreach ($this->getErrors() as $error)
		{
			$this->view->setError($error);
		}

		// Output the HTML
		$this->view
			->setLayout('edit')
			->display();
	}

	/**
	 * Save changes to an entry and go back to edit form
	 *
	 * @return  void
	 */
	public function applyTask()
	{
		$this->saveTask(false);
	}

	/**
	 * Save changes to an entry
	 *
	 * @param   boolean  $redirect  Redirect (true) or fall through to edit form (false) ?
	 * @return  void
	 */
	public function saveTask($redirect=true)
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Incoming
		$page = JRequest::getVar('page', array(), 'post');
		$page = array_map('trim', $page);

		// Initiate extended database class
		$row = new WikiModelPage(intval($page['id']));
		if (!$row->bind($page))
		{
			$this->addComponentMessage($row->getError(), 'error');
			$this->editTask($row);
			return;
		}

		// Get parameters
		$params = JRequest::getVar('params', array(), 'post');
		if (is_array($params))
		{
			$pparams = new JRegistry($row->get('params'));
			$pparams->loadArray($params);

			$row->set('params', $pparams->toString());
		}

		// Store new content
		if (!$row->store(true))
		{
			$this->addComponentMessage($row->getError(), 'error');
			$this->editTask($row);
			return;
		}

		if (!$row->updateAuthors($page['authors']))
		{
			$this->addComponentMessage($row->getError(), 'error');
			$this->editTask($row);
			return;
		}

		$row->tag($page['tags']);

		if ($redirect)
		{
			// Set the redirect
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
				JText::_('COM_WIKI_PAGE_SAVED')
			);
		}

		JRequest::setVar('id', $row->get('id'));

		$this->editTask($row);
	}

	/**
	 * Remove one or more pages
	 *
	 * @return  void
	 */
	public function removeTask()
	{
		// Incoming
		$ids = JRequest::getVar('id', array(0));
		$ids = (!is_array($ids) ? array($ids) : $ids);

		if (count($ids) <= 0)
		{
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
				JText::_('COM_WIKI_ERROR_MISSING_ID'),
				'warning'
			);
			return;
		}

		$step = JRequest::getInt('step', 1);
		$step = (!$step) ? 1 : $step;

		// What step are we on?
		switch ($step)
		{
			case 1:
				JRequest::setVar('hidemainmenu', 1);

				// Instantiate a new view
				$this->view->ids = $ids;

				// Set any errors
				foreach ($this->getErrors() as $error)
				{
					$this->view->setError($error);
				}

				// Output the HTML
				$this->view->display();
			break;

			case 2:
				// Check for request forgeries
				JRequest::checkToken() or jexit('Invalid Token');

				// Check if they confirmed
				$confirmed = JRequest::getInt('confirm', 0);
				if (!$confirmed)
				{
					// Instantiate a new view
					$this->view->ids = $ids;

					$this->addComponentMessage(JText::_('COM_WIKI_CONFIRM_DELETE'), 'error');

					// Output the HTML
					$this->view->display();
					return;
				}

				if (!empty($ids))
				{
					foreach ($ids as $id)
					{
						// Finally, delete the page itself
						$page = new WikiModelPage(intval($id));
						if (!$page->delete())
						{
							$this->setError($page->getError());
						}
					}
				}

				$this->setRedirect(
					'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
					JText::sprintf('COM_WIKI_PAGES_DELETED', count($ids))
				);
			break;
		}
	}

	/**
	 * Set the access level to public
	 *
	 * @return  void
	 */
	public function accesspublicTask()
	{
		$this->accessTask(0);
	}

	/**
	 * Set the access level to registered users
	 *
	 * @return  void
	 */
	public function accessregisteredTask()
	{
		$this->accessTask(1);
	}

	/**
	 * Set the access level to special
	 *
	 * @return  void
	 */
	public function accessspecialTask()
	{
		$this->accessTask(2);
	}

	/**
	 * Set the access level
	 *
	 * @param   integer  $access  Access value to set
	 * @return  void
	 */
	public function accessTask($access = 0)
	{
		// Check for request forgeries
		JRequest::checkToken('get') or jexit('Invalid Token');

		// Incoming
		$id = JRequest::getInt('id', 0);

		// Make sure we have an ID to work with
		if (!$id)
		{
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
				JText::_('COM_WIKI_ERROR_MISSING_ID'),
				'warning'
			);
			return;
		}

		// Load the article
		$row = new WikiModelPage(intval($id));
		$row->set('access', $access);

		// Check and store the changes
		if (!$row->store())
		{
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
				$row->getError(),
				'error'
			);
			return;
		}

		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=' . $this->_controller
		);
	}

	/**
	 * Reset the page hits
	 *
	 * @return  void
	 */
	public function resethitsTask()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Incoming
		$id = JRequest::getInt('id', 0);

		// Make sure we have an ID to work with
		if (!$id)
		{
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
				JText::_('COM_WIKI_ERROR_MISSING_ID'),
				'warning'
			);
			return;
		}

		// Load and reset the article's hits
		$page = new WikiModelPage(intval($id));
		$page->set('hits', 0);

		if (!$page->store())
		{
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
				$page->getError(),
				'error'
			);
			return;
		}

		// Set the redirect
		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=' . $this->_controller
		);
	}

	/**
	 * Set the state for a page
	 *
	 * @return  void
	 */
	public function stateTask()
	{
		// Check for request forgeries
		JRequest::checkToken('get') or jexit('Invalid Token');

		// Incoming
		$id = JRequest::getInt('id', 0);

		// Make sure we have an ID to work with
		if (!$id)
		{
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
				JText::_('COM_WIKI_ERROR_MISSING_ID'),
				'warning'
			);
			return;
		}

		// Load and reset the article's hits
		$page = new WikiModelPage(intval($id));
		$page->set('state', JRequest::getInt('state', 0));

		if (!$page->store())
		{
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
				$page->getError(),
				'error'
			);
			return;
		}

		// Set the redirect
		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=' . $this->_controller
		);
	}

	/**
	 * Cancels a task and redirects to listing
	 *
	 * @return  void
	 */
	public function cancelTask()
	{
		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=' . $this->_controller
		);
	}
}

