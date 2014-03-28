<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2013 Purdue University. All rights reserved.
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
 * @copyright Copyright 2005-2013 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

defined('_JEXEC') or die('Restricted access');

	ximport('Hubzero_User_Profile');
	ximport('Hubzero_User_Profile_Helper');
	$juser = JFactory::getUser();

	$this->comment->set('section', $this->filters['section']);
	$this->comment->set('category', $this->category->get('alias'));

	$this->config->set('access-edit-post', false);
	if ($juser->get('id') == $this->comment->get('created_by'))
	{
		$this->config->set('access-edit-post', true);
	}

	$name = JText::_('PLG_GROUPS_FORUM_ANONYMOUS');
	$huser = '';
	if (!$this->comment->get('anonymous')) 
	{
		$huser = Hubzero_User_Profile::getInstance($this->comment->get('created_by'));
		if (is_object($huser) && $huser->get('name')) 
		{
			$name = '<a href="' . JRoute::_('index.php?option=com_members&id=' . $this->comment->get('created_by')) . '">' . $this->escape(stripslashes($huser->get('name'))) . '</a>';
		}
	}

	$cls = isset($this->cls) ? $this->cls : 'odd';

	if ($this->comment->get('reports'))
	{
		$comment = '<p class="warning">' . JText::_('PLG_GROUPS_FORUM_COMMENT_REPORTED') . '</p>';
	}
	else
	{
		$comment = $this->comment->content('parsed');
	}
?>
	<li class="comment <?php echo $cls; ?><?php if (!$this->comment->get('parent')) { echo ' start'; } ?>" id="c<?php echo $this->comment->get('id'); ?>">
		<p class="comment-member-photo">
			<img src="<?php echo Hubzero_User_Profile_Helper::getMemberPhoto($huser, $this->comment->get('anonymous')); ?>" alt="" />
		</p>
		<div class="comment-content">
			<p class="comment-title">
				<strong><?php echo $name; ?></strong> 
				<a class="permalink" href="<?php echo JRoute::_($this->comment->link('anchor')); ?>" title="<?php echo JText::_('PLG_GROUPS_FORUM_PERMALINK'); ?>">
					<span class="comment-date-at"><?php echo JText::_('PLG_GROUPS_FORUM_AT'); ?></span> 
					<span class="time"><time datetime="<?php echo $this->comment->created(); ?>"><?php echo $this->comment->created('time'); ?></time></span> 
					<span class="comment-date-on"><?php echo JText::_('PLG_GROUPS_FORUM_ON'); ?></span> 
					<span class="date"><time datetime="<?php echo $this->comment->created(); ?>"><?php echo $this->comment->created('date'); ?></time></span>
					<?php if ($this->comment->wasModified()) { ?>
						&mdash; <?php echo JText::_('PLG_GROUPS_FORUM_EDITED'); ?>
						<span class="comment-date-at"><?php echo JText::_('PLG_GROUPS_FORUM_AT'); ?></span> 
						<span class="time"><time datetime="<?php echo $this->comment->modified(); ?>"><?php echo $this->comment->modified('time'); ?></time></span> 
						<span class="comment-date-on"><?php echo JText::_('PLG_GROUPS_FORUM_ON'); ?></span> 
						<span class="date"><time datetime="<?php echo $this->comment->modified(); ?>"><?php echo $this->comment->modified('date'); ?></time></span>
					<?php } ?>
				</a>
			</p>
			<div class="comment-body">
				<?php echo $comment; ?>
			</div>
			<p class="comment-options">
			<?php if (
						$this->config->get('access-manage-thread')
					 || $this->config->get('access-delete-thread') 
					 || $this->config->get('access-edit-thread')
					 || $this->config->get('access-delete-post') 
					 || $this->config->get('access-edit-post')
					) { ?>
				<?php if ($this->comment->get('parent') && $this->config->get('access-delete-post')) { ?>
					<a class="icon-delete delete" data-id="c<?php echo $this->comment->get('id'); ?>" href="<?php echo JRoute::_($this->comment->link('delete')); ?>"><!-- 
						--><?php echo JText::_('PLG_GROUPS_FORUM_DELETE'); ?><!-- 
					--></a>
				<?php } ?>
				<?php if ($this->config->get('access-edit-thread') || $this->config->get('access-edit-post')) { ?>
					<a class="icon-edit edit" data-id="c<?php echo $this->comment->get('id'); ?>" href="<?php echo JRoute::_($this->comment->link('edit')); ?>"><!-- 
						--><?php echo JText::_('PLG_GROUPS_FORUM_EDIT'); ?><!-- 
					--></a>
				<?php } ?>
			<?php } ?>

			<?php if (!$this->comment->get('reports')) { ?>
				<?php if (!$this->thread->get('closed') && $this->config->get('threading') == 'tree' && $this->depth < $this->config->get('threading_depth', 3)) { ?>
					<?php if (JRequest::getInt('reply', 0) == $this->comment->get('id')) { ?>
					<a class="icon-reply reply active" data-txt-active="<?php echo JText::_('PLG_GROUPS_FORUM_CANCEL'); ?>" data-txt-inactive="<?php echo JText::_('PLG_GROUPS_FORUM_REPLY'); ?>" href="<?php echo JRoute::_($this->comment->link()); ?>" rel="comment-form<?php echo $this->comment->get('id'); ?>"><!-- 
					--><?php echo JText::_('PLG_GROUPS_FORUM_CANCEL'); ?><!-- 
				--></a>
					<?php } else { ?>
					<a class="icon-reply reply" data-txt-active="<?php echo JText::_('PLG_GROUPS_FORUM_CANCEL'); ?>" data-txt-inactive="<?php echo JText::_('PLG_GROUPS_FORUM_REPLY'); ?>" href="<?php echo JRoute::_($this->comment->link('reply')); ?>" rel="comment-form<?php echo $this->comment->get('id'); ?>"><!-- 
					--><?php echo JText::_('PLG_GROUPS_FORUM_REPLY'); ?><!-- 
				--></a>
					<?php } ?>
				<?php } ?>
				<a class="icon-abuse abuse" href="<?php echo JRoute::_($this->comment->link('abuse')); ?>" rel="comment-form<?php echo $this->comment->get('id'); ?>"><!-- 
					--><?php echo JText::_('PLG_GROUPS_FORUM_REPORT_ABUSE'); ?><!-- 
				--></a>
			<?php } ?>
			</p>
			

		<?php if (!$this->thread->get('closed') && $this->config->get('threading') == 'tree' && $this->depth < $this->config->get('threading_depth', 3)) { ?>
			<div class="comment-add<?php if (JRequest::getInt('reply', 0) != $this->comment->get('id')) { echo ' hide'; } ?>" id="comment-form<?php echo $this->comment->get('id'); ?>">
				<form id="cform<?php echo $this->comment->get('id'); ?>" action="<?php echo JRoute::_($this->thread->link()); ?>" method="post" enctype="multipart/form-data">
					<fieldset>
						<legend><span><?php echo JText::sprintf('PLG_GROUPS_FORUM_REPLYING_TO', (!$this->comment->get('anonymous') ? $name : JText::_('PLG_GROUPS_FORUM_ANONYMOUS'))); ?></span></legend>

						<input type="hidden" name="fields[id]" value="0" />
						<input type="hidden" name="fields[state]" value="1" />
						<input type="hidden" name="fields[scope]" value="<?php echo $this->thread->get('scope'); ?>" />
						<input type="hidden" name="fields[category_id]" value="<?php echo $this->thread->get('category_id'); ?>" />
						<input type="hidden" name="fields[scope_id]" value="<?php echo $this->thread->get('scope_id'); ?>" />
						<input type="hidden" name="fields[scope_sub_id]" value="<?php echo $this->thread->get('scope_sub_id'); ?>" />
						<input type="hidden" name="fields[object_id]" value="<?php echo $this->thread->get('object_id'); ?>" />
						<input type="hidden" name="fields[parent]" value="<?php echo $this->comment->get('id'); ?>" />
						<input type="hidden" name="fields[thread]" value="<?php echo $this->comment->get('thread'); ?>" />
						<input type="hidden" name="fields[created]" value="" />
						<input type="hidden" name="fields[created_by]" value="<?php echo $juser->get('id'); ?>" />

						<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
						<input type="hidden" name="cn" value="<?php echo $this->escape($this->group->get('cn')); ?>" />
						<input type="hidden" name="active" value="forum" />
						<input type="hidden" name="action" value="savethread" />

						<?php echo JHTML::_('form.token'); ?>

						<label for="comment-<?php echo $this->comment->get('id'); ?>-content">
							<span class="label-text"><?php echo JText::_('PLG_GROUPS_FORUM_FIELD_COMMENTS'); ?></span>
							<?php
							ximport('Hubzero_Wiki_Editor');
							echo Hubzero_Wiki_Editor::getInstance()->display('fields[comment]', 'field_' . $this->comment->get('id') . '_comment', '', 'minimal no-footer', '35', '4');
							?>
						</label>

						<label class="upload-label" for="comment-<?php echo $this->comment->get('id'); ?>-file">
							<span class="label-text"><?php echo JText::_('PLG_GROUPS_FORUM_FIELD_FILE'); ?>:</span>
							<input type="file" name="upload" id="comment-<?php echo $this->comment->get('id'); ?>-file" />
						</label>

						<label class="reply-anonymous-label" for="comment-<?php echo $this->comment->get('id'); ?>-anonymous">
							<input class="option" type="checkbox" name="fields[anonymous]" id="comment-<?php echo $this->comment->get('id'); ?>-anonymous" value="1" /> 
							<?php echo JText::_('PLG_GROUPS_FORUM_FIELD_ANONYMOUS'); ?>
						</label>

						<p class="submit">
							<input type="submit" value="<?php echo JText::_('PLG_GROUPS_FORUM_SUBMIT'); ?>" /> 
						</p>
					</fieldset>
				</form>
			</div><!-- / .addcomment -->
		<?php } ?>
		</div><!-- / .comment-content -->
		<?php
		if ($this->config->get('threading') == 'tree' && $this->depth < $this->config->get('threading_depth', 3)) 
		{
			$view = new Hubzero_Plugin_View(
				array(
					'folder'  => 'groups',
					'element' => 'forum',
					'name'    => 'threads',
					'layout'  => '_list'
				)
			);
			$view->option     = $this->option;
			$view->group      = $this->group;

			$view->parent     = $this->comment->get('id');
			$view->thread     = $this->comment->get('thread');
			$view->comments   = $this->comment->get('replies');

			$view->thread     = $this->thread;
			$view->config     = $this->config;
			$view->depth      = $this->depth;
			$view->cls        = $cls;
			$view->filters    = $this->filters;
			$view->category   = $this->category;

			$view->display();
		}
		?>
	</li>