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
 * @author    Alissa Nedossekina <alisa@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Answers\Tables;

use Date;
use Lang;
use User;

/**
 * Table class for answers response
 */
class Response extends \JTable
{
	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database
	 * @return  void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__answers_responses', 'id', $db);
	}

	/**
	 * Validate data
	 *
	 * @return  boolean  True if data is valid
	 */
	public function check()
	{
		$this->question_id = intval($this->question_id);
		if (!$this->question_id)
		{
			$this->setError(Lang::txt('Missing question ID.'));
		}

		$this->answer = trim($this->answer);
		if ($this->answer == '')
		{
			$this->setError(Lang::txt('Your response must contain text.'));
		}

		if ($this->getError())
		{
			return false;
		}

		//$this->answer     = nl2br($this->answer);
		$this->helpful    = intval($this->helpful);
		$this->nothelpful = intval($this->nothelpful);
		$this->state      = intval($this->state);

		$this->anonymous  = intval($this->anonymous);
		if ($this->anonymous > 1)
		{
			$this->anonymous = 1;
		}

		$this->created    = $this->created    ?: Date::toSql();
		$this->created_by = $this->created_by ?: User::get('id');

		return true;
	}

	/**
	 * Get records based on filters
	 *
	 * @param   array  $filters  Filters to build query from
	 * @return  array
	 */
	public function getRecords($filters=array())
	{
		include_once(PATH_CORE . DS . 'components' . DS . 'com_support' . DS . 'tables' . DS . 'reportabuse.php');
		$ab = new \Components\Support\Tables\ReportAbuse($this->_db);

		if (isset($filters['question_id']))
		{
			$qid = $filters['question_id'];
		}
		else
		{
			$qid = $this->question_id;
		}
		if ($qid == null)
		{
			return false;
		}

		if (!User::isGuest())
		{
			$query  = "SELECT r.*";
			$query .= ", (SELECT COUNT(*) FROM $ab->_tbl AS a WHERE a.category='answers' AND a.state=0 AND a.referenceid=r.id) AS reports";
			$query .= ", l.helpful AS vote FROM $this->_tbl AS r LEFT JOIN #__answers_log AS l ON r.id=l.response_id AND ip=" . $this->_db->quote($filters['ip']) . " WHERE r.state!=2 AND r.question_id=" . $this->_db->quote($qid);
		}
		else
		{
			$query  = "SELECT r.*";
			$query .= ", (SELECT COUNT(*) FROM $ab->_tbl AS a WHERE a.category='answers' AND a.state=0 AND a.referenceid=r.id) AS reports";
			$query .= " FROM $this->_tbl AS r WHERE r.state!=2 AND r.question_id=" . $this->_db->quote($qid);
		}
		$query .= " ORDER BY r.state DESC, r.created DESC";

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	/**
	 * Get all users and their votes for responses on a question
	 *
	 * @param   integer  $qid  Question ID
	 * @return  mixed    False on error, array on success
	 */
	public function getActions($qid=null)
	{
		$qid = $qid ?: $this->question_id;

		if ($qid == null)
		{
			return false;
		}

		$query = "SELECT id, helpful, nothelpful, state, created_by FROM `$this->_tbl` WHERE question_id=" . $this->_db->quote($qid) . " AND state!='2'";

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	/**
	 * Load a response with vote information
	 *
	 * @param      integer $id Record ID
	 * @param      string  $ip IP address
	 * @return     mixed False on error, array on success
	 */
	public function getResponse($id=null, $ip = null)
	{
		$id = $id ?: $this->id;
		$ip = $ip ?: $this->ip;

		if ($id == null || $ip == null)
		{
			return false;
		}

		$query  = "SELECT r.*, l.helpful AS vote FROM $this->_tbl AS r LEFT JOIN `#__answers_log` AS l ON r.id=l.response_id AND ip=" . $this->_db->quote($ip) . " WHERE r.state!=2 AND r.id=" . $this->_db->quote($id);

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	/**
	 * Set a response to "deleted"
	 *
	 * @param   integer  $id  Record ID
	 * @return  boolean  True on success
	 */
	public function deleteResponse($id=null)
	{
		$id = $id ?: $this->id;

		if ($id == null)
		{
			return false;
		}

		$query  = "UPDATE `$this->_tbl` SET state=" . $this->_db->quote(2) . " WHERE id=" . $this->_db->quote($id);

		$this->_db->setQuery($query);
		$this->_db->query();
		return true;
	}

	/**
	 * Get the response IDs for a question
	 *
	 * @param   integer  $qid  Question ID
	 * @return  mixed    False if error, array on success
	 */
	public function getIds($qid=null)
	{
		$qid = $qid ?: $this->question_id;

		if ($qid == null)
		{
			return false;
		}

		$this->_db->setQuery("SELECT id FROM `$this->_tbl` WHERE question_id=" . $this->_db->quote($qid));
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
		$query = "FROM `$this->_tbl` AS m LEFT JOIN #__users AS u ON m.created_by=u.id";

		$where = array();

		if (isset($filters['filterby']))
		{
			switch ($filters['filterby'])
			{
				case 'all':
					$where[] = "(m.state=1 OR m.state=0)";
				break;
				case 'accepted':
					$where[] = "m.state=1";
				break;
				case 'rejected':
				default:
					$where[] = "m.state=0";
				break;
			}
		}
		else
		{
			if (isset($filters['state']))
			{
				if (is_array($filters['state']))
				{
					$filters['state'] = array_map('intval', $filters['state']);
					$where[] = "m.state IN (" . implode(',', $filters['state']) . ")";
				}
				else if ($filters['state'] >= 0)
				{
					$where[] = "m.state=" . $this->_db->quote($filters['state']);
				}
			}
		}

		if (isset($filters['question_id']) && $filters['question_id'] > 0)
		{
			$where[] = "m.question_id=" . $this->_db->quote($filters['question_id']);
		}

		if (count($where) > 0)
		{
			$query .= " WHERE " . implode(" AND ", $where);
		}

		return $query;
	}

	/**
	 * Get a count of, single entry, or list of entries
	 * 
	 * @param   string   $rtrn     Data to return
	 * @param   array    $filters  Filters to apply to data retrieval
	 * @param   array    $select   List of fields to select
	 * @return  mixed
	 * @since   1.3.1
	 */
	public function find($what='', $filters=array(), $select=array())
	{
		$what = strtolower($what);
		$select = (array) $select;

		switch ($what)
		{
			case 'count':
				$query = "SELECT COUNT(*) " . $this->_buildQuery($filters);

				$this->_db->setQuery($query);
				return $this->_db->loadResult();
			break;

			case 'one':
				$filters['limit'] = 1;

				$result = null;
				if ($results = $this->find('list', $filters))
				{
					$result = $results[0];
				}

				return $result;
			break;

			case 'first':
				$filters['start'] = 0;

				return $this->find('one', $filters);
			break;

			case 'all':
				if (isset($filters['limit']))
				{
					unset($filters['limit']);
				}
				return $this->find('list', $filters);
			break;

			case 'list':
			default:
				if (empty($select))
				{
					$select = array(
						'm.*',
						'u.name'
					);
				}

				$query  = "SELECT " . implode(', ', $select) . " " . $this->_buildQuery($filters);

				if (isset($filters['sortby']) && $filters['sortby'] != '')
				{
					$query .= " ORDER BY " . $filters['sortby'];
				}
				else
				{
					if (!isset($filters['sort']))
					{
						$filters['sort'] = 'created';
					}
					if (!isset($filters['sort_Dir']))
					{
						$filters['sort_Dir'] = 'ASC';
					}
					$filters['sort_Dir'] = strtoupper($filters['sort_Dir']);
					if (!in_array($filters['sort_Dir'], array('ASC', 'DESC')))
					{
						$filters['sort_Dir'] = 'ASC';
					}
					if (isset($filters['sort']))
					{
						$query .= " ORDER BY " . $filters['sort'] . " " .  $filters['sort_Dir'];
					}
				}

				if (isset($filters['limit']) && $filters['limit'] > 0)
				{
					$filters['start'] = (isset($filters['start']) ? $filters['start'] : 0);

					$query .= " LIMIT " . (int) $filters['start'] . "," . (int) $filters['limit'];
				}

				$this->_db->setQuery($query);
				return $this->_db->loadObjectList();
			break;
		}
	}
}

