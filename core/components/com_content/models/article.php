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

namespace Components\Content\Models;

use Hubzero\Database\Relational;
use Hubzero\Database\Asset;
use Hubzero\Utility\String;
use Hubzero\Config\Registry;
use Component;
use Lang;
use User;
use Date;
use Hubzero\Form\Form;

require_once Component::path('com_categories') . '/models/category.php';
/**
 * Model class for a blog entry
 */
class Article extends Relational
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
		'publish_up',
		'publish_down',
		'modified',
		'modified_by',
		'metadata',
		'attribs',
		'asset_id'
	);

	/**
	 * Automatic fields to populate every time a row is created
	 *
	 * @var  array
	 */
	public $initiate = array(
		'created',
		'created_by',
		'ordering'
	);

	/**
	 * Rules array converted into JSON string.
	 *
	 * @var string
	 */

	public $assetRules;

	/**
	 * Registry
	 *
	 * @var  object
	 */
	public $params = NULL;

	/**
	 *	Registry params object 
	 *
	 * @var  object
	 */
	protected $paramsRegistry = null;

	protected $namespace = 'content';

	protected $table = '#__content';


	/**
	 * Sets up additional custom rules
	 *
	 * @return  void
	 */
	public function setup()
	{
		$this->addRule('publish_down', function($data)
		{
			if (!$data['publish_down'] || $data['publish_down'] == '0000-00-00 00:00:00')
			{
				return false;
			}
			return $data['publish_down'] >= $data['publish_up'] ? false : Lang::txt('The entry cannot end before it begins');
		});
	}

	public function category()
	{
		return $this->belongsToOne('\Components\Categories\Models\Category', 'catid');
	}

	public function categories()
	{
		return \Components\Categories\Models\Category::all()->whereEquals('extension', 'com_content');
	}

	public function accessLevel()
	{
		return $this->belongsToOne('\Hubzero\Access\Viewlevel', 'access');
	}

	public function asset()
	{
		return $this->belongsToOne('Hubzero\Access\Asset', 'asset_id');
	}

	/**
     * Generates automatic owned by field value
     *
     * @param   array   $data  the data being saved
     * @return  string
     */
    public function automaticPublishUp($data)
    {
        if (!isset($data['publish_up']))
        {
            $data['publish_up'] = null;
        }

        $publish_up = $data['publish_up'];

        if (!$publish_up || $publish_up == '0000-00-00 00:00:00')
        {
            $publish_up = $this->isNew() ? \Date::toSql() : $this->created;
        }

        return $publish_up;
    }

    /**
     * Generates automatic owned by field value
     *
     * @param   array   $data  the data being saved
     * @return  string
     */
    public function automaticPublishDown($data)
    {
        if (!isset($data['publish_down']) || !$data['publish_down'])
        {
            $data['publish_down'] = '0000-00-00 00:00:00';
        }
        return $data['publish_down'];
	}
	
	public function automaticModified($data)
	{
		$data['modified'] = Date::of()->toSql();
		return $data['modified'];
	}

	public function automaticModifiedBy($data)
	{
		$data['modified_by'] = User::getInstance()->get('id');
		return $data['modified_by'];
	}

	public function automaticOrdering($data)
	{
		if (empty($data['ordering']) && !empty($data['catid']))
		{
			$lastOrderedRow = self::all()->whereEquals('catid', $data['catid'])
										 ->order('ordering', 'desc')
										 ->row();
			$lastOrderNum = $lastOrderedRow->get('ordering', 0);
			$data['ordering'] = $lastOrderNum + 1;
		}
		return $data['ordering'];
	}

	/**
	 * Get params as Registry object
	 *
	 * @return object
	 */
	public function transformAttribs()
	{
		if(!($this->paramsRegistry instanceof Registry))
		{
			$itemRegistry = new Registry($this->get('attribs'));
			$componentRegistry = Component::params('com_content');
			$componentRegistry->merge($itemRegistry);
			$this->paramsRegistry = $componentRegistry;
		}
		return $this->paramsRegistry;
	}

	public function transformName()
	{
		return $this->get('title');
	}

	public function transformMetadata()
	{
		$metadata = $this->get('metadata');
		if (!empty($metadata) && !is_array($metadata))
		{
			return json_decode($metadata);
		}
	}

	public function automaticAttribs($data)
	{
        if (!empty($data['attribs']))
        {
            $attribs = json_encode($data['attribs']);
			return $attribs;
        }
		return false;
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
        $file = __DIR__ . '/forms/article.xml';
        $file = Filesystem::cleanPath($file);
        $form = new Form('content', array('control' => 'fields'));
        if (!$form->loadFile($file, false, '//form'))
        {
            $this->addError(Lang::txt('JERROR_LOADFILE_FAILED'));
        }
		$data = $this->getAttributes();
        $data['attribs'] = $this->attribs->toArray();
		$data['metadata'] = $this->metadata;
		if ($this->isNew())
		{
			unset($data['asset_id']);
		}
        $form->bind($data);
        return $form;
    }

	public static function saveorder($ordering)
	{
		if (empty($ordering) || !is_array($ordering))
		{
			return false;
		}
		foreach ($ordering as $catid => $order)
		{
			$existingOrderedRows = self::all()->whereEquals('catid', $catid)
											  ->order('ordering', 'asc')
											  ->rows();
			if (count($existingOrderedRows) <= 1)
			{
				continue;
			}
			$existingOrderIds = array();
			foreach ($existingOrderedRows as $row)
			{
				$pkValue = $row->get('id');
				$existingOrderIds[$pkValue] = $row->ordering;
			}
			$newOrder = $order + $existingOrderIds;
			if ($newOrder != $existingOrderIds)
			{
				asort($newOrder);
				$iterator = 1;
				foreach ($newOrder as $pk => $orderValue)
				{
					$existingOrderedRows->seek($pk)->set('ordering', $iterator);
					$iterator++;
				}
				if (!$existingOrderedRows->save())
				{
					return false;
				}
			}
		}
		return true;
	}


	public function move($delta, $where = '')
	{
		// If the change is none, do nothing.
		if (empty($delta))
		{
			return true;
		}

		// Select the primary key and ordering values from the table.
		$query = self::all()
			->whereEquals('catid', $this->get('catid'));

		// If the movement delta is negative move the row up.
		if ($delta < 0)
		{
			$query->where('ordering', '<', (int) $this->get('ordering'));
			$query->order('ordering', 'desc');
		}
		// If the movement delta is positive move the row down.
		elseif ($delta > 0)
		{
			$query->where('ordering', '>', (int) $this->get('ordering'));
			$query->order('ordering', 'asc');
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
			$prev = $this->get('ordering');

			// Update the ordering field for this instance to the row's ordering value.
			$this->set('ordering', (int) $row->get('ordering'));

			// Check for a database error.
			if (!$this->save())
			{
				return false;
			}

			// Update the ordering field for the row to this instance's ordering value.
			$row->set('ordering', (int) $prev);

			// Check for a database error.
			if (!$row->save())
			{
				return false;
			}
		}
		else
		{
			// Update the ordering field for this instance.
			$this->set('ordering', (int) $this->get('ordering'));

			// Check for a database error.
			if (!$this->save())
			{
				return false;
			}
		}

		return true;
	}
}
