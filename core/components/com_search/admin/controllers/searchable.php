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

namespace Components\Search\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\Search\Models\Solr\Blacklist;
use Components\Search\Models\Solr\Facet;
use Components\Search\Models\Solr\SearchComponent;
use \Hubzero\Search\Query;
use \Hubzero\Search\Index;
use Components\Search\Helpers\SolrHelper;
use Components\Search\Helpers\DiscoveryHelper;
use Components\Developer\Models\Application;
use Hubzero\Access\Group as Accessgroup;
use stdClass;
use Hubzero\Utility\Inflector as Inflector;

require_once Component::path('com_search') . DS . 'helpers' . DS . 'solr.php';
require_once Component::path('com_search') . DS . 'models' . DS . 'solr' . DS . 'blacklist.php';
require_once Component::path('com_search') . DS . 'models' . DS . 'solr' . DS . 'searchcomponent.php';
require_once Component::path('com_search') . DS . 'models' . DS . 'solr' . DS . 'facet.php';
require_once Component::path('com_developer') . DS . 'models' . DS . 'application.php';

/**
 * Search AdminController Class
 */
class Searchable extends AdminController
{

	/**
	 * Manage facets
	 * 
	 * @return  void
	 */
	public function displayTask()
	{
		// Load the subfacets, if applicable
		$components = SearchComponent::all()
			->rows();
		$this->view
			->set('components', $components)
			->display();
	}

	public function activateIndexTask()
	{
		$ids = Request::getArray('id', array());
		$components = SearchComponent::all()
			->whereIn('id', $ids)
			->where('state', 'IS', null)
			->orWhereEquals('state', 0)
			->rows();
		foreach ($components as $component)
		{
			$results = $component->getSearchResults();
			if (!empty($results))
			{
				$newQuery = new \Hubzero\Search\Index($this->config);
				$newQuery->index($results);
				$component->set('state', 1);
				$date = Date::of()->toSql();
				$component->set('indexed', $date);
				if ($component->save())
				{
					Notify::success('Successfully indexed ' . $component->name); 
				}
			}
		}

		App::redirect(Route::url('index.php?option=' . $this->_option . '&controller=searchable', false));
	}

	public function deleteIndexTask()
	{
		$ids = Request::getArray('id', array());
		$components = SearchComponent::all()
			->whereIn('id', $ids)
			->whereEquals('state', 1)
			->rows();
		foreach ($components as $component)
		{
			$searchIndex = new \Hubzero\Search\Index($this->config);
			$componentName = Inflector::singularize($component->name);
			$deleteQuery = array('hubtype' => $componentName);
			$searchIndex->delete($deleteQuery);
			$component->set('state', 0);
			$date = Date::of()->toSql();
			$component->set('indexed', null);
			if ($component->save())
			{
				Notify::success('Successfully removed items from index' . $component->name); 
			}
		}

		App::redirect(Route::url('index.php?option=' . $this->_option . '&controller=searchable', false));
		
	}	
	public function discoverTask()
	{
		$componentModel = new \Components\Search\Models\Solr\SearchComponent();
		$components = $componentModel->getNewComponents();
		if ($components->count() > 0)
		{
			if ($components->save())
			{
				\Notify::success('New Searchable Components found');
			}
		}
		else
		{
			\Notify::warning('No new components found.');
		}

		$resourceSearchResults = \Components\Resources\Models\Orm\Resource::searchResults();
		App::redirect(
			Route::url('index.php?option=com_search&task=display&controller=searchable', false)
		);
	}
}
