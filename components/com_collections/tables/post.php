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
defined('_JEXEC') or die('Restricted access');

/**
 * Table class for forum posts
 */
class CollectionsTablePost extends JTable
{
	/**
	 * int(11) Primary key
	 * 
	 * @var integer 
	 */
	var $id         = NULL;

	/**
	 * int(11)
	 * 
	 * @var integer 
	 */
	var $collection_id = NULL;

	/**
	 * int(11)
	 * 
	 * @var integer 
	 */
	var $item_id = NULL;

	/**
	 * datetime(0000-00-00 00:00:00)
	 * 
	 * @var string  
	 */
	var $created    = NULL;

	/**
	 * int(11)
	 * 
	 * @var integer 
	 */
	var $created_by = NULL;

	/**
	 * text
	 * 
	 * @var string
	 */
	var $description = NULL;

	/**
	 * tinyint(2)
	 * 
	 * @var integer 
	 */
	var $original = NULL;

	/**
	 * Constructor
	 *
	 * @param      object &$db JDatabase
	 * @return     void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__collections_posts', 'id', $db);
	}

	/**
	 * Load a record by its bulletin and user IDs
	 * 
	 * @param      integer $item_id Bulletin ID
	 * @param      integer $user_id     User ID
	 * @return     boolean True upon success, False if errors
	 */
	public function loadByBoard($collection_id=null, $item_id=null)
	{
		if (!$item_id || !$collection_id) 
		{
			return false;
		}
		$item_id = intval($item_id);
		$collection_id = intval($collection_id);

		$query = "SELECT * FROM $this->_tbl WHERE item_id=" . $this->_db->Quote($item_id) . " AND collection_id=" . $this->_db->Quote($collection_id);

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
	 * Validate data
	 * 
	 * @return     boolean True if data is valid
	 */
	public function check()
	{
		$this->collection_id = intval($this->collection_id);

		if (!$this->collection_id) 
		{
			$this->setError(JText::_('Please provide a collection.'));
			return false;
		}

		$juser = JFactory::getUser();
		if (!$this->id) 
		{
			$this->created = JFactory::getDate()->toSql();
			$this->created_by = $juser->get('id');
		}

		return true;
	}

	/**
	 * Build a query based off of filters passed
	 * 
	 * @param      array $filters Filters to construct query from
	 * @return     string SQL
	 */
	public function buildQuery($filters=array())
	{
		$query  = " FROM $this->_tbl AS p";
		$query .= " INNER JOIN #__collections AS c ON c.id=p.collection_id";
		$query .= " INNER JOIN #__collections_items AS i ON p.item_id=i.id";
		$query .= " LEFT JOIN #__users AS u ON p.created_by=u.id";

		if (isset($filters['user_id']) && $filters['user_id']) 
		{
			$query .= " LEFT JOIN #__collections_votes AS v ON v.item_id=p.item_id AND v.user_id=" . $this->_db->Quote($filters['user_id']);
		}

		$where = array();

		if (isset($filters['collection_id']) && $filters['collection_id']) 
		{
			if (is_array($filters['collection_id']))
			{
				$filters['collection_id'] = array_map('intval', $filters['collection_id']);
				$where[] = "p.collection_id IN (" . implode(',', $filters['collection_id']) . ")";
			}
			else
			{
				$where[] = "p.collection_id=" . $this->_db->Quote($filters['collection_id']);
			}
		}
		if (isset($filters['object_id']) && $filters['object_id']) 
		{
			$where[] = "c.object_id=" . $this->_db->Quote($filters['object_id']);
		}
		if (isset($filters['object_type']) && $filters['object_type']) 
		{
			$where[] = "c.object_type=" . $this->_db->Quote($filters['object_type']);
		}
		if (isset($filters['created_by']) && $filters['created_by']) 
		{
			$where[] = "p.created_by=" . $this->_db->Quote($filters['created_by']);
		}
		if (isset($filters['item_id']) && $filters['item_id']) 
		{
			$where[] = "p.item_id=" . $this->_db->Quote($filters['item_id']);
		}
		if (isset($filters['state']) && $filters['state'] >= 0) 
		{
			$where[] = "i.state=" . $this->_db->Quote($filters['state']);
		}
		if (isset($filters['access']) && $filters['access'] >= 0) 
		{
			$where[] = "i.access=" . $this->_db->Quote($filters['access']);
			$where[] = "c.access=" . $this->_db->Quote($filters['access']);
		}
		if (isset($filters['original'])) 
		{
			$where[] = "p.original=" . $this->_db->Quote($filters['original']);
		}
		if (isset($filters['search']) && $filters['search'] != '') 
		{
			$where[] = "(LOWER(i.title) LIKE '%" . $this->_db->getEscaped(strtolower($filters['search'])) . "%' OR 
						LOWER(i.description) LIKE '%" . $this->_db->getEscaped(strtolower($filters['search'])) . "%' OR 
						LOWER(p.description) LIKE '%" . $this->_db->getEscaped(strtolower($filters['search'])) . "%')";
		}

		if (count($where) > 0)
		{
			$query .= " WHERE ";
			$query .= implode(" AND ", $where);
		}

		if (isset($filters['limit']) && $filters['limit'] != 0) 
		{
			if (!isset($filters['sort']) || !$filters['sort']) 
			{
				$filters['sort'] = 'p.created';
			}
			if (!isset($filters['sort_Dir']) || !$filters['sort_Dir']) 
			{
				$filters['sort_Dir'] = 'DESC';
			}
			$query .= " ORDER BY " . $filters['sort'] . " " . $filters['sort_Dir'];
		}

		return $query;
	}

	/**
	 * Get a record count
	 * 
	 * @param      array $filters Filters to construct query from
	 * @return     integer
	 */
	public function getCount($filters=array())
	{
		$filters['limit'] = 0;

		$query = "SELECT COUNT(*) " . $this->buildQuery($filters);

		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}

	/**
	 * Get records
	 * 
	 * @param      array $filters Filters to construct query from
	 * @return     array
	 */
	public function getRecords($filters=array())
	{
		$query = "SELECT p.*, c.alias, c.title, c.object_type, c.object_id, u.name,
				i.title AS item_title, 
				i.description AS item_description, 
				i.url AS item_url, 
				i.created AS item_created, 
				i.created_by AS item_created_by,
				i.positive AS item_positive, 
				i.access AS item_access, 
				i.negative AS item_negative, 
				i.type AS item_type, 
				i.object_id As item_object_id,
				(SELECT COUNT(*) FROM #__collections_posts AS s WHERE s.item_id=p.item_id AND s.original=0) AS item_reposts,
				(SELECT COUNT(*) FROM #__item_comments AS ct WHERE ct.item_id=p.item_id AND ct.item_type='collection' AND ct.state IN (1, 3)) AS item_comments";
		if (isset($filters['user_id']) && $filters['user_id']) 
		{
			$query .= ", v.id AS item_voted ";
		}
		$query .= $this->buildQuery($filters);

		if ($filters['limit'] != 0) 
		{
			$query .= ' LIMIT ' . intval($filters['start']) . ',' . intval($filters['limit']);
		}

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
}
