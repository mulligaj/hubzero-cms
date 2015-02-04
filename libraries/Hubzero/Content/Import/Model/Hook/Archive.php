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
 * @author    Christopher Smoak <csmoak@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Hubzero\Content\Import\Model\Hook;

use Hubzero\Base\Model\ItemList;
use Hubzero\Base\Object;
use Hubzero\Content\Import\Model\Hook;
use Hubzero\Content\Import\Table;

/**
 * Import Hook archive model
 */
class Archive extends Object
{
	/**
	 * JDatabase
	 *
	 * @var  object
	 */
	private $_db = NULL;

	/**
	 * Record list
	 *
	 * @var  object
	 */
	private $_hooks = NULL;

	/**
	 * Record total
	 *
	 * @var  integer
	 */
	private $_hooks_total = NULL;

	/**
	 * Constructor
	 *
	 * @return  void
	 */
	public function __construct()
	{
		$this->_db = \JFactory::getDBO();
	}

	/**
	 * Get Instance of Archive
	 *
	 * @param   string  $key  Instance Key
	 * @return  object
	 */
	static function &getInstance($key=null)
	{
		static $instances;

		if (!isset($instances))
		{
			$instances = array();
		}

		if (!isset($instances[$key]))
		{
			$instances[$key] = new static();
		}

		return $instances[$key];
	}

	/**
	 * Get a count or list of import hooks
	 *
	 * @param   string   $rtrn     What data to return
	 * @param   array    $filters  Filters to apply to data retrieval
	 * @param   boolean  $boolean  Clear cached data?
	 * @return  mixed
	 */
	public function hooks($rtrn = 'list', $filters = array(), $clear = false)
	{
		switch (strtolower($rtrn))
		{
			case 'count':
				if (is_null($this->_hooks_total) || $clear)
				{
					$tbl = new Table\Hook($this->_db);

					$this->_hooks_total = $tbl->find('count', $filters);
				}
				return $this->_hooks_total;
			break;

			case 'list':
			default:
				if (!($this->_hooks instanceof ItemList) || $clear)
				{
					$tbl = new Table\Hook($this->_db);
					if ($results = $tbl->find('list', $filters))
					{
						foreach ($results as $key => $result)
						{
							$results[$key] = new Hook($result);
						}
					}
					$this->_hooks = new ItemList($results);
				}
				return $this->_hooks;
			break;
		}
	}
}