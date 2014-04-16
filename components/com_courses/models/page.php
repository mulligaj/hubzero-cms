<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
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
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_courses' . DS . 'tables' . DS . 'page.php');
require_once(JPATH_ROOT . DS . 'components' . DS . 'com_courses' . DS . 'models' . DS . 'abstract.php');

/**
 * Courses model class for a course
 */
class CoursesModelPage extends CoursesModelAbstract
{
	/**
	 * JTable class name
	 * 
	 * @var string
	 */
	protected $_tbl_name = 'CoursesTablePage';

	/**
	 * Model context
	 * 
	 * @var string
	 */
	protected $_context = 'com_courses.page.content';

	/**
	 * Object scope
	 * 
	 * @var string
	 */
	protected $_scope = 'page';

	/**
	 * Get the state of the entry as either text or numerical value
	 * 
	 * @param      string  $as      Format to return state in [text, number]
	 * @param      integer $shorten Number of characters to shorten text to
	 * @return     mixed String or Integer
	 */
	public function content($as='parsed', $shorten=0)
	{
		$as = strtolower($as);

		switch ($as)
		{
			case 'parsed':
				if ($content = $this->get('content_parsed'))
				{
					if ($shorten)
					{
						$content = \Hubzero\Utility\String::truncate($content, $shorten, array('html' => true));
					}
					return $content;
				}

				$config = array(
					'option'   => JRequest::getCmd('option', 'com_courses'),
					'scope'    => JRequest::getVar('gid', ''),
					'pagename' => $this->get('url'),
					'pageid'   => '',
					'filepath' => DS . ltrim($this->config()->get('uploadpath', '/site/courses'), DS) . DS . $this->get('course_id') . DS . 'pagefiles' . ($this->get('offering_id') ? DS . $this->get('offering_id') : ''),
					'domain'   => $this->get('course_id')
				);
				if ($this->get('offering_id'))
				{
					$config['scope'] = CoursesModelCourse::getInstance($this->get('course_id'))->get('alias') . DS . CoursesModelOffering::getInstance($this->get('offering_id'))->get('alias') . DS . 'pages';
				}
				if ($this->get('section_id'))
				{
					$config['filepath'] = DS . trim($this->config()->get('uploadpath', '/site/courses'), DS) . DS . $this->get('course_id') . DS . 'sections' . DS . $this->get('section_id') . DS . 'pagefiles';
				}

				$content = stripslashes($this->get('content'));
				$this->importPlugin('content')->trigger('onContentPrepare', array(
					$this->_context,
					&$this,
					&$config
				));

				$this->set('content_parsed', $this->get('content'));
				$this->set('content', $content);

				return $this->content($as, $shorten);
			break;

			case 'clean':
				$content = strip_tags($this->content('parsed'));
				if ($shorten)
				{
					$content = \Hubzero\Utility\String::truncate($content, $shorten);
				}
				return $content;
			break;

			case 'raw':
			default:
				$content = stripslashes($this->get('content'));
				$content = preg_replace('/^(<!-- \{FORMAT:.*\} -->)/i', '', $content);
				if ($shorten)
				{
					$content = \Hubzero\Utility\String::truncate($content, $shorten);
				}
				return $content;
			break;
		}
	}
}

