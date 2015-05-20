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

$item = $this->post->item();

$this->juser = JFactory::getUser();

$base = 'index.php?option=' . $this->option . '&cn=' . $this->group->get('cn') . '&active=' . $this->name;
?>
<div class="post full <?php echo $item->type(); ?>" id="b<?php echo $this->post->get('id'); ?>" data-id="<?php echo $this->post->get('id'); ?>" data-closeup-url="<?php echo JRoute::_($base . '&scope=post/' . $this->post->get('id')); ?>" data-width="600" data-height="350">
	<div class="content">
		<div class="creator attribution clearfix">
			<?php if ($item->get('type') == 'file' || $item->get('type') == 'collection') { ?>
				<?php
				$name = $this->escape(stripslashes($item->creator('name')));

				if ($item->creator('public')) { ?>
					<a href="<?php echo JRoute::_($item->creator()->getLink()); ?>" title="<?php echo $name; ?>" class="img-link">
						<img src="<?php echo $item->creator()->getPicture(); ?>" alt="<?php echo JText::_('PLG_GROUPS_COLLECTIONS_PROFILE_PICTURE', $name); ?>" />
					</a>
				<?php } else { ?>
					<span class="img-link">
						<img src="<?php echo $item->creator()->getPicture(); ?>" alt="<?php echo JText::_('PLG_GROUPS_COLLECTIONS_PROFILE_PICTURE', $name); ?>" />
					</span>
				<?php } ?>
				<p>
					<a href="<?php echo JRoute::_($item->creator()->getLink()); ?>">
						<?php echo $this->escape(stripslashes($item->creator('name'))); ?>
					</a> created this post
					<br />
					<span class="entry-date">
						<span class="entry-date-at">@</span>
						<span class="time"><time datetime="<?php echo $item->created(); ?>"><?php echo $item->created('time'); ?></time></span>
						<span class="entry-date-on">on</span>
						<span class="date"><time datetime="<?php echo $item->created(); ?>"><?php echo $item->created('date'); ?></time></span>
					</span>
				</p>
			<?php } else { ?>
				<p class="typeof <?php echo $item->get('type'); ?>">
					<?php echo $this->escape($item->type('title')); ?>
				</p>
			<?php } ?>
		</div><!-- / .attribution -->
		<?php
		$this->view('default_' . $item->type(), 'post')
		     ->set('name', $this->name)
		     ->set('option', $this->option)
		     ->set('group', $this->group)
		     ->set('params', $this->params)
		     ->set('row', $this->post)
		     ->display();
		?>
	<?php if (count($item->tags()) > 0) { ?>
		<div class="tags-wrap">
			<?php echo $item->tags('render'); ?>
		</div>
	<?php } ?>
		<div class="meta">
			<p class="stats">
				<span class="likes">
					<?php echo JText::sprintf('%s likes', $item->get('positive', 0)); ?>
				</span>
				<span class="comments">
					<?php echo JText::sprintf('%s comments', $item->get('comments', 0)); ?>
				</span>
				<span class="reposts">
					<?php echo JText::sprintf('%s reposts', $item->get('reposts', 0)); ?>
				</span>
			</p>
	<?php /*if (!$this->juser->get('guest')) { ?>
			<div class="actions">
		<?php if ($item->get('created_by') == $this->juser->get('id')) { ?>
				<a class="edit" data-id="<?php echo $this->post->get('id'); ?>" href="<?php echo JRoute::_($base . '&scope=post/' . $this->post->get('id') . '/edit'); ?>">
					<span><?php echo JText::_('Edit'); ?></span>
				</a>
		<?php } else { ?>
				<a class="vote <?php echo ($item->get('voted')) ? 'unlike' : 'like'; ?>" data-id="<?php echo $this->post->get('id'); ?>" data-text-like="<?php echo JText::_('Like'); ?>" data-text-unlike="<?php echo JText::_('Unlike'); ?>" href="<?php echo JRoute::_($base . '&scope=post/' . $this->post->get('id') . '/vote'); ?>">
					<span><?php echo ($item->get('voted')) ? JText::_('Unlike') : JText::_('Like'); ?></span>
				</a>
		<?php } ?>
				<a class="comment" data-id="<?php echo $this->post->get('id'); ?>" href="<?php echo JRoute::_($base . '&scope=post/' . $this->post->get('id') . '/comment'); ?>">
					<span><?php echo JText::_('Comment'); ?></span>
				</a>
				<a class="repost" data-id="<?php echo $this->post->get('id'); ?>" href="<?php echo JRoute::_($base . '&scope=post/' . $this->post->get('id') . '/collect'); ?>">
					<span><?php echo JText::_('Collect'); ?></span>
				</a>
		<?php if ($this->post->get('original') && ($item->get('created_by') == $this->juser->get('id') || $this->params->get('access-delete-item'))) { ?>
				<a class="delete" data-id="<?php echo $this->post->get('id'); ?>" href="<?php echo JRoute::_($base . '&scope=post/' . $this->post->get('id') . '/delete'); ?>">
					<span><?php echo JText::_('Delete'); ?></span>
				</a>
		<?php } else if ($this->post->get('created_by') == $this->juser->get('id') || $this->params->get('access-edit-item')) { ?>
				<a class="unpost" data-id="<?php echo $this->post->get('id'); ?>" href="<?php echo JRoute::_($base . '&scope=post/' . $this->post->get('id') . '/remove'); ?>">
					<span><?php echo JText::_('Remove'); ?></span>
				</a>
		<?php } ?>
			</div><!-- / .actions -->
	<?php }*/ ?>
		</div><!-- / .meta -->
<?php //if ($this->post->created_by != $this->post->created_by) { ?>
		<div class="convo attribution clearfix">
			<a href="<?php echo JRoute::_($this->post->creator()->getLink()); ?>" title="<?php echo $this->escape(stripslashes($this->post->creator()->get('name'))); ?>" class="img-link">
				<img src="<?php echo $this->post->creator()->getPicture(); ?>" alt="Profile picture of <?php echo $this->escape(stripslashes($this->post->creator()->get('name'))); ?>" />
			</a>
			<p>
				<?php
				$who = $this->escape(stripslashes($this->post->creator()->get('name')));
				if ($this->post->creator('public'))
				{
					$who = '<a href="' . JRoute::_($this->post->creator()->getLink()) . '">' . $name . '</a>';
				}

				$where = '<a href="' . JRoute::_($base . '&scope=' . $this->collection->get('alias')) . '">' . $this->escape(stripslashes($this->collection->get('title'))) . '</a>';

				echo JText::sprintf('PLG_GROUPS_COLLECTIONS_ONTO', $who, $where);
				?>
				<br />
				<span class="entry-date">
					<span class="entry-date-at">@</span>
					<span class="time"><time datetime="<?php echo $this->post->created(); ?>"><?php echo $this->post->created('time'); ?></time></span>
					<span class="entry-date-on">on</span>
					<span class="date"><time datetime="<?php echo $this->post->created(); ?>"><?php echo $this->post->created('date'); ?></time></span>
				</span>
			</p>
		</div><!-- / .attribution -->
<?php
if ($item->get('comments'))
{
	foreach ($item->comments() as $comment)
	{
		$cuser = \Hubzero\User\Profile::getInstance($comment->created_by);
?>
		<div class="commnts">
			<div class="comment convo clearfix" id="c<?php echo $comment->id; ?>">
				<a href="<?php echo JRoute::_('index.php?option=com_members&id=' . $comment->created_by); ?>" class="img-link">
					<img src="<?php echo \Hubzero\User\Profile\Helper::getMemberPhoto($cuser, $comment->anonymous); ?>" class="profile user_image" alt="Profile picture of <?php echo $this->escape(stripslashes($cuser->get('name'))); ?>" />
				</a>
				<p>
					<a href="<?php echo JRoute::_('index.php?option=com_members&id=' . $comment->created_by); ?>"><?php echo $this->escape(stripslashes($cuser->get('name'))); ?></a> said <br />
					<span class="entry-date">
						<span class="entry-date-at">@</span>
						<span class="time"><time datetime="<?php echo $comment->created; ?>"><?php echo JHTML::_('date', $comment->created, JText::_('TIME_FORMAT_HZ1')); ?></time></span>
						<span class="entry-date-on">on</span>
						<span class="date"><time datetime="<?php echo $comment->created; ?>"><?php echo JHTML::_('date', $comment->created, JText::_('DATE_FORMAT_HZ1')); ?></time></span>
					</span>
				</p>
				<blockquote>
					<p><?php echo stripslashes($comment->content); ?></p>
				</blockquote>
			</div>
		</div>
<?php
	}
}
	if (!$this->juser->get('guest'))
	{
		$now = JFactory::getDate();
		?>
		<div class="commnts">
			<div class="comment convo clearfix">
				<a href="<?php echo JRoute::_('index.php?option=com_members&id=' . $this->juser->get('id')); ?>" class="img-link">
					<img src="<?php echo \Hubzero\User\Profile\Helper::getMemberPhoto($this->juser, 0); ?>" class="profile user_image" alt="Profile picture of <?php echo $this->escape(stripslashes($this->juser->get('name'))); ?>" />
				</a>
				<p>
					<a href="<?php echo JRoute::_('index.php?option=com_members&id=' . $this->juser->get('id')); ?>"><?php echo $this->escape(stripslashes($this->juser->get('name'))); ?></a> will say <br />
					<span class="entry-date">
						<span class="entry-date-at">@</span>
						<span class="time"><time datetime="<?php echo $now; ?>"><?php echo JHTML::_('date', $now, JText::_('TIME_FORMAT_HZ1')); ?></time></span>
						<span class="entry-date-on">on</span>
						<span class="date"><time datetime="<?php echo $now; ?>"><?php echo JHTML::_('date', $now, JText::_('DATE_FORMAT_HZ1')); ?></time></span>
					</span>
				</p>
				<form action="<?php echo JRoute::_($base . '&scope=post/' . $this->post->get('id') . '/savecomment'); ?>" method="post" id="comment-form" enctype="multipart/form-data">
					<fieldset>
						<input type="hidden" name="comment[id]" value="0" />
						<input type="hidden" name="comment[item_id]" value="<?php echo $item->get('id'); ?>" />
						<input type="hidden" name="comment[item_type]" value="collection" />
						<input type="hidden" name="comment[state]" value="1" />

						<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
						<input type="hidden" name="cn" value="<?php echo $this->group->get('cn'); ?>" />
						<input type="hidden" name="scope" value="post/<?php echo $this->post->get('id'); ?>/savecomment" />
						<input type="hidden" name="action" value="savecomment" />
						<input type="hidden" name="no_html" value="<?php echo $this->no_html; ?>" />

						<?php echo JHTML::_('form.token'); ?>

						<textarea name="comment[content]" cols="35" rows="3"></textarea>
						<input type="submit" class="comment-submit" value="<?php echo JText::_('Post comment'); ?>" />
					</fieldset>
				</form>
			</div>
		</div>
		<?php
	}
	?>
	</div><!-- / .content -->
</div><!-- / .bulletin -->