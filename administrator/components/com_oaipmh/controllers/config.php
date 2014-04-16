<?php
/**
 * @package     hubzero-cms
 * @copyright   Copyright 2005-2011 Purdue University. All rights reserved.
 * @license     http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
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
 */
defined('_JEXEC') or die( 'Restricted access' );

/**
 * Controller class for OAIPMH config
 */
class OaipmhControllerConfig extends \Hubzero\Component\AdminController
{
	/**
	 * Display config optins
	 * 
	 * @return     void
	 */
	public function displayTask()
	{
		// get dc specs
		$query = "SELECT id, name, query FROM #__oaipmh_dcspecs ORDER BY id";
		$this->database->setQuery($query);
		$this->view->dcs = $this->database->loadRowList();

		// get query groups
		$query = "SELECT DISTINCT display FROM #__oaipmh_dcspecs";
		$this->database->setQuery($query);
		$this->view->sets = $this->database->loadResultArray();	

		// get last datestamp **
		//$query = "SELECT submitted FROM #__publication_versions WHERE submitted != '0000-00-00 00:00:00' ORDER BY submitted LIMIT 1";
		//$this->database->setQuery($query);
		$this->view->last = null; //$this->database->loadResult();

		// display panel
		$this->view->display();
	}

	/**
	 * Save changes
	 * 
	 * @return     void
	 */
	public function saveTask() 
	{
		// check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// vars
		$queries = JRequest::getVar('queries','','post');
		$qid     = JRequest::getVar('qid','','post');
		$display = JRequest::getVar('display','','post');

		// update specs
		$count = count($queries);
		for ($i=0; $i<=$count-1; $i++) 
		{
			$SQL = "UPDATE #__oaipmh_dcspecs SET query = " . $this->database->Quote($queries[$i]) . " WHERE id = " . $this->database->Quote($qid[$i]);
			$this->database->Execute($SQL);
		}

		// redirect
		$this->setRedirect(
			'index.php?option=' . $this->_option,
			JText::_('Config Saved')
		);
	}

	/**
	 * Add a set
	 * 
	 * @return     void
	 */
	protected function addsetTask() 
	{
		// increment set number, load a fresh one
		$sets = JRequest::getVar('sets', '', 'request');

		$names = array(
			"resource IDs",
			"specify sets",
			"title",
			"creator",
			"subject",
			"date",
			"identifier",
			"description",
			"type",
			"publisher",
			"rights",
			"contributor",
			"relation",
			"format",
			"coverage",
			"language",
			"source"
		);
		for ($i=0; $i<17; $i++) 
		{
			$SQL = "INSERT INTO #__oaipmh_dcspecs (name, query, display) VALUES (" . $this->database->Quote($names[$i]) . ",''," . $this->database->Quote($sets) . ")";
			$this->database->Execute($SQL);
		}
		// redirect
		$this->setRedirect(
			'index.php?option=' . $this->_option,
			JText::_('Group Added')
		);
	}

	/**
	 * Remove a set
	 * 
	 * @return     void
	 */
	public function removesetTask() 
	{
		// remove 1 query set
		$id = JRequest::getVar('id', '', 'request');

		$SQL = "DELETE FROM #__oaipmh_dcspecs WHERE display = " . $this->database->Quote($id) . " LIMIT 17";
		$this->database->Execute($SQL);

		// redirect
		$this->setRedirect(
			'index.php?option=' . $this->_option,
			JText::_('Group Removed')
		);
	}
}