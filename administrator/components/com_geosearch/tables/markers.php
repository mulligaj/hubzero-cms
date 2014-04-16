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
 * later version. Quote all SQL values!
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
defined('_JEXEC') or die('Restricted access');

/**
 * Table class for tags
 */
class GeosearchMarkers extends JTable
{
	/**
	 * int(11)
	 * 
	 * @var integer
	 */
	var $id          = NULL;
		
	/**
	 * int(11)
	 * 
	 * @var integer
	 */
	var $uidNumber          = NULL;
	
	/**
	 * string(100)
	 * 
	 * @var string
	 */
	var $address1       = NULL;
	
	/**
	 * string(100)
	 * 
	 * @var string
	 */
	var $address2       = NULL;
	
	/**
	 * string(100)
	 * 
	 * @var string
	 */
	var $addressCity       = NULL;
	
	/**
	 * string(100)
	 * 
	 * @var string
	 */
	var $addressRegion       = NULL;
	
	/**
	 * string(100)
	 * 
	 * @var string
	 */
	var $addressPostal        = NULL;
	
	/**
	 * string(100)
	 * 
	 * @var string
	 */
	var $addressCountry        = NULL;
	
	/**
	 * string(100)
	 * 
	 * @var string
	 */
	var $addressLatitude        = NULL;

	/**
	 * string(100)
	 * 
	 * @var string
	 */
	var $addressLongitude     = NULL;

	/**
	 * Constructor
	 * 
	 * @param      object &$db JDatabase
	 * @return     void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__geosearch_markers', 'id', $db);
	}

	/**
	 * 
	 * Gets user addresses
	 * 
	 * @param      array user IDs
	 * @return     object
	 */
	public function getAddresses($uids=array())
	{
		$where = "";
		if ($uids != "")
		{
			$i = 0;
			foreach ($uids as $uid) 
			{
				$where .= "a.uidNumber = " . $this->_db->quote($uid);
				$i++;
				if (count($uids) > 1 && $i < count($uids)) 
				{ 
					$where .= " OR "; 
				}
			}
			$where = "AND ($where)";
		}
		// TODO: check for address visible if logged in (:1)
		$login = "AND params NOT LIKE '%\"access_address\":\"2\"%'"; 
		$this->_db->setQuery("SELECT a.* FROM #__xprofiles_address a, #__xprofiles x WHERE (a.uidNumber = x.uidNumber AND x.public = " . $this->_db->quote('1') . " $login) $where");
		return $this->_db->loadObjectList();
	}
	
	/**
	 * 
	 * Address Search 
	 * 
	 * @param      int distance (UI)
	 * @param      array search center lat/lng coords
	 * @param      string scope
	 * @param      string unit label
	 * @return     object
	 */
	public function getAddressLimit($distance=0,$clatlng=array(),$scope='', $unit='mi', $filters=array()) 
	{ 
		// set the table
		if ($scope == 'member') 
		{
			$tbl = "#__xprofiles_address";
			$where = 'HAVING';
		} 
		else 
		{
			$tbl = $this->_tbl;
			$where = "WHERE scope = " . $this->_db->quote($scope) . " HAVING";
		}

		// set the units
		$R = ($unit == 'mi') ? 3956 : 6371;
		$sql = "SELECT *, ($R * ACOS(COS(RADIANS($clatlng[0])) * COS(RADIANS(addressLatitude)) * COS(RADIANS(addressLongitude) - RADIANS($clatlng[1])) + SIN(RADIANS($clatlng[0])) * SIN(RADIANS(addressLatitude)))) 
		AS distance FROM " . $this->_db->nameQuote($tbl) . " $where distance < ".$this->_db->quote($distance)." ORDER BY distance LIMIT {$filters['start']}, {$filters['limit']}";
		$this->_db->setQuery($sql);
		return $this->_db->loadObjectList();
	}
	
	/**
	 * 
	 * Gets Events addresses
	 * 
	 * @param      string year
	 * @param      array event IDs
	 * @return     object
	 */
	public function getEvents($year='',$eids=array())
	{
		// get user object
		$juser = JFactory::getUser();

		$where = "";
		if ($eids != "") 
		{
			$i = 0;
			foreach ($eids as $eid) 
			{
				$where .= "e.id = " . $this->_db->quote($eid);
				$i++;
				if (count($eids) > 1 && $i < count($eids)) 
				{ 
					$where .= " OR "; 
				}
			}
			$where = "AND ($where)";
		}

		$sql = "SELECT e.id, e.scope, e.scope_id, e.adresse_info, m.addressLatitude, m.addressLongitude 
				FROM #__events e LEFT JOIN $this->_tbl m ON (e.id = m.scope_id AND m.scope = " . $this->_db->quote('event') . ")
				WHERE e.publish_up LIKE '" . $this->_db->getEscaped($year) . "%' AND (e.publish_down >= '" . $this->_db->getEscaped($year) . "%' OR e.publish_down = " . $this->_db->quote('0000-00-00 00:00:00') . ")
				AND e.state = " . $this->_db->quote('1') . " AND e.adresse_info NOT LIKE " . $this->_db->quote('%online%') . " $where";
		
		$this->_db->setQuery($sql);
		$events = $this->_db->loadObjectList();
		
		foreach ($events as $k => $event)
		{
			if ($event->scope == 'group')
			{
				$group = \Hubzero\User\Group::getInstance($event->scope_id);
				if (!$group)
				{
					unset($events[$k]);
					continue;
				}

				// only show group events with a
				$access = \Hubzero\User\Group\Helper::getPluginAccess($group, 'calendar');
				if ($access == 'nobody' || $access == 'registered' && $juser->get('guest')
					|| ($access == 'members' && !in_array($juser->get('id'), $group->get('members'))))
				{
					unset($events[$k]);
					continue;
				}
			}
		}
		
		return array_values(array_filter($events));
	}
	
	/**
	 * 
	 * Gets Jobs addresses
	 * 
	 * @param      array job IDs
	 * @return     object
	 */
	public function getJobs($jids=array())
	{
		$where = "";
		if ($jids != "") 
		{
			$i = 0;
			foreach ($jids as $jid) 
			{
				$where .= "j.id = " . $this->_db->quote($jid);
				$i++;
				if (count($jids) > 1 && $i < count($jids)) 
				{ 
					$where .= " OR "; 
				}
			}
			$where = "AND ($where)";
		}

		$sql = "SELECT j.id, j.companyLocation, j.companyLocationCountry, m.addressLatitude, m.addressLongitude FROM #__jobs_openings j 
		LEFT JOIN $this->_tbl m ON (j.id = m.scope_id AND m.scope = " . $this->_db->quote('job') . ") WHERE j.status = " . $this->_db->quote('1') . " $where";
		$this->_db->setQuery($sql);
		return $this->_db->loadObjectList();
	}
	
	/**
	 * 
	 * Check if Organization has marker table entery
	 * 
	 * @param      int org ID
	 * @return     mixed
	 */
	public function checkOrgMarker($id=0)
	{
		$sql = "SELECT * FROM $this->_tbl WHERE scope = " . $this->_db->quote('org') . " AND scope_id = " . $this->_db->quote($id);
		$this->_db->setQuery($sql);
		$this->_db->query();
		if ($this->_db->getNumRows() > 0) 
		{
			return $this->_db->loadRow();
		} 
		else 
		{
			return 0;
		}
	}
	
	/**
	 * 
	 * Gets Organization addresses
	 * 
	 * @param      array resource IDs
	 * @return     object
	 */
	public function getOrgs($oids=array())
	{
		$where = "";
		if ($oids != "") 
		{
			$i = 0;
			foreach ($oids as $oid) 
			{
				$where .= "id = $oid";
				$i++;
				if (count($oids) > 1 && $i < count($oids)) 
				{ 
					$where .= " OR "; 
				}
			}
			$where = "AND ($where)";
		}

		$sql = "SELECT * FROM #__resources WHERE type = " . $this->_db->quote('90') . " $where";
		$this->_db->setQuery($sql);
		return $this->_db->loadObjectList();
	}
	
	/**
	 * 
	 * Update geocoded address
	 * 
	 * @param      int ID
	 * @param      float lat
	 * @param      float lng
	 * @param      string scope
	 * @return     null
	 */
	public function update($id=0,$lat='',$lng='',$scope='')
	{
		switch ($scope) 
		{
			case 'members':
				$tbl = "#__xprofiles_address";
				$where = "uidNumber = $id";
				break;
			case 'event':
			case 'job':
			case 'org':
				$tbl = $this->_tbl;
				$where = "scope_id = $id";
				break;
		}
		
		if ($tbl == $this->_tbl) 
		{
			$sql = "INSERT INTO $tbl (scope, scope_id, addressLatitude, addressLongitude) VALUES (".$this->_db->quote($scope).", ".$this->_db->quote($id).", ".$this->_db->quote($lat).", ".$this->_db->quote($lng).")";
		} 
		else 
		{
			$sql = "UPDATE $tbl SET addressLatitude = ".$this->_db->quote($lat).", addressLongitude = ".$this->_db->quote($lng)." WHERE $where";
		}
		
		$this->_db->setQuery($sql);
		$this->_db->query();
	}
}

