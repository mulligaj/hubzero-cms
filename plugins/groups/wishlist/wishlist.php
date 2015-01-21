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
 * @author    Alissa Nedossekina <alisa@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Groups Plugin class for wishlist
 */
class plgGroupsWishlist extends \Hubzero\Plugin\Plugin
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
	 * @return     array
	 */
	public function &onGroupAreas()
	{
		$area = array(
			'name' => 'wishlist',
			'title' => JText::_('PLG_GROUPS_WISHLIST'),
			'default_access' => $this->params->get('plugin_access', 'members'),
			'display_menu_tab' => $this->params->get('display_tab', 1),
			'icon' => 'f078'
		);

		return $area;
	}

	/**
	 * Return data on a group view (this will be some form of HTML)
	 *
	 * @param      object  $group      Current group
	 * @param      string  $option     Name of the component
	 * @param      string  $authorized User's authorization level
	 * @param      integer $limit      Number of records to pull
	 * @param      integer $limitstart Start of records to pull
	 * @param      string  $action     Action to perform
	 * @param      array   $access     What can be accessed
	 * @param      array   $areas      Active area(s)
	 * @return     array
	 */
	public function onGroup($group, $option, $authorized, $limit=0, $limitstart=0, $action='', $access, $areas=null)
	{
		$return = 'html';
		$active = 'wishlist';

		// The output array we're returning
		$arr = array(
			'html' => ''
		);

		//get this area details
		$this_area = $this->onGroupAreas();

		// Check if our area is in the array of areas we want to return results for
		if (is_array($areas) && $limit)
		{
			if (!in_array($this_area['name'], $areas))
			{
				$return = 'metadata';
			}
		}

		//Create user object
		$juser = JFactory::getUser();

		//get the group members
		$members = $group->get('members');

		//if we want to return content
		if ($return == 'html')
		{
			//set group members plugin access level
			$group_plugin_acl = $access[$active];

			//if set to nobody make sure cant access
			if ($group_plugin_acl == 'nobody')
			{
				$arr['html'] = '<p class="info">' . JText::sprintf('GROUPS_PLUGIN_OFF', ucfirst($active)) . '</p>';
				return $arr;
			}

			//check if guest and force login if plugin access is registered or members
			if ($juser->get('guest')
			 && ($group_plugin_acl == 'registered' || $group_plugin_acl == 'members'))
			{
				$url = JRoute::_('index.php?option=com_groups&cn=' . $group->get('cn') . '&active=' . $active, false, true);

				$this->redirect(
					JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($url)),
					JText::sprintf('GROUPS_PLUGIN_REGISTERED', ucfirst($active)),
					'warning'
				);
				return;
			}

			//check to see if user is member and plugin access requires members
			if (!in_array($juser->get('id'), $members)
			 && $group_plugin_acl == 'members'
			 && $authorized != 'admin')
			{
				$arr['html'] = '<p class="info">'
					. JText::sprintf('GROUPS_PLUGIN_REQUIRES_MEMBER', ucfirst($active)) . '</p>';
				return $arr;
			}
		}

		//instantiate database
		$database = JFactory::getDBO();

		// Set some variables so other functions have access
		$this->juser = $juser;
		$this->database = $database;
		$this->authorized = $authorized;
		$this->members = $members;
		$this->group = $group;
		$this->option = $option;
		$this->action = $action;

		//include com_wishlist files
		require_once(JPATH_ROOT . DS . 'components' . DS . 'com_wishlist' . DS . 'models' . DS . 'wishlist.php');
		require_once(JPATH_ROOT . DS . 'components' . DS . 'com_wishlist' . DS . 'controllers' . DS . 'wishlist.php');

		// Get the component parameters
		$this->config = JComponentHelper::getParams('com_wishlist');

		$lang = JFactory::getLanguage();
		$lang->load('com_wishlist');

		//set some more vars
		$gid = $this->group->get('gidNumber');
		$cn = $this->group->get('cn');
		$category = 'group';
		$admin = 0;

		// Configure controller
		$controller = new WishlistControllerWishlist();

		// Get filters
		$filters = $controller->getFilters(0);
		$filters['limit'] = $this->params->get('limit');

		// Load some objects
		$obj = new Wishlist($this->database);
		$objWish = new Wish($this->database);
		$objOwner = new WishlistOwner($this->database);

		// Get wishlist id
		$id = $obj->get_wishlistID($gid, $category);

		// Create a new list if necessary
		if (!$id)
		{
			// create private list for group
			if (\Hubzero\User\Group::exists($gid))
			{
				$group = \Hubzero\User\Group::getInstance($gid);
				$id = $obj->createlist($category, $gid, 0, $cn . ' ' . JText::_('PLG_GROUPS_WISHLIST_NAME_GROUP'));
			}
		}

		// get wishlist data
		$wishlist = $obj->get_wishlist($id, $gid, $category);

		//if we dont have a wishlist display error
		if (!$wishlist)
		{
			$arr['html'] = '<p class="error">' . JText::_('PLG_GROUPS_WISHLIST_ERROR_WISHLIST_NOT_FOUND') . '</p>';
			return $arr;
		}

		// Get list owners
		$owners = $objOwner->get_owners($id, $this->config->get('group'), $wishlist);

		//if user is guest and wishlist isnt public
		//if(!$wishlist->public && $juser->get('guest'))
		//{
		//	$arr['html'] = '<p class="warning">' . JText::_('The Group Wishlist is not a publicly viewable list.') . '</p>';
		//	return $arr;
		//}

		// Authorize admins & list owners
		if ($juser->authorize($option, 'manage'))
		{
			$admin = 1;
		}

		//authorized based on wishlist
		if (in_array($juser->get('id'), $owners['individuals']))
		{
			$admin = 2;
		}
		else if (in_array($juser->get('id'), $owners['advisory']))
		{
			$admin = 3;
		}

		//get item count
		$items = $objWish->get_count($id, $filters, $admin);

		$arr['metadata']['count'] = $items;

		if ($return == 'html')
		{
			// Get wishes
			$wishlist->items = $objWish->get_wishes($wishlist->id, $filters, $admin, $juser);

			// HTML output
			// Instantiate a view
			$view = new \Hubzero\Plugin\View(
				array(
					'folder'  => $this->_type,
					'element' => $this->_name,
					'name'    => 'browse'
				)
			);

			// Pass the view some info
			$view->option = $option;
			//$view->owners = $owners;
			$view->group = $this->group;
			$view->juser = $juser;
			$view->wishlist = $wishlist;
			$view->items = $items;
			$view->filters = $filters;
			$view->admin = $admin;
			$view->config = $this->config;
			if ($this->getError())
			{
				foreach ($this->getErrors() as $error)
				{
					$view->setError($error);
				}
			}

			// Return the output
			$arr['html'] = $view->loadTemplate();
		}
		return $arr;
	}

	/**
	 * Return count of items that will be deleted when group is deleted
	 * 
	 * @param      object $group Group being deleted
	 * @return     string
	 */
	public function onGroupDeleteCount($group)
	{
		// include com_wishlist files
		require_once JPATH_ROOT . DS . 'components' . DS . 'com_wishlist' . DS . 'models' . DS . 'wishlist.php';

		// Load some objects
		$database = JFactory::getDBO();
		$wishlist = new Wishlist($database);
		$wish     = new Wish($database);

		// Get wishlist id
		$id = $wishlist->get_wishlistID($group->get('gidNumber'), 'group');

		// no id means no list
		if (!$id)
		{
			return JText::sprintf('PLG_GROUPS_WISHLIST_LOG', 0);
		}

		// get wishes count
		$wishes = $wish->get_count($id, array(
			'filterby' => 'all'
		), 1);

		// return message
		return JText::sprintf('PLG_GROUPS_WISHLIST_LOG', $wishes);
	}

	/**
	 * Delete any associated wishes & lists when group is deleted
	 * 
	 * @param      object $group Group being deleted
	 * @return     string Log of items removed
	 */
	public function onGroupDelete($group)
	{
		// include com_wishlist files
		require_once JPATH_ROOT . DS . 'components' . DS . 'com_wishlist' . DS . 'models' . DS . 'wishlist.php';

		// Load some objects
		$database = JFactory::getDBO();
		$wishlist = new Wishlist($database);
		$wish     = new Wish($database);

		// Get wishlist id
		$id = $wishlist->get_wishlistID($group->get('gidNumber'), 'group');

		// no id means no list
		if (!$id)
		{
			return '';
		}

		// Get wishes
		$wishes = $wish->get_wishes($id, array(
			'filterby' => 'all',
			'sortby'   => ''
		), 1);

		// delete each wish
		foreach ($wishes as $item)
		{
			$wish->load($item->id);
			$wish->delete();
		}

		// delete wishlist
		$wishlist->load($id);
		$wishlist->delete();

		// return message
		return JText::sprintf('PLG_GROUPS_WISHLIST_LOG', count($wishes));
	}
}

