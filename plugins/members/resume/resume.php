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
 * @author    Alissa Nedossekina <alisa@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Members Plugin class for resumes
 */
class plgMembersResume extends \Hubzero\Plugin\Plugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 */
	protected $_autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param      object &$subject Event observer
	 * @param      array  $config   Optional config values
	 * @return     void
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$lang = JFactory::getLanguage();
		$lang->load('com_jobs');

		include_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_jobs' . DS . 'tables' . DS . 'admin.php');
		include_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_jobs' . DS . 'tables' . DS . 'application.php');
		include_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_jobs' . DS . 'tables' . DS . 'category.php');
		include_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_jobs' . DS . 'tables' . DS . 'employer.php');
		include_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_jobs' . DS . 'tables' . DS . 'job.php');
		include_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_jobs' . DS . 'tables' . DS . 'prefs.php');
		include_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_jobs' . DS . 'tables' . DS . 'resume.php');
		include_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_jobs' . DS . 'tables' . DS . 'seeker.php');
		include_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_jobs' . DS . 'tables' . DS . 'shortlist.php');
		include_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_jobs' . DS . 'tables' . DS . 'stats.php');
		include_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_jobs' . DS . 'tables' . DS . 'type.php');

		$this->config = JComponentHelper::getParams('com_jobs');
	}

	/**
	 * Event call to determine if this plugin should return data
	 *
	 * @param      object  $user   JUser
	 * @param      object  $member MembersProfile
	 * @return     array   Plugin name
	 */
	public function &onMembersAreas($user, $member)
	{
		// default areas returned to nothing
		$areas = array();

		// if this is the logged in user show them
		if ($user->get('id') == $member->get('uidNumber') || $this->isEmployer($user, $member))
		{
			$areas['resume'] = JText::_('PLG_MEMBERS_RESUME');
			$areas['icon'] = 'f016';
		}

		return $areas;
	}

	/**
	 * Check if a user has employer authorization
	 *
	 * @param      object $user       JUser
	 * @param      object $member     \Hubzero\User\Profile
	 * @return     integer 1 = authorized, 0 = not
	 */
	public function isEmployer($user=null, $member=null)
	{
		$database = JFactory::getDBO();
		$employer = new Employer($database);
		$juser = JFactory::getUser();

		// Check if they're a site admin (from Joomla)
		if ($juser->authorise('core.admin', 'com_members.component'))
		{
			return 1;
		}

		// determine who is veiwing the page
		$emp = 0;
		$emp = $employer->isEmployer($juser->get('id'));

		// check if they belong to a dedicated admin group
		if ($this->config->get('admingroup'))
		{
			$profile = \Hubzero\User\Profile::getInstance($juser->get('id'));
			$ugs = $profile->getGroups('all');
			if ($ugs && count($ugs) > 0)
			{
				foreach ($ugs as $ug)
				{
					if ($ug->cn == $this->config->get('admingroup'))
					{
						$emp = 1;
					}
				}
			}
		}

		if ($member)
		{
			$my =  $member->get('uidNumber') == $juser->get('id') ? 1 : 0;
			$emp = $my && $emp ? 0 : $emp;
		}

		return $emp;
	}

	/**
	 * Check if the user is part of the administration group
	 *
	 * @param      integer $admin Var to set
	 * @return     integer 1 = authorized, 0 = not
	 */
	public function isAdmin($admin = 0)
	{
		$juser = JFactory::getUser();

		// check if they belong to a dedicated admin group
		if ($this->config->get('admingroup'))
		{
			$profile = \Hubzero\User\Profile::getInstance($juser->get('id'));
			$ugs = $profile->getGroups('all');
			if ($ugs && count($ugs) > 0)
			{
				foreach ($ugs as $ug)
				{
					if ($ug->cn == $this->config->get('admingroup'))
					{
						$admin = 1;
					}
				}
			}
		}

		return $admin;
	}

	/**
	 * Event call to return data for a specific member
	 *
	 * @param      object  $user   JUser
	 * @param      object  $member MembersProfile
	 * @param      string  $option Component name
	 * @param      string  $areas  Plugins to return data
	 * @return     array   Return array of html
	 */
	public function onMembers($user, $member, $option, $areas)
	{
		$return = 'html';
		$active = 'resume';

		// Check if our area is in the array of areas we want to return results for
		if (is_array($areas))
		{
			if (!array_intersect($areas, $this->onMembersAreas($user, $member))
			 && !array_intersect($areas, array_keys($this->onMembersAreas($user, $member))))
			{
				// do nothing
			}
		}

		// The output array we're returning
		$arr = array(
			'html' => '',
			'metadata' => '',
			'searchresult' => ''
		);

		// Do we need to return any data?
		if ($return != 'html' && $return != 'metadata')
		{
			return $arr;
		}

		// Jobs component needs to be enabled
		if (!$this->config->get('component_enabled'))
		{
			$arr['html'] = '<p class="warning">' . JText::_('PLG_MEMBERS_RESUME_WARNING_DISABLED') . '</p>';
			return $arr;
		}

		// Get authorization
		$emp = $this->isEmployer($user, $member);

		// Are we returning HTML?
		if ($return == 'html'  && $areas[0] == 'resume')
		{
			$database = JFactory::getDBO();
			$juser = JFactory::getUser();

			$task = JRequest::getVar('action','');

			switch ($task)
			{
				case 'uploadresume': $arr['html'] = $this->_upload($database, $option, $member); break;
				case 'deleteresume': $arr['html'] = $this->_deleteresume($database, $option, $member, $emp);   break;
				case 'edittitle':    $arr['html'] = $this->_view($database, $option, $member, $emp, 1);   break;
				case 'savetitle':    $arr['html'] = $this->_save($database, $option, $member, $task, $emp);   break;
				case 'saveprefs':    $arr['html'] = $this->_save($database, $option, $member, $task, $emp);   break;
				case 'editprefs':    $arr['html'] = $this->_view($database, $option, $member, $emp, 0, $editpref = 2); break;
				case 'activate':     $arr['html'] = $this->_activate($database, $option, $member, $emp); break;
				case 'download':     $arr['html'] = $this->_download($member); break;
				case 'view':
				default: $arr['html'] = $this->_view($database, $option, $member, $emp, $edittitle = 0); break;
			}
		}
		else if ($emp)
		{
			//$arr['metadata'] = '<p class="resume"><a href="'.JRoute::_($member->getLink() . '&active=resume').'">'.ucfirst(JText::_('Resume')).'</a></p>' . "\n";
			$arr['metadata'] = '';
		}

		return $arr;
	}

	/**
	 * Save data
	 *
	 * @param      object  $database JDatabase
	 * @param      string  $option   Component name
	 * @param      object  $member   \Hubzero\User\Profile
	 * @param      string  $task     Task to perform
	 * @param      integer $emp      Is user employer?
	 * @return     string
	 */
	protected function _save($database, $option, $member, $task, $emp)
	{
		$lookingfor = JRequest::getVar('lookingfor','');
		$tagline    = JRequest::getVar('tagline','');
		$active     = JRequest::getInt('activeres', 0);
		$author     = JRequest::getInt('author', 0);
		$title      = JRequest::getVar('title','');

		if ($task == 'saveprefs')
		{
			$js = new JobSeeker($database);

			if (!$js->loadSeeker($member->get('uidNumber')))
			{
				$this->setError(JText::_('PLG_MEMBERS_RESUME_ERROR_PROFILE_NOT_FOUND'));
				return '';
			}

			if (!$js->bind($_POST))
			{
				echo $this->alert($js->getError());
				exit();
			}

			$js->active = $active;
			$js->updated = JFactory::getDate()->toSql();

			if (!$js->store())
			{
				echo $this->alert($js->getError());
				exit();
			}
		}
		else if ($task == 'savetitle' && $author && $title)
		{
			$resume = new Resume ($database);
			if ($resume->loadResume($author))
			{
				$resume->title = $title;
				if (!$resume->store())
				{
					echo $this->alert($resume->getError());
					exit();
				}
			}
		}

		return $this->_view($database, $option, $member, $emp);
	}

	/**
	 * Set a user as being a 'job seeker'
	 *
	 * @param      object  $database JDatabase
	 * @param      string  $option   Component name
	 * @param      object  $member   \Hubzero\User\Profile
	 * @param      integer $emp      Is user employer?
	 * @return     string
	 */
	protected function _activate($database, $option, $member, $emp)
	{
		// are we activating or disactivating?
		$active = JRequest::getInt('on', 0);

		$js = new JobSeeker($database);

		if (!$js->loadSeeker($member->get('uidNumber')))
		{
			$this->setError(JText::_('PLG_MEMBERS_RESUME_ERROR_PROFILE_NOT_FOUND'));
			return '';
		}
		else if (!$active)
		{
			$js->active = $active;
			$js->updated = JFactory::getDate()->toSql();

			// store new content
			if (!$js->store())
			{
				echo $js->getError();
				exit();
			}

			return $this->_view($database, $option, $member, $emp);
		}
		else
		{
			// ask to confirm/add search preferences
			return $this->_view($database, $option, $member, $emp, 0, 1);
		}
	}

	/**
	 * View user's resumes
	 *
	 * @param      object  $database  JDatabase
	 * @param      string  $option    Component name
	 * @param      object  $member    \Hubzero\User\Profile
	 * @param      integer $emp       Is user employer?
	 * @param      integer $edittitle Parameter description (if any) ...
	 * @param      integer $editpref  Parameter description (if any) ...
	 * @return     string
	 */
	protected function _view($database, $option, $member, $emp, $edittitle = 0, $editpref = 0)
	{
		$out = '';
		$juser = JFactory::getUser();
		$self = $member->get('uidNumber') == $juser->get('id') ? 1 : 0;

		// get job seeker info on the user
		$js = new JobSeeker($database);
		if (!$js->loadSeeker($member->get('uidNumber')))
		{
			// make a new entry
			$js = new JobSeeker($database);
			$js->uid = $member->get('uidNumber');
			$js->active = 0;

			// check content
			if (!$js->check())
			{
				echo $js->getError();
				exit();
			}

			// store new content
			if (!$js->store())
			{
				echo $js->getError();
				exit();
			}
		}

		$jt = new JobType($database);
		$jc = new JobCategory($database);

		// get active resume
		$resume = new Resume($database);
		$file = '';
		$path = $this->build_path($member->get('uidNumber'));

		if ($resume->loadResume($member->get('uidNumber')))
		{
			$file = JPATH_ROOT . $path . DS . $resume->filename;
			if (!is_file($file))
			{
				$file = '';
			}
		}

		// get seeker stats
		$jobstats = new JobStats($database);
		$stats = $jobstats->getStats($member->get('uidNumber'), 'seeker');

		$view = new \Hubzero\Plugin\View(
			array(
				'folder'  => $this->_type,
				'element' => $this->_name,
				'name'    => $this->_name
			)
		);
		$view->self   = $self;
		$view->js     = $js;
		$view->jt = $jt;
		$view->jc = $jc;
		$view->resume = $resume;
		$view->file   = $file;
		$view->stats  = $stats;
		$view->config = $this->config;
		$view->member = $member;
		$view->option = $option;
		$view->edittitle = $edittitle;
		$view->emp    = $emp;
		$view->editpref = $editpref;
		$view->path = $path;
		$view->params = $this->params;

		if ($this->getError())
		{
			foreach ($this->getErrors() as $error)
			{
				$view->setError($error);
			}
		}

		return $view->loadTemplate();
	}

	/**
	 * Build the path for uploading a resume to
	 *
	 * @param      integer $uid User ID
	 * @return     mixed False if errors, string otherwise
	 */
	public function build_path($uid)
	{
		// Get the configured upload path
		$base_path = $this->params->get('webpath', '/site/members');
		$base_path = DS . trim($base_path, DS);

		$dir = \Hubzero\Utility\String::pad($uid);

		$listdir = $base_path . DS . $dir;

		if (!is_dir(JPATH_ROOT . $listdir))
		{
			jimport('joomla.filesystem.folder');
			if (!JFolder::create(JPATH_ROOT . $listdir))
			{
				return false;
			}
		}

		// Build the path
		return $listdir;
	}

	/**
	 * Upload a resume
	 *
	 * @param      object $database JDatabase
	 * @param      string $option   Component name
	 * @param      object $member   \Hubzero\User\Profile
	 * @return     string
	 */
	protected function _upload($database, $option, $member)
	{
		$path = $this->build_path($member->get('uidNumber'));
		$emp = JRequest::getInt('emp', 0);

		if (!$path)
		{
			$this->setError(JText::_('PLG_MEMBERS_RESUME_SUPPORT_NO_UPLOAD_DIRECTORY'));
			return $this->_view($database, $option, $member, $emp);
		}

		// Check for request forgeries
		JRequest::checkToken('get') or JRequest::checkToken() or jexit('Invalid Token');

		// Incoming file
		$file = JRequest::getVar('uploadres', '', 'files', 'array');

		if (!$file['name'])
		{
			$this->setError(JText::_('PLG_MEMBERS_RESUME_SUPPORT_NO_FILE'));
			return $this->_view($database, $option, $member, $emp);
		}

		// Incoming
		$title = JRequest::getVar('title', '');
		$default_title = $member->get('firstname') ? $member->get('firstname') . ' ' . $member->get('lastname') . ' ' . ucfirst(JText::_('PLG_MEMBERS_RESUME_RESUME')) : $member->get('name') . ' ' . ucfirst(JText::_('PLG_MEMBERS_RESUME_RESUME'));
		$path = JPATH_ROOT.$path;

		// Replace file title with user name
		$file_ext = substr($file['name'], strripos($file['name'], '.'));
		$file['name']  = $member->get('firstname') ? $member->get('firstname') . ' ' . $member->get('lastname') . ' ' . ucfirst(JText::_('PLG_MEMBERS_RESUME_RESUME')) : $member->get('name') . ' ' . ucfirst(JText::_('PLG_MEMBERS_RESUME_RESUME'));
		$file['name'] .= $file_ext;

		// Make the filename safe
		jimport('joomla.filesystem.file');
		$file['name'] = JFile::makeSafe($file['name']);
		$file['name'] = str_replace(' ', '_', $file['name']);

		$ext = strtolower(JFile::getExt($file['name']));
		if (!in_array($ext, explode(',', $this->params->get('file_ext', 'jpg,jpeg,jpe,bmp,tif,tiff,png,gif,pdf,txt,rtf,doc,docx,ppt'))))
		{
			$this->setError(JText::_('Disallowed file type.'));
			return $this->_view($database, $option, $member, $emp);
		}

		$row = new Resume($database);

		if (!$row->loadResume($member->get('uidNumber')))
		{
			$row = new Resume($database);
			$row->id   = 0;
			$row->uid  = $member->get('uidNumber');
			$row->main = 1;
		}
		else if (file_exists($path . DS . $row->filename)) // remove prev file first
		{
			JFile::delete($path . DS . $row->filename);

			// Remove stats for prev resume
			$jobstats = new JobStats($database);
			$jobstats->deleteStats($member->get('uidNumber'), 'seeker');
		}

		// Perform the upload
		if (!JFile::upload($file['tmp_name'], $path . DS . $file['name']))
		{
			$this->setError(JText::_('ERROR_UPLOADING'));
		}
		else
		{
			$fpath = $path . DS . $file['name'];

			if (!JFile::isSafe($fpath))
			{
				JFile::delete($fpath);

				$this->setError(JText::_('File rejected because the anti-virus scan failed.'));
				return $this->_view($database, $option, $member, $emp);
			}

			// File was uploaded, create database entry
			$title = htmlspecialchars($title);
			$row->created = JFactory::getDate()->toSql();
			$row->filename = $file['name'];
			$row->title = $title ? $title : $default_title;

			if (!$row->check())
			{
				$this->setError($row->getError());
			}
			if (!$row->store())
			{
				$this->setError($row->getError());
			}
		}
		return $this->_view($database, $option, $member, $emp);
	}

	/**
	 * Delete a resume
	 *
	 * @param      object  $database JDatabase
	 * @param      string  $option   Component name
	 * @param      object  $member   \Hubzero\User\Profile
	 * @param      integer $emp      Is user employer?
	 * @return     string
	 */
	protected function _deleteresume($database, $option, $member, $emp)
	{
		$row = new Resume($database);
		if (!$row->loadResume($member->get('uidNumber')))
		{
			$this->setError(JText::_('Resume ID not found.'));
			return '';
		}

		// Incoming file
		$file = $row->filename;

		$path = $this->build_path($member->get('uidNumber'));

		if (!file_exists(JPATH_ROOT . $path . DS . $file) or !$file)
		{
			$this->setError(JText::_('FILE_NOT_FOUND'));
		}
		else
		{
			// Attempt to delete the file
			jimport('joomla.filesystem.file');
			if (!JFile::delete(JPATH_ROOT . $path . DS . $file))
			{
				$this->setError(JText::_('UNABLE_TO_DELETE_FILE'));
			}
			else
			{
				$row->delete();

				// Remove stats for prev resume
				$jobstats = new JobStats($database);
				$jobstats->deleteStats ($member->get('uidNumber'), 'seeker');

				// Do not include profile in search without a resume
				$js = new JobSeeker ($database);
				$js->loadSeeker($member->get('uidNumber'));
				$js->bind(array('active' => 0));
				if (!$js->store())
				{
					$this->setError($js->getError());
				}
			}
		}

		// Push through to the main view
		return $this->_view($database, $option, $member, $emp);
	}

	/**
	 * Show a shortlist
	 *
	 * @return     void
	 */
	public function onMembersShortlist()
	{
		$oid = JRequest::getInt('oid', 0);

		if ($oid)
		{
			$this->shortlist($oid, $ajax=1);
		}
	}

	/**
	 * Retrieve a shortlist for a user
	 *
	 * @param      integer $oid  List ID
	 * @param      integer $ajax Being displayed via AJAX?
	 * @return     void
	 */
	public function shortlist($oid, $ajax=0)
	{
		$juser = JFactory::getUser();
		if (!$juser->get('guest'))
		{
			$database = JFactory::getDBO();

			$shortlist = new Shortlist($database);
			$shortlist->loadEntry($juser->get('id'), $oid, 'resume');

			if (!$shortlist->id)
			{
				$shortlist->emp      = $juser->get('id');
				$shortlist->seeker   = $oid;
				$shortlist->added    = JFactory::getDate()->toSql();
				$shortlist->category = 'resume';
				$shortlist->check();
				$shortlist->store();
			}
			else
			{
				$shortlist->delete();
			}

			if ($ajax)
			{
				// get seeker info
				$js = new JobSeeker($database);
				$seeker = $js->getSeeker($oid, $juser->get('id'));

				$view = new \Hubzero\Plugin\View(
					array(
						'folder'  => $this->_type,
						'element' => $this->_name,
						'name'    => $this->_name,
						'layout'  => 'seeker'
					)
				);
				$view->seeker = $seeker[0];
				$view->emp = 1;
				$view->admin = 0;
				$view->option = 'com_members';
				$view->list = 1;
				$view->params = $this->params;
				$view->display();
			}
		}
	}

	/**
	 * Return javascript to generate an alert prompt
	 *
	 * @param      string $msg Message to show
	 * @return     string HTML
	 */
	public function alert($msg)
	{
		return "<script type=\"text/javascript\"> alert('" . $msg . "'); window.history.go(-1); </script>\n";
	}

	/**
	 * Generate a select form
	 *
	 * @param      string $name  Field name
	 * @param      array  $array Data to populate select with
	 * @param      mixed  $value Value to select
	 * @param      string $class Class to add
	 * @return     string HTML
	 */
	public function formSelect($name, $array, $value, $class='')
	{
		$out  = '<select name="' . $name . '" id="' . $name . '"';
		$out .= ($class) ? ' class="' . $class . '">' . "\n" : '>' . "\n";
		foreach ($array as $avalue => $alabel)
		{
			$selected = ($avalue == $value || $alabel == $value)
					  ? ' selected="selected"'
					  : '';
			$out .= ' <option value="' . $avalue . '"' . $selected . '>' . $alabel . '</option>' . "\n";
		}
		$out .= '</select>' . "\n";
		return $out;
	}

	/**
	 * Convert a timestamp to a more human readable string such as "3 days ago"
	 *
	 * @param      string $date Timestamp
	 * @return     string
	 */
	public static function nicetime($date)
	{
		if (empty($date))
		{
			return 'No date provided';
		}

		$periods = array('second', 'minute', 'hour', 'day', 'week', 'month', 'year', 'decade');
		$lengths = array('60', '60', '24', '7', '4.35', '12', '10');

		$now = strtotime(JFactory::getDate());
		$unix_date = strtotime($date);

		// check validity of date
		if (empty($unix_date))
		{
			return JText::_('Bad date');
		}

		// is it future date or past date
		if ($now > $unix_date) {
			$difference = $now - $unix_date;
			$tense = 'ago';

		}
		else
		{
			$difference = $unix_date - $now;
			//$tense = "from now";
			$tense = '';
		}

		for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++)
		{
			$difference /= $lengths[$j];
		}

		$difference = round($difference);

		if ($difference != 1)
		{
			$periods[$j] .= 's';
		}

		return "$difference $periods[$j] {$tense}";
	}

	/**
	 * Short description for 'download'
	 *
	 * Long description (if any) ...
	 *
	 * @param      mixed $member Parameter description (if any) ...
	 * @return     unknown Return description (if any) ...
	 */
	protected function _download($member)
	{
		$database = JFactory::getDBO();
		$juser    = JFactory::getUser();

		// Ensure we have a database object
		if (!$database)
		{
			JError::raiseError(500, JText::_('DATABASE_NOT_FOUND'));
			return;
		}

		// Incoming
		$uid = $member->get('uidNumber');

		// Load the resume
		$resume = new Resume($database);
		$file = '';
		$path = $this->build_path($uid);

		if ($resume->loadResume($uid))
		{
			$file = JPATH_ROOT . $path . DS . $resume->filename;
		}

		if (!is_file($file))
		{
			JError::raiseError(404, JText::_('FILE_NOT_FOUND'));
			return;
		}

		// Use user name as file name
		$default_title = $member->get('firstname') ? $member->get('firstname') . ' ' . $member->get('lastname') . ' ' . ucfirst(JText::_('Resume')) : $member->get('name') . ' ' . ucfirst(JText::_('Resume'));
		$default_title .= substr($resume->filename, strripos($resume->filename, '.'));;

		// Initiate a new content server and serve up the file
		$xserver = new \Hubzero\Content\Server();
		$xserver->filename($file);

		// record view
		$stats = new JobStats($database);
		if ($juser->get('id') != $uid)
		{
			$stats->saveView($uid, 'seeker');
		}

		$xserver->disposition('attachment');
		$xserver->acceptranges(false); // @TODO fix byte range support
		$xserver->saveas(stripslashes($resume->title));
		$result = $xserver->serve_attachment($file, stripslashes($default_title), false); // @TODO fix byte range support

		if (!$result)
		{
			JError::raiseError(500, JText::_('SERVER_ERROR'));
		}
		else
		{
			exit;
		}
	}
}

