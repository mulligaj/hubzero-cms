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
 * @author    Alissa Nedossekina <alisa@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

/**
 * Projects build route
 *
 * @param  array &$query
 * @return array Return
 */
function ProjectsBuildRoute(&$query)
{
	$segments = array();
	$scope = 0;

	if (!empty($query['controller']))
	{
		$segments[] = $query['controller'];
		unset($query['controller']);
	}
	if (!empty($query['alias']))
	{
		$segments[] = $query['alias'];
		unset($query['alias']);
	}
	if (!empty($query['id']))
	{
		$segments[] = $query['id'];
		unset($query['id']);
	}
	if (!empty($query['task']))
	{
		if (empty($query['scope']))
		{
			$segments[] = $query['task'];
			unset($query['task']);
		}
	}
	if (!empty($query['active']))
	{
		$segments[] = $query['active'];
		unset($query['active']);
	}
	if (!empty($query['pid']))
	{
		$segments[] = $query['pid'];
		unset($query['pid']);
	}
	// Publications
	if (!empty($query['section']))
	{
		$segments[] = $query['section'];
		unset($query['section']);
	}
	if (!empty($query['move']))
	{
		$segments[] = $query['move'];
		unset($query['move']);
	}
	if (!empty($query['step']))
	{
		$segments[] = $query['step'];
		unset($query['step']);
	}
	if (!empty($query['tool']))
	{
		$segments[] = $query['tool'];
		unset($query['tool']);
	}
	if (!empty($query['scope']))
	{
		// For wiki routing
		$segments = array();
		$scope = 1;
		$parts = explode ( '/', $query['scope'] );
		if (count($parts) >= 3)
		{
			$segments[] = $parts[1]; // alias
			$segments[] = 'notes'; // active

			for ( $i = 3; $i < count($parts); $i++ )
			{
				$segments[] = $parts[$i]; // inlcude parent page names
			}
		}
		unset($query['scope']);
	}
	if (!empty($query['pagename']))
	{
		$segments[] = $query['pagename'];
		unset($query['pagename']);
	}
	if (!empty($query['action']))
	{
		$segments[] = $query['action'];
		unset($query['action']);
	}
	elseif ($scope == 1)
	{
		$segments[] = !empty($query['task']) ? $query['task'] : 'view'; // wiki action
		if (!empty($query['task']))
		{
			unset($query['task']);
		}
	}
	if (!empty($query['media']))
	{
		$segments[] = $query['media'];
		unset($query['media']);
	}

	return $segments;
}

/**
 * Projects parse route
 *
 * @param  array $segments
 * @return array Return
 */
function ProjectsParseRoute($segments)
{
	$vars  = array();

	// General project tasks
	$tasks = array(	'start', 'setup', 'edit',
		'browse', 'intro', 'features', 'auth',
		'delete', 'fixownership','stats'
	);

	// Valid tasks
	$mediaTasks = array( 'img', 'deleteimg', 'upload', 'media', 'thumb' );

	if (empty($segments[0]))
	{
		return $vars;
	}

	// Id?
	if (is_numeric($segments[0]))
	{
		$vars['id'] = $segments[0];

		if (empty($segments[1]))
		{
			$vars['task'] = 'view';
			return $vars;
		}
	}

	// Alias?
	if (!is_numeric($segments[0]))
	{
		if ($segments[0] == 'get')
		{
			$vars['controller'] = 'get';
			return $vars;
		}
		if ($segments[0] == 'reports')
		{
			$vars['controller'] = 'reports';
			if (!empty($segments[1]))
			{
				$vars['task'] = $segments[1];
			}
			return $vars;
		}
		elseif (in_array($segments[0], $tasks))
		{
			$vars['task'] = $segments[0];
			return $vars;
		}
		elseif ($segments[0] == 'media')
		{
			$vars['task'] = 'media';
			$vars['controller'] = 'media';
			if (!empty($segments[1]))
			{
				$vars['alias']  = $segments[1];
			}
			if (!empty($segments[2]))
			{
				$vars['media']  = $segments[2];
			}

			return $vars;
		}
		else
		{
			$vars['alias']  = $segments[0];
		}
	}
	if (empty($segments[1]))
	{
		$vars['task'] = 'view';
		return $vars;
	}

	if (!empty($segments[1]))
	{
		if ($segments[1] == 'view')
		{
			$vars['task'] = $segments[1];
			if (!empty($segments[2]))
			{
				$vars['active'] = $segments[2];
			}
			return $vars;
		}
		elseif (in_array($segments[1], $tasks))
		{
			$vars['task'] = $segments[1];
			return $vars;
		}
		elseif (in_array($segments[1], $mediaTasks))
		{
			$vars['controller'] = 'media';
			$vars['task'] = $segments[1];
			if (!empty($segments[2]))
			{
				$vars['media'] = $segments[2];
			}

			return $vars;
		}
		else
		{
			$vars['active'] = $segments[1];
			$vars['task'] = 'view';

			// Publications
			if (!empty($segments[2]) && $vars['active'] == 'publications')
			{
				if (is_numeric($segments[2]))
				{
					$vars['pid'] = $segments[2];
					$blocks = array();

					if (is_file(JPATH_ROOT . DS . 'administrator' . DS . 'components'
						. DS . 'com_publications' . DS . 'tables' . DS . 'block.php'))
					{
						include_once(JPATH_ROOT . DS . 'administrator' . DS . 'components'
							. DS . 'com_publications' . DS . 'tables' . DS . 'block.php');
						$database = JFactory::getDBO();

						$b = new PublicationBlock($database);
						$blocks = $b->getBlocks('block');
					}

					if (!empty($segments[3]) && in_array($segments[3], $blocks))
					{
						$vars['section'] = $segments[3];

						if (!empty($segments[4]) && $segments[4] == 'continue')
						{
							$vars['move'] = $segments[4];

							if (!empty($segments[5]))
							{
								if (is_numeric($segments[5]))
								{
									$vars['step'] = $segments[5];

									if (!empty($segments[6]))
									{
										$vars['action'] = $segments[6];
									}
								}
								else
								{
									$vars['action'] = $segments[5];
								}
							}
						}
						elseif (!empty($segments[4]))
						{
							if (is_numeric($segments[4]))
							{
								$vars['step'] = $segments[4];

								if (!empty($segments[5]))
								{
									$vars['action'] = $segments[5];
								}
							}
							else
							{
								$vars['action'] = $segments[4];
							}
						}
					}
				}
				else
				{
					$vars['action'] = $segments[2];
				}
				return $vars;
			}

			// Apps
			if (!empty($segments[2]) && $vars['active'] == 'tools')
			{
				// App actions
				$appActions = array('status', 'history', 'wiki', 'browse',
					'edit', 'start', 'save', 'register', 'attach', 'source',
					'cancel', 'update', 'message', 'update'
				);
				if (in_array( $segments[2], $appActions ))
				{
					$vars['action'] = $segments[2];
				}
				else
				{
					$vars['tool'] = $segments[2];
				}
				if (!empty($segments[3]) && in_array( $segments[3], $appActions ))
				{
					$vars['action'] = $segments[3];
				}
			}

			// Notes
			elseif (!empty($segments[2]) && !is_numeric($segments[2]) && $vars['active'] == 'notes')
			{
				// Wiki actions
				$wiki_actions = array('media', 'list', 'upload',
					'deletefolder', 'deletefile', 'view',
					'new', 'edit', 'save', 'cancel',
					'delete', 'deleteversion', 'approve',
					'rename', 'saverename', 'history',
					'compare', 'comments', 'editcomment',
					'addcomment', 'savecomment', 'removecomment',
					'reportcomment', 'deleterevision', 'pdf'
				);

				$remaining = array_slice($segments, 2);
				$action = array_pop($remaining);
				$pagename = '';

				if (in_array( $action, $wiki_actions ))
				{
					$vars['action'] = $action;
					$pagename = array_pop($remaining);
				}
				else
				{
					$vars['action'] = 'view';
					$pagename = $action;
				}
				$vars['pagename'] = $pagename;

				// Collect scope
				if (isset($vars['alias']))
				{
					if (count($remaining) > 0)
					{
						$scope = 'projects' . DS . $vars['alias'] . DS . 'notes';

						for ( $i = 0; $i < count($remaining); $i++ )
						{
							$scope .= DS . $remaining[$i]; // inlcude parent page names
						}
						if ($vars['action'] == 'new')
						{
							$scope .= DS . $pagename;
						}
						$vars['scope'] = $scope;
					}
					elseif ($vars['action'] == 'new')
					{
						$scope = 'projects' . DS . $vars['alias'] . DS . 'notes' . DS . $pagename;
						$vars['scope'] = $scope;
					}
				}

				return $vars;
			}

			// All other plugins
			elseif (!empty($segments[2]) && !is_numeric($segments[2]))
			{
				$vars['action'] = $segments[2];
			}
		}
	}

	return $vars;
}