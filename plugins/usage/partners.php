<?php
/**
 * @package		HUBzero CMS
 * @author		Shawn Rice <zooley@purdue.edu>
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

//-----------

jimport( 'joomla.plugin.plugin' );
JPlugin::loadLanguage( 'plg_usage_partners' );

//-----------

class plgUsagePartners extends JPlugin
{
	public function plgUsagePartners(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// load plugin parameters
		$this->_plugin = JPluginHelper::getPlugin( 'usage', 'partners' );
		$this->_params = new JParameter( $this->_plugin->params );
	}

	//-----------

	public function onUsageAreas()
	{
		$areas = array(
			'partners' => JText::_('USAGE_PARTNERS')
		);
		return $areas;
	}
	
	//-----------
	
	public function onUsageDisplay( $option, $task, $db, $months, $monthsReverse, $enddate ) 
	{
		// Check if our task is the area we want to return results for
		if ($task) {
			if (!in_array( $task, $this->onUsageAreas() ) 
			 && !in_array( $task, array_keys( $this->onUsageAreas() ) )) {
				return '';
			}
		}
		
		// Set some vars
		$thisyear = date("Y");
		
		$o = UsageHtml::options( $db, $enddate, $thisyear, $monthsReverse, 'check_for_regiondata' );

		// Build HTML
		$html  = UsageHtml::form( $o, $option, $task );
		$html .= UsageHelper::toplist($db, 24, 1, $enddate);
		$html .= UsageHelper::toplist($db, 22, 2, $enddate);
		$html .= UsageHelper::toplist($db, 26, 3, $enddate);
		$html .= UsageHelper::toplist($db, 25, 4, $enddate);
		$html .= UsageHelper::toplist($db, 27, 5, $enddate);
		$html .= UsageHelper::toplist($db, 23, 6, $enddate);
		$html .= UsageHelper::toplist($db, 21, 7, $enddate);
		$html .= UsageHelper::toplist($db, 20, 8, $enddate);

		// Return HTML
		return $html;
	}
}
?>