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

namespace Components\Kb\Models;

use Components\Kb\Tables;
use Hubzero\Base\Object;
use Hubzero\Base\ItemList;
use Hubzero\Base\Model;
use User;

require_once(__DIR__ . DS . 'category.php');

/**
 * Knowledgebase archive model class
 */
class Archive extends Object
{
	/**
	 * Category
	 *
	 * @var object
	 */
	private $_category = null;

	/**
	 * \Hubzero\Base\Model
	 *
	 * @var object
	 */
	private $_categories = null;

	/**
	 * Category count
	 *
	 * @var integer
	 */
	private $_categories_count = null;

	/**
	 * \Hubzero\Base\Model
	 *
	 * @var object
	 */
	private $_articles = null;

	/**
	 * Article count
	 *
	 * @var integer
	 */
	private $_articles_count = null;

	/**
	 * JDatabase
	 *
	 * @var object
	 */
	private $_db = NULL;

	/**
	 * Registry
	 *
	 * @var object
	 */
	private $_config;

	/**
	 * Constructor
	 *
	 * @return     void
	 */
	public function __construct()
	{
		$this->_db = \App::get('db');

		$this->_config = \Component::params('com_kb');
	}

	/**
	 * Returns a reference to this model
	 *
	 * @param   string  $key
	 * @return  object
	 */
	static function &getInstance($key='site')
	{
		static $instances;

		if (!isset($instances))
		{
			$instances = array();
		}

		if (!isset($instances[$key]))
		{
			$instances[$key] = new self();
		}

		return $instances[$key];
	}

	/**
	 * Set and get a specific category
	 *
	 * @return     void
	 */
	public function category($id=null)
	{
		if (!isset($this->_category)
		 || ($id !== null && (int) $this->_category->get('id') != $id && (string) $this->_category->get('alias') != $id))
		{
			$this->_category = null;

			if ($this->_categories instanceof ItemList)
			{
				foreach ($this->_categories as $key => $entry)
				{
					if ((int) $category->get('id') == $id || (string) $category->get('alias') == $id)
					{
						$this->_category = $category;
						break;
					}
				}
			}

			if (!$this->_category)
			{
				$this->_category = Category::getInstance($id);
			}
		}
		return $this->_category;
	}

	/**
	 * Get a count or list of categories
	 *
	 * @param      string  $rtrn    What data to return
	 * @param      array   $filters Filters to apply to data retrieval
	 * @param      boolean $boolean Clear cached data?
	 * @return     mixed
	 */
	public function categories($rtrn='list', $filters=array(), $clear=false)
	{
		if (!isset($filters['state']))
		{
			$filters['state'] = Model::APP_STATE_PUBLISHED;
		}
		if (!isset($filters['access']))
		{
			$filters['access'] = User::getAuthorisedViewLevels();
		}
		if (!isset($filters['section']))
		{
			$filters['section'] = 0;
		}
		if (!isset($filters['empty']))
		{
			$filters['empty'] = false;
		}
		if (!isset($filters['sort']))
		{
			$filters['sort'] = 'title';
		}
		if (!isset($filters['sort_Dir']))
		{
			$filters['sort_Dir']  = 'ASC';
		}

		switch (strtolower($rtrn))
		{
			case 'count':
				if (!isset($this->_categories_count) || !is_numeric($this->_categories_count) || $clear)
				{
					$tbl = new Tables\Category($this->_db);
					$this->_categories_count = (int) $tbl->find('count', $filters);
				}
				return $this->_categories_count;
			break;

			case 'list':
			case 'results':
			default:
				if (!$this->_categories instanceof ItemList || $clear)
				{
					$tbl = new Tables\Category($this->_db);
					if ($results = $tbl->find('list', $filters))
					{
						foreach ($results as $key => $result)
						{
							$results[$key] = new Category($result);
						}
					}
					else
					{
						$results = array();
					}
					$this->_categories = new ItemList($results);
					return $this->_categories;
				}
			break;
		}
	}

	/**
	 * Get a count or list of articles
	 *
	 * @param      string  $rtrn    What data to return
	 * @param      array   $filters Filters to apply to data retrieval
	 * @param      boolean $boolean Clear cached data?
	 * @return     mixed
	 */
	public function articles($rtrn='list', $filters=array(), $clear=false)
	{
		if (!isset($filters['state']))
		{
			$filters['state']  = Model::APP_STATE_PUBLISHED;
		}
		if (!isset($filters['access']))
		{
			$filters['access'] = User::getAuthorisedViewLevels();
		}
		if (!isset($filters['start']))
		{
			$filters['start']  = 0;
		}

		switch (strtolower($rtrn))
		{
			case 'popular':
				$filters['sort']     = 'popularity';
				$filters['sort_Dir'] = 'DESC';

				return $this->articles('list', $filters, true);
			break;

			case 'recent':
				$filters['sort']     = 'recent';
				$filters['sort_Dir'] = 'DESC';

				return $this->articles('list', $filters, true);
			break;

			case 'count':
				if (!isset($this->_articles_count) || !is_numeric($this->_articles_count) || $clear)
				{
					$tbl = new Tables\Article($this->_db);
					$this->_articles_count = (int) $tbl->find('count', $filters);
				}
				return $this->_articles_count;
			break;

			case 'list':
			case 'results':
			default:
				if (!$this->_articles instanceof ItemList || $clear)
				{
					if (isset($filters['sort']))
					{
						$filters['order'] = $filters['sort'] . " " . $filters['sort_Dir'];
					}
					$tbl = new Tables\Article($this->_db);
					if ($results = $tbl->find('list', $filters))
					{
						foreach ($results as $key => $result)
						{
							$results[$key] = new Article($result);
						}
					}
					else
					{
						$results = array();
					}
					$this->_articles = new ItemList($results);
					return $this->_articles;
				}
			break;
		}
	}
}
