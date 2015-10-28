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
 * Table class for knowledge base categories
 */
class Category extends \JTable
{
	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database
	 * @return  void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__faq_categories', 'id', $db);
	}

	/**
	 * Validate data
	 *
	 * @return  boolean  True if data is valid
	 */
	public function check()
	{
		$this->title = trim($this->title);
		if ($this->title == '')
		{
			$this->setError(Lang::txt('COM_KB_ERROR_EMPTY_TITLE'));
		}

		if (!$this->alias)
		{
			$this->alias = str_replace(' ', '-', strtolower($this->title));
		}
		$this->alias = preg_replace("/[^a-zA-Z0-9\-]/", '', $this->alias);

		$this->id          = intval($this->id);
		$this->description = trim($this->description);
		$this->section     = intval($this->section);
		$this->state       = intval($this->state);

		if ($this->section)
		{
			$this->_db->setQuery("SELECT COUNT(*) FROM `#__faq` WHERE `section`=" . $this->_db->quote($this->section) . " AND `alias`=" . $this->_db->quote($this->alias));
			if ($result = $this->_db->loadResult())
			{
				$this->setError(Lang::txt('COM_KB_ERROR_ALIAS_IN_USE'));
			}
		}

		$this->access = intval($this->access);

		if ($this->getError())
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;
		return 'com_kb.category.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return  string
	 */
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	/**
	 * Get the parent asset id for the record
	 *
	 * @param   JTable   $table  A JTable object for the asset parent.
	 * @param   integer  $id     The id for the asset
	 * @return  integer  The id of the asset's parent
	 */
	protected function _getAssetParentId($table = null, $id = null)
	{
		// Initialise variables.
		$assetId = null;
		$db = $this->getDbo();

		if ($assetId === null)
		{
			// Build the query to get the asset id for the parent category.
			$query = $db->getQuery(true);
			$query->select('id');
			$query->from('#__assets');
			$query->where('name = ' . $db->quote('com_kb'));

			// Get the asset id from the database.
			$db->setQuery($query);
			if ($result = $db->loadResult())
			{
				$assetId = (int) $result;
			}
		}

		// Return the asset id.
		if ($assetId)
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId($table, $id);
		}
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the JTable instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 * @return  boolean  True if successful. False if row not found or on error (internal error state set in that case).
	 */
	public function load($keys = null, $reset = true)
	{
		if (is_string($keys) && !is_numeric($keys))
		{
			return parent::load(array(
				'alias' => $keys
			), $reset);
		}

		return parent::load($keys, $reset);
	}

	/**
	 * Load a record and bind to $this
	 *
	 * @param   string   $oid  Alias
	 * @return  boolean  True upon success, False if errors
	 */
	public function loadAlias($oid=NULL)
	{
		return $this->load($oid);
	}

	/**
	 * Build a query from filters
	 *
	 * @param   array   $filters  Filters to build query from
	 * @return  string  SQL
	 */
	private function _buildQuery($filters=array())
	{
		$query = "FROM `$this->_tbl` AS a LEFT JOIN `#__viewlevels` AS g ON g.`id` = a.`access`";

		$where = array();

		if (isset($filters['section']) && $filters['section'] >= 0)
		{
			$where[] = "a.`section`=" . $this->_db->quote($filters['section']);
		}
		if (isset($filters['state']) && $filters['state'] >= 0)
		{
			$where[] = "a.`state`=" . $this->_db->quote($filters['state']);
		}
		if (isset($filters['access']))
		{
			if (is_array($filters['access']))
			{
				if (!empty($filters['access']))
				{
					$where[] = "a.`access` IN (" . implode(",", $filters['access']) . ")";
				}
			}
			else if ($filters['access'] > 0)
			{
				$where[] = "a.`access`=" . $this->_db->quote($filters['access']);
			}
		}
		if (isset($filters['search']) && $filters['search'])
		{
			$where[] = "(a.`title` LIKE " . $this->_db->quote('%' . $filters['search'] . '%') . " OR a.`description` LIKE " . $this->_db->quote('%' . $filters['search'] . '%') . ")";
		}
		if (isset($filters['empty']) && !$filters['empty'])
		{
			if (isset($filters['section']) && $filters['section'] > 0)
			{
				$where[] = "(SELECT COUNT(*) FROM `#__faq` AS fa WHERE fa.category=a.id) > 0";
			}
			else
			{
				$where[] = "(SELECT COUNT(*) FROM `#__faq` AS fa WHERE fa.section=a.id) > 0";
			}
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
	 */
	public function find($what='', $filters=array())
	{
		$what = strtolower($what);

		switch ($what)
		{
			case 'count':
				$query = "SELECT COUNT(a.id) " . $this->_buildQuery($filters);

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
				// Make sure article count takes access and state into consideration
				$access = '';
				if (isset($filters['state']) && $filters['state'] >= 0)
				{
					$access .= " AND fa.`state`=" . $this->_db->quote($filters['state']);
				}
				if (isset($filters['access']))
				{
					if (is_array($filters['access']))
					{
						if (!empty($filters['access']))
						{
							$where[] = "fa.`access` IN (" . implode(",", $filters['access']) . ")";
						}
					}
					else if ($filters['access'] > 0)
					{
						$where[] = "fa.`access`=" . $this->_db->quote($filters['access']);
					}
				}

				if (isset($filters['section']) && $filters['section'] > 0)
				{
					$query  = "SELECT a.*, g.`title` AS groupname,
						(SELECT COUNT(*) FROM `#__faq` AS fa WHERE fa.category=a.id $access) AS articles,
						(SELECT COUNT(*) FROM `$this->_tbl` AS fc WHERE fc.section=a.id) AS categories ";
				}
				else
				{
					$query  = "SELECT a.*, g.`title` AS groupname,
						(SELECT COUNT(*) FROM `#__faq` AS fa WHERE fa.section=a.id $access) AS articles,
						(SELECT COUNT(*) FROM `$this->_tbl` AS fc WHERE fc.section=a.id) AS categories ";
				}

				$query .= $this->_buildQuery($filters);

				if (isset($filters['sort']) && $filters['sort'])
				{
					if (substr($filters['sort'], 0, 2) != 'a.' && array_key_exists($filters['sort'], $this->getFields()))
					{
						$filters['sort'] = 'a.' . $filters['sort'];
					}
					$filters['sort_Dir'] = (isset($filters['sort_Dir'])) ? $filters['sort_Dir'] : 'DESC';
					$filters['sort_Dir'] = strtoupper($filters['sort_Dir']);
					if (!in_array($filters['sort_Dir'], array('ASC', 'DESC')))
					{
						$filters['sort_Dir'] = 'DESC';
					}

					$query .= " ORDER BY " . $filters['sort'] . " " .  $filters['sort_Dir'];
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

