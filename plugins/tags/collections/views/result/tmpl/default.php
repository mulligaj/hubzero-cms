<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2014 Purdue University. All rights reserved.
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
 * @copyright Copyright 2005-2014 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>
<li class="collections-entry">
	<p class="title">
		<a href="<?php echo JRoute::_($this->entry->link()); ?>">
			<?php if ($title = $this->entry->get('title')) { ?>
				<?php echo $this->escape(stripslashes($title)); ?>
			<?php } else { ?>
				<?php echo JText::sprintf('PLG_TAGS_COLLECTIONS_POST_NUM', $this->entry->get('id')); ?>
			<?php } ?>
		</a>
	</p>
	<p class="details">
		<?php echo JText::_('PLG_TAGS_COLLECTIONS'); ?>
		<span>|</span>
		<?php echo $this->entry->created('date'); ?>
		<span>|</span>
		<?php echo JText::sprintf('PLG_TAGS_COLLECTIONS_POSTED_BY', '<cite><a href="' . JRoute::_('index.php?option=com_members&id=' . $this->entry->get('created_by')) . '">' . $this->escape(stripslashes($this->entry->creator('name'))) . '</a></cite>'); ?>
	</p>
	<?php if ($content = $this->entry->description('clean', 200)) { ?>
		<p><?php echo $content; ?></p>
	<?php } ?>
	<p class="href">
		<?php echo rtrim(JURI::base(), '/') . '/' . ltrim(JRoute::_($this->entry->link()), '/'); ?>
	</p>
</li>