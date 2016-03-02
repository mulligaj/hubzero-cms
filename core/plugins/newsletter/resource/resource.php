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
 * @author 		Kevin Wojkovich <kevinw@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined( '_HZEXEC_' ) or die();

use Components\Resources\Models\Orm\Resource;

require_once(PATH_CORE . DS . 'components' . DS . 'com_resources' . DS . 'models' . DS . 'orm' . DS . 'resource.php');

class plgNewsletterResource extends \Hubzero\Plugin\Plugin
{
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	public function onGetEnabledDigests()
	{
		$name = 'resource';
		return $name;
	}

	public function onGetLatest($num = 5, $dateField = 'created', $sort = 'DESC')
	{
		$model = Resource::getLatest($num, $dateField, $sort)->rows()->toObject();

		$objects = array();

		foreach ($model as $m)
		{
			$object = new stdClass;
			$object->title = $m->title;
			$object->body = htmlspecialchars_decode($m->introtext);
			$object->date = Date::of($m->publish_up)->toLocal("F j, Y");
			$object->path = 'resources/' . $m->id;
			$object->id = $m->id;

			array_push($objects, $object);
		}
		return $objects;
	}
}
