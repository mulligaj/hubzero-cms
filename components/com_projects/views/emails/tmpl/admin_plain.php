<?php
/**
 * @package		HUBzero CMS
 * @author		Alissa Nedossekina <alisa@purdue.edu>
 * @copyright	Copyright 2005-2009 by Purdue Research Foundation, West Lafayette, IN 47906
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 *
 * Copyright 2005-2009 by Purdue Research Foundation, West Lafayette, IN 47906.
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License,
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$juri 	 = JURI::getInstance();
$jconfig = JFactory::getConfig();
$base 	 = rtrim($juri->base(), DS);
if (substr($base, -13) == 'administrator')
{
	$base 		= substr($base, 0, strlen($base)-13);
	$sef 		= 'projects/' . $this->project->alias;
	$sef_browse = 'projects/browse';
}
else
{
	$sef 		= JRoute::_('index.php?option=' . $this->option . '&alias=' . $this->project->alias);
	$sef_browse = JRoute::_('index.php?option=' . $this->option . a . 'task=browse');
}

$link = rtrim($base, DS) . DS . trim($sef, DS);
$browseLink = rtrim($base, DS) . DS . trim($sef_browse, DS);

$message  = JText::_('COM_PROJECTS_EMAIL_ADMIN_NOTIFICATION') ."\n";
$message .= '-------------------------------' ."\n";
$message .= JText::_('COM_PROJECTS_PROJECT') . ': ' . $this->project->title . ' (' . $this->project->alias . ')' . "\n";
$message .= ucfirst(JText::_('COM_PROJECTS_CREATED')) . ' '
		 . JHTML::_('date', $this->project->created, 'M d, Y') . ' '
		 . JText::_('COM_PROJECTS_BY') . ' ';
$message .= $this->project->owned_by_group
			? $this->nativegroup->cn . ' ' . JText::_('COM_PROJECTS_GROUP')
			: $this->project->fullname;
$message .= "\n";

if ($this->project->private == 0)
{
	$message .= JText::_('COM_PROJECTS_EMAIL_URL') . ': ' . $link . "\n";
}
$message .= '-------------------------------' ."\n\n";

if ($this->config->get('restricted_data', 0) && $this->reviewer == 'sensitive')
{
	$message .= JText::_('COM_PROJECTS_EMAIL_HIPAA') . ': ' . $this->params->get('hipaa_data') ."\n";
	$message .= JText::_('COM_PROJECTS_EMAIL_FERPA') . ': ' . $this->params->get('ferpa_data') ."\n";
	$message .= JText::_('COM_PROJECTS_EMAIL_EXPORT') . ': ' . $this->params->get('export_data') ."\n";
	if ($this->params->get('followup'))
	{
		$message .= JText::_('COM_PROJECTS_EMAIL_FOLLOWUP_NEEDED') . ': ' . $this->params->get('followup') ."\n";
	}
	$message .= '-------------------------------' ."\n\n";
}
if ($this->config->get('grantinfo', 0) && $this->reviewer == 'sponsored')
{
	$message .= JText::_('COM_PROJECTS_EMAIL_GRANT_TITLE') . ': ' . $this->params->get('grant_title') ."\n";
	$message .= JText::_('COM_PROJECTS_EMAIL_GRANT_PI') . ': ' . $this->params->get('grant_PI') ."\n";
	$message .= JText::_('COM_PROJECTS_EMAIL_GRANT_AGENCY') . ': ' . $this->params->get('grant_agency') ."\n";
	$message .= JText::_('COM_PROJECTS_EMAIL_GRANT_BUDGET') . ': ' . $this->params->get('grant_budget') ."\n";
	$message .= '-------------------------------' ."\n\n";
}

// Append a message
if ($this->message)
{
	$message .= $this->message ."\n";
}

if ($this->config->get('ginfo_group', 0) && $this->reviewer == 'sponsored')
{
	$message .= '-------------------------------' ."\n\n";
	$message .= JText::_('COM_PROJECTS_EMAIL_LINK_SPS') ."\n";
	$message .= $browseLink . '?reviewer=sponsored' . "\n\n";
}

if ($this->config->get('sdata_group', 0) && $this->reviewer == 'sensitive')
{
	$message .= '-------------------------------' ."\n\n";
	$message .= JText::_('COM_PROJECTS_EMAIL_LINK_HIPAA') ."\n";
	$message .= $juri->base() . $browseLink . '?reviewer=sensitive' . "\n";
}

$message = str_replace('<br />', '', $message);
$message = preg_replace('/\n{3,}/', "\n\n", $message);

echo $message;

?>
