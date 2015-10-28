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

namespace Hubzero\Bank;

/**
 * Table class for bak transactions
 */
class Transaction extends \JTable
{
	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database
	 * @return  void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__users_transactions', 'id', $db);
	}

	/**
	 * Validate data
	 *
	 * @return  boolean  True if data is valid
	 */
	public function check()
	{
		$this->uid = intval($this->uid);
		if (!$this->uid)
		{
			$this->setError(\Lang::txt('Entry must have a user ID.'));
		}

		$this->type = trim($this->type);
		if (!$this->type)
		{
			$this->setError(\Lang::txt('Entry must have a type (e.g., deposit, withdraw).'));
		}

		$this->category = trim($this->category);
		if (!$this->category)
		{
			$this->setError(\Lang::txt('Entry must have a category.'));
		}

		if ($this->getError())
		{
			return false;
		}

		$this->referenceid = intval($this->referenceid);
		$this->amount      = intval($this->amount);
		$this->balance     = intval($this->balance);

		if (!$this->created)
		{
			$this->created = \Date::toSql();
		}

		return true;
	}

	/**
	 * Get a history of transactions for a user
	 *
	 * @param   integer  $limit  Number of records to return
	 * @param   integer  $uid    User ID
	 * @return  mixed    False if errors, array on success
	 */
	public function history($limit=50, $uid=null)
	{
		if ($uid == null)
		{
			$uid = $this->uid;
		}
		if ($uid == null)
		{
			return false;
		}

		$lmt = "";
		if ($limit > 0)
		{
			$lmt .= " LIMIT " . $limit;
		}
		$this->_db->setQuery("SELECT * FROM $this->_tbl WHERE uid=" . $this->_db->quote($uid) . " ORDER BY created DESC, id DESC" . $lmt);
		return $this->_db->loadObjectList();
	}

	/**
	 * Delete records for a given category, type, and reference combination
	 *
	 * @param   string   $category     Transaction category (royalties, etc)
	 * @param   string   $type         Transaction type (deposit, withdraw, etc)
	 * @param   integer  $referenceid  Reference ID (resource ID, etc)
	 * @return  boolean  False if errors, True on success
	 */
	public function deleteRecords($category=null, $type=null, $referenceid=null)
	{
		if ($referenceid == null)
		{
			$referenceid = $this->referenceid;
		}
		if ($referenceid == null)
		{
			return false;
		}
		if ($type == null)
		{
			$type = $this->type;
		}
		if ($category == null)
		{
			$category = $this->category;
		}

		$query = "DELETE FROM $this->_tbl WHERE category=" . $this->_db->quote($category) . " AND type=" . $this->_db->quote($type) . " AND referenceid=" . $this->_db->quote($referenceid);

		$this->_db->setQuery($query);
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return true;
	}

	/**
	 * Get a list of transactions of a certain type for a reference item and, optionally, user
	 *
	 * @param   string   $category     Transaction category (royalties, etc)
	 * @param   string   $type         Transaction type (deposit, withdraw, etc)
	 * @param   integer  $referenceid  Reference ID (resource ID, etc)
	 * @param   integer  $uid          User ID
	 * @return  mixed    False if errors, array on success
	 */
	public function getTransactions($category=null, $type=null, $referenceid=null, $uid=null)
	{
		if ($referenceid == null)
		{
			$referenceid = $this->referenceid;
		}
		if ($referenceid == null)
		{
			return false;
		}
		if ($type == null)
		{
			$type = $this->type;
		}
		if ($category == null)
		{
			$category = $this->category;
		}
		$query = "SELECT amount, SUM(amount) as sum, count(*) as total FROM $this->_tbl WHERE category=" . $this->_db->quote($category) . " AND type=" . $this->_db->quote($type) . " AND referenceid=" . $this->_db->quote($referenceid);
		if ($uid)
		{
			$query .= " AND uid=" . $this->_db->quote($uid);
		}
		$query .= " GROUP BY referenceid";

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	/**
	 * Get get the transaction amount for a category, type, reference item and, optionally, user
	 *
	 * @param   string   $category     Transaction category (royalties, etc)
	 * @param   string   $type         Transaction type (deposit, withdraw, etc)
	 * @param   integer  $referenceid  Reference ID (resource ID, etc)
	 * @param   integer  $uid          User ID
	 * @return  mixed    False if errors, integer on success
	 */
	public function getAmount($category=null, $type=null, $referenceid=null, $uid=null)
	{
		if ($referenceid == null)
		{
			$referenceid = $this->referenceid;
		}
		if ($referenceid == null)
		{
			return false;
		}
		if ($type == null)
		{
			$type = $this->type;
		}
		if ($category == null)
		{
			$category = $this->category;
		}

		$query = "SELECT amount FROM $this->_tbl WHERE category=" . $this->_db->quote($category) . " AND type=" . $this->_db->quote($type) . " AND referenceid=" . $this->_db->quote($referenceid);
		if ($uid)
		{
			$query .= " AND uid=" . $this->_db->quote($uid);
		}
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}

	/**
	 * Get a point total/average for a combination of category, type, user, etc.
	 *
	 * @param   string   $category     Transaction category (royalties, etc)
	 * @param   string   $type         Transaction type (deposit, withdraw, etc)
	 * @param   integer  $referenceid  Reference ID (resource ID, etc)
	 * @param   integer  $royalty      If getting royalties
	 * @param   string   $action       Action to filter by (asked, answered, misc)
	 * @param   integer  $uid          User ID
	 * @param   integer  $allusers     Get total for all users?
	 * @param   string   $when         Datetime filter
	 * @param   integer  $calc         How total is calculated (record sum, avg, record count)
	 * @return  integer
	 */
	public function getTotals($category=null, $type=null, $referenceid=null, $royalty=0, $action=null, $uid=null, $allusers = 0, $when=null, $calc=0)
	{
		if ($referenceid == null)
		{
			$referenceid = $this->referenceid;
		}
		if ($type == null)
		{
			$type = $this->type;
		}
		if ($category == null)
		{
			$category = $this->category;
		}

		if ($uid == null)
		{
			$uid = \User::get('id');
		}

		$query = "SELECT ";
		if ($calc == 0)
		{
			$query .= " SUM(amount)";
		}
		else if ($calc == 1)
		{
			// average
			$query .= " AVG(amount)";
		}
		else if ($calc == 2)
		{
			// num of transactions
			$query .= " COUNT(*)";
		}
		$query .= " FROM $this->_tbl WHERE type=" . $this->_db->quote($type) . " ";
		if ($category)
		{
			$query .= " AND category=" . $this->_db->quote($category) . " ";
		}
		if ($referenceid)
		{
			$query .= " AND referenceid=" . $this->_db->quote($referenceid);
		}
		if ($royalty)
		{
			$query .= " AND description like 'Royalty payment%' ";
		}
		if ($action == 'asked')
		{
			$query .= " AND description like '%posting question%' ";
		}
		else if ($action == 'answered')
		{
			$query .= " AND (description like '%answering question%' OR description like 'Answer for question%' OR description like 'Answered question%') ";
		}
		else if ($action == 'misc')
		{
			$query .= " AND (description NOT LIKE '%posting question%' AND description NOT LIKE '%answering question%'
							AND description NOT LIKE 'Answer for question%' AND description NOT LIKE 'Answered question%') ";
		}
		if (!$allusers)
		{
			$query .= " AND uid=$uid ";
		}
		if ($when)
		{
			$query .= " AND created LIKE '" . $when . "%' ";
		}

		$this->_db->setQuery($query);
		$total = $this->_db->loadResult();
		return $total ? $total : 0;
	}
}

