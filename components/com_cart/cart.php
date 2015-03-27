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
 * @author    Ilya Shunko <ishunko@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

//import needed Joomla! libs
jimport('joomla.filesystem.folder');
jimport('joomla.application.component.view');

// enable only if PRO is enabled
$juser = JFactory::getUser();
if(!$juser->proEnabled)
{
    JError::raiseError(404, JText::_('Component is not available at this time'));
}

// require base component controller
require_once(JPATH_ROOT . DS . 'components' . DS . 'com_cart' . DS . 'controllers' . DS . 'component.php');

//build controller path and name
$controllerName = JRequest::getCmd('controller', '');

if (empty($controllerName))
{
	// Load default controller if no controller provided
	$controllerName = 'cart';
}

elseif (!file_exists(JPATH_ROOT . DS . 'components' . DS . 'com_cart' . DS . 'controllers' . DS . $controllerName . '.php'))
{
	JError::raiseError(404, JText::_('Page Not Found'));
}

require_once(JPATH_ROOT . DS . 'components' . DS . 'com_cart' . DS . 'controllers' . DS . $controllerName . '.php');
$controllerName = 'CartController' . ucfirst(strtolower($controllerName));

// Instantiate controller and execute
$controller = new $controllerName();
//$controller->disableDefaultTask();
$controller->execute();
$controller->redirect();

