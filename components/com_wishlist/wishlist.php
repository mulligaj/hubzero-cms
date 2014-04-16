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

if (version_compare(JVERSION, '1.6', 'lt'))
{
	$jacl = JFactory::getACL();
	$jacl->addACL('com_wishlist', 'manage', 'users', 'super administrator');
	$jacl->addACL('com_wishlist', 'manage', 'users', 'administrator');
	$jacl->addACL('com_wishlist', 'manage', 'users', 'manager');
}

jimport('joomla.application.component.view');

include_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'tables' . DS . 'wishlist.php');
include_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'tables' . DS . 'wishlist.plan.php');
include_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'tables' . DS . 'wishlist.owner.php');
include_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'tables' . DS . 'wishlist.owner.group.php');
include_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'tables' . DS . 'wish.php');
include_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'tables' . DS . 'wish.rank.php');
include_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'tables' . DS . 'wish.attachment.php');
include_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'economy.php');
require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'html.php');
require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'tags.php');
require_once(JPATH_COMPONENT . DS . 'controllers' . DS . 'wishlist.php');

require_once(JPATH_ROOT . DS . 'components' . DS . 'com_wishlist' . DS . 'models' . DS . 'wish.php');
require_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_resources' . DS . 'tables' . DS . 'resource.php');

// Instantiate controller
$controller = new WishlistController();
$controller->mainframe = JFactory::getApplication();
$controller->execute();
$controller->redirect();

