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
 * @author    Christopher Smoak <csmoak@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Newsletter\Api\Controllers;

use Components\Newsletter\Tables\Newsletter;
use Hubzero\Component\ApiController;
use Exception;
use stdClass;
use Request;
use Route;
use Lang;

$base = dirname(dirname(__DIR__)) . DS . 'tables' . DS;

require_once($base . 'newsletter.php');
require_once($base . 'template.php');
require_once($base . 'primary.php');
require_once($base . 'secondary.php');

/**
 * API controller class for newsletters
 */
class Newslettersv1_0 extends ApiController
{
	/**
	 * Return data for the current newsletter
	 *
	 * @apiMethod GET
	 * @apiUri    /newsletters/current
	 * @return    void
	 */
	public function currentTask()
	{
		$result = array();

		$database = \App::get('db');
		$newsletterNewsletter = new Newsletter($database);

		// get the current newsletter
		$newsletter = $newsletterNewsletter->getCurrentNewsletter();

		// build the newsletter based on campaign
		$result['id']      = $newsletter->issue;
		$result['title']   = $newsletter->name;
		$result['content'] = $newsletterNewsletter->buildNewsletter($newsletter);

		$obj = new stdClass();
		$obj->newsletter = $result;

		$this->send($obj);
	}

	/**
	 * Return data for newsletters
	 *
	 * @apiMethod GET
	 * @apiUri    /newsletters/list
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       5
	 * }
	 * @apiParameter {
	 * 		"name":          "start",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @return    void
	 */
	public function listTask()
	{
		$limit = Request::getInt('limit', 5);
		$start = Request::getInt('start', 0);

		$database = \App::get('db');
		$newsletterNewsletter = new Newsletter($database);

		$newsletters = $newsletterNewsletter->getNewsletters(null, true);

		$obj = new stdClass();
		$obj->newsletters = $newsletters;

		$this->send($obj);
	}

	/**
	 * Return data for past newsletters
	 *
	 * @apiMethod GET
	 * @apiUri    /newsletters/archive
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result to return.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       5
	 * }
	 * @apiParameter {
	 * 		"name":          "start",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @return    void
	 */
	public function archiveTask()
	{
		$limit = Request::getInt('limit', 5);
		$start = Request::getInt('start', 0);

		$result = array();

		$database = \App::get('db');
		$newsletterNewsletter = new Newsletter($database);

		// get newsletters
		$newsletters = $newsletterNewsletter->getNewsletters();

		// add newsletter details to return array
		foreach ($newsletters as $k => $newsletter)
		{
			$result[$k]['id']      = $newsletter->issue;
			$result[$k]['title']   = $newsletter->name;
			$result[$k]['content'] = $newsletterNewsletter->buildNewsletter($newsletter);
		}

		$obj = new stdClass();
		$obj->newsletters = $result;

		$this->send($obj);
	}
}
