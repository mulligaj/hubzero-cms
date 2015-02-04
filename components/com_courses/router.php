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
 * Turn querystring parameters into an SEF route
 *
 * @param  array &$query Querystring
 */
function CoursesBuildRoute(&$query)
{
	$segments = array();

	if (!empty($query['controller']))
	{
		if ($query['controller'] == 'certificate')
		{
			$segments[] = $query['controller'];
		}
		unset($query['controller']);
	}

	if (!empty($query['gid']))
	{
		$segments[] = $query['gid'];
		unset($query['gid']);
	}
	if (!empty($query['offering']))
	{
		$segments[] = $query['offering'];
		unset($query['offering']);
	}
	if (!empty($query['active']))
	{
		$segments[] = $query['active'];
		if ($query['active'] == '' && !empty($query['task']))
		{
			$segments[] = $query['task'];
			unset($query['task']);
		}
		unset($query['active']);
	}
	elseif (!empty($query['asset']))
	{
		$segments[] = 'asset';
		$segments[] = $query['asset'];
		unset($query['asset']);
	}
	else
	{
		if ((empty($query['scope']) || $query['scope'] == '') && !empty($query['task']))
		{
			$segments[] = $query['task'];
			unset($query['task']);
		}
	}
	if (!empty($query['unit']))
	{
		$segments[] = $query['unit'];
		unset($query['unit']);
	}
	if (!empty($query['b']))
	{
		$segments[] = $query['b'];
		unset($query['b']);
	}
	if (!empty($query['c']))
	{
		$segments[] = $query['c'];
		unset($query['c']);
	}

	return $segments;
}

/**
 * Parse a SEF route
 *
 * @param  array $segments Exploded route
 * @return array
 */
function CoursesParseRoute($segments)
{
	$vars = array();

	if (empty($segments))
	{
		return $vars;
	}

	if (isset($segments[0]))
	{
		if (in_array($segments[0], array('intro', 'browse', 'badge')))
		{
			$vars['controller'] = 'courses';
			$vars['task'] = $segments[0];

			if ($segments[0] == 'badge' && isset($segments[1]) && is_numeric($segments[1]))
			{
				$vars['badge_id'] = $segments[1];

				if (in_array($segments[2], array('image', 'criteria', 'validation')))
				{
					$vars['action'] = $segments[2];

					if ($segments[2] == 'validation' && isset($segments[3]))
					{
						$vars['validation_token'] = $segments[3];
					}
				}
				return $vars;
			}
		}
		else if ($segments[0] == 'certificate')
		{
			$vars['controller'] = $segments[0];
			if (isset($segments[1]))
			{
				$vars['course'] = $segments[1];
			}
			if (isset($segments[2]))
			{
				$vars['offering'] = $segments[2];
			}
			return $vars;
		}
		else
		{
			if ($segments[0] == 'new')
			{
				$vars['task'] = $segments[0];
			}
			else
			{
				$vars['gid'] = $segments[0];
				$vars['task'] = 'display';
			}
			$vars['controller'] = 'course';
		}
	}

	if (isset($segments[1]))
	{
		$vars['controller'] = 'course';
		switch ($segments[1])
		{
			case 'overview':
			case 'reviews':
			case 'offerings':
			case 'faq':
				$vars['active'] = $segments[1];
			break;

			case 'logo':
			case 'edit':
			case 'newoffering':
			case 'saveoffering':
			case 'deletepage':
				$vars['task'] = $segments[1];
			break;

			case 'instructors':
				$vars['controller'] = 'managers';
			break;

			case 'delete':
			case 'join':
			case 'accept':
			case 'cancel':
			case 'invite':
			case 'customize':
			case 'manage':
				if (isset($segments[2]))
				{
					$vars['task'] = 'editoutline';
					$vars['offering'] = $segments[2];
				}
				$vars['controller'] = 'offering';
				if (isset($segments[3]))
				{
					$vars['task'] = 'manage';
					$vars['controller'] = $segments[3];
				}
				return $vars;
			break;

			case 'editoutline':
			case 'offerings':
			//case 'managemodules':
			case 'ajaxupload':
				$vars['task'] = $segments[1];
				$vars['controller'] = 'media';
			break;

			// Defaults
			default:
				$pagefound = false;
				require_once(JPATH_ROOT . DS . 'components' . DS . 'com_courses' . DS . 'models' . DS . 'course.php');
				$course = CoursesModelCourse::getInstance($vars['gid']);
				if ($course->exists())
				{
					$pages = $course->pages(array('active' => 1));

					foreach ($pages as $page)
					{
						if ($page->get('url') == $segments[1])
						{
							$pagefound = true;
							$vars['active'] = $segments[1];
							break;
						}
					}
				}

				if (!$pagefound)
				{
					$vars['offering'] = $segments[1];
					$vars['controller'] = 'offering';
				}
			break;
		}
	}

	if (isset($segments[2]))
	{
		if ($segments[2] == 'form.index'
			|| $segments[2] == 'form.layout'
			|| $segments[2] == 'form.saveLayout'
			|| $segments[2] == 'form.upload'
			|| $segments[2] == 'form.deploy'
			|| $segments[2] == 'form.showDeployment'
			|| $segments[2] == 'form.complete')
		{
			$vars['controller'] = 'form';
			$vars['task']       = substr($segments[2], 5);
		}
		elseif ($segments[2] == 'asset' && isset($segments[3]) && is_numeric($segments[3]))
		{
			$vars['controller'] = 'offering';
			$vars['task']       = 'asset';
			$vars['asset_id']   = $segments[3];

			if (isset($segments[4]))
			{
				$vars['file'] = $segments[4];
			}
		}
		else if ($vars['controller'] == 'course' && isset($vars['active']))
		{
			$vars['task'] = 'download';
			$vars['file'] = $segments[2];
		}
		else
		{
			if ($segments[2] == 'enroll' || $segments[2] == 'logo')
			{
				$vars['task'] = $segments[2];
			}
			else
			{
				$vars['active'] = $segments[2];
			}
			$vars['controller'] = 'offering';
		}
	}
	if (isset($segments[3]))
	{
		$vars['unit'] = $segments[3];
	}
	if (isset($segments[4]))
	{
		$vars['group'] = $segments[4];
	}
	if (isset($segments[5]))
	{
		$vars['asset'] = $segments[5];
	}
	if (isset($segments[6]))
	{
		$vars['d'] = $segments[6];
	}

	return $vars;
}

