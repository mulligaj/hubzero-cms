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

namespace Components\Kb\Tables;

use Lang;

/**
 * Table class for knowledge base votes
 */
class Vote extends \JTable
{
	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database
	 * @return  void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__faq_helpful_log', 'id', $db);
	}

	/**
	 * Validate data
	 *
	 * @return  boolean  True if data is valid
	 */
	public function check()
	{
		$this->object_id = intval($this->object_id);
		if (!$this->object_id)
		{
			$this->setError(Lang::txt('COM_KB_ERROR_MISSING_ARTICLE_ID'));
		}

		$this->type = strtolower(trim($this->type));
		if (!in_array($this->type, array('entry', 'comment')))
		{
			$this->setError(Lang::txt('COM_KB_ERROR_UNKNOWN_TYPE'));
		}

		if ($this->getError())
		{
			return false;
		}

		return true;
	}

	/**
	 * Get the vote for a specific object/type combination and user
	 *
	 * @param   integer  $object_id  Object ID
	 * @param   integer  $user_id    User ID
	 * @param   string   $ip         IP Address
	 * @param   string   $type       Object type (article, comment)
	 * @return  string
	 */
	public function getVote($object_id=NULL, $user_id=NULL, $ip=NULL, $type=NULL)
	{
		$object_id = $object_id ?: $this->object_id;
		$user_id   = $user_id   ?: $this->user_id;
		$ip        = $ip        ?: $this->ip;
		$type      = $type      ?: $this->type;

		$this->_db->setQuery(
			"SELECT vote FROM `$this->_tbl` 
			WHERE object_id=" . $this->_db->quote($object_id) . " 
			AND (user_id=" . $this->_db->quote($user_id) . " OR ip=" . $this->_db->quote($ip) . ")
			AND type=" . $this->_db->quote($type)
		);
		return $this->_db->loadResult();
	}

	/**
	 * Delete a record for a specific object/user combination
	 *
	 * @param      integer $object_id Object ID
	 * @param      integer $user_id   User ID
	 * @return     boolean True upon success
	 */
	public function deleteVote($object_id=NULL, $user_id=NULL, $ip=NULL, $type=NULL)
	{
		$object_id = $object_id ?: $this->object_id;
		$user_id   = $user_id   ?: $this->user_id;
		$ip        = $ip        ?: $this->ip;
		$type      = $type      ?: $this->type;

		$sql  = "DELETE FROM $this->_tbl WHERE object_id=" . $this->_db->quote($object_id) . " AND type=" . $this->_db->quote($type);
		if ($user_id || $ip)
		{
			$sql .= " AND (user_id=" . $this->_db->quote($user_id) . " OR ip=" . $this->_db->quote($ip) . ")";
		}

		$this->_db->setQuery($sql);
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}
}

