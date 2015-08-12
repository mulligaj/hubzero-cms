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
 * Controller class for knowledge base categories
 */
class StorefrontControllerProducts extends \Hubzero\Component\AdminController
{
	/**
	 * Display a list of all categories
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Get configuration
		$config = JFactory::getConfig();
		$app = JFactory::getApplication();

		// Get filters
		$this->view->filters = array(
			'state'  => -1,
			'access' => -1,
			'empty'  => 1
		);
		$this->view->filters['section'] = $app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.id',
			'id',
			0,
			'int'
		);
		if (!$this->view->filters['section'])
		{
			$this->view->filters['section'] = $app->getUserStateFromRequest(
				$this->_option . '.' . $this->_controller . '.cid',
				'cid',
				0,
				'int'
			);
		}
		$this->view->filters['sort'] = trim($app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.sort',
			'filter_order',
			'a.title'
		));
		$this->view->filters['sort_Dir'] = trim($app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.sortdir',
			'filter_order_Dir',
			'ASC'
		));

		// Get paging variables
		$this->view->filters['limit'] = $app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.limit',
			'limit',
			$config->getValue('config.list_limit'),
			'int'
		);
		$this->view->filters['start'] = $app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.limitstart',
			'limitstart',
			0,
			'int'
		);

		$obj = new StorefrontModelArchive();

		// Get record count
		$this->view->total = $obj->products('count', $this->view->filters);

		// Get records
		$this->view->rows  = $obj->products('list', $this->view->filters);

		// Initiate paging
		jimport('joomla.html.pagination');
		$this->view->pageNav = new JPagination(
			$this->view->total,
			$this->view->filters['start'],
			$this->view->filters['limit']
		);

		// Set any errors
		if ($this->getError())
		{
			foreach ($this->getErrors() as $error)
			{
				$this->view->setError($error);
			}
		}

		// Output the HTML
		//print_r($this->view); die;
		$this->view->display();
	}

	/**
	 * Create a new category
	 *
	 * @return  void
	 */
	public function addTask()
	{
		$this->editTask();
	}

	/**
	 * Edit a category
	 *
	 * @return  void
	 */
	public function editTask($row=null)
	{
		JRequest::setVar('hidemainmenu', 1);

		if (is_object($row))
		{
			$this->view->row = $row;
			$this->view->task = 'edit';
		}
		else
		{
			// Incoming
			$id = JRequest::getVar('id', array(0));

			if (is_array($id) && !empty($id))
			{
				$id = $id[0];
			}

			// Load product
			$obj = new StorefrontModelArchive();
			$this->view->row = $obj->product($id);
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
	 * Save a category and come back to the edit form
	 *
	 * @return  void
	 */
	public function applyTask()
	{
		$this->saveTask(false);
	}

	/**
	 * Save a category
	 *
	 * @param   boolean  $redirect  Redirect the page after saving
	 * @return  void
	 */
	public function saveTask($redirect=true)
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Incoming
		$fields   = JRequest::getVar('fields', array(), 'post');

		$obj = new StorefrontModelArchive();

		// Save product
		try {
			$obj->updateProduct($fields['pId'], $fields);
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			// Get the product
			$product = $obj->product($fields['pId']);
			$this->editTask($product);
			return;
		}

		if (!isset($fields['access']))
		{
			//$row->set('access', JRequest::getInt('access', 0, 'post'));
		}

		if ($redirect)
		{
			// Redirect
			$this->setRedirect(
				'index.php?option='.$this->_option . '&controller=' . $this->_controller,
				JText::_('COM_STOREFRONT_PRODUCT_SAVED')
			);
			return;
		}

		// Get the product
		$product = $obj->product($fields['pId']);

		$this->editTask($product);
	}

	/**
	 * Remove an entry
	 *
	 * @return  void
	 */
	public function removeTask()
	{
		// Incoming
		$step = JRequest::getInt('step', 1);
		$step = (!$step) ? 1 : $step;

		// What step are we on?
		switch ($step)
		{
			case 1:
				JRequest::setVar('hidemainmenu', 1);

				// Incoming
				$id = JRequest::getVar('id', array(0));
				if (is_array($id) && !empty($id))
				{
					$id = $id[0];
				}

				$this->view->id = $id;

				// Set any errors
				if ($this->getError())
				{
					$this->view->setError($this->getError());
				}

				// Output the HTML
				$this->view->display();
			break;

			case 2:
				// Check for request forgeries
				JRequest::checkToken() or jexit('Invalid Token');

				// Incoming
				$id = JRequest::getInt('id', 0);

				// Make sure we have an ID to work with
				if (!$id)
				{
					$this->setRedirect(
						'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
						JText::_('COM_KB_NO_ID'),
						'error'
					);
					return;
				}

				$msg = null;
				$typ = null;

				// Delete the category
				$category = new StorefrontModelCategory($id);

				// Check if we're deleting collection and all FAQs or just the collection page
				$category->set('delete_action', JRequest::getVar('action', 'removefaqs'));
				if (!$category->delete())
				{
					$msg = $category->getError();
					$typ = 'error';
				}

				// Set the redirect
				$this->setRedirect(
					'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
					$msg,
					$typ
				);
			break;
		}
	}

	/**
	 * Set the access level of an article to 'public'
	 *
	 * @return     void
	 */
	public function accesspublicTask()
	{
		return $this->accessTask(0);
	}

	/**
	 * Set the access level of an article to 'registered'
	 *
	 * @return     void
	 */
	public function accessregisteredTask()
	{
		return $this->accessTask(1);
	}

	/**
	 * Set the access level of an article to 'special'
	 *
	 * @return     void
	 */
	public function accessspecialTask()
	{
		return $this->accessTask(2);
	}

	/**
	 * Set the access level of an article
	 *
	 * @param      integer $access Access level to set
	 * @return     void
	 */
	public function accessTask($access=0)
	{
		// Incoming
		$id = JRequest::getInt('id', 0);

		// Make sure we have an ID to work with
		if (!$id)
		{
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
				JText::_('COM_KB_NO_ID'),
				'error'
			);
			return;
		}

		// Load the article
		$row = new StorefrontModelCategory($id);
		$row->set('access', $access);

		// Check and store the changes
		if (!$row->store(true))
		{
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
				$row->getError(),
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
	 * Calls stateTask to publish entries
	 *
	 * @return     void
	 */
	public function publishTask()
	{
		$this->stateTask(1);
	}

	/**
	 * Calls stateTask to unpublish entries
	 *
	 * @return     void
	 */
	public function unpublishTask()
	{
		$this->stateTask(0);
	}

	/**
	 * Set the state of an entry
	 *
	 * @param      integer $state State to set
	 * @return     void
	 */
	public function stateTask($state=0)
	{
		// Incoming
		$cid = JRequest::getInt('cid', 0);

		$ids = JRequest::getVar('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
				($state == 1 ? JText::_('COM_KB_SELECT_PUBLISH') : JText::_('COM_KB_SELECT_UNPUBLISH')),
				'error'
			);
			return;
		}

		// Update record(s)
		foreach ($ids as $id)
		{
			// Updating a category
			$row = new StorefrontModelCategory(intval($id));
			$row->set('state', $state);
			$row->store();
		}

		// Set message
		switch ($state)
		{
			case '-1':
				$message = JText::sprintf('COM_KB_ARCHIVED', count($ids));
			break;
			case '1':
				$message = JText::sprintf('COM_KB_PUBLISHED', count($ids));
			break;
			case '0':
				$message = JText::sprintf('COM_KB_UNPUBLISHED', count($ids));
			break;
		}

		// Set the redirect
		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=' . $this->_controller . ($cid ? '&id=' . $cid : ''),
			$message
		);
	}

	/**
	 * Cancel a task (redirects to default task)
	 *
	 * @return     void
	 */
	public function cancelTask()
	{
		// Set the redirect
		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=' . $this->_controller
		);
	}
}

