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
 * the GNU Lesser General Public License as state by the Free Software
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

switch ($this->action)
{
	case 'image':
		// Build the upload path
		$img_location  = JPATH_ROOT . DS . trim($this->config->get('uploadpath', '/site/courses'), DS);
		$img_location .= DS . 'badges' . DS . $this->badge->get('id') . DS;

		if (is_dir($img_location))
		{
			$file = JFolder::files($img_location, 'badge');

			if (isset($file[0]))
			{
				$img_location .= $file[0];
			}
			else
			{
				JError::raiseError(404, JText::_('COM_COURSES_FILE_NOT_FOUND'));
				return;
			}
		}
		else
		{
			JError::raiseError(404, JText::_('COM_COURSES_FILE_NOT_FOUND'));
			return;
		}

		// Initiate a new content server and serve up the file
		$xserver = new \Hubzero\Content\Server();
		$xserver->filename($img_location);
		$xserver->disposition('inline');
		$xserver->acceptranges(false);

		if (!$xserver->serve()) 
		{
			// Should only get here on error
			JError::raiseError(404, JText::_('COM_COURSES_SERVER_ERROR'));
		} 
		else 
		{
			exit;
		}
		return;
	break;

	case 'criteria':
		$title = "Badge Criteria";
		$body = "<div class=\"criteria-text\">\n";
		$body .= $this->badge->get('criteria_text') . "\n";
		$body .= "</div>\n";
	break;

	case 'validation':
		if (!$this->token)
		{
			JError::raiseError(421, 'Invalid request');
		}

		require_once JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_courses' . DS . 'tables' . DS . 'member.badge.php';
		require_once JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_courses' . DS . 'tables' . DS . 'member.php';
		require_once JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_courses' . DS . 'tables' . DS . 'section.badge.criteria.php';

		$memberBadge = new CoursesTableMemberBadge(JFactory::getDBO());
		$memberBadge->load(array('validation_token'=>$this->token));

		if (!$memberBadge->get('id'))
		{
			JError::raiseError(421, 'Invalid request');
		}

		$memberTbl = new CoursesTableMember(JFactory::getDBO());
		$memberTbl->loadByMemberId($memberBadge->member_id);
		$user_id = $memberTbl->get('user_id');

		$criteria = new CoursesTableSectionBadgeCriteria(JFactory::getDBO());
		$criteria->load($memberBadge->get('criteria_id'));

		$title = "Badge Validation";
		$body  = "<img class=\"badge-img\" src=\"".$this->badge->get('img_url')."\" width=\"125\" />\n";
		$body .= "<div class=\"badge-validation\">\n";
		$body .= "This serves to verify that " . JFactory::getUser($user_id)->get('name') . " \n";
		$body .= "has completed the requirements of this badge on " . JFactory::getDate($memberBadge->get('earned_on'))->format('M d, Y') . ", \n";
		$body .= "meeting the requirements of the badge at that time.  The requirements at the time of completion were:\n";
		$body .= "</div>\n";
		$body .= "<div class=\"badge-criteria\">\n";
		$body .= $criteria->get('text');
		$body .= "</div>\n";
	break;

	default:
		JError::raiseError(421, 'Invalid request');
	break;
}
?>

<div id="content-header" class="full">
	<h2><?php echo JText::_($title); ?></h2>
</div>

<div class="main section">
	<?php echo $body; ?>
</div>