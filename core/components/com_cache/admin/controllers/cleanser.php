<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @copyright Copyright 2005-2014 Open Source Matters, Inc.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 */

namespace Components\Cache\Admin\Controllers;

use Components\Cache\Helpers\Cache as Helper;
use Components\Cache\Models\Cache as Handler;
use Hubzero\Component\AdminController;
use Exception;
use Request;
use Route;
use Lang;
use App;

/**
 * Cache Controller
 */
class Cleanser extends AdminController
{
	/**
	 * Determine a task and execute it
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->model = new Handler();

		parent::execute();
	}

	/**
	 * Display
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Set the default view name and format from the Request.
		$vName = Request::getCmd('view', 'cache');

		// Get and render the view.
		switch ($vName)
		{
			case 'purge':
			break;

			case 'cache':
			default:
				$this->view->model      = $this->model;
				$this->view->data       = $this->model->getData();
				$this->view->client     = $this->model->getClient();
				$this->view->pagination = $this->model->getPagination();
				$this->view->state      = $this->model->getState();

				// Check for errors.
				if (count($errors = $this->model->getErrors()))
				{
					throw new Exception(implode("\n", $errors), 500);
				}
			break;
		}

		Helper::addSubmenu($vName);

		$this->view
			->setName($vName)
			->setLayout('default')
			->display();
	}

	/**
	 * Delete
	 *
	 * @return  void
	 */
	public function deleteTask()
	{
		// Check for request forgeries
		Request::checkToken() or exit(Lang::txt('JInvalid_Token'));

		$cid = Request::getVar('cid', array(), 'post', 'array');

		if (empty($cid))
		{
			throw new Exception(Lang::txt('JERROR_NO_ITEMS_SELECTED'), 500);
		}
		else
		{
			$this->model->cleanlist($cid);
		}

		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&client=' . $this->model->getClient()->id, false)
		);
	}

	/**
	 * Purge
	 *
	 * @return  void
	 */
	public function purgeTask()
	{
		// Check for request forgeries
		Request::checkToken() or exit(Lang::txt('JInvalid_Token'));

		$ret = $this->model->purge();

		$msg = Lang::txt('COM_CACHE_EXPIRED_ITEMS_HAVE_BEEN_PURGED');
		$msgType = 'message';

		if ($ret === false)
		{
			$msg = Lang::txt('Error purging expired items');
			$msgType = 'error';
		}

		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&view=purge', false),
			$msg,
			$msgType
		);
	}
}
