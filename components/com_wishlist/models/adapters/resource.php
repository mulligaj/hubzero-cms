<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2013 Purdue University. All rights reserved.
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
 * @copyright Copyright 2005-2013 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

require_once(__DIR__ . DS . 'abstract.php');
require_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_resources' . DS . 'tables' . DS . 'resource.php');

/**
 * Adapter class for a forum post link for course forum
 */
class WishlistModelAdapterResource extends WishlistModelAdapterAbstract
{
	/**
	 * URL segments
	 *
	 * @var string
	 */
	protected $_segments = array(
		'option' => 'com_wishlist',
	);

	/**
	 * Constructor
	 *
	 * @param      integer $referenceid Scope ID (group, course, etc.)
	 * @return     void
	 */
	public function __construct($referenceid=0)
	{
		$this->set('referenceid', $referenceid)
		     ->set('category', 'resource')
		     ->set('option', $this->_segments['option']);

		//$this->_item = new ResourcesResource($this->get('referenceid'));
		$database = JFactory::getDBO();
		$this->_item = new ResourcesResource($database);
		$this->_item->load($this->get('referenceid'));
		$this->_item->typetitle = $this->_item->getTypeTitle();

		if ($this->_item->standalone != 1 || $this->_item->published != 1)
		{
			$this->_item->id = 0;
		}

	//	$this->_segments['id']     = ($this->_item->alias ? $this->_item->alias : $this->_item->id);
		$this->_segments['active'] = 'wishlist';
	}

	/**
	 * Generate and return the title for this wishlist
	 *
	 * @return     string
	 */
	public function title()
	{
		return ($this->_item->type == 7 && isset($this->_item->alias))
				? JText::_('COM_WISHLIST_NAME_RESOURCE_TOOL') . ' ' . $this->_item->alias
				: JText::_('COM_WISHLIST_NAME_RESOURCE_ID') . ' ' . $this->_item->id;
	}

	/**
	 * Generate and return various links to the entry
	 * Link will vary depending upon action desired, such as edit, delete, etc.
	 *
	 * @param      string $type   The type of link to return
	 * @param      mixed  $params Optional string or associative array of params to append
	 * @return     string
	 */
	public function link($type='', $params=null)
	{
		$segments = $this->_segments;

		if ($this->get('category'))
		{
			$segments['category'] = $this->get('category');
		}
		if ($this->get('referenceid'))
		{
			$segments['rid'] = $this->get('referenceid');
		}

		$anchor = '';

		// If it doesn't exist or isn't published
		switch (strtolower($type))
		{
			case 'base':
				return $this->_base . '?' . (string) $this->_build($this->_segments);
			break;

			case 'edit':
				if ($this->get('wishid'))
				{
					$segments['task'] = 'editwish';
					$segments['wishid'] = $this->get('wishid');
				}
			break;

			case 'delete':
				$segments['task'] = 'deletewish';
				if ($this->get('wishid'))
				{
					$segments['wishid'] = $this->get('wishid');
				}
			break;

			case 'add':
			case 'addwish':
			case 'new':
				$segments['task'] = 'addwish';
			break;

			case 'settings':
				unset($segments['category']);
				unset($segments['rid']);

				$segments['task'] = 'settings';
				$segments['id'] = $this->get('wishlist');
			break;

			case 'savesettings':
				unset($segments['category']);
				unset($segments['rid']);

				$segments['task'] = 'savesettings';
				$segments['listid'] = $this->get('wishlist');
			break;

			case 'comments':
				if ($this->get('wishid'))
				{
					$segments['task'] = 'wish';
					$segments['wishid'] = $this->get('wishid');
					$segments['com'] = 1;
					$anchor = '#comments';
				}
			break;

			case 'changestatus':
				if ($this->get('wishid'))
				{
					$segments['task'] = 'wish';
					$segments['wishid'] = $this->get('wishid');
					$segments['action'] = 'changestatus';
					$anchor = '#action';
				}
			break;

			case 'withdraw':
				if ($this->get('wishid'))
				{
					$segments['task'] = 'wish';
					$segments['wishid'] = $this->get('wishid');
					$segments['action'] = 'delete';
					$anchor = '#action';
				}
			break;

			case 'addbonus':
				if ($this->get('wishid'))
				{
					$segments['task'] = 'wish';
					$segments['wishid'] = $this->get('wishid');
					$segments['action'] = 'addbonus';
					$anchor = '#action';
				}
			break;

			case 'privacy':
				if ($this->get('wishid'))
				{
					$segments['task'] = 'editprivacy';
					$segments['wishid'] = $this->get('wishid');
				}
			break;

			case 'move':
				if ($this->get('wishid'))
				{
					$segments['task'] = 'wish';
					$segments['wishid'] = $this->get('wishid');
					$segments['action'] = 'move';
					$anchor = '#action';
				}
			break;

			case 'comment':
				if ($this->get('wishid'))
				{
					$segments['task'] = 'wish';
					$segments['wishid'] = $this->get('wishid');
					$segments['cat'] = 'wish';
					$anchor = '#commentform';
				}
			break;

			case 'editplan':
				if ($this->get('wishid'))
				{
					$segments['task'] = 'wish';
					$segments['wishid'] = $this->get('wishid');
					$segments['action'] = 'editplan';
					$anchor = '#plan';
				}
			break;

			case 'rank':
				if ($this->get('wishid'))
				{
					$segments['task'] = 'wish';
					$segments['wishid'] = $this->get('wishid');
					$segments['action'] = 'rank';
					$anchor = '#action';
				}
			break;

			case 'report':
			case 'reportabuse':
				return 'index.php?option=com_support&task=reportabuse&category=wish&id=' . $this->get('wishid') . '&parent=' . $this->get('wishlist');
			break;

			case 'permalink':
			default:
				$segments['task'] = 'wishlist';
				if ($this->get('wishid'))
				{
					$segments['task'] = 'wish';
					$segments['wishid'] = $this->get('wishid');
				}
			break;
		}

		if (is_string($params))
		{
			$params = str_replace('&amp;', '&', $params);

			if (substr($params, 0, 1) == '#')
			{
				$anchor = $params;
			}
			else
			{
				if (substr($params, 0, 1) == '?')
				{
					$params = substr($params, 1);
				}
				parse_str($params, $parsed);
				$params = $parsed;
			}
		}

		$segments = array_merge($segments, (array) $params);

		return $this->_base . '?' . (string) $this->_build($segments) . (string) $anchor;
	}

	/**
	 * Append an item to the breadcrumb trail.
	 * If no item is provided, it will build the trail up to the list
	 *
	 * @param      string $title Breadcrumb title
	 * @param      string $url   Breadcrumb URL
	 * @return     string
	 */
	public function pathway($title=null, $url=null)
	{
		$pathway = JFactory::getApplication()->getPathway();

		if (!$title)
		{
			$pathway->addItem(
				JText::_('Resources'),
				'index.php?option=com_resources'
			);
			$pathway->addItem(
				stripslashes($this->_item->title),
				'index.php?option=com_resources&id=' . $this->get('referenceid')
			);
			$pathway->addItem(
				JText::_('Wishlist'),
				'index.php?option=com_resources&active=wishlist&id=' . $this->get('referenceid')
			);
		}
		else
		{
			$pathway->addItem(
				$title,
				$url
			);
		}

		return $this;
	}
}
