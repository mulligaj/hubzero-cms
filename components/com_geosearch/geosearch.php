<?php
/**
 * @package     hubzero-cms
 * @copyright   Copyright 2005-2012 Purdue University. All rights reserved.
 * @license     http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 * @author	    Brandon Beatty
 *
 * Copyright 2005-2012 Purdue University. All rights reserved.
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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

if (version_compare(JVERSION, '1.6', 'lt'))
{
	$jacl = JFactory::getACL();
	$jacl->addACL($option, 'manage', 'users', 'super administrator');
	$jacl->addACL($option, 'manage', 'users', 'administrator');
	$jacl->addACL($option, 'manage', 'users', 'manager');
}

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'tables' . DS . 'markers.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'tables' . DS . 'tags.php');

require_once( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jobs'.DS.'tables'.DS.'job.php' );
require_once( JPATH_ROOT.DS.'components'.DS.'com_jobs'.DS.'models'.DS.'job.php' );
require_once( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_members'.DS.'tables'.DS.'profile.php' );
require_once( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_resources'.DS.'tables'.DS.'resource.php' );
require_once( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_resources'.DS.'tables'.DS.'type.php' );
require_once( JPATH_ROOT.DS.'components'.DS.'com_events'.DS.'tables'.DS.'event.php' );
require_once( JPATH_ROOT.DS.'components'.DS.'com_events'.DS.'helpers'.DS.'tags.php' );
require_once( JPATH_ROOT.DS.'components'.DS.'com_events'.DS.'helpers'.DS.'date.php' );
require_once( JPATH_ROOT.DS.'components'.DS.'com_members'.DS.'helpers'.DS.'tags.php' );
include_once( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_members'.DS.'tables'.DS.'organizationtype.php' );
require_once( JPATH_ROOT.DS.'components'.DS.'com_resources'.DS.'helpers'.DS.'tags.php' );

$controllerName = JRequest::getCmd('controller', 'map');
if (!file_exists(JPATH_COMPONENT_SITE . DS . 'controllers' . DS . $controllerName . '.php'))
{
	$controllerName = 'map';
}
require_once(JPATH_COMPONENT_SITE . DS . 'controllers' . DS . $controllerName . '.php');
$controllerName = 'GeosearchController' . ucfirst(strtolower($controllerName));

// Instantiate controller
$controller = new $controllerName();
$controller->execute();
$controller->redirect();
