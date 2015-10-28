<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Tags\Tables;

use stdClass;
use User;
use Date;
use Lang;

/**
 * Table class for attaching tags to objects
 */
class Object extends \JTable
{
	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database
	 * @return  void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__tags_object', 'id', $db);
	}

	/**
	 * Validate data
	 *
	 * @return  True  if data is valid
	 */
	public function check()
	{
		$this->objectid = intval($this->objectid);
		if (!$this->objectid)
		{
			$this->setError(Lang::txt('Missing scope ID.'));
		}

		$this->tbl = trim($this->tbl);
		if (!$this->tbl)
		{
			$this->setError(Lang::txt('Missing scope.'));
		}

		$this->tagid = intval($this->tagid);
		if (!$this->tagid)
		{
			$this->setError(Lang::txt('Missing tag ID.'));
		}

		if ($this->getError())
		{
			return false;
		}

		$this->label = trim($this->label);

		$this->taggerid = intval($this->taggerid);
		if (!$this->taggerid)
		{
			$this->taggerid = User::get('id');
		}

		if (!$this->id)
		{
			$this->taggedon = Date::toSql();
		}

		return true;
	}

	/**
	 * Load a database row and populate this object with results
	 * Uses unique tag string as identifier
	 *
	 * @param   string   $tbl       Object type
	 * @param   integer  $objectid  Object ID
	 * @param   integer  $tagid     Tag ID
	 * @param   integer  $taggerid  User ID
	 * @return  boolean  True if tag found and loaded
	 */
	public function loadByObjectTag($tbl=null, $objectid=null, $tagid=null, $taggerid=null)
	{
		if ($tbl === null || $objectid === null || $tagid === null)
		{
			return false;
		}

		$query = "SELECT * FROM $this->_tbl
				WHERE `tagid`=" . $this->_db->quote((int) $tagid) . "
				AND `objectid`=" . $this->_db->quote((int) $objectid) . "
				AND `tbl`=" . $this->_db->quote((string) $tbl);
		if ($taggerid > 0)
		{
			$query .= " AND `taggerid`=" . $this->_db->quote((int) $taggerid);
		}
		$query .= " LIMIT 1";

		$this->_db->setQuery($query);
		if ($result = $this->_db->loadAssoc())
		{
			return $this->bind($result);
		}

		$this->setError($this->_db->getErrorMsg());
		return false;
	}

	/**
	 * Delete attachments to a tag
	 *
	 * @param   integer  $tagid     Tag ID
	 * @param   string   $tbl       Object type
	 * @param   integer  $objectid  Object ID
	 * @param   integer  $taggerid  Tagger ID
	 * @param   boolean  $admin     Admin authorization
	 * @return  boolean  True if records removed
	 */
	public function deleteObjects($tagid=null, $tbl=null, $objectid=null, $taggerid=null, $admin=false)
	{
		$tagid = $tagid ?: $this->tagid;

		if (!$tagid)
		{
			$this->setError(Lang::txt('Missing argument.'));
			return false;
		}

		$sql = "DELETE FROM $this->_tbl WHERE tagid=" . $this->_db->quote($tagid);

		$filters = '';
		if ($tbl)
		{
			$filters .= " AND tbl=" . $this->_db->quote($tbl);
		}
		if ($objectid)
		{
			$filters .= " AND objectid=" . $this->_db->quote($objectid);
		}
		if (!$admin)
		{
			$filters .= " AND taggerid=" . $this->_db->quote($taggerid);
		}

		$this->_db->setQuery("SELECT id FROM $this->_tbl WHERE tagid=" . $this->_db->quote($tagid) . " $filters");
		$items = $this->_db->loadColumn();

		$this->_db->setQuery($sql . $filters);
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		else
		{
			require_once(__DIR__ . DS . 'log.php');

			$data = new stdClass;
			$data->tbl      = $tbl;
			$data->objectid = $objectid;
			$data->taggerid = $taggerid;
			$data->tagid    = $tagid;
			$data->entres   = $items;

			$log = new Log($this->_db);
			$log->log($tagid, 'objects_removed', json_encode($data));
		}
		return true;
	}

	/**
	 * Remove all tag references for a given object
	 *
	 * @param   string   $tbl        Object type
	 * @param   integer  $objectid   Object ID
	 * @param   integer  $tagger_id  Tagger ID
	 * @return  boolean  True if records removed
	 */
	public function removeAllTags($tbl=null, $objectid=null, $tagger_id=null)
	{
		$tbl      = $tbl      ?: $this->tbl;
		$objectid = $objectid ?: $this->objectid;

		if (!$tbl || !$objectid)
		{
			$this->setError(Lang::txt('Missing argument.'));
			return false;
		}

		$query = "SELECT id FROM $this->_tbl WHERE `objectid`=" . $this->_db->quote((int) $objectid) . " AND `tbl`=" . $this->_db->quote($tbl);
		if ($tagger_id)
		{
			$query .= " AND `taggerid`=" . $this->_db->quote((int) $taggerid);
		}

		$this->_db->setQuery($query);
		$items = $this->_db->loadColumn();

		$sql = "DELETE FROM $this->_tbl WHERE `tbl`=" . $this->_db->quote($tbl) . " AND `objectid`=" . $this->_db->quote((int) $objectid);
		if ($tagger_id)
		{
			$query .= " AND `taggerid`=" . $this->_db->quote((int) $taggerid);
		}

		$this->_db->setQuery($sql);
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		else
		{
			require_once(__DIR__ . DS . 'log.php');

			$data = new stdClass;
			$data->tbl      = $tbl;
			$data->objectid = $objectid;
			$data->entries  = $items;

			$log = new Log($this->_db);
			$log->log($objectid, 'tags_removed', json_encode($data));
		}
		return true;
	}

	/**
	 * Get a record count for a tag
	 *
	 * @param   integer  $tagid  Tag ID
	 * @return  mixed    Integer if successful, false if not
	 */
	public function getCount($tagid=null)
	{
		$tagid = $tagid ?: $this->tagid;

		if (!$tagid)
		{
			$this->setError(Lang::txt('Missing argument.'));
			return false;
		}

		$this->_db->setQuery("SELECT COUNT(*) FROM $this->_tbl WHERE tagid=" . $this->_db->quote($tagid));
		return $this->_db->loadResult();
	}

	/**
	 * Get all the tags on an object
	 *
	 * @param   integer  $objectid  Object ID
	 * @param   string   $tbl       Object type
	 * @param   integer  $state     Admin authorization
	 * @param   integer  $offset    Where to start pulling records
	 * @param   integer  $limit     Number of records to pull
	 * @return  mixed    Array if successful, false if not
	 */
	public function getTagsOnObject($objectid=null, $tbl=null, $state=0, $offset=0, $limit=10)
	{
		$tbl      = $tbl      ?: $this->tbl;
		$objectid = $objectid ?: $this->objectid;

		if (!$tbl || !$objectid)
		{
			$this->setError(Lang::txt('Missing argument.'));
			return false;
		}

		$sql = "SELECT DISTINCT t.*
				FROM $this->_tbl AS rt
				INNER JOIN `#__tags` AS t ON (rt.tagid = t.id)
				WHERE rt.objectid=" . $this->_db->quote($objectid) . " AND rt.tbl=" . $this->_db->quote($tbl);

		if (isset($this->label))
		{
			$sql .= " AND rt.label=" . $this->_db->quote($this->label);
		}
		switch ($state)
		{
			case 0: $sql .= " AND t.admin=0"; break;
			case 1: $sql .= ""; break;
		}
		$sql .= " ORDER BY t.raw_tag ASC";
		if ($limit > 0)
		{
			$sql .= " LIMIT " . intval($offset) . ", " . intval($limit);
		}

		$this->_db->setQuery($sql);
		return $this->_db->loadAssocList();
	}

	/**
	 * Get a count of tags on an object
	 *
	 * @param   integer  $tagid     Tag ID
	 * @param   integer  $objectid  Object ID
	 * @param   string   $tbl       Object type
	 * @return  mixed    Integer if successful, false if not
	 */
	public function getCountForObject($tagid=null, $objectid=null, $tbl=null)
	{
		$tagid    = $tagid    ?: $this->tagid;
		$tbl      = $tbl      ?: $this->tbl;
		$objectid = $objectid ?: $this->objectid;

		if (!$tagid || !$tbl || !$objectid)
		{
			$this->setError(Lang::txt('Missing argument.'));
			return false;
		}

		$this->_db->setQuery("SELECT COUNT(*) FROM $this->_tbl WHERE tagid=" . $this->_db->quote($tagid) . " AND objectid=" . $this->_db->quote($objectid) . " AND tbl=" . $this->_db->quote($tbl));
		return $this->_db->loadResult();
	}

	/**
	 * Move all references to one tag to another tag
	 *
	 * @param   integer  $oldtagid  ID of tag to be moved
	 * @param   integer  $newtagid  ID of tag to move to
	 * @return  boolean  True if records changed
	 */
	public function moveObjects($oldtagid=null, $newtagid=null)
	{
		$oldtagid = $oldtagid ?: $this->tagid;

		if (!$oldtagid || !$newtagid)
		{
			return false;
		}

		$this->_db->setQuery("SELECT id FROM $this->_tbl WHERE tagid=" . $this->_db->quote($oldtagid));
		$items = $this->_db->loadColumn();

		$this->_db->setQuery("UPDATE $this->_tbl SET tagid=" . $this->_db->quote($newtagid) . " WHERE tagid=" . $this->_db->quote($oldtagid));
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		else
		{
			require_once(__DIR__ . DS . 'log.php');

			$data = new stdClass;
			$data->old_id  = $oldtagid;
			$data->new_id  = $newtagid;
			$data->entries = $items;

			$log = new Log($this->_db);
			$log->log($newtagid, 'objects_moved', json_encode($data));
		}
		return true;
	}

	/**
	 * Copy all tags on an object to another object
	 *
	 * @param   integer  $oldtagid  ID of tag to be copied
	 * @param   integer  $newtagid  ID of tag to copy to
	 * @return  boolean  True if records copied
	 */
	public function copyObjects($oldtagid=null, $newtagid=null)
	{
		$oldtagid = $oldtagid ?: $this->tagid;

		if (!$oldtagid || !$newtagid)
		{
			return false;
		}

		$this->_db->setQuery("SELECT * FROM $this->_tbl WHERE tagid=" . $this->_db->quote($oldtagid));
		if ($rows = $this->_db->loadObjectList())
		{
			$entries = array();
			foreach ($rows as $row)
			{
				$to = new self($this->_db);
				$to->objectid = $row->objectid;
				$to->tagid    = $newtagid;
				$to->strength = $row->strength;
				$to->taggerid = $row->taggerid;
				$to->taggedon = $row->taggedon;
				$to->tbl      = $row->tbl;
				$to->store();

				$entries[] = $row->id;
			}
			require_once(__DIR__ . DS . 'log.php');

			$data = new stdClass;
			$data->old_id  = $oldtagid;
			$data->new_id  = $newtagid;
			$data->entries = $entries;

			$log = new Log($this->_db);
			$log->log($newtagid, 'objects_copied', json_encode($data));
		}
		return true;
	}

	/**
	 * Build a query from filters
	 *
	 * @param   array   $filters  Filters to determien hwo to build query
	 * @return  string  SQL
	 */
	public function _buildQuery($filters)
	{
		$query  = " FROM $this->_tbl AS o INNER JOIN #__tags AS t ON (o.tagid = t.id)";

		$where = array();

		if (isset($filters['objectid']) && (int) $filters['objectid'] > 0)
		{
			$where[] = "o.objectid=" . $this->_db->quote(intval($filters['objectid']));
		}
		if (isset($filters['tbl']) && (string) $filters['tbl'] != '')
		{
			$where[] = "o.tbl=" . $this->_db->quote($filters['tbl']);
		}
		if (isset($filters['tagid']) && (int) $filters['tagid'] > 0)
		{
			$where[] = "o.tagid=" . $this->_db->quote(intval($filters['tagid']));
		}
		if (isset($filters['strength']) && (int) $filters['strength'] >= 0)
		{
			$where[] = "o.strength=" . $this->_db->quote(intval($filters['strength']));
		}
		if (isset($filters['taggerid']) && (int) $filters['taggerid'] > 0)
		{
			$where[] = "o.taggerid=" . $this->_db->quote(intval($filters['taggerid']));
		}

		if (count($where) > 0)
		{
			$query .= " WHERE ";
			$query .= implode(" AND ", $where);
		}

		return $query;
	}

	/**
	 * Get a record count
	 *
	 * @param   array    $filters  Filters to determien hwo to build query
	 * @return  integer
	 */
	public function count($filters=array())
	{
		$this->_db->setQuery("SELECT COUNT(*) " . $this->_buildQuery($filters));
		return $this->_db->loadResult();
	}

	/**
	 * Get records
	 *
	 * @param   array  $filters  Filters to determien hwo to build query
	 * @return  array
	 */
	public function find($filters=array())
	{
		$query = "SELECT *, o.id AS taggedid " . $this->_buildQuery($filters);

		if (!isset($filters['sort']) || $filters['sort'] == '')
		{
			$filters['sort'] = 'taggedon';
		}

		if (isset($filters['sort']) && $filters['sort'] != '')
		{
			if (!isset($filters['sort_Dir']) || !in_array(strtoupper($filters['sort_Dir']), array('ASC', 'DESC')))
			{
				$filters['sort_Dir'] = 'ASC';
			}
			$query .= " ORDER BY " . $filters['sort'] . " " . $filters['sort_Dir'];
		}

		if (isset($filters['limit']) && $filters['limit'] != 0  && $filters['limit'] != 'all')
		{
			if (!isset($filters['start']))
			{
				$filters['start'] = 0;
			}
			$query .= " LIMIT " . $filters['start'] . "," . $filters['limit'];
		}

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	/**
	 * Get a list of object types for a specified tag
	 *
	 * @param   integer  $tagid    Tag ID
	 * @param   array    $filters  Filters to determien hwo to build query
	 * @return  array
	 */
	public function getTblsForTag($tagid, $filters=array())
	{
		if ($tagid)
		{
			$filters['tagid'] = $tagid;
		}

		$query  = "SELECT DISTINCT o.tbl " . $this->_buildQuery($filters);
		$query .= " ORDER BY tbl ASC";

		if (isset($filters['limit']) && $filters['limit'] != 0  && $filters['limit'] != 'all')
		{
			if (!isset($filters['start']))
			{
				$filters['start'] = 0;
			}
			$query .= " LIMIT " . $filters['start'] . "," . $filters['limit'];
		}

		$this->_db->setQuery($query);
		return $this->_db->loadColumn();
	}
}

