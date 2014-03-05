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
 * @author    Sam Wilson <samwilson@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ROOT . DS . 'components' . DS . 'com_forum' . DS . 'models' . DS . 'post.php';
require_once JPATH_ROOT . DS . 'components' . DS . 'com_forum' . DS . 'models' . DS . 'category.php';
require_once JPATH_ROOT . DS . 'components' . DS . 'com_forum' . DS . 'models' . DS . 'section.php';

$juri    = JURI::getInstance();
$jconfig = JFactory::getConfig();
?>

Instructor Digest Course Update: <?php echo $this->course->get('title'); ?>

=======================

Enrollments
<?php echo $this->enrollments; ?> total
<?php echo $this->passing; ?> passing
<?php echo $this->failing; ?> failing

=======================

Discussion Topics
<?php echo $this->posts_cnt; ?> total
<?php echo $this->latest_cnt; ?> new

=======================

Latest Discussions:
<?php if (count($this->latest) > 0) : ?>
<?php foreach ($this->latest as $post) : ?>
----------------------------------------
<?php $postObj = ForumModelPost::getInstance($post->id); ?>
<?php echo JFactory::getUser($post->created_by)->get('name'); ?> | created: <?php echo JHTML::_('date', $post->created, 'M j, Y g:i:s a') . "\n"; ?>
<?php echo $postObj->content('raw') . "\n"; ?>
----------------------------------------

<?php endforeach; ?>
<?php else : ?>
No new comments to display

<?php endif; ?>
<?php echo $juri->root(); ?> sent this email because you are the primary instructor of a course. Visit our <?php echo $juri->root(); ?>legal/privacy and our <?php echo $juri->root(); ?>support pages if you have any questions.