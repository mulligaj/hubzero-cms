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

$likes = 0;
if ($this->rows->total() > 0) 
{
	foreach ($this->rows as $row)
	{
		$likes += $row->get('positive', 0);
	}
}

$this->juser = JFactory::getUser();

$base = 'index.php?option=' . $this->option;
?>
<div id="content-header">
	<h2><?php echo JText::_('COM_COLLECTIONS'); ?></h2>
</div>

<div id="content-header-extra">
	<ul>
		<li>
			<a class="icon-info about btn" href="<?php echo JRoute::_($base . '&controller=' . $this->controller . '&task=about'); ?>">
				<span><?php echo JText::_('COM_COLLECTIONS_GETTING_STARTED'); ?></span>
			</a>
		</li>
	</ul>
</div>

<form method="get" action="<?php echo JRoute::_($base . '&controller=' . $this->controller . '&task=' . $this->task); ?>" id="collections">
	<fieldset class="filters">
		<div class="filters-inner">
			<ul>
				<li>
					<a class="collections count" href="<?php echo JRoute::_($base . '&controller=' . $this->controller . '&task=all'); ?>">
						<span><?php echo JText::sprintf('COM_COLLECTIONS_HEADER_NUM_COLLECTIONS', $this->collections); ?></span>
					</a>
				</li>
				<li>
					<a class="posts count active" href="<?php echo JRoute::_($base . '&controller=' . $this->controller . '&task=posts'); ?>">
						<span><?php echo JText::sprintf('COM_COLLECTIONS_HEADER_NUM_POSTS', $this->total); ?></span>
					</a>
				</li>
			</ul>
			<div class="clear"></div>
			<p>
				<label for="filter-search">
					<span><?php echo JText::_('COM_COLLECTIONS_SEARCH_LABEL'); ?></span>
					<input type="text" name="search" id="filter-search" value="<?php echo $this->escape($this->filters['search']); ?>" placeholder="<?php echo JText::_('COM_COLLECTIONS_SEARCH_PLACEHOLDER'); ?>" />
				</label>
				<input type="submit" class="filter-submit" value="<?php echo JText::_('COM_COLLECTIONS_GO'); ?>" />
			</p>
		</div><!-- / .filters-inner -->
	</fieldset>

	<div class="main section">
		<div id="posts" data-base="<?php echo JURI::base(true); ?>">
<?php 
if ($this->rows->total() > 0) 
{
	foreach ($this->rows as $row)
	{
		$item = $row->item();
?>
		<div class="post <?php echo $item->type(); ?>" id="b<?php echo $row->get('id'); ?>" data-id="<?php echo $row->get('id'); ?>" data-closeup-url="<?php echo JRoute::_($base . '&controller=posts&post=' . $row->get('id')); ?>" data-width="600" data-height="350">
			<div class="content">
				<?php
					$view = new JView(
						array(
							'name'    => 'posts',
							'layout'  => 'display_' . $item->type()
						)
					);
					$view->option     = $this->option;
					$view->params     = $this->config;
					$view->row        = $row;
					$view->display();
				?>
			<?php if (count($item->tags()) > 0) { ?>
				<div class="tags-wrap">
					<?php echo $item->tags('render'); ?>
				</div>
			<?php } ?>
				<div class="meta">
					<p class="stats">
						<span class="likes">
							<?php echo JText::sprintf('COM_COLLECTIONS_NUM_LIKES', $item->get('positive', 0)); ?>
						</span>
						<span class="comments">
							<?php echo JText::sprintf('COM_COLLECTIONS_NUM_COMMENTS', $item->get('comments', 0)); ?>
						</span>
						<span class="reposts">
							<?php echo JText::sprintf('COM_COLLECTIONS_NUM_REPOSTS', $item->get('reposts', 0)); ?>
						</span>
					</p>
				<?php if (!$this->juser->get('guest')) { ?>
					<div class="actions">
				<?php if ($row->get('created_by') == $this->juser->get('id')) { ?>
						<a class="edit" data-id="<?php echo $row->get('id'); ?>" href="<?php echo JRoute::_($base . '&controller=posts&post=' . $row->get('id') . '&task=edit'); ?>">
							<span><?php echo JText::_('COM_COLLECTIONS_EDIT'); ?></span>
						</a>
				<?php } else { ?>
						<a class="vote <?php echo ($item->get('voted')) ? 'unlike' : 'like'; ?>" data-id="<?php echo $item->get('id'); ?>" data-text-like="<?php echo JText::_('COM_COLLECTIONS_LIKE'); ?>" data-text-unlike="<?php echo JText::_('COM_COLLECTIONS_UNLIKE'); ?>" href="<?php echo JRoute::_($base . '&controller=posts&post=' . $row->get('id') . '&task=vote'); ?>">
							<span><?php echo ($item->get('voted')) ? JText::_('COM_COLLECTIONS_UNLIKE') : JText::_('COM_COLLECTIONS_LIKE'); ?></span>
						</a>
				<?php } ?>
						<a class="comment" data-id="<?php echo $row->get('id'); ?>" href="<?php echo JRoute::_($base . '&controller=posts&post=' . $row->get('id') . '&task=comment'); ?>">
							<span><?php echo JText::_('COM_COLLECTIONS_COMMENT'); ?></span>
						</a>
						<a class="repost" data-id="<?php echo $row->get('id'); ?>" href="<?php echo JRoute::_($base . '&controller=posts&post=' . $row->get('id') . '&task=collect'); ?>">
							<span><?php echo JText::_('COM_COLLECTIONS_COLLECT'); ?></span>
						</a>
					</div><!-- / .actions -->
					<?php } ?>
				</div><!-- / .meta -->
				<div class="convo attribution clearfix">
					<a href="<?php echo JRoute::_('index.php?option=com_members&id=' . $row->creator('id') . '&active=collections'); ?>" title="<?php echo $this->escape(stripslashes($row->creator('name'))); ?>" class="img-link">
						<img src="<?php echo $row->creator()->getPicture(); ?>" alt="<?php echo JText::sprintf('COM_COLLECTIONS_PROFILE_PICTURE', $this->escape(stripslashes($row->creator('name')))); ?>" />
					</a>
					<p>
						<a href="<?php echo JRoute::_('index.php?option=com_members&id=' . $row->creator('id') . '&active=collections'); ?>">
							<?php echo $this->escape(stripslashes($row->creator('name'))); ?>
						</a> 
						onto 
						<a href="<?php echo JRoute::_($row->link()); ?>">
							<?php echo $this->escape(stripslashes($row->get('title'))); ?>
						</a>
						<br />
						<span class="entry-date">
							<span class="entry-date-at"><?php echo JText::_('COM_COLLECTIONS_AT'); ?></span> 
							<span class="time"><?php echo JHTML::_('date', $row->get('created'), JText::_('TIME_FORMAT_HZ1')); ?></span> 
							<span class="entry-date-on"><?php echo JText::_('COM_COLLECTIONS_ON'); ?></span> 
							<span class="date"><?php echo JHTML::_('date', $row->get('created'), JText::_('DATE_FORMAT_HZ1')); ?></span>
						</span>
					</p>
				</div><!-- / .attribution -->
			</div><!-- / .content -->
		</div><!-- / .post -->
<?php
	}
}
else
{
?>
		<div id="collections-introduction">
	<?php if ($this->config->get('access-create-bulletin')) { ?>
			<div class="instructions">
				<ol>
					<li><?php echo JText::_('COM_COLLECTIONS_INSTRUCTIONS_STEP1'); ?></li>
					<li><?php echo JText::_('COM_COLLECTIONS_INSTRUCTIONS_STEP2'); ?></li>
					<li><?php echo JText::_('COM_COLLECTIONS_INSTRUCTIONS_STEP3'); ?></li>
					<li><?php echo JText::_('COM_COLLECTIONS_INSTRUCTIONS_STEP4'); ?></li>
				</ol>
			</div>
	<?php } else { ?>
			<div class="instructions">
				<p><?php echo JText::_('COM_COLLECTIONS_NO_POSTS_FOUND'); ?></p>
			</div>
	<?php } ?>
		</div><!-- / #collections-introduction -->
<?php
}
?>
		</div><!-- / #posts -->
		<?php if ($this->total > $this->filters['limit']) { echo $this->pageNav->getListFooter(); } ?>
		<div class="clear"></div>
	</div><!-- / .main section -->
</form>