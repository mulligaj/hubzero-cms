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

namespace Components\Content\Admin\Controllers;

use Hubzero\Component\AdminController;
use Hubzero\Utility\Inflector;
use Hubzero\Access\Access;
use Hubzero\Access\Rules;
use Hubzero\Access\Asset;
use Components\Content\Models\Article;
use Request;
use Config;
use Notify;
use Route;
use User;
use Lang;
use Date;
use App;

class Newarticles extends AdminController
{
	public function execute()
	{
		$this->registerTask('add', 'edit');
		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save');
		$this->registerTask('save2copy', 'save');
		$this->registerTask('publish', 'state');
		$this->registerTask('unpublish', 'state');
		$this->registerTask('orderup', 'reorder');
		$this->registerTask('orderdown', 'reorder');
		parent::execute();
	}
	public function displayTask()
	{
		if ($layout = Request::getVar('layout'))
        {
            $this->context .= '.'.$layout;
        }
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
			'author_id' => Request::getState(
				$this->_option . '.' . $this->_controller . 'author_id',
				'author_id'
			),
			'published' => Request::getState(
				$this->_option . '.' . $this->_controller . 'published',
				'filter_published',
				''
			),
			'category_id' => Request::getState(
				$this->_option . '.' . $this->_controller . 'category_id',
				'filter_category_id'
			),
			'level' => Request::getState(
				$this->_option . '.' . $this->_controller . 'level',
				0
			),
			'language' => Request::getState(
				$this->_option . '.' . $this->_controller . 'language',
				''
			),
			'sort' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sort',
				'filter_order',
				'title'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sortdir',
				'filter_order_Dir',
				'ASC'
			)
		);
		$searchableFields = array(
			'category_id' => 'catid',
			'access' => 'access'
		);
		$articles = Article::all();
		foreach ($searchableFields as $index => $column)
		{
			if (isset($filters[$index]) && $filters[$index] != '')
			{
				$articles->whereEquals($column, $filters[$index]);
			}	
		}
		if (isset($filters['published']))
		{
			if ($filters['published'] == '')
			{
				$articles->where('state', '>=', 0);
			}
			elseif ($filters['published'] != '*')
			{
				$articles->whereEquals('state', $filters['published']);
			}
		}

		$articles->including('accessLevel')
				 ->including('category')
				 ->including('author');
		if (strtolower($filters['sort']) == 'ordering')
		{
			$articles->order('catid', 'asc');
		}
		$articles->order($filters['sort'], $filters['sort_Dir']);
		$items = $articles->paginated('limitstart', 'limit')->rows();
		$itemsArray = array();
		foreach ($items as $item)
		{
			$itemsArray[] = $item;
		}
		$this->view->set('pagination', $items->pagination);
		$this->view->set('authors', array());
		$this->view->set('f_levels', array());
		$this->view->set('filters', $filters);
		$this->view->set('items', $itemsArray);
		$this->view->setLayout('default')->display();
	}

	public function editTask($article = null)
	{
		$id = Request::getInt('id', 0);
		if (!($article instanceof Article))
		{	
			$article = Article::oneOrNew($id);
		}
		
		if (!$article->isNew())
		{
			$assetId = $article->get('asset_id');
			$createdBy = $article->get('created_by');
			$checkedOut = $article->get('checked_out');
			if ($checkedOut)
			{
				if ($checkedOut != User::getInstance()->get('id'))
				{
					Notify::error(Lang::txt('Currently checked out'));
					$this->cancelTask();
				}
			}
			if (!User::authorise('core.edit', $assetId))
			{
				if (!User::authorise('core.edit.own', $assetId)
					|| $createdBy != User::getInstance()->get('id'))
				{
					App::abort(403, Lang::txt('COM_CONTENT_NOT_AUTHORIZED'));
				}
			}
			$article->set('checked_out', User::getInstance()->get('id'));
			$article->set('checked_out_time', Date::of()->toSql());
			$article->save();
		}
		else
		{
			if (!User::authorise('core.create', 'com_content'))
			{
				App::abort(403, Lang::txt('COM_CONTENT_NOT_AUTHORIZED'));
			}
		}
		$newTasks = array('save2new', 'save2copy');
		$task = in_array($this->_task, $newTasks) ? 'add' : $this->_task;
		$this->view->set('task', $task);
		$this->view->set('item', $article);
		$this->view->set('form', $article->getForm());
		$this->view->setLayout('edit');
		$this->view->display();
	}

	public function saveTask()
	{
		Request::checkToken();
		$items = Request::getVar('fields', array());
		$articleId = Request::getInt('id');
		$article = Article::oneOrNew($articleId);
		$checkedOut = $article->get('checked_out');
		if ($checkedOut)
		{
			$article->set('checked_out', null);
			$article->set('checked_out_time', null);
			$article->save();
		}
		if (!empty($items['rules']))
		{
			$rules = array_map(function($item){
				return array_filter($item, 'strlen');
			}, $items['rules']);
			$article->assetRules = new Rules($rules);
		}
		unset($items['rules']);
		$article->set($items);


		if ($this->_task == 'save2copy')
		{
			$article->set('id', 0);
		}
		if (!$article->save())
		{
			Notify::error($article->getError());
			return $this->editTask($article);
		}

		Notify::success(Lang::txt('COM_CONTENT_SAVE_SUCCESS'));
		if ($this->_task == 'apply' || $this->_task == 'save2copy')
		{
			return $this->editTask($article);
		}
		elseif ($this->_task == 'save2new')
		{
			Request::setVar('id', 0);
			return $this->editTask();
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

		foreach ($ids as $id)
		{
			// Load the record and reorder it
			$model = Article::oneOrFail(intval($id));

			if (!$model->move($inc))
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

	public function saveorderTask()
	{
		Request::checkToken();
		$ordering = Request::getVar('order', array());
		if (!Article::saveorder($ordering))
		{
			Notify::error(Lang::txt('COM_CONTENT_ORDERING_ERROR'));
		}
		else
		{
			Notify::success(Lang::txt('COM_CONTENT_ORDERING_SUCCESS'));
		}
		$this->cancelTask();
	}

	public function unpublishTask()
	{
		Request::checkToken();
		$ids = Request::getArray('cid');
		if (!empty($ids))
		{
			$articles = Article::all()->whereIn('id', $ids)->rows();
			foreach ($articles as $article)
			{
				$article->set('state', '0');
			}
			if (!$articles->save())
			{
				Notify::error($articles->getError());
			}
			else
			{
				$count = count($articles);
				$title = Inflector::pluralize('article', $count);
				Notify::success(Lang::txt('COM_CONTENT_N_ITEMS_UNPUBLISHED', $count, $title));
			}
		}
		else
		{
			Notify::warning(Lang::txt('COM_CONTENT_NO_SELECTION'));
		}
		$this->cancelTask();
	}

	public function publishTask()
	{
		Request::checkToken();
		$ids = Request::getArray('cid');
		if (!empty($ids))
		{
			$articles = Article::all()->whereIn('id', $ids)->rows();
			foreach ($articles as $article)
			{
				$article->set('state', '1');
			}
			if (!$articles->save())
			{
				Notify::error($articles->getError());
			}
			else
			{
				$count = (int) count($articles);
				$title = Inflector::pluralize('article', $count);
				Notify::success(Lang::txt('COM_CONTENT_N_ITEMS_PUBLISHED', $count, $title));
			}
		}
		else
		{
			Notify::warning(Lang::txt('COM_CONTENT_NO_SELECTION'));
		}
		$this->cancelTask();
	}

	public function archiveTask()
	{
		Request::checkToken();
		$ids = Request::getArray('cid');
		$articles = Article::all()->whereIn('id', $ids)->rows();
		foreach ($articles as $article)
		{
			$article->set('state', '2');
		}
		if (!$articles->save())
		{
			Notify::error($articles->getError());	
		}
		else
		{
			$count = (int) count($articles);
			$title = Inflector::pluralize('article', $count);
			Notify::success(Lang::txt('COM_CONTENT_N_ITEMS_ARCHIVED', $count, $title));
		}
		$this->cancelTask();
	}

	public function checkinTask()
	{
		Request::checkToken();
		$ids = Request::getArray('cid');
		$articles = Article::all()->whereIn('id', $ids)->rows();
		foreach ($articles as $key => $article)
		{
			if (!User::authorise('core.admin', $article->asset_id))
			{
				if ($article->checked_out != User::getInstance()->get('id'))
				{
					Notify::warning(Lang::txt('COM_CONTENT_CHECKED_IN_ERROR', $article->id));
					$articles->drop($key);
					continue;
				}
			}
			$article->set('checked_out', null);
			$article->set('checked_out_time', null);
		}
		if ($articles->count() > 0)
		{
			if ($articles->save())
			{
				$count = (int) count($articles);
				$title = Inflector::pluralize(Lang::txt('COM_CONTENT_ARTICLE'), $count);
				Notify::success(Lang::txt('COM_CONTENT_N_ITEMS_CHECKED_IN_MORE', $count, $title));
			}
		}
		$this->cancelTask();
	}

	public function deleteTask()
	{
		Request::checkToken();
		$ids = Request::getArray('cid');
		$articles = Article::all()->whereIn('id', $ids)->rows();
		foreach ($articles as $article)
		{
			$article->set('state', '-2');
		}
		if (!$articles->save())
		{
			Notify::error($articles->getError());	
		}
		else
		{
			$count = (int) count($articles);
			$title = Inflector::pluralize('article', $count);
			Notify::success(Lang::txt('COM_CONTENT_N_ITEMS_DELETED', $count, $title));
		}
		$this->cancelTask();
	}

	public function cancelTask()
	{
		if ($this->_task == 'cancel')
		{
			$id = Request::getInt('id', 0);
			$article = Article::oneOrFail($id);
			$articleCheckedOut = $article->get('checked_out');
			if (User::getInstance()->get('id') == $articleCheckedOut)
			{
				$article->set('checked_out', null);
				$article->set('checked_out_time', null);
				$article->save();
			}
		}
		parent::cancelTask();
	}
}
