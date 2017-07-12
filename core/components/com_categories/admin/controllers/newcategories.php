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

namespace Components\Categories\Admin\Controllers;

use Hubzero\Component\AdminController;
use Hubzero\Utility\Inflector;
use Hubzero\Access\Access;
use Hubzero\Access\Rules;
use Hubzero\Access\Asset;
use Components\Categories\Models\Category;
use Components\Categories\Admin\Helpers\CategoriesHelper;
use Request;
use Config;
use Notify;
use Route;
use User;
use Lang;
use Date;
use App;

class Newcategories extends AdminController
{
	public function execute()
	{
		$this->registerTask('add', 'edit');
		$this->registerTask('apply', 'save');
		$this->registerTask('publish', 'state');
		$this->registerTask('unpublish', 'state');
		$this->registerTask('orderup', 'reorder');
		$this->registerTask('orderdown', 'reorder');
		parent::execute();
	}

	public function displayTask()
	{
		$filters = array(
			'search' => Request::getState(
				$this->_option . '.' . $this->_controller . 'search',
				'search',
				''
			),
			'access' => Request::getState(
				$this->_option . '.' . $this->_controller . 'access',
				'filter_access',
				0
			),
			'published' => Request::getState(
				$this->_option . '.' . $this->_controller . 'published',
				'filter_published',
				''
			),
			'level' => Request::getState(
				$this->_option . '.' . $this->_controller . 'level',
				'filter_level'
			),
			'language' => Request::getState(
				$this->_option . '.' . $this->_controller . 'language',
				''
			),
			'sort' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sort',
				'filter_order',
				'lft'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sortdir',
				'filter_order_Dir',
				'ASC'
			),
			'extension' => Request::getState(
				$this->_option . '.' . $this->_controller . '.extension',
				'extension'
			)
		);
		$searchableFields = array(
			'category_id' => 'catid',
			'access' => 'access',
			'extension' => 'extension'
		);
		$categories = Category::all();
		foreach ($searchableFields as $index => $column)
		{
			if (isset($filters[$index]) && $filters[$index] != '')
			{
				$categories->whereEquals($column, $filters[$index]);
			}	
		}

		if (isset($filters['published']))
		{
			if ($filters['published'] == '')
			{
				$categories->where('published', '>=', 0);
			}
			elseif ($filters['published'] != '*')
			{
				$categories->whereEquals('published', $filters['published']);
			}
		}

		if (!empty($filters['level']))
		{
			$categories->where('level', '<=', $filters['level']);
		}

		$categories->order($filters['sort'], $filters['sort_Dir']);
		$items = $categories->paginated('limitstart', 'limit');
		$itemsArray = array();
		$ordering = array();
		$levels = Category::all()
			->whereEquals('extension', $filters['extension'])
			->group('level')
			->order('level', 'asc')
			->rows();
		$fLevels = array();
		foreach ($levels as $level)
		{
			$level = $level->get('level');
			$fLevels[$level] = $level;
		}
		foreach ($items as $item)
		{
			$itemsArray[] = $item;
			$parentId = $item->get('parent_id', '0');
			$ordering[$parentId][] = $item->get('id');
		}
		$extension = $filters['extension'];
		
		$this->view->set('pagination', $items->pagination);
		$this->view->set('f_levels', $fLevels);
		$this->view->set('filters', $filters);
		$this->view->set('ordering', $ordering);
		$this->view->set('items', $itemsArray);
		$this->view->setLayout('default')->display();
	}

	public function editTask($category = null)
	{
		$id = Request::getInt('id', 0);
		$extension = Request::getCmd('extension');
		if (!($category instanceof Article))
		{	
			$category = Category::oneOrNew($id);
		}
		if ($category->isNew())
		{
			$category->set('extension', $extension);
		}
		$canDo = CategoriesHelper::getActions($extension, 'category', $category->get('id', 0));
		$this->view->set('item', $category);
		$this->view->set('form', $category->getForm());
		$this->view->set('canDo', $canDo);
		$this->view->setLayout('edit');
		$this->view->display();
	}

	public function saveTask()
	{
		Request::checkToken();
		$items = Request::getVar('fields', array());
		$extension = Request::getCmd('extension');
		$categoryId = Request::getInt('id');
		$category = Category::oneOrNew($categoryId);
		if (!empty($items['rules']))
		{
			$rules = array_map(function($item){
				return array_filter($item, 'strlen');
			}, $items['rules']);
			$category->assetRules = new Rules($rules);
			$category->setNameSpace($extension);
		}
		unset($items['rules']);
		$category->set($items);
		if (!$category->saveAsChildOf($items['parent_id']))
		{
			Notify::error($category->getError());
			return $this->editTask($category);
		}

		Notify::success(Lang::txt('COM_CATEGORY_SAVED'));
		if ($this->_task == 'apply')
		{
			return $this->editTask($category);
		}
		$this->cancelTask();
	}

	public function deleteTask()
	{
		Request::checkToken();
		$ids = Request::getArray('cid');
		$categories = Category::all()->whereIn('id', $ids)->rows();
		foreach ($categories as $category)
		{
			$category->set('published', '-2');
		}
		if (!$categories->save())
		{
			Notify::error(Lang::txt('COM_CATEGORY_DELETE_ERROR'));	
		}
		else
		{
			$count = (int) count($categories);
			$title = Inflector::pluralize(Lang::txt('COM_CATEGORY'), $count);
			Notify::success(Lang::txt('COM_CATEGORIES_N_ITEMS_DELETED', $count, $title));
		}
		$this->cancelTask();
	}

	public function unpublishTask()
	{
		Request::checkToken();
		$ids = Request::getArray('cid');
		if (!empty($ids))
		{
			$categories = Category::all()->whereIn('id', $ids)->rows();
			foreach ($categories as $category)
			{
				$category->set('published', '0');
			}
			if (!$categories->save())
			{
				Notify::error(Lang::txt('COM_CATEGORY_UNPUBLISH_ERROR'));
			}
			else
			{
				$count = (int) count($categories);
				$title = Inflector::pluralize(Lang::txt('COM_CATEGORY'), $count);
				Notify::success(Lang::txt('COM_CATEGORIES_N_ITEMS_UNPUBLISHED', $count, $title));
			}
		}
		else
		{
			Notify::warning(Lang::txt('COM_CATEGORY_NO_SELECTION'));
		}
		$this->cancelTask();
	}

	public function publishTask()
	{
		Request::checkToken();
		$ids = Request::getArray('cid');
		if (!empty($ids))
		{
			$categories = Category::all()->whereIn('id', $ids)->rows();
			foreach ($categories as $category)
			{
				$category->set('published', '1');
			}
			if (!$categories->save())
			{
				Notify::error(Lang::txt('COM_CATEGORY_PUBLISH_ERROR'));
			}
			else
			{
				$count = (int) count($categories);
				$title = Inflector::pluralize(Lang::txt('COM_CATEGORY'), $count);
				Notify::success(Lang::txt('COM_CATEGORIES_N_ITEMS_PUBLISHED', $count, $title));
			}
		}
		else
		{
			Notify::warning(Lang::txt('COM_CATEGORY_NO_SELECTION'));
		}
		$this->cancelTask();
	}

	/**
	 * Changes the order of one or more records.
	 *
	 * @return  boolean  True on success
	 */
	public function reorderTask()
	{
		// Check for request forgeries.
		Request::checkToken(['get', 'post']);

		if (!User::authorise('core.edit.state', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Initialise variables.
		$ids = Request::getVar('cid', null, 'post', 'array');
		$inc = ($this->getTask() == 'orderup') ? -1 : +1;

		$success = 0;
		$extension = Request::getState($this->_option . '.' . $this->_controller . '.extension', 'extension');

		foreach ($ids as $id)
		{
			// Load the record and reorder it
			$model = Category::oneOrFail(intval($id));

			if (!$model->move($inc, $extension))
			{
				Notify::error(Lang::txt('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError()));
				continue;
			}

			$success++;
		}

		if ($success)
		{
			Cache::clean($this->_option);
			// Set the success message
			Notify::success(Lang::txt('JLIB_APPLICATION_SUCCESS_ITEM_REORDERED'));
		}

		// Redirect back to the listing
		$this->cancelTask();
	}

	/**
	 * Cancels a task and redirects to default view
	 *
	 * @return  void
	 */
	public function cancelTask()
	{
		$extension = Request::getCmd('extension');
		// Set the redirect
		\App::redirect(
			\Route::url('index.php?option=' . $this->_option . ($this->_controller ? '&controller=' . $this->_controller : '') . '&extension=' . $extension, false)
		);
	}

	public function saveorderTask()
	{
		Request::checkToken();
		$ordering = Request::getVar('order', array());
		$extension = Request::getState($this->_option . '.' . $this->_controller . '.extension', 'extension');
		if (!Category::saveorder($ordering, $extension))
		{
			Notify::error(Lang::txt('COM_CONTENT_ORDERING_ERROR'));
		}
		else
		{
			Notify::success(Lang::txt('COM_CONTENT_ORDERING_SUCCESS'));
		}
		$this->cancelTask();
	}
}
