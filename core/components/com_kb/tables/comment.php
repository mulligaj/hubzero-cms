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

use User;
use Lang;
use Date;

/**
 * Table class for knowledge base article comments
 */
class Comment extends \JTable
{
	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database
	 * @return  void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__faq_comments', 'id', $db);
	}

	/**
	 * Validate data
	 *
	 * @return  boolean  True if data is valid
	 */
	public function check()
	{
		$this->content = trim($this->content);
		if ($this->content == '')
		{
			$this->setError(Lang::txt('Your comment must contain text.'));
		}

		$this->entry_id = intval($this->entry_id);
		if (!$this->entry_id)
		{
			$this->setError(Lang::txt('Missing entry ID.'));
		}

		if (!$this->id)
		{
			$this->created    = Date::toSql();
			$this->created_by = User::get('id');
		}

		$this->created_by = intval($this->created_by);
		if (!$this->created_by)
		{
			$this->created_by = User::get('id');
		}

		$this->parent     = intval($this->parent);
		$this->helpful    = intval($this->helpful);
		$this->nothelpful = intval($this->nothelpful);
		$this->anonymous  = intval($this->anonymous);
		$this->state      = intval($this->state);

		if ($this->getError())
		{
			return false;
		}

		return true;
	}

	/**
	 * Load a record and bind to $this
	 *
	 * @param   integer  $entry_id  Entry ID
	 * @param   integer  $user_id   User ID
	 * @return  boolean  True upon success, False if errors
	 */
	public function loadUserComment($entry_id, $user_id)
	{
		return parent::load(array(
			'entry_id'   => $entry_id,
			'created_by' => $user_id
		));
	}

	/**
	 * Get all comments for an entry and parent comment
	 *
	 * @param   integer  $entry_id  Entry ID
	 * @param   integer  $parent    Parent comment
	 * @return  array
	 */
	public function getComments($entry_id=NULL, $parent=NULL)
	{
		if (!$entry_id)
		{
			$entry_id = $this->entry_id;
		}
		if (!$parent)
		{
			$parent = 0;
		}

		if (!User::isGuest())
		{
			$sql  = "SELECT c.*, v.vote FROM $this->_tbl AS c ";
			$sql .= "LEFT JOIN #__faq_helpful_log AS v ON v.object_id=c.id AND v.user_id=" . User::get('id') . " AND v.type='comment' ";
		}
		else
		{
			$sql = "SELECT c.* FROM $this->_tbl AS c ";
		}
		$sql .= "WHERE c.entry_id=" . $this->_db->quote($entry_id) . " AND c.parent=" . $this->_db->quote($parent) . " AND c.state IN (1, 3) ORDER BY created ASC";

		$this->_db->setQuery($sql);
		return $this->_db->loadObjectList();
	}

	/**
	 * Get all comments (and their abuse reports) on an entry
	 *
	 * @param   integer  $entry_id  Entry ID
	 * @return  array
	 */
	public function getAllComments($entry_id=NULL)
	{
		if (!$entry_id)
		{
			$entry_id = $this->entry_id;
		}

		$comments = $this->getComments($entry_id, 0);
		if ($comments)
		{
			foreach ($comments as $key => $row)
			{
				$comments[$key]->replies = $this->getComments($entry_id, $row->id);
				if ($comments[$key]->replies)
				{
					foreach ($comments[$key]->replies as $ky => $rw)
					{
						$comments[$key]->replies[$ky]->replies = $this->getComments($entry_id, $rw->id);
					}
				}
			}
		}
		return $comments;
	}

	/**
	 * Delete all children of a comment
	 *
	 * @param   integer  $id  Comment ID
	 * @return  boolean  True upon success
	 */
	public function deleteChildren($id=NULL)
	{
		if (!$id)
		{
			$id = $this->id;
		}

		$this->_db->setQuery("SELECT id FROM $this->_tbl WHERE parent=" . $this->_db->quote($id));
		$comments = $this->_db->loadObjectList();
		if ($comments)
		{
			foreach ($comments as $row)
			{
				// Delete abuse reports
				/*$this->_db->setQuery("DELETE FROM #__abuse_reports WHERE referenceid=".$row->id." AND category='blog'");
				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}*/

				// Delete children
				$this->_db->setQuery("DELETE FROM $this->_tbl WHERE parent=" . $this->_db->quote($row->id));
				if (!$this->_db->query())
				{
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}

			$this->_db->setQuery("DELETE FROM $this->_tbl WHERE parent=" . $this->_db->quote($id));
			if (!$this->_db->query())
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}
}

