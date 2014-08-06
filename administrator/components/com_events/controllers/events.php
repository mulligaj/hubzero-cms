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

/**
 * Events controller for entries
 */
class EventsControllerEvents extends \Hubzero\Component\AdminController
{
	/**
	 * Determine task and attempt to execute it
	 *
	 * @return     void
	 */
	public function execute()
	{
		$this->config = new EventsConfigs($this->database);
		$this->config->load();

		$tables = $this->database->getTableList();
		$table = $this->database->getPrefix() . 'events_respondent_race_rel';
		if (!in_array($table, $tables))
		{
			$this->database->setQuery("CREATE TABLE `#__events_respondent_race_rel` (
			  `respondent_id` int(11) default NULL,
			  `race` varchar(255) default NULL,
			  `tribal_affiliation` varchar(255) default NULL
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
			if (!$this->database->query())
			{
				echo $this->database->getErrorMsg();
				return false;
			}
		}

		/**
		 * Start day
		 */
		define('_CAL_CONF_STARDAY', $this->config->getCfg('starday'));

		/**
		 * Nav bar color
		 */
		define('_CAL_CONF_DEFCOLOR', $this->config->getCfg('navbarcolor'));

		parent::execute();
	}

	/**
	 * Display a list of entries
	 *
	 * @return     void
	 */
	public function displayTask()
	{
		// Get configuration
		$app = JFactory::getApplication();
		$config = JFactory::getConfig();

		// Incoming
		$this->view->filters = array();
		$this->view->filters['limit']    = $app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.limit',
			'limit',
			$config->getValue('config.list_limit'),
			'int'
		);
		$this->view->filters['start']    = $app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.limitstart',
			'limitstart',
			0,
			'int'
		);
		$this->view->filters['search']   = urldecode($app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.search',
			'search',
			''
		));
		$this->view->filters['catid']    = JRequest::getVar('catid', 0, '', 'int');
		$this->view->filters['scope_id'] = JRequest::getVar('group_id', 0, '', 'int');

		$ee = new EventsEvent($this->database);

		// Get a record count
		$this->view->total = $ee->getCount($this->view->filters);

		// Get records
		$this->view->rows = $ee->getRecords($this->view->filters);

		// Initiate paging
		jimport('joomla.html.pagination');
		$this->view->pageNav = new JPagination(
			$this->view->total,
			$this->view->filters['start'],
			$this->view->filters['limit']
		);

		// Get list of categories
		$categories[] = JHTML::_('select.option', '0', '- ' . JText::_('COM_EVENTS_CAL_LANG_EVENT_ALLCAT'), 'value', 'text');
		$this->database->setQuery("SELECT id AS value, title AS text FROM #__categories WHERE extension='$this->_option'");

		$categories = array_merge($categories, $this->database->loadObjectList());
		$this->view->clist = JHTML::_('select.genericlist', $categories, 'catid', 'class="inputbox"','value', 'text', $this->view->filters['catid'], false, false);

		//get list of groups
		$groups[] = JHTML::_('select.option', '0', '- ' . JText::_('COM_EVENTS_ALL_GROUPS'), 'value', 'text');
		$sql = "SELECT DISTINCT(g.gidNumber) AS value, g.description AS text
				FROM jos_events AS e, jos_xgroups AS g
				WHERE e.scope='group'
				AND e.scope_id=g.gidNumber";
		$this->database->setQuery($sql);
		$groups = array_merge($groups, $this->database->loadObjectList());
		$this->view->glist = JHTML::_('select.genericlist', $groups, 'group_id', 'class="inputbox"','value', 'text', $this->view->filters['scope_id'], false, false);

		// Set any errors
		if ($this->getError())
		{
			foreach ($this->getErrors() as $error)
			{
				$this->view->setError($error);
			}
		}

		// Output the HTML
		$this->view->display();
	}

	/**
	 * Show a form for adding an entry
	 *
	 * @return     void
	 */
	public function addpageTask()
	{
		$ids = JRequest::getVar('id', array(0));

		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=pages&task=add&id[]=' . $ids[0]
		);
	}

	/**
	 * Show a form for adding an entry
	 *
	 * @return     void
	 */
	public function respondentsTask()
	{
		$ids = JRequest::getVar('id', array(0));

		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=respondents&id[]=' . $ids[0]
		);
	}

	/**
	 * Show a form for adding an entry
	 *
	 * @return     void
	 */
	public function addTask()
	{
		$this->editTask();
	}

	/**
	 * Display a form for editing an entry
	 *
	 * @return     void
	 */
	public function editTask()
	{
		JRequest::setVar('hidemainmenu', 1);

		// Instantiate a new view
		$this->view->setLayout('edit');

		$this->view->config = $this->config;

		$config = JFactory::getConfig();
		$offset = $config->getValue('config.offset');

		// We need at least one category before we can proceed
		$cat = new EventsCategory($this->database);
		if ($cat->getCategoryCount($this->_option) < 1)
		{
			JError::raiseError(500, JText::_('COM_EVENTS_LANG_NEED_CATEGORY'));
			return;
		}

		// Incoming
		$ids = JRequest::getVar('id', array(), 'request');
		$id  = (isset($ids[0])) ? $ids[0] : 0;

		// Load the event object
		$this->view->row = new EventsEvent($this->database);
		$this->view->row->load($id);

		// Fail if checked out not by 'me'
		if ($this->view->row->checked_out
		 && $this->view->row->checked_out <> $this->juser->get('id'))
		{
			$this->_redirect = 'index.php?option=' . $this->_option;
			$this->_message = JText::_('COM_EVENTS_CAL_LANG_WARN_CHECKEDOUT');
		}

		$document = JFactory::getDocument();
		$document->addStyleSheet('..' . DS . 'components' . DS . $this->_option . DS . 'calendar.css');

		if ($this->view->row->id)
		{
			$this->view->row->checkout($this->juser->get('id'));

			if (trim($this->view->row->publish_down) == '0000-00-00 00:00:00')
			{
				$this->view->row->publish_down = JText::_('COM_EVENTS_CAL_LANG_NEVER');
			}

			$start_publish = JHTML::_('date', $this->view->row->publish_up, 'Y-m-d');
			$start_time = JHTML::_('date', $this->view->row->publish_up, 'H:i');

			$stop_publish = JHTML::_('date', $this->view->row->publish_down, 'Y-m-d');
			$end_time = JHTML::_('date', $this->view->row->publish_down, 'H:i');
		}
		else
		{
			$this->view->row->state = 0;
			$start_publish = JFactory::getDate()->format('Y-m-d');
			$stop_publish = JFactory::getDate()->format('Y-m-d');
			$start_time = "08:00";
			$end_time = "17:00";
		}

		// Get list of groups
		$this->database->setQuery("SELECT 0 AS value, 'Public' AS text");
		$groups = $this->database->loadObjectList();

		$this->view->fields = $this->config->getCfg('fields');
		if (!empty($this->view->fields))
		{
			for ($i=0, $n=count($this->view->fields); $i < $n; $i++)
			{
				// explore the text and pull out all matches
				array_push($this->view->fields[$i], $this->parseTag($this->view->row->content, $this->view->fields[$i][0]));
				// clean the original text of any matches
				$this->view->row->content = str_replace('<ef:' . $this->view->fields[$i][0] . '>' . end($this->view->fields[$i]) . '</ef:' . $this->view->fields[$i][0] . '>', '', $this->view->row->content);
			}
			$this->view->row->content = trim($this->view->row->content);
		}

		list($start_hrs, $start_mins) = explode(':', $start_time);
		list($end_hrs, $end_mins) = explode(':', $end_time);
		$start_pm = false;
		$end_pm = false;
		if ($this->config->getCfg('calUseStdTime') == 'YES')
		{
			$start_hrs = intval($start_hrs);
			if ($start_hrs >= 12) $start_pm = true;
			if ($start_hrs > 12) $start_hrs -= 12;
			else if ($start_hrs == 0) $start_hrs = 12;

			$end_hrs = intval($end_hrs);
			if ($end_hrs >= 12) $end_pm = true;
			if ($end_hrs > 12) $end_hrs -= 12;
			else if ($end_hrs == 0) $end_hrs = 12;
		}
		if (strlen($start_mins) == 1) $start_mins = '0' . $start_mins;
		if (strlen($start_hrs) == 1) $start_hrs = '0' . $start_hrs;
		$start_time = $start_hrs . ':' . $start_mins;
		if (strlen($end_mins) == 1) $end_mins = '0' . $end_mins;
		if (strlen($end_hrs) == 1) $end_hrs = '0' . $end_hrs;
		$end_time = $end_hrs . ':' . $end_mins;

		$this->view->times = array();
		$this->view->times['start_publish'] = $start_publish;
		$this->view->times['start_time'] = $start_time;
		$this->view->times['start_pm'] = $start_pm;
		$this->view->times['stop_publish'] = $stop_publish;
		$this->view->times['end_time'] = $end_time;
		$this->view->times['end_pm'] = $end_pm;

		// Get tags on this event
		$rt = new EventsTags($this->database);
		$this->view->tags = $rt->get_tag_string($this->view->row->id, 0, 0, NULL, 0, 1);

		// Set any errors
		if ($this->getError())
		{
			foreach ($this->getErrors() as $error)
			{
				$this->view->setError($error);
			}
		}

		// Output the HTML
		$this->view->display();
	}

	/**
	 * Short description for 'parseTag'
	 *
	 * Long description (if any) ...
	 *
	 * @param      unknown $text Parameter description (if any) ...
	 * @param      string $tag Parameter description (if any) ...
	 * @return     string Return description (if any) ...
	 */
	private function parseTag($text, $tag)
	{
		preg_match("#<ef:" . $tag . ">(.*?)</ef:" . $tag . ">#s", $text, $matches);
		if (count($matches) > 0)
		{
			$match = $matches[0];
			$match = str_replace('<ef:' . $tag . '>', '', $match);
			$match = str_replace('</ef:' . $tag . '>', '', $match);
		}
		else
		{
			$match = '';
		}
		return $match;
	}

	/**
	 * Cancel a task by redirecting to main page
	 *
	 * @return     void
	 */
	public function cancelTask()
	{
		// Incoming
		if (($id = JRequest::getInt('id', 0)))
		{
			// Check in the event
			$event = new EventsEvent($this->database);
			$event->load($id);
			$event->checkin();
		}

		// Redirect
		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=' . $this->_controller
		);
	}

	/**
	 * Save an entry
	 *
	 * @return     void
	 */
	public function saveTask()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		$config = JFactory::getConfig();
		$offset = $config->getValue('config.offset');

		$juser = JFactory::getUser();

		// Incoming
		$start_time = JRequest::getVar('start_time', '08:00', 'post');
		$start_pm   = JRequest::getInt('start_pm', 0, 'post');
		$end_time   = JRequest::getVar('end_time', '17:00', 'post');
		$end_pm     = JRequest::getInt('end_pm', 0, 'post');

		// Bind the posted data to an event object
		$row = new EventsEvent($this->database);
		if (!$row->bind($_POST))
		{
			JError::raiseError(500, $row->getError());
			return;
		}

		// New entry or existing?
		if ($row->id)
		{
			// Existing - update modified info
			$row->modified = JFactory::getDate()->toSql();
			if (!$juser->get('guest'))
			{
				$row->modified_by = $juser->get('id');
			}
		}
		else
		{
			// New - set created info
			$row->created = JFactory::getDate()->toSql();
			if (!$juser->get('guest'))
			{
				$row->created_by = $juser->get('id');
			}
		}

		// Set some fields and do some cleanup work
		if ($row->catid)
		{
			$row->catid = intval($row->catid);
		}

		//$row->title = $this->view->escape($row->title);

		//$row->content = JRequest::getVar('econtent', '', 'post');
		$row->content = $_POST['econtent'];
		$row->content = $this->_clean($row->content);

		// Get the custom fields defined in the events configuration
		$fields = JRequest::getVar('fields', array(), 'post');
		$fields = array_map('trim', $fields);
		// Wrap up the content of the field and attach it to the event content
		$fs = $this->config->fields;
		foreach ($fields as $param=>$value)
		{
			if (trim($value) != '')
			{
				$row->content .= '<ef:' . $param . '>' . $this->_clean($value) . '</ef:' . $param . '>';
			}
			else
			{
				foreach ($fs as $f)
				{
					if ($f[0] == $param && end($f) == 1)
					{
						echo EventsHtml::alert(JText::sprintf('EVENTS_REQUIRED_FIELD_CHECK', $f[1]));
						exit();
					}
				}
			}
		}

		// Clean adresse
		$row->adresse_info = $this->_clean($row->adresse_info);

		// Clean contact
		$row->contact_info = $this->_clean($row->contact_info);

		// Clean extra
		$row->extra_info = $this->_clean($row->extra_info);

		// Prepend http:// to URLs without it
		if ($row->extra_info != NULL)
		{
			if ((substr($row->extra_info, 0, 7) != 'http://')
			 && (substr($row->extra_info, 0, 8) != 'https://'))
			{
				$row->extra_info = 'http://' . $row->extra_info;
			}
		}

		// reformat the time into 24hr format if necessary
		if ($this->config->getCfg('calUseStdTime') =='YES')
		{
			list($hrs,$mins) = explode(':', $start_time);
			$hrs = intval($hrs);
			$mins = intval($mins);
			if ($hrs != 12 && $start_pm) $hrs += 12;
			else if ($hrs == 12 && !$start_pm) $hrs = 0;
			if ($hrs < 10) $hrs = '0' . $hrs;
			if ($mins < 10) $mins = '0' . $mins;
			$start_time = $hrs . ':' . $mins;

			list($hrs,$mins) = explode(':', $end_time);
			$hrs = intval($hrs);
			$mins = intval($mins);
			if ($hrs!= 12 && $end_pm) $hrs += 12;
			else if ($hrs == 12 && !$end_pm) $hrs = 0;
			if ($hrs < 10) $hrs = '0' . $hrs;
			if ($mins < 10) $mins = '0' . $mins;
			$end_time = $hrs . ':' . $mins;
		}

		// build local timezone
		$tz = new DateTimezone(JFactory::getConfig()->get('offset'));

		if ($row->publish_up)
		{
			$publishtime = $row->publish_up . ' ' . $start_time . ':00';
			$row->publish_up = JFactory::getDate($publishtime, $tz)->toSql();
		}
		else
		{
			$row->publish_up = JFactory::getDate()->toSql();
		}

		if ($row->publish_down)
		{
			$publishtime = $row->publish_down . ' ' . $end_time . ':00';
			$row->publish_down = JFactory::getDate($publishtime, $tz)->toSql();
		}


		// If this is a new event, publish it, otherwise retain its state
		if (!$row->id)
		{
			$row->state = 1;
		}

		// Get parameters
		$params = JRequest::getVar('params', '', 'post');
		if (is_array($params))
		{
			//email is reaquired
			$params['show_email'] = 1;
			$p = new JParameter(null);
			$p->loadArray($params);
			$row->params = $p->toString();
		}

		if (!$row->check())
		{
			JError::raiseError(500, $row->getError());
			return;
		}
		if (!$row->store())
		{
			JError::raiseError(500, $row->getError());
			return;
		}
		$row->checkin();

		// Incoming tags
		$tags = JRequest::getVar('tags', '', 'post');

		// Save the tags
		$rt = new EventsTags($this->database);
		$rt->tag_object($juser->get('id'), $row->id, $tags, 1, 0);

		// Redirect
		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
			JText::_('COM_EVENTS_CAL_LANG_SAVED')
		);
	}

	/**
	 * Strip some unwanted content.
	 * This includes script tags, HTML comments, sctyle tags, etc.
	 *
	 * @param      string $string String to clean
	 * @return     string
	 */
	private function _clean($string)
	{
		if (get_magic_quotes_gpc())
		{
			$string = stripslashes($string);
		}

		// strip out any KL_PHP, script, style, HTML comments
		$string = preg_replace('/{kl_php}(.*?){\/kl_php}/s', '', $string);
		$string = preg_replace("'<head[^>]*?>.*?</head>'si", '', $string);
		$string = preg_replace("'<body[^>]*?>.*?</body>'si", '', $string);
		$string = preg_replace("'<style[^>]*>.*?</style>'si", '', $string);
		$string = preg_replace("'<script[^>]*>.*?</script>'si", '', $string);
		$string = preg_replace('/<!--.+?-->/', '', $string);

		$string = str_replace(array("&amp;","&lt;","&gt;"), array("&amp;amp;","&amp;lt;","&amp;gt;",), $string);
		// fix &entitiy\n;

		$string = preg_replace('#(&\#*\w+)[\x00-\x20]+;#u', "$1;", $string);
		$string = preg_replace('#(&\#x*)([0-9A-F]+);*#iu', "$1$2;", $string);
		$string = html_entity_decode($string, ENT_COMPAT, "UTF-8");

		// remove any attribute starting with "on" or xmlns
		$string = preg_replace('#(<[^>]+[\x00-\x20\"\'])(on|xmlns)[^>]*>#iUu', "$1>", $string);
		// remove javascript: and vbscript: protocol
		$string = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*)[\\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu', '$1=$2nojavascript...', $string);
		$string = preg_replace('#([a-z]*)[\x00-\x20]*=([\'\"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu', '$1=$2novbscript...', $string);
		//<span style="width: expression(alert('Ping!'));"></span>
		// only works in ie...
		$string = preg_replace('#(<[^>]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*expression[\x00-\x20]*\([^>]*>#iU', "$1>", $string);
		$string = preg_replace('#(<[^>]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*behaviour[\x00-\x20]*\([^>]*>#iU', "$1>", $string);
		$string = preg_replace('#(<[^>]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*>#iUu', "$1>", $string);
		//remove namespaced elements (we do not need them...)
		$string = preg_replace('#</*\w+:\w[^>]*>#i', '', $string);
		//remove really unwanted tags
		do {
			$oldstring = $string;
			$string = preg_replace('#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', '', $string);
		} while ($oldstring != $string);

		return $string;
	}

	/**
	 * Publish one or more entries
	 *
	 * @return     void
	 */
	public function publishTask()
	{
		// Incoming
		$ids = JRequest::getVar('id', array());
		if (!is_array($ids))
		{
			$ids = array();
		}

		// Make sure we have an ID
		if (empty($ids))
		{
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller
			);
			return;
		}

		// Instantiate an event object
		$event = new EventsEvent($this->database);

		// Loop through the IDs and publish the event
		foreach ($ids as $id)
		{
			$event->publish($id);
		}

		// Redirect
		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
			JText::_('COM_EVENTS_CAL_LANG_PUBLISHED')
		);
	}

	/**
	 * Unpublish one or more entries
	 *
	 * @return     void
	 */
	public function unpublishTask()
	{
		// Incoming
		$ids = JRequest::getVar('id', array());
		if (!is_array($ids))
		{
			$ids = array();
		}

		// Make sure we have an ID
		if (empty($ids))
		{
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller
			);
			return;
		}

		// Instantiate an event object
		$event = new EventsEvent($this->database);

		// Loop through the IDs and unpublish the event
		foreach ($ids as $id)
		{
			$event->unpublish($id);
		}

		// Redirect
		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
			JText::_('COM_EVENTS_CAL_LANG_UNPUBLISHED')
		);
	}

	/**
	 * Set the event type
	 *
	 * @return     void
	 */
	public function settypeTask()
	{
		// Incoming
		$ids = JRequest::getVar('id', array());
		if (!is_array($ids))
		{
			$ids = array();
		}

		// Make sure we have an ID
		if (empty($ids))
		{
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller
			);
			return;
		}

		switch (strtolower(JRequest::getVar('type', 'event')))
		{
			case 'announcement':
				$v = 1;
			break;

			case 'event':
			default:
				$v = 0;
			break;
		}

		// Loop through the IDs and publish the event
		foreach ($ids as $id)
		{
			// Instantiate an event object
			$event = new EventsEvent($this->database);
			$event->load($id);
			$event->announcement = $v;
			if (!$event->store())
			{
				JError::raiseError(500, $event->getError());
				return;
			}
		}

		// Redirect
		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=' . $this->_controller
		);
	}

	/**
	 * Remove one or more entries for an event
	 *
	 * @return     void
	 */
	public function removeTask()
	{
		// Incoming
		$ids = JRequest::getVar('id', array());
		if (!is_array($ids))
		{
			$ids = array();
		}

		// Make sure we have an ID
		if (empty($ids))
		{
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller
			);
			return;
		}

		// Instantiate an event object
		$event = new EventsEvent($this->database);

		// Instantiate an event tags object
		$rt = new EventsTags($this->database);

		// Instantiate a page object
		$ep = new EventsPage($this->database);

		// Instantiate a respondent object
		$er = new EventsRespondent(array());

		// Loop through the IDs and unpublish the event
		foreach ($ids as $id)
		{
			// Delete tags on this event
			$rt->remove_all_tags($id);

			// Delete the event
			$event->delete($id);

			// Delete any associated pages
			$ep->deletePages($id);

			// Delete any associated respondents
			$er->deleteRespondents($id);
		}

		// Redirect
		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
			JText::_('COM_EVENTS_CAL_LANG_REMOVED')
		);
	}
}

