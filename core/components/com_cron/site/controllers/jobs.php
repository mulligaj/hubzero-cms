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

namespace Components\Cron\Site\Controllers;

use Components\Cron\Models\Manager;
use Hubzero\Component\SiteController;
use Request;
use User;
use Date;
use Event;
use stdClass;

/**
 * Controller class for bulletin boards
 */
class Jobs extends SiteController
{
	/**
	 * Determines task being called and attempts to execute it
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->registerTask('display', 'tick');

		parent::execute();
	}

	/**
	 * Display a list of latest whiteboard entries
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		if (!User::authorise('core.manage', $this->_option))
		{
			$ip = Request::ip();

			$ips = explode(',', $this->config->get('whitelist',''));
			$ips = array_map('trim', $ips);

			if (!in_array($ip, $ips))
			{
				$ips = gethostbynamel($_SERVER['SERVER_NAME']);

				if (!in_array($ip, $ips))
				{
					$ips = gethostbynamel('localhost');

					if (!in_array($ip, $ips))
					{
						header("HTTP/1.1 404 Not Found");
						exit();
					}
				}
			}
		}

		Request::setVar('no_html', 1);
		Request::setVar('tmpl', 'component');

		$model = new Manager();

		$filters = array(
			'state'     => 1,
			'available' => true,
			'next_run'  => Date::toLocal('Y-m-d H:i:s')
		);

		$output = new stdClass;
		$output->jobs = array();

		if ($results = $model->jobs('list', $filters))
		{
			foreach ($results as $job)
			{
				if ($job->get('active') || !$job->isAvailable())
				{
					continue;
				}

				// Show related content
				$job->mark('start_run');

				$results = Event::trigger('cron.' . $job->get('event'), array($job));
				if ($results && is_array($results))
				{
					// Set it as active in case there were multiple plugins called on
					// the event. This is to ensure ALL processes finished.
					$job->set('active', 1);
					$job->store();

					foreach ($results as $result)
					{
						if ($result)
						{
							$job->set('active', 0);
						}
					}
				}

				$job->mark('end_run');
				$job->set('last_run', Date::toLocal('Y-m-d H:i:s')); //Date::toSql());
				$job->set('next_run', $job->nextRun());
				$job->store();

				$output->jobs[] = $job->toArray();
			}
		}

		$this->view
			->set('no_html', Request::getInt('no_html', 0))
			->set('output', $output)
			->display();
	}
}
