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

$this->juser = JFactory::getUser();

$base = 'index.php?option=' . $this->option . '&id=' . $this->member->get('uidNumber') . '&active=' . $this->name;
?>

<?php if (!$this->juser->get('guest') && !$this->params->get('access-create-item')) { ?>
<ul id="page_options">
	<li>
		<?php if ($this->model->isFollowing()) { ?>
		<a class="unfollow btn" data-text-follow="<?php echo JText::_('PLG_MEMBERS_COLLECTIONS_FOLLOW_ALL'); ?>" data-text-unfollow="<?php echo JText::_('PLG_MEMBERS_COLLECTIONS_UNFOLLOW_ALL'); ?>" href="<?php echo JRoute::_($base . '&task=unfollow'); ?>">
			<span><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_UNFOLLOW_ALL'); ?></span>
		</a>
		<?php } else { ?>
		<a class="follow btn" data-text-follow="<?php echo JText::_('PLG_MEMBERS_COLLECTIONS_FOLLOW_ALL'); ?>" data-text-unfollow="<?php echo JText::_('PLG_MEMBERS_COLLECTIONS_UNFOLLOW_ALL'); ?>" href="<?php echo JRoute::_($base . '&task=follow'); ?>">
			<span><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_FOLLOW_ALL'); ?></span>
		</a>
		<?php } ?>
	</li>
</ul>
<?php } ?>

<form method="get" action="<?php echo JRoute::_($base . '&task=' . $this->collection->get('alias')); ?>" id="collections">

	<p class="overview">
		<span class="title count">
			"<?php echo $this->escape(stripslashes($this->collection->get('title'))); ?>"
		</span>
		<span class="posts count">
			<?php echo JText::sprintf('PLG_MEMBERS_COLLECTIONS_NUM_POSTS', $this->posts); ?>
		</span>
<?php if (!$this->juser->get('guest')) { ?>
	<?php if ($this->rows && $this->params->get('access-create-item')) { ?>
		<a class="icon-add add btn tooltips" title="<?php echo JText::_('PLG_MEMBERS_COLLECTIONS_NEW_POST_TITLE'); ?>" href="<?php echo JRoute::_($base . '&task=post/new&board=' . $this->collection->get('alias')); ?>">
			<span><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_NEW_POST'); ?></span>
		</a>
	<?php } else { ?>
		<?php if ($this->collection->isFollowing()) { ?>
			<a class="unfollow btn tooltips" data-text-follow="<?php echo JText::_('PLG_MEMBERS_COLLECTIONS_FOLLOW_THIS'); ?>" data-text-unfollow="<?php echo JText::_('PLG_MEMBERS_COLLECTIONS_UNFOLLOW_THIS'); ?>" title="<?php echo JText::_('PLG_MEMBERS_COLLECTIONS_UNFOLLOW_TITLE'); ?>" href="<?php echo JRoute::_($base . '&task=' . $this->collection->get('alias') . '/unfollow'); ?>">
				<span><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_UNFOLLOW_THIS'); ?></span>
			</a>
		<?php } else { ?>
			<a class="follow btn tooltips" data-text-follow="<?php echo JText::_('PLG_MEMBERS_COLLECTIONS_FOLLOW_THIS'); ?>" data-text-unfollow="<?php echo JText::_('PLG_MEMBERS_COLLECTIONS_UNFOLLOW_THIS'); ?>" title="<?php echo JText::_('PLG_MEMBERS_COLLECTIONS_FOLLOW_TITLE'); ?>" href="<?php echo JRoute::_($base . '&task=' . $this->collection->get('alias') . '/follow'); ?>">
				<span><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_FOLLOW_THIS'); ?></span>
			</a>
		<?php } ?>
		<a class="repost btn tooltips" title="<?php echo JText::_('PLG_MEMBERS_COLLECTIONS_COLLECT_TITLE'); ?>" href="<?php echo JRoute::_($base . '&task=' . $this->collection->get('alias') . '/collect'); ?>">
			<span><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_COLLECT'); ?></span>
		</a>
	<?php } ?>
<?php } ?>
		<span class="clear"></span>
	</p>

<?php 
if ($this->rows->total() > 0) 
{
	?>
	<div id="posts">
	<?php
	foreach ($this->rows as $row)
	{
		$item = $row->item();
?>
		<div class="post <?php echo $item->type(); ?>" id="b<?php echo $row->get('id'); ?>" data-id="<?php echo $row->get('id'); ?>" data-closeup-url="<?php echo JRoute::_($base . '&task=post/' . $row->get('id')); ?>" data-width="600" data-height="350">
			<div class="content">
			<?php
				$view = new \Hubzero\Plugin\View(
					array(
						'folder'  => 'members',
						'element' => $this->name,
						'name'    => 'post',
						'layout'  => 'default_' . $item->type()
					)
				);
				$view->name       = $this->name;
				$view->option     = $this->option;
				$view->member     = $this->member;
				$view->params     = $this->params;
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
							<?php echo JText::sprintf('PLG_MEMBERS_COLLECTIONS_NUM_LIKES', $item->get('positive', 0)); ?>
						</span>
						<span class="comments">
							<?php echo JText::sprintf('PLG_MEMBERS_COLLECTIONS_NUM_COMMENTS', $item->get('comments', 0)); ?>
						</span>
						<span class="reposts">
							<?php echo JText::sprintf('PLG_MEMBERS_COLLECTIONS_NUM_REPOSTS', $item->get('reposts', 0)); ?>
						</span>
					</p>
			<?php if (!$this->juser->get('guest')) { ?>
					<div class="actions">
				<?php if ($item->get('created_by') == $this->juser->get('id')) { ?>
						<a class="edit" data-id="<?php echo $row->get('id'); ?>" href="<?php echo JRoute::_($base . '&task=post/' . $row->get('id') . '/edit'); ?>">
							<span><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_EDIT'); ?></span>
						</a>
				<?php } else { ?>
						<a class="vote <?php echo ($item->get('voted')) ? 'unlike' : 'like'; ?>" data-id="<?php echo $row->get('id'); ?>" data-text-like="<?php echo JText::_('PLG_MEMBERS_COLLECTIONS_LIKE'); ?>" data-text-unlike="<?php echo JText::_('Unlike'); ?>" href="<?php echo JRoute::_($base . '&task=post/' . $row->get('id') . '/vote'); ?>">
							<span><?php echo ($item->get('voted')) ? JText::_('PLG_MEMBERS_COLLECTIONS_UNLIKE') : JText::_('PLG_MEMBERS_COLLECTIONS_LIKE'); ?></span>
						</a>
				<?php } ?>
						<a class="comment" data-id="<?php echo $row->get('id'); ?>" href="<?php echo JRoute::_($base . '&task=post/' . $row->get('id') . '/comment'); ?>">
							<span><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_COMMENT'); ?></span>
						</a>
						<a class="repost" data-id="<?php echo $row->get('id'); ?>" href="<?php echo JRoute::_($base . '&task=post/' . $row->get('id') . '/collect'); ?>">
							<span><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_COLLECT'); ?></span>
						</a>
				<?php if ($row->get('original') && ($item->get('created_by') == $this->juser->get('id') || $this->params->get('access-delete-item'))) { ?>
						<a class="delete" data-id="<?php echo $row->get('id'); ?>" href="<?php echo JRoute::_($base . '&task=post/' . $row->get('id') . '/delete'); ?>">
							<span><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_DELETE'); ?></span>
						</a>
				<?php } else if ($row->get('created_by') == $this->juser->get('id') || $this->params->get('access-edit-item')) { ?>
						<a class="unpost" data-id="<?php echo $row->get('id'); ?>" href="<?php echo JRoute::_($base . '&task=post/' . $row->get('id') . '/remove'); ?>">
							<span><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_REMOVE'); ?></span>
						</a>
				<?php } ?>
					</div><!-- / .actions -->
			<?php } ?>
				</div><!-- / .meta -->

				<div class="convo attribution reposted clearfix">
					<a href="<?php echo JRoute::_('index.php?option=com_members&id=' . $row->get('created_by')); ?>" title="<?php echo $this->escape(stripslashes($row->creator('name'))); ?>" class="img-link">
						<img src="<?php echo \Hubzero\User\Profile\Helper::getMemberPhoto($this->member, 0); ?>" alt="<?php echo JText::sprintf('PLG_MEMBERS_COLLECTIONS_PROFILE_PICTURE', $this->escape(stripslashes($row->creator('name')))); ?>" />
					</a>
					<p>
						<a href="<?php echo JRoute::_('index.php?option=com_members&id=' . $row->get('created_by') . '&active=collections'); ?>">
							<?php echo $this->escape(stripslashes($row->creator()->get('name'))); ?>
						</a> 
						<?php echo JText::_('PLG_MEMBERS_COLLECTIONS_ONTO'); ?>
						<a href="<?php echo JRoute::_($base . ($this->collection->get('is_default') ? '' : '/' . $this->collection->get('alias'))); ?>">
							<?php echo $this->escape(stripslashes($this->collection->get('title'))); ?>
						</a>
						<br />
						<span class="entry-date">
							<span class="entry-date-at"><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_AT'); ?></span> 
							<span class="time"><time datetime="<?php echo $row->created(); ?>"><?php echo $row->created('time'); ?></time></span> 
							<span class="entry-date-on"><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_ON'); ?></span> 
							<span class="date"><time datetime="<?php echo $row->created(); ?>"><?php echo $row->created('date'); ?></time></span>
						</span>
					</p>
				</div><!-- / .attribution -->

			</div><!-- / .content -->
		</div><!-- / .post -->
	<?php } ?>
	</div><!-- / #posts -->
	<?php if ($this->posts > $this->filters['limit']) { echo $this->pageNav->getListFooter(); } ?>
	<div class="clear"></div>
<?php } else { ?>
		<div id="collection-introduction">
	<?php if ($this->params->get('access-create-item')) { ?>
			<div class="instructions">
				<ol>
					<li><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_POST_INSTRUCTIONS_STEP1'); ?></li>
					<li><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_POST_INSTRUCTIONS_STEP2'); ?></li>
					<li><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_POST_INSTRUCTIONS_STEP3'); ?></li>
					<li><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_POST_INSTRUCTIONS_STEP4'); ?></li>
				</ol>
			</div><!-- / .instructions -->
	<?php } else { ?>
			<div class="instructions">
				<p><?php echo JText::_('PLG_MEMBERS_COLLECTIONS_EMPTY_COLLECTION'); ?></p>
			</div><!-- / .instructions -->
	<?php } ?>
		</div><!-- / #collection-introduction -->
<?php } ?>
</form>