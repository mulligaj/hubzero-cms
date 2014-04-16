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
 * Display sponsors on a resource page
 */
class plgResourcesGroups extends \Hubzero\Plugin\Plugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 */
	protected $_autoloadLanguage = true;

	/**
	 * Return the alias and name for this category of content
	 * 
	 * @param      object $resource Current resource
	 * @return     array
	 */
	public function &onResourcesSubAreas($resource)
	{
		$areas = array(
			'groups' => JText::_('PLG_RESOURCES_GROUPS')
		);
		return $areas;
	}

	/**
	 * Return data on a resource sub view (this will be some form of HTML)
	 * 
	 * @param      object  $resource Current resource
	 * @param      string  $option    Name of the component
	 * @param      integer $miniview  View style
	 * @return     array
	 */
	public function onResourcesSub($resource, $option, $miniview=0)
	{
		$arr = array(
			'area' => $this->_name,
			'html' => '',
			'metadata' => ''
		);

		if (!$resource->group_owner || substr($resource->group_owner, 0, strlen('app-')) == 'app-')
		{
			return $arr;
		}

		// Get recommendations
		$this->database = JFactory::getDBO();

		// Instantiate a view
		$this->view = new \Hubzero\Plugin\View(
			array(
				'folder'  => 'resources',
				'element' => $this->_name,
				'name'    => 'display'
			)
		);

		$group = \Hubzero\User\Group::getInstance($resource->group_owner);
		if (!$group || !$group->get('gidNumber'))
		{
			return $arr;
		}

		\Hubzero\Document\Assets::addPluginStylesheet('resources', $this->_name);

		if ($miniview) 
		{
			$this->view->setLayout('mini');
		}

		// Pass the view some info
		$this->view->option   = $option;
		$this->view->resource = $resource;
		$this->view->params   = $this->params;
		$this->view->group    = $group;

		if ($this->getError()) 
		{
			$this->view->setError($this->getError());
		}

		// Return the output
		$arr['html'] = $this->view->loadTemplate();

		return $arr;
	}
}

