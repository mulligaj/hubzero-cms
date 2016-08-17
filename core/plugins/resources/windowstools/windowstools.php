<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
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
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// No direct access
defined('_HZEXEC_') or die();

require_once(PATH_CORE . DS . 'components' . DS . 'com_tools' . DS . 'helpers' . DS . 'utils.php');
require_once(PATH_CORE . DS . 'components' . DS . 'com_tools' . DS . 'tables' . DS . 'session.php');

/**
 * Resources Plugin class for Windows tools
 */
class plgResourcesWindowstools extends \Hubzero\Plugin\Plugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var  boolean
	 */
	protected $_autoloadLanguage = true;

	/**
	 * Generate a Windows tool invoke URL to redirect to
	 *
	 * @param   string  $option  Name of the component
	 * @return  void
	 */
	public function invoke($option)
	{
		$url = $this->generateInvokeUrl($option);

		$no_html = Request::getInt('no_html', 0);

		$response = new StdClass;
		$response->success = false;
		$response->message = Lang::txt('No invoke URL found.');

		if (User::isGuest())
		{
			$response->message = Lang::txt('Login is required to perform this action.');
			$url = '';
		}

		if ($url)
		{
			$response->success = true;
			$response->message = $url;

			if (!$no_html)
			{
				$rurl = $_SERVER['HTTP_REFERER'];

				print("<html><body><a id=\"runapplink\" href=\"$url\">Run app</a><script> document.getElementById('runapplink').click(); window.setTimeout(function(){ window.location = \"$rurl\"; },1000);</script><br>This page should go back ot the hub application page automatically. If it doesn't, click <a href='$rurl'>here.</a></html>");
				exit();
				App::redirect($url);
			}
		}

		if (!$no_html)
		{
			App::abort(404, Lang::txt('No invoke URL found.'));
		}

		echo json_encode($response);
		exit();
	}

	/**
	 * Generate a Windows tool invoke URL to redirect to
	 *
	 * @param   string  $option  Name of the component
	 * @return  string
	 */
	public function generateInvokeUrl($option, $appid = null)
	{
		$appid = ($appid ? $appid : Request::getVar('appid'));

		if (!$appid)
		{
			return '';
		}

		// Get summary usage data
		$startdate = new \DateTime('midnight first day of this month');
		$enddate   = new \DateTime('midnight first day of next month');

		$db = App::get('db');
		$sql  = 'SELECT truncate(sum(walltime)/60/60,3) as totalhours FROM `sessionlog` ';
		$sql .= 'WHERE start >' . $db->quote($startdate->format('Y-m-d H:i:s')) . ' ';
		$sql .= 'AND start <' . $db->quote($enddate->format('Y-m-d H:i:s'));
		$db->setQuery($sql);
		$totalUsageFigure = $db->loadObjectList();

		$params = Component::params('com_tools');
		$maxhours = $params->get('windows_monthly_max_hours', '100');

		if (floatval($totalUsageFigure[0]->totalhours) > floatval($maxhours))
		{
			return '';
		}

		// Get the middleware database
		$mwdb = \Components\Tools\Helpers\Utils::getMWDBO();

		// Get the session table
		$ms = new \Components\Tools\Tables\Session($mwdb);
		$ms->bind(array(
			'username' => User::get('username'),
			'remoteip' => $ip
		));

		// Save the entry
		$ms->store();

		// Get back the ID
		$sessionID = $ms->sessnum;

		// Opaque data
		$od = "username=" . User::get('username');
		$od = $od . ",email=" . User::get('email');
		$od = $od . ",userip=" . Request::ip();
		$od = $od . ",sessionid=" . $sessionID;
		$od = $od . ",ts=" . (new \DateTime())->format('Y.m.d.H.i.s');

		$eurl = exec("/usr/bin/hz-aws-appstream getentitlementurl --appid '" . $appid. "' --opaquedata '" . $od . "'");

		$url = rtrim($this->params->get('invoke_url', 'http://wapps.hubzero.org'), '/') . '/v1?standaloneUrl=' . $eurl;

		return $url;
	}

	/**
	 * Return the alias and name for this category of content
	 *
	 * @param   object  $resource  Current resource
	 * @return  array
	 */
	public function &onResourcesAreas($model)
	{
		$areas = array();

		if ($model->type->params->get('plg_' . $this->_name))
		{
			$areas['windowstools'] = Lang::txt('PLG_RESOURCES_WINDOWSTOOLS_SETUP');
		}

		return $areas;
	}

	/**
	 * Return data on a resource view (this will be some form of HTML)
	 *
	 * @param   object  $resource  Current resource
	 * @param   string  $option    Name of the component
	 * @param   array   $areas     Active area(s)
	 * @param   string  $rtrn      Data to be returned
	 * @return  array
	 */
	public function onResources($model, $option, $areas, $rtrn='all')
	{
		$arr = array(
			'area'     => $this->_name,
			'html'     => '',
			'metadata' => ''
		);

		// Check if our area is in the array of areas we want to return results for
		if (is_array($areas))
		{
			if (!array_intersect($areas, $this->onResourcesAreas($model))
			 && !array_intersect($areas, array_keys($this->onResourcesAreas($model))))
			{
				$rtrn = 'metadata';
			}
		}

		if ($rtrn == 'all' || $rtrn == 'html')
		{
			// Check admin access
			$isAuthorised = User::authorise('core.manage', 'com_resources');

			// Get the current page
			include_once(__DIR__ . DS . 'models' . DS . 'page.php');

			$page = Plugins\Resources\Windowstools\Models\Page::all()
				->whereIn('access', User::getAuthorisedViewLevels())
				->whereEquals('state', Plugins\Resources\Windowstools\Models\Page::STATE_PUBLISHED)
				->whereEquals('plugin', $this->_name)
				->order('ordering', 'asc')
				->row();

			if (!$page->get('id'))
			{
				if (file_exists(__DIR__ . DS . 'assets' . DS . 'txt' . DS . 'default.txt'))
				{
					$contents = file_get_contents(__DIR__ . DS . 'assets' . DS . 'txt' . DS . 'default.txt');

					$page->set('content', $contents);
					$page->set('title', Lang::txt('PLG_RESOURCES_WINDOWSTOOLS'));
					$page->set('state', Plugins\Resources\Windowstools\Models\Page::STATE_PUBLISHED);
					$page->set('plugin', $this->_name);
					$page->set('access', 1);
					$page->save();
				}
			}

			// Instantiate a view
			$view = $this->view('default', 'display')
				->set('option', $option)
				->set('resource', $model->resource)
				->set('page', $page)
				->set('name', $this->_name)
				->set('isAuthorised', $isAuthorised)
				->set('base', 'index.php?option=' . $option . '&' . ($model->resource->alias ? 'alias=' . $model->resource->alias : '&id=' . $model->resource->id) . '&active=' . $this->_name);

			$action = Request::getCmd('action');

			if ($action && $isAuthorised)
			{
				switch ($action)
				{
					case 'edit':
						// Show the edit form
						$view->setLayout('edit');
					break;

					case 'save':
						// Save changes
						// This will fall through to the default page
						Request::checkToken();

						$fields = Request::getVar('fields', array(), 'post', 'none', 2);

						$page->set($fields);

						if (!$page->save())
						{
							Notify::error($page->getError());
						}
					break;
				}
			}

			// Return the output
			$arr['html'] = $view->loadTemplate();
		}

		return $arr;
	}
}
