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
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Table class for resource audience level
 */
class ResourceMediaTrackingDetailed extends JTable
{
	var $id                          = NULL;
	var $user_id                     = NULL;
	var $session_id                  = NULL;
	var $ip_address                  = NULL;
	var $object_id                   = NULL;
	var $object_type                 = NULL;
	var $object_duration             = NULL;
	var $current_position            = NULL;
	var $farthest_position           = NULL;
	var $current_position_timestamp  = NULL;
	var $farthest_position_timestamp = NULL;
	var $completed                   = NULL;

	//-----

	public function __construct(&$db)
	{
		parent::__construct('#__media_tracking_detailed', 'id', $db);
	}

	//-----

	public function loadByDetailId( $id )
	{
		//start sequel
		$sql  = "SELECT m.* FROM $this->_tbl AS m WHERE id=" . $this->_db->quote( $id );
		$this->_db->setQuery( $sql );
		return $this->_db->loadObject();
	}
}