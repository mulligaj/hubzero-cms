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
 * @author    Sam Wilson <samwilson@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Time\Helpers;

use Components\Time\Models\Hub;
use Components\Time\Models\Task;

/**
 * Filters helper class for time component
 */
class Filters
{
	/**
	 * Gets the request filters and returns them
	 *
	 * @param  string $namespace the application state variable namespace
	 * @return array
	 **/
	public static function getFilters($namespace)
	{
		// Process query filters
		$q = User::getState("{$namespace}.query");
		if ($incoming = Request::getVar('q', false))
		{
			$q[] = $incoming;
		}

		// Set some defaults for the filters, if not set otherwise
		if (!is_array($q))
		{
			$q[0]['column']   = ($namespace == 'com_time.tasks') ? 'assignee_id' : 'user_id';
			$q[0]['operator'] = 'e';
			$q[0]['value']    = User::get('id');
		}

		// Translate operators and augment query filters with human-friendly text
		$query = self::filtersMap($q);

		// Turn search into array of results, if not already
		$search = Request::getVar('search', User::getState("{$namespace}.search", ''));
		// If we have a search and it's not an array (i.e. it's coming in fresh with this request)
		if ($search && !is_array($search))
		{
			// Explode multiple words into array
			$search = explode(" ", $search);
			// Only allow alphabetical characters for search
			$search = preg_replace("/[^a-zA-Z]/", "", $search);
		}

		// Set some values in the session
		User::setState("{$namespace}.search", $search);
		User::setState("{$namespace}.query",  $query);

		return array('search' => $search, 'q' => $query);
	}

	/**
	 * Gets the column names
	 *
	 * @param  string $table   the table to get column names for
	 * @param  array  $exclude an array of columns to exclude
	 * @return array
	 */
	public static function getColumnNames($table, $exclude=array())
	{
		// Get the column names
		$prefix  = Config::get('dbprefix');
		$db      = App::get('db');
		$cols    = $db->getTableColumns($prefix.$table);
		$columns = array();

		// Loop through them and make a guess at the human readable equivalent
		foreach ($cols as $c => $type)
		{
			if (!in_array($c, $exclude))
			{
				$human = $c;

				// Try to be tricky and remove id from column names
				if (strpos($human, '_id') > 0)
				{
					$human = str_replace('_id', '', $human);
				}

				// Now replace other instances of '_' with spaces
				$human = str_replace('_', ' ', $human);
				$human = ucwords($human);
				$columns[] = array("raw" => $c, "human" => $human);
			}
		}

		return $columns;
	}

	/**
	 * Build the operators html
	 *
	 * @return string $html - html for operators select box
	 */
	public static function buildSelectOperators()
	{
		$html  = '<select name="q[operator]" id="filter-operator">';
		$html .= '<option value="e">equals (&#61;)</option>';
		$html .= '<option value="de">doesn\'t equal (&#8800;)</option>';
		$html .= '<option value="gt">is greater than (&#62;)</option>';
		$html .= '<option value="lt">is less than (&#60;)</option>';
		$html .= '<option value="gte">is greater than or equal to (&#62;&#61;)</option>';
		$html .= '<option value="lte">is less than or equal to (&#60;&#61;)</option>';
		$html .= '<option value="like">is like (LIKE)</option>';
		$html .= '</select>';

		return $html;
	}

	/**
	 * Augment query filters
	 *
	 * Here we're basically modifying what we get from the database,
	 * either by changing the display column (ex: 'user_id' => 'User'),
	 * or by changing the display value (ex: '1' to 'yes', or '1000' to Sam Wilson)
	 *
	 * @param  $q - query arguments
	 * @return void
	 */
	public static function filtersMap($q=array())
	{
		// Initialize variables
		$filters   = [];
		$return    = [];
		$dcolumn   = '';
		$doperator = '';
		$dvalue    = '';

		// First, make sure we have something to iterate over
		if (!empty($q[0]))
		{
			// Go through query filters
			foreach ($q as $val)
			{
				// Make sure we're not deleting this filter
				if (!array_key_exists('delete', $val))
				{
					// Add an if statement here if you want to augment a fields 'human readable' value
					// Augment user_id information
					if ($val['column'] == 'user_id')
					{
						$val['human_column']   = 'User';
						$val['o']              = self::translateOperator($val['operator']);
						$val['human_operator'] = self::mapOperator($val['o']);
						$val['human_value']    = User::getInstance($val['value'])->name;
						$filters[]  = $val;
					}
					// Augment name information for multiple fields
					elseif ($val['column'] == 'assignee_id' || $val['column'] == 'liaison_id')
					{
						$val['human_column']   = ucwords(str_replace('_id', '', $val['column']));
						$val['o']              = self::translateOperator($val['operator']);
						$val['human_operator'] = self::mapOperator($val['o']);
						$val['human_value']    = User::getInstance($val['value'])->name;

						if (is_null($val['human_value']))
						{
							$val['human_value'] = 'Unidentified';
						}
						$filters[]  = $val;
					}
					// Augment task_id information
					elseif ($val['column'] == 'task_id')
					{
						$val['human_column']   = 'Task';
						$val['o']              = self::translateOperator($val['operator']);
						$val['human_operator'] = self::mapOperator($val['o']);
						$val['human_value']    = Task::oneOrFail($val['value'])->name;
						$filters[]  = $val;
					}
					// Augment 'active' column information
					elseif ($val['column'] == 'active')
					{
						$val['human_column']   = ucwords($val['column']);
						$val['o']              = self::translateOperator($val['operator']);
						$val['human_operator'] = self::mapOperator($val['o']);
						$val['human_value']    = ($val['value'] == 1) ? 'yes' : 'no';
						$filters[]  = $val;
					}
					elseif ($val['column'] == 'hub_id')
					{
						$val['human_column']   = 'Hub';
						$val['o']              = self::translateOperator($val['operator']);
						$val['human_operator'] = self::mapOperator($val['o']);
						$val['human_value']    = Hub::oneOrFail($val['value'])->name;
						$filters[]  = $val;
					}
					// All others
					else
					{
						$val['human_column']   = ucwords(str_replace("_", " ", $val['column']));
						$val['o']              = self::translateOperator($val['operator']);
						$val['human_operator'] = self::mapOperator($val['o']);
						$val['human_value']    = $val['value'];
						$filters[]  = $val;
					}
				}
				else // we're establishing the details of the query filter to delete (which we'll do below)
				{
					// Values to delete
					$dcolumn   = $val['column'];
					$doperator = $val['operator'];
					$dvalue    = $val['value'];
				}
			}
		}

		// Distil down the results to only unique filters
		$filters = array_map("unserialize", array_unique(array_map("serialize", $filters)));

		// Now go through them again and only keep ones not marked for deletion (there's probably a much better way to do this)
		foreach ($filters as $filter)
		{
			if (!($filter['column'] == $dcolumn && $filter['operator'] == $doperator && $filter['value'] == $dvalue))
			{
				$return[] = $filter;
			}
		}

		return $return;
	}

	/**
	 * Override default filter values
	 *
	 * ex: change hub_id to Hub
	 *
	 * @param  $vals   - incoming values
	 * @param  $column - incoming column for which values pertain
	 * @return $return - outgoing values
	 */
	public static function filtersOverrides($vals, $column)
	{
		$return = [];

		if ($column == 'task_id')
		{
			$ids   = array_map(function($task) { return $task->val; }, $vals);
			$tasks = Task::whereIn('id', $ids)->rows();
		}

		foreach ($vals as $val)
		{
			// Just so I don't have to keep writing $val->val
			$value = $val->val;

			$x            = array();
			$x['value']   = $value;
			$x['display'] = $value;

			// Now override at will...
			if ($column == 'assignee_id' || $column == 'liaison_id' || $column == 'user_id')
			{
				$x['value']   = $value;
				$x['display'] = User::getInstance($value)->get('name');

				if ($value == 0)
				{
					$x['display'] = 'No User';
				}
			}
			elseif ($column == 'hub_id')
			{
				$x['value']   = $value;
				$x['display'] = Hub::oneOrFail($value)->name;
			}
			elseif ($column == 'task_id')
			{
				$x['value']   = $value;
				$x['display'] = $tasks->seek($value)->name;
			}
			elseif ($column == 'active')
			{
				$x['value']   = $value;
				$x['display'] = ($value) ? 'Yes' : 'No';
			}

			$return[] = $x;
		}

		// Get an array of kays for sorting purposes
		// We do this here, as opposed to in the query, because the data could have been modified at this point by the overrides above
		foreach ($return as $key => $row)
		{
			$display[$key] = $row['display'];
		}

		// Do the sort
		array_multisort($display, SORT_ASC, $return);

		return $return;
	}

	/**
	 * Translate operators from form value to database operator
	 *
	 * @param  $o - operator of interest
	 * @return void
	 */
	private static function translateOperator($o)
	{
		if ($o == 'e')
		{
			return '=';
		}
		elseif ($o == 'de')
		{
			return '!=';
		}
		elseif ($o == 'gt')
		{
			return '>';
		}
		elseif ($o == 'gte')
		{
			return '>=';
		}
		elseif ($o == 'lt')
		{
			return '<';
		}
		elseif ($o == 'lte')
		{
			return '<=';
		}
		elseif ($o == 'like')
		{
			return 'LIKE';
		}
		return $o;
	}

	/**
	 * Map operator symbol to text equivalent (ex: '>' = 'is greater than')
	 *
	 * @param  $o - operator of interest
	 * @return string - value of operator
	 */
	private static function mapOperator($o)
	{
		if ($o == '=')
		{
			return 'is';
		}
		elseif ($o == '!=')
		{
			return 'is not';
		}
		elseif ($o == '>')
		{
			return 'is greater than';
		}
		elseif ($o == '>=')
		{
			return 'is greater than or equal to';
		}
		elseif ($o == '<')
		{
			return 'is less than';
		}
		elseif ($o == '<=')
		{
			return 'is less than or equal to';
		}
		elseif ($o == 'LIKE')
		{
			return 'is like';
		}
		return $o;
	}
}