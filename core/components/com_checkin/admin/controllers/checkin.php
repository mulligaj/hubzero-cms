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

namespace Components\Checkin\Admin\Controllers;

use Hubzero\Component\AdminController;
use Exception;
use Submenu;
use Request;
use Route;
use Lang;
use App;

/**
 * Checkin Controller
 */
class Checkin extends AdminController
{
	/**
	 * Determine a task and execute it
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->model = new \Components\Checkin\Models\Checkin();

		parent::execute();
	}

	/**
	 * Display admin control panel
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Load the submenu.
		$this->addSubmenu(Request::getWord('option', 'com_checkin'));

		$this->view->items      = $this->model->getItems();
		$this->view->pagination = $this->model->getPagination();
		$this->view->state      = $this->model->getState();

		// Check for errors.
		if (count($errors = $this->model->getErrors()))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->view
			->setLayout('default')
			->display();
	}

	/**
	 * Checkin items
	 *
	 * @return  void
	 */
	public function checkinTask()
	{
		// Check for request forgeries
		Request::checkToken() or exit(Lang::txt('JInvalid_Token'));

		// Initialise variables.
		$ids = Request::getVar('cid', array(), '', 'array');

		$msg = null;

		if (empty($ids))
		{
			throw new Exception(Lang::txt('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'), 500);
		}
		else
		{
			// Checked in the items.
			$msg = Lang::txts('COM_CHECKIN_N_ITEMS_CHECKED_IN', $this->model->checkin($ids));
		}

		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false),
			($msg ?: null)
		);
	}

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 * @return  void
	 */
	protected function addSubmenu($vName)
	{
		Submenu::addEntry(
			Lang::txt('JGLOBAL_SUBMENU_CHECKIN'),
			Route::url('index.php?option=com_checkin'),
			$vName == 'com_checkin'
		);
		Submenu::addEntry(
			Lang::txt('JGLOBAL_SUBMENU_CLEAR_CACHE'),
			Route::url('index.php?option=com_cache'),
			$vName == 'cache'
		);
		Submenu::addEntry(
			Lang::txt('JGLOBAL_SUBMENU_PURGE_EXPIRED_CACHE'),
			Route::url('index.php?option=com_cache&view=purge'),
			$vName == 'purge'
		);
	}
}
