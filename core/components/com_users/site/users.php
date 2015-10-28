<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.5
 */

defined('_HZEXEC_') or die();

require_once JPATH_COMPONENT . '/helpers/route.php';

\Hubzero\Document\Assets::addComponentStylesheet('com_users');

// Launch the controller.
$controller = JControllerLegacy::getInstance('Users');
$controller->execute(Request::getCmd('task', 'display'));
$controller->redirect();
