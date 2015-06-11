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
defined('_JEXEC') or die( 'Restricted access' );

$juri = JURI::getInstance();
$jconfig = JFactory::getConfig();
$db = JFactory::getDBO();

$creator = JUser::getInstance($this->resource->created_by);

$type = new ResourcesType($db);
$type->load($this->resource->type);

$link = rtrim($juri->base(), '/') . '/' . ltrim(JRoute::_('index.php?option=com_resources&id=' . $this->resource->id), '/');

$message  = JText::_('COM_RESOURCES_NEW_SUBMISSION') . "\n";
$message .= '----------------------------' . "\n";
$message .= strtoupper(JText::_('COM_RESOURCES_ID')) . ' #' . $this->resource->id . "\n";
$message .= strtoupper(JText::_('COM_RESOURCES_TYPE')) . ': ' . $type->type . "\n";
$message .= strtoupper(JText::_('COM_RESOURCES_CREATED')) . ': ' . $this->resource->created . "\n";
$message .= strtoupper(JText::_('COM_RESOURCES_CREATOR')) . ': ' . $creator->get('name') . "\n";
$message .= '----------------------------' . "\n\n";
$message .= strtoupper(JText::_('COM_RESOURCES_TITLE')) . ': ' . $this->resource->title . "\n\n";
$message .= $this->resource->introtext . "\n\n";
$message .= 'To view the submission and take actions, go to: ' . "\n";
$message .= $link . "\n";

echo $message;
