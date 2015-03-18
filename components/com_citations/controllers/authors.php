<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 Purdue University. All rights reserved.
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
 * @copyright Copyright 2005-2015 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Manage a citation's author entries
 */
class CitationsControllerAuthors extends \Hubzero\Component\SiteController
{
	/**
	 * Execute a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Incoming member ID
		$id = JRequest::getInt('citation', 0);

		if ($id == 0)
		{
			$this->setError(JText::_('COM_CITATIONS_ERROR_MISSING_CITATION'));
		}

		$this->citation = new CitationsCitation($this->database);
		$this->citation->id = $id;
		if ($id > 0)
		{
			$this->citation->load($id);
		}

		if ($this->citation->id == 0)
		{
			$this->setError(JText::_('COM_CITATIONS_ERROR_INVALID_CITATION'));
		}

		parent::execute();
	}

	/**
	 * Add a user as a manager of a course
	 *
	 * @return  void
	 */
	public function addTask()
	{
		// Check for request forgeries
		JRequest::checkToken('get') or JRequest::checkToken() or jexit('Invalid Token');

		if ($this->getError())
		{
			return $this->displayTask();
		}

		// Incoming host
		$m = JRequest::getVar('author', '');

		$mbrs = explode(',', $m);
		$mbrs = array_map('trim', $mbrs);

		foreach ($mbrs as $mbr)
		{
			$user = null;
			if (!strstr($mbr, ' '))
			{
				$user = \Hubzero\User\Profile::getInstance($mbr);
			}

			// Make sure the user exists
			if (!is_object($user) || !$user->get('username'))
			{
				$user = new \Hubzero\User\Profile();
				$user->set('name', $mbr);
			}

			$author = new CitationsAuthor($this->database);
			$author->cid          = $this->citation->id;
			$author->author       = $user->get('name');
			$author->uidNumber    = $user->get('uidNumber');
			$author->organization = $user->get('organization');
			$author->givenName    = $user->get('givenName');
			$author->middleName   = $user->get('middleName');
			$author->surname      = $user->get('surname');
			$author->email        = $user->get('email');

			if (!$author->check())
			{
				$this->setError($author->getError());
				continue;
			}
			if (!$author->store())
			{
				$this->setError($author->getError());
				continue;
			}
		}

		// Push through to the view
		$this->displayTask();
	}

	/**
	 * Update author ordering
	 *
	 * @return  void
	 */
	public function updateTask()
	{
		// Check for request forgeries
		JRequest::checkToken('get') or JRequest::checkToken() or jexit('Invalid Token');

		if ($this->getError())
		{
			return $this->displayTask();
		}

		$author = new CitationsAuthor($this->database);

		$mbrs = JRequest::getVar('author', array());
		$mbrs = (!is_array($mbrs) ? array($mbrs) : $mbrs);

		$users = array();
		foreach ($mbrs as $i => $mbr)
		{
			$author = new CitationsAuthor($this->database);
			$author->load(intval($mbr));
			$author->ordering = ($i + 1);

			if (!$author->store())
			{
				$this->setError(JText::_('COM_CITATIONS_ERROR_UNABLE_TO_UPDATE') . ' ' . $mbr);
			}
		}

		// Push through to the view
		$this->displayTask();
	}

	/**
	 * Remove one or more authors from a citation
	 *
	 * @return  void
	 */
	public function removeTask()
	{
		// Check for request forgeries
		JRequest::checkToken('get') or JRequest::checkToken() or jexit('Invalid Token');

		if ($this->getError())
		{
			return $this->displayTask();
		}

		$author = new CitationsAuthor($this->database);

		$mbrs = JRequest::getVar('author', array());
		$mbrs = (!is_array($mbrs) ? array($mbrs) : $mbrs);

		$users = array();
		foreach ($mbrs as $mbr)
		{
			if (!$author->delete(intval($mbr)))
			{
				$this->setError(JText::_('COM_CITATIONS_ERROR_UNABLE_TO_REMOVE') . ' ' . $mbr);
			}
		}

		// Push through to the view
		$this->displayTask();
	}

	/**
	 * Display a list of authors for a citation
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		foreach ($this->getErrors() as $error)
		{
			$this->view->setError($error);
		}

		// Output the HTML
		$this->view
			->set('citation', $this->citation)
			->setLayout('display')
			->display();
	}
}

