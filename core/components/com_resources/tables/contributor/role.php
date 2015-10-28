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

namespace Components\Resources\Tables\Contributor;

/**
 * Table class for resource contributor role
 */
class Role extends \JTable
{
	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database
	 * @return  void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__author_roles', 'id', $db);
	}

	/**
	 * Validate data
	 *
	 * @return  boolean  True if data is valid
	 */
	public function check()
	{
		$this->title = trim($this->title);

		if (!$this->title)
		{
			$this->setError(\Lang::txt('Please provide a title.'));
			return false;
		}

		if (!$this->alias)
		{
			$this->alias = str_replace(' ', '-', strtolower($this->title));
		}
		$this->alias = preg_replace("/[^a-zA-Z0-9\-]/", '', $this->alias);

		if (!$this->id)
		{
			$this->created    = \Date::toSql();
			$this->created_by = \User::get('id');
		}
		else
		{
			$this->modified    = \Date::toSql();
			$this->modified_by = \User::get('id');
		}

		return true;
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the JTable instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match. If not set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 * @return  boolean  True if successful. False if row not found or on error (internal error state set in that case).
	 */
	public function load($keys = null, $reset = true)
	{
		if (is_numeric($keys))
		{
			return parent::load($keys);
		}

		return parent::load(array(
			'alias' => $keys
		), $reset);
	}

	/**
	 * Get a record count
	 *
	 * @param   array    $filters  Filters to build query from
	 * @return  integer
	 */
	public function getCount($filters=array())
	{
		$query = "SELECT COUNT(*) " . $this->_buildQuery($filters);

		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}

	/**
	 * Get records
	 *
	 * @param   array  $filters  Filters to build query from
	 * @return  array
	 */
	public function getRecords($filters=array())
	{
		$query  = "SELECT r.* " . $this->_buildQuery($filters);

		if (!isset($filters['sort']) || !$filters['sort'])
		{
			$filters['sort'] = 'title';
		}
		if (!isset($filters['sort_Dir']) || !$filters['sort_Dir'])
		{
			$filters['sort_Dir'] = 'ASC';
		}
		$query .= " ORDER BY " . $filters['sort'] . " " . $filters['sort_Dir'];

		if (isset($filters['limit']) && $filters['limit'] != 0)
		{
			$query .= ' LIMIT ' . (int) $filters['start'] . ',' . (int) $filters['limit'];
		}

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	/**
	 * Build a query from filters
	 *
	 * @param   array   $filters  Filters to build query from
	 * @return  string  SQL
	 */
	protected function _buildQuery($filters=array())
	{
		$query = "FROM `$this->_tbl` AS r";

		$where = array();
		if (isset($filters['state']))
		{
			$where[] = "r.state=" . $this->_db->quote($filters['state']);
		}
		if (isset($filters['search']) && $filters['search'] != '')
		{
			$where[] = "(LOWER(r.title) LIKE " . $this->_db->quote('%' . strtolower($filters['search']) . '%') . "
				OR LOWER(r.alias) LIKE " . $this->_db->quote('%' . strtolower($filters['search']) . '%') . ")";
		}

		if (count($where) > 0)
		{
			$query .= " WHERE " . implode(" AND ", $where);
		}

		return $query;
	}

	/**
	 * Get all the roles associated with a type
	 *
	 * @param   integer  $type_id  Type ID
	 * @return  array
	 */
	public function getRolesForType($type_id=null)
	{
		$type_id = intval($type_id);

		if ($type_id === null)
		{
			$this->setError(\Lang::txt('Missing argument'));
			return false;
		}

		$this->_db->setQuery(
			"SELECT r.id, r.title, r.alias
			FROM `$this->_tbl` AS r
			JOIN `#__author_role_types` AS rt ON r.id=rt.role_id AND rt.type_id=" . $this->_db->quote($type_id) . "
			ORDER BY r.title ASC"
		);
		return $this->_db->loadObjectList();
	}

	/**
	 * Get all the types associated with a role
	 *
	 * @param   integer  $role_id  Role ID
	 * @return  array
	 */
	public function getTypesForRole($role_id=null)
	{
		$role_id = $role_id ?: $this->id;
		$role_id = intval($role_id);

		if (!$role_id)
		{
			$this->setError(\Lang::txt('Missing argument'));
			return false;
		}

		$this->_db->setQuery(
			"SELECT r.id, r.type, r.alias
			FROM `#__resource_types` AS r
			LEFT JOIN `#__author_role_types` AS rt ON r.id=rt.type_id
			WHERE rt.role_id=" . $this->_db->quote($role_id) . "
			ORDER BY r.type ASC"
		);
		return $this->_db->loadObjectList();
	}

	/**
	 * Associated types with a role
	 *
	 * @param   integer  $role_id  Role ID
	 * @param   array    $current  Current types associated
	 * @return  boolean  True on success
	 */
	public function setTypesForRole($role_id=null, $current=null)
	{
		if ($role_id === null)
		{
			$role_id = $this->id;
		}

		include_once(__DIR__ . DS . 'roletype.php');

		$rt = new RoleType($this->_db);

		return $rt->setTypesForRole($role_id, $current);
	}

	/**
	 * Delete a record
	 *
	 * @param   integer  $oid  Record to delete
	 * @return  boolean  True on success
	 */
	public function delete($oid=null)
	{
		if ($oid === null)
		{
			$oid = $this->id;
		}

		include_once(__DIR__ . DS . 'roletype.php');

		$rt = new RoleType($this->_db);
		if (!$rt->deleteForRole($oid))
		{
			$this->setError($rt->getError());
			return false;
		}

		return parent::delete($oid);
	}
}

