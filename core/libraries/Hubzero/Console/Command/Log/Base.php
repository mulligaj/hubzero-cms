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

namespace Hubzero\Console\Command\Log;

/**
 * Log base class
 **/
class Base
{
	/**
	 * Fields available in this log and their default visibility
	 *
	 * @var  array
	 **/
	protected static $fields = array();

	/**
	 * If dates/times are present, how are they formatted
	 *
	 * @var  string
	 **/
	protected static $dateFormat = null;

	/**
	 * Check if given field is valid
	 *
	 * @return  bool
	 **/
	public static function isField($field)
	{
		return (array_key_exists($field, static::$fields));
	}

	/**
	 * Get date format string
	 *
	 * @return  string
	 **/
	public static function getDateFormat()
	{
		return static::$dateFormat;
	}

	/**
	 * Get log path
	 *
	 * @return  string
	 **/
	public static function path()
	{
		return '';
	}

	/**
	 * Show current output format
	 *
	 * @param   \Hubzero\Console\Output  $output  The output object
	 * @return  void
	 **/
	public static function format($output)
	{
		$output->addString('The profile log has the following format (');
		$output->addString('* indicates visible field', array('color'=>'blue'));
		$output->addLine('):');

		$i = 0;
		foreach (static::$fields as $field => $status)
		{
			if ($i != 0)
			{
				$output->addString(' ');
			}

			$output->addString('<');

			if ($i < 10)
			{
				$output->addString($i . ':');
			}

			if ($status)
			{
				$output->addString('*' . $field, array('color'=>'blue'));
			}
			else
			{
				$output->addString($field);
			}

			$output->addString('>');
			$i++;
		}

		$output->addSpacer()->addSpacer();
	}

	/**
	 * Toggle field visibility
	 *
	 * @return  string|bool
	 **/
	public static function toggle($field, $status = null)
	{
		// If we're toggling field based on position in array
		if (strlen($field) == 1 && is_numeric($field))
		{
			$i = 0;
			foreach (static::$fields as $f => $s)
			{
				if ($i == $field)
				{
					if (!isset($status))
					{
						$status = ($s) ? false : true;
					}

					static::$fields[$f] = $status;

					// Return textual description of what happened
					return (($status) ? 'Showing ' : 'Hiding ') . $f;
				}
				$i++;
			}
		}
		// All fields either on or off
		else if ($field == 'all')
		{
			foreach (static::$fields as $f => $s)
			{
				static::$fields[$f] = $status;
			}

			return (($status) ? 'Showing ' : 'Hiding ') . 'all fields';
		}
		// Toggling comma-separated list of fields
		else if (strpos($field, ','))
		{
			$fields = explode(',', $field);
			$valid  = array();

			foreach ($fields as $f)
			{
				$f = trim($f);
				if (isset(static::$fields[$f]))
				{
					$valid[] = $f;
					static::$fields[$f] = $status;
				}
			}

			$return = (($status) ? 'Showing ' : 'Hiding ');
			if (empty($valid))
			{
				$return = 'No valid fields provided';
			}
			else
			{
				$return .= implode(', ', $valid);
			}

			return $return;
		}
		// Toggling single field
		else if (isset(static::$fields[$field]))
		{
			static::$fields[$field] = $status;

			return (($status) ? 'Showing ' : 'Hiding ') . $field;
		}
		// Who knows what's going on here!
		else
		{
			return false;
		}
	}

	/**
	 * Parse log line
	 *
	 * @param   string  $line      Log line
	 * @param   object  $output    Output object
	 * @param   array   $settings  Settings to honor
	 * @return  void
	 **/
	public static function parse($line, $output, $settings)
	{
		$bits     = explode(' ', $line, count(static::$fields));
		$index    = 0;
		$i_used   = 0;
		$style    = null;
		$exceeded = false;

		// First loop through and see if any of our thresholds are exceeded
		if (is_array($settings['threshold']))
		{
			foreach (static::$fields as $field => $show)
			{
				if (isset($settings['threshold'][$field]))
				{
					$operator = $settings['threshold'][$field]['operator'];
					$value    = $settings['threshold'][$field]['value'];
					switch ($operator)
					{
						case '=':
							$statement = (trim($bits[$index]) == $value);
							break;

						case '<':
							$statement = (trim($bits[$index]) < $value);
							break;

						case '>':
						default:
							$statement = (trim($bits[$index]) > $value);
							break;
					}

					if ($statement)
					{
						$exceeded[] = $field;
					}
				}

				$index++;
			}
		}

		// Reset index
		$index = 0;

		// Loop through and do actual output
		foreach (static::$fields as $field => $show)
		{
			if ($show)
			{
				if ($exceeded)
				{
					$style = array('color'=>'red');

					if (in_array($field, $exceeded))
					{
						$style = 'error';
					}
				}

				if ($i_used != 0)
				{
					$output->addString(' ');
				}

				$value = trim($bits[$index]);

				// See if we need to change date format
				if (isset(static::$dateFormat))
				{
					$d = \DateTime::createFromFormat(static::$dateFormat, $value);
					if ($d && $d->format(static::$dateFormat) == $value)
					{
						$value = $d->format($settings['dateFormat']);
					}
				}

				if (method_exists(get_called_class(), 'parse' . ucfirst($field)))
				{
					$method = 'parse' . ucfirst($field);
					$value  = static::$method($value);
				}

				$output->addString($value, $style);

				// Increment used count
				$i_used++;
			}

			// Increment total count
			$index++;
		}

		$output->addSpacer();

		// See if the total time exceeds the given threshold
		if ($exceeded && !$settings['noBeep'])
		{
			$output->beep();
		}
	}
}