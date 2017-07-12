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
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Categories\Models;

use Hubzero\Database\Nested;
use Hubzero\Database\Rows;
use Hubzero\Utility\String;
use Hubzero\Config\Registry;
use Hubzero\Form\Form;
use Component;
use Lang;
use User;
use Date;

/**
 * Model class for a blog entry
 */
class Category extends Nested
{

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public $orderBy = 'published_up';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'desc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'title'    => 'notempty',
		'content'  => 'notempty',
		'scope'    => 'notempty'
	);

	/**
	 * Automatically fillable fields
	 *
	 * @var  array
	 */
	public $always = array(
		'params',
		'metadata',
		'asset_id',
		'modified_user_id'
	);

	/**
	 * Automatic fields to populate every time a row is created
	 *
	 * @var  array
	 */
	public $initiate = array(
		'created_time',
		'created_user_id'
	);

	/**
	 * Rules array converted into JSON string.
	 *
	 * @var string
	 */
	public $assetRules;

	protected $namespace = 'category';

	protected $table = '#__categories';

	public function setNameSpace($name)
	{
		if (!empty($name))
		{
			$underscorePos = strpos($name, "_");
			if ($underscorePos !== false)
			{
				$name = substr($name, $underscorePos + 1);
			}

			$this->namespace = $name;
		}
	}

	/**
     * Generates automatic created time field value
     *
     * @param   array   $data  the data being saved
     * @return  string
     */
    public function automaticCreatedTime($data)
    {
        if (!isset($data['created_time']))
        {
            $data['created_time'] = Date::toSql();
        }

        $created_time = $data['created_time'];
        return $created_time;
    }

	/**
	 * Generates userId of person logged in if no user ID provided upon creation.
	 *
	 * @param array $data	the data being saved
	 * @return string
	 */
	public function automaticCreatedUserId($data)
	{
		if (empty($data['created_user_id']))
		{
			return User::getInstance()->get('id');
		}
	}

	public function automaticModifiedUserId($data)
	{
		if (empty($data['modified_user_id']))
		{
			return User::getInstance()->get('id');
		}
	}

	public function transformName()
	{
		return $this->get('title');
	}
	/**
	 * Get params as Registry object
	 *
	 * @return object
	 */
	public function transformParams()
	{
		if(!($this->paramsRegistry instanceof Registry))
		{
			$itemRegistry = new Registry($this->get('params'));
			$this->paramsRegistry = $itemRegistry;
		}
		return $this->paramsRegistry;
	}

	public function automaticParams($data)
	{
        if (!empty($data['params']))
        {
            $params = json_encode($data['params']);
			return $params;
        }
		return false;
	}

	public function transformMetadata()
	{
		if(!($this->metadataRegistry instanceof Registry))
		{
			$itemRegistry = new Registry($this->get('metadata'));
			$this->metadataRegistry = $itemRegistry;
		}
		return $this->metadataRegistry;
	}

	public function automaticMetadata($data)
	{
        if (!empty($data['metadata']))
        {
            $metadata = json_encode($data['metadata']);
			return $metadata;
        }
		return false;
	}


	public function getForm()
    {
        $file = __DIR__ . '/forms/category.xml';
        $file = Filesystem::cleanPath($file);
        $form = new Form('categories', array('control' => 'fields'));
        if (!$form->loadFile($file, false, '//form'))
        {
            $this->addError(Lang::txt('JERROR_LOADFILE_FAILED'));
        }
		$data = $this->getAttributes();
        $data['params'] = $this->params->toArray();
        $data['metadata'] = $this->metadata->toArray();
		if ($this->isNew())
		{
			unset($data['asset_id']);
		}
        $form->bind($data);
        return $form;
    }

	public static function saveorder($ordering, $extension)
	{
		if (empty($ordering) || !is_array($ordering))
		{
			return false;
		}
		$storage = null;
		foreach ($ordering as $parentid => $order)
		{
			$existingOrderedRows = self::all()->whereEquals('parent_id', $parentid)
											  ->whereEquals('extension', $extension)
											  ->order('lft', 'asc')
											  ->rows();
			if (count($existingOrderedRows) <= 1)
			{
				continue;
			}
			$existingLftIds = array();
			foreach ($existingOrderedRows as $row)
			{
				$pkValue = $row->get('id');
				$existingLftIds[$pkValue] = $row->lft;
			}
			asort($order);
			if (array_keys($order) !== array_keys($existingLftIds))
			{
				$startLft = array_shift($existingLftIds);
				foreach (array_keys($order) as $pk)
				{
					$row = $existingOrderedRows->seek($pk);
					$storage = $row->updatePositionWithChildren($startLft, $storage);
					$startLft = $storage->last()->get('rgt') + 1;
				}
			}
		}

		if ($storage && !$storage->save())
		{
			return false;
		}
		return true;
	}

	public function move($delta, $extension, $where = '')
	{
		// If the change is none, do nothing.
		if (empty($delta))
		{
			return true;
		}

		// Select the primary key and ordering values from the table.
		$query = self::all()
			->whereEquals('parent_id', $this->get('parent_id'))
			->whereEquals('extension', $extension);

		// If the movement delta is negative move the row up.
		if ($delta < 0)
		{
			$query->where('lft', '<', (int) $this->get('lft'));
			$query->order('lft', 'desc');
		}
		// If the movement delta is positive move the row down.
		elseif ($delta > 0)
		{
			$query->where('lft', '>', (int) $this->get('lft'));
			$query->order('lft', 'asc');
		}

		// Add the custom WHERE clause if set.
		if ($where)
		{
			$query->whereRaw($where);
		}

		// Select the first row with the criteria.
		$row = $query->row();

		// If a row is found, move the item.
		if ($row->get($this->pk))
		{
			if ($delta < 0)
			{
				$thisStart = $row->get('lft');
				$storage = $this->updatePositionWithChildren($thisStart);
				$rowStart = $storage->last()->get('rgt') + 1;	
				$row->updatePositionWithChildren($rowStart, $storage);
			}
			else if ($delta > 0)
			{
				$rowStart = $this->get('lft');
				$storage = $row->updatePositionWithChildren($rowStart);
				$thisStart = $storage->last()->get('rgt') + 1;	
				$this->updatePositionWithChildren($thisStart, $storage);
			}
			if (!$storage->save())
			{
				return false;
			}
		}
		return true;
	}

	public function updatePositionWithChildren($iterator, $storage = null)
	{
		if (!($storage instanceof Rows))
		{
			$storage = new Rows();
		}
		$children = $this->getChildren();
		$this->set('lft', $iterator);
		if ($children->count() < 1)
		{
			$iterator++;
			$this->set('rgt', $iterator);
			$storage->push($this);
		}
		else
		{
			foreach($children as $child)
			{
				$iterator++;
				$storage = $child->updatePositionWithChildren($iterator, $storage);
				$iterator = $storage->last()->get('rgt');
			}
			$iterator++;
			$this->set('rgt', $iterator);
			$storage->push($this);
		}
		return $storage;
	}

	public function parents()
	{
		$id = $this->get('id');
		$extension = $this->get('extension');
		$parents = self::all()
			->whereEquals('extension', $extension)
			->where('parent_id', '!=', $id)
			->order('lft', 'asc');
		return $parents;
	}

	public function nestedTitle()
	{
		$nestedPad = str_repeat('- ', $this->get('level', 1));
		$title = $nestedPad . $this->get('title');
		return $title;
	}
}
