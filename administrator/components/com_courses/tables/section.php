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
 * @author    Sam Wilson <samwilson@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 *
 * Course section table class
 * 
 */
class CoursesTableSection extends JTable
{
	/**
	 * ID, primary key for course instances table
	 * int(11)
	 * 
	 * @var int(11)
	 */
	var $id = NULL;

	/**
	 * int(11)
	 * 
	 * @var integer
	 */
	var $offering_id = NULL;

	/**
	 * varchar(255)
	 * 
	 * @var string
	 */
	var $alias = NULL;

	/**
	 * varchar(255)
	 * 
	 * @var string
	 */
	var $title = NULL;

	/**
	 * int(11)
	 * 
	 * @var integer
	 */
	var $state = NULL;

	/**
	 * Start date for instance
	 * 
	 * @var string
	 */
	var $start_date = NULL;

	/**
	 * End date for instance
	 * 
	 * @var string
	 */
	var $end_date = NULL;

	/**
	 * Start publishing date
	 * 
	 * @var string
	 */
	var $publish_up = NULL;

	/**
	 * End publishing date
	 * 
	 * @var string
	 */
	var $publish_down = NULL;

	/**
	 * Created date for unit
	 * 
	 * @var string
	 */
	var $created = NULL;

	/**
	 * int(11)
	 * 
	 * @var integer
	 */
	var $created_by = NULL;

	/**
	 * tinyint(2)
	 * 
	 * @var integer
	 */
	var $enrollment = NULL;

	/**
	 * int(11)
	 * 
	 * @var integer
	 */
	var $grade_policy_id = NULL;

	/**
	 * text
	 * 
	 * @var string
	 */
	var $params = NULL;

	/**
	 * Contructor method for JTable class
	 * 
	 * @param  database object
	 * @return void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__courses_offering_sections', 'id', $db);
	}

	/**
	 * Returns a reference to a CoursesTableSection object
	 *
	 * This method must be invoked as:
	 *     $inst = CoursesInstance::getInstance($alias);
	 *
	 * @param      string $pagename The page to load
	 * @param      string $scope    The page scope
	 * @return     object CoursesTableSection
	 */
	public static function getInstance($type, $prefix = 'JTable', $config = array())
	{
		static $instances;

		$alias = $type;
		$offering_id = $prefix;

		if (!isset($instances)) 
		{
			$instances = array();
		}

		if (!isset($instances[$alias . '_' . $offering_id])) 
		{
			$inst = new CoursesTableSection(JFactory::getDBO());
			$inst->load($alias, $offering_id);

			$instances[$alias . '_' . $offering_id] = $inst;
		}

		return $instances[$alias . '_' . $offering_id];
	}

	/**
	 * Load a record and bind to $this
	 * 
	 * @param      string $oid Record alias
	 * @return     boolean True on success
	 */
	public function load($oid=NULL, $offering_id=null)
	{
		if ($oid === NULL) 
		{
			return false;
		}
		if (is_numeric($oid))
		{
			return parent::load($oid);
		}

		if ($oid == '!!default!!')
		{
			$query = "SELECT * FROM $this->_tbl WHERE `is_default`=1 AND `offering_id`=" . $this->_db->Quote(intval($offering_id)) . " LIMIT 1";
		}
		else
		{
			$query = "SELECT * FROM $this->_tbl WHERE `alias`=" . $this->_db->Quote(trim($oid)) . " AND offering_id=" . $this->_db->Quote(intval($offering_id));
		}

		$this->_db->setQuery($query);
		if ($result = $this->_db->loadAssoc()) 
		{
			return $this->bind($result);
		} 
		else 
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
	}

	/**
	 * Override the check function to do a little input cleanup
	 * 
	 * @return return true
	 */
	public function check()
	{
		$this->offering_id = intval($this->offering_id);
		if (!$this->offering_id)
		{
			$this->setError(JText::_('Please provide an offering ID.'));
			return false;
		}

		$this->title = trim($this->title);
		if (!$this->title) 
		{
			$this->setError(JText::_('Please provide a title.'));
			return false;
		}

		if (!$this->alias)
		{
			$this->alias = strtolower($this->title);
		}
		$this->alias = preg_replace("/[^a-zA-Z0-9\-_]/", '', $this->alias);

		$this->_db->setQuery("SELECT id FROM `#__courses_offering_sections` WHERE `offering_id`=" . $this->_db->Quote($this->offering_id) . " AND `alias`=" . $this->_db->Quote($this->alias));
		$id = $this->_db->loadResult();
		if ($id && $id != $this->id)
		{
			$this->setError(JText::sprintf('A section with the alias "%s" already exists for the specified offering.', $this->alias));
			return false;
		}

		if (!$this->id)
		{
			$juser = JFactory::getUser();
			$this->created = JFactory::getDate()->toSql();
			$this->created_by = $juser->get('id');
		}

		return true;
	}

	/**
	 * Build query method
	 * 
	 * @param  array $filters
	 * @return $query database query
	 */
	private function _buildQuery($filters=array())
	{
		$query  = " FROM $this->_tbl AS os";
		$query .= " INNER JOIN #__courses_offerings AS o ON o.id=os.offering_id";

		$where = array();

		if (isset($filters['offering_id']) && $filters['offering_id']) 
		{
			$where[] = "os.offering_id=" . $this->_db->Quote(intval($filters['offering_id']));
		}

		if (isset($filters['state'])) 
		{
			$where[] = "os.state=" . $this->_db->Quote(intval($filters['state']));
		}

		if (isset($filters['enrollment'])) 
		{
			$where[] = "os.enrollment=" . $this->_db->Quote(intval($filters['enrollment']));
		}

		if (isset($filters['search']) && $filters['search']) 
		{
			$where[] = "(LOWER(os.alias) LIKE '%" . $this->_db->getEscaped(strtolower($filters['search'])) . "%' 
					  OR LOWER(os.title) LIKE '%" . $this->_db->getEscaped(strtolower($filters['search'])) . "%')";
		}

		if (isset($filters['available']) && $filters['available']) 
		{
			$now = JFactory::getDate()->toSql();

			$where[] = "(os.publish_up = '0000-00-00 00:00:00' OR os.publish_up <= " . $this->_db->Quote($now) . ")";
			$where[] = "(os.publish_down = '0000-00-00 00:00:00' OR os.publish_down >= " . $this->_db->Quote($now) . ")";
		}

		if (count($where) > 0)
		{
			$query .= " WHERE ";
			$query .= implode(" AND ", $where);
		}

		return $query;
	}

	/**
	 * Get a count of course offerings
	 * 
	 * @param  array $filters
	 * @return object Return course units
	 */
	public function count($filters=array())
	{
		$query  = "SELECT COUNT(os.id)";
		$query .= $this->_buildquery($filters);

		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}

	/**
	 * Get an object list of course units
	 * 
	 * @param  array $filters
	 * @return object Return course units
	 */
	public function find($filters=array())
	{
		$query  = "SELECT os.*";
		$query .= $this->_buildquery($filters);

		if (!isset($filters['sort']) || $filters['sort'] == '') 
		{
			$filters['sort'] = 'os.title';
		}
		if (!isset($filters['sort_Dir']) || !in_array(strtoupper($filters['sort_Dir']), array('ASC', 'DESC'))) 
		{
			$filters['sort_Dir'] = 'ASC';
		}

		$query .= " ORDER BY " . $filters['sort'] . " " . $filters['sort_Dir'];

		if (isset($filters['limit']) && $filters['limit'] != 0) 
		{
			if (!isset($filters['start'])) 
			{
				$filters['start'] = 0;
			}
			$query .= " LIMIT " . (int) $filters['start'] . "," . (int) $filters['limit'];
		}

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
}