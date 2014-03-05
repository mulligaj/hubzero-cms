<?php 
defined('_JEXEC') or die('Restricted access');

	ximport('Hubzero_User_Profile');
	ximport('Hubzero_User_Profile_Helper');

	$juser = JFactory::getUser();

	$cls = isset($this->cls) ? $this->cls : 'odd';

	$name = JText::_('PLG_MEMBERS_BLOG_ANONYMOUS');
	$huser = new Hubzero_User_Profile;
	if (!$this->comment->get('anonymous')) 
	{
		$huser = Hubzero_User_Profile::getInstance($this->comment->get('created_by'));
		if (is_object($huser) && $huser->get('name')) 
		{
			$name = '<a href="' . JRoute::_('index.php?option=com_members&id=' . $this->comment->get('created_by')) . '">' . $this->escape(stripslashes($huser->get('name'))) . '</a>';
		}
	}

	if ($this->comment->get('reports'))
	{
		$comment = '<p class="warning">' . JText::_('PLG_MEMBERS_BLOG_COMMENT_REPORTED_AS_ABUSIVE') . '</p>';
	}
	else
	{
		$comment  = $this->comment->content('parsed');
	}
?>
	<li class="comment <?php echo $cls; ?>" id="c<?php echo $this->comment->get('id'); ?>">
		<p class="comment-member-photo">
			<a class="comment-anchor" name="c<?php echo $this->comment->get('id'); ?>"></a>
			<img src="<?php echo Hubzero_User_Profile_Helper::getMemberPhoto($huser, $this->comment->get('anonymous')); ?>" alt="" />
		</p>
		<div class="comment-content">
			<p class="comment-title">
				<strong><?php echo $name; ?></strong> 
				<a class="permalink" href="<?php echo JRoute::_($this->base . '#c' . $this->comment->get('id')); ?>" title="<?php echo JText::_('PLG_MEMBERS_BLOG_PERMALINK'); ?>">
					<span class="comment-date-at"><?php echo JText::_('PLG_MEMBERS_BLOG_AT'); ?></span> 
					<span class="time"><time datetime="<?php echo $this->comment->get('created'); ?>"><?php echo JHTML::_('date', $this->comment->get('created'), JText::_('TIME_FORMAT_HZ1')); ?></time></span> 
					<span class="comment-date-on"><?php echo JText::_('PLG_MEMBERS_BLOG_ON'); ?></span> 
					<span class="date"><time datetime="<?php echo $this->comment->get('created'); ?>"><?php echo JHTML::_('date', $this->comment->get('created'), JText::_('DATE_FORMAT_HZ1')); ?></time></span>
				</a>
			</p>

			<?php echo $comment; ?>

			<p class="comment-options">
			<?php /*if ($this->config->get('access-edit-thread')) { // || $juser->get('id') == $this->comment->created_by ?>
				<?php if ($this->config->get('access-delete-thread')) { ?>
					<a class="icon-delete delete" href="<?php echo JRoute::_($this->base . '&action=delete&comment=' . $this->comment->get('id')); ?>"><!-- 
						--><?php echo JText::_('PLG_MEMBERS_BLOG_DELETE'); ?><!-- 
					--></a>
				<?php } ?>
				<?php if ($this->config->get('access-edit-thread')) { ?>
					<a class="icon-edit edit" href="<?php echo JRoute::_($this->base . '&action=edit&comment=' . $this->comment->get('id')); ?>"><!-- 
						--><?php echo JText::_('PLG_MEMBERS_BLOG_EDIT'); ?><!-- 
					--></a>
				<?php } ?>
			<?php }*/ ?>
			<?php if (!$this->comment->get('reports')) { ?>
				<?php if ($this->depth < $this->config->get('comments_depth', 3)) { ?>
					<?php if (JRequest::getInt('reply', 0) == $this->comment->get('id')) { ?>
					<a class="icon-reply reply active" data-txt-active="<?php echo JText::_('PLG_MEMBERS_BLOG_CANCEL'); ?>" data-txt-inactive="<?php echo JText::_('PLG_MEMBERS_BLOG_REPLY'); ?>" href="<?php echo JRoute::_($this->base); ?>" rel="comment-form<?php echo $this->comment->get('id'); ?>"><!-- 
					--><?php echo JText::_('PLG_MEMBERS_BLOG_CANCEL'); ?><!-- 
				--></a>
					<?php } else { ?>
					<a class="icon-reply reply" data-txt-active="<?php echo JText::_('PLG_MEMBERS_BLOG_CANCEL'); ?>" data-txt-inactive="<?php echo JText::_('PLG_MEMBERS_BLOG_REPLY'); ?>" href="<?php echo JRoute::_($this->base . '&reply=' . $this->comment->get('id')); ?>" rel="comment-form<?php echo $this->comment->get('id'); ?>"><!-- 
					--><?php echo JText::_('PLG_MEMBERS_BLOG_REPLY'); ?><!-- 
				--></a>
					<?php } ?>
				<?php } ?>
				<a class="icon-abuse abuse" href="<?php echo JRoute::_('index.php?option=com_support&task=reportabuse&category=blog&id=' . $this->comment->get('id') . '&parent=' . $this->comment->get('entry_id')); ?>" rel="comment-form<?php echo $this->comment->get('id'); ?>"><!-- 
					--><?php echo JText::_('PLG_MEMBERS_BLOG_REPORT_ABUSE'); ?><!-- 
				--></a>
			<?php } ?>
			</p>

		<?php if ($this->depth < $this->config->get('comments_depth', 3)) { ?>
			<div class="comment-add<?php if (JRequest::getInt('reply', 0) != $this->comment->get('id')) { echo ' hide'; } ?>" id="comment-form<?php echo $this->comment->get('id'); ?>">
				<form id="cform<?php echo $this->comment->get('id'); ?>" action="<?php echo JRoute::_($this->base); ?>" method="post" enctype="multipart/form-data">
					<a name="commentform<?php echo $this->comment->get('id'); ?>"></a>
					<fieldset>
						<legend><span><?php echo JText::sprintf('PLG_MEMBERS_BLOG_REPLYING_TO', (!$this->comment->get('anonymous') ? $name : JText::_('PLG_MEMBERS_BLOG_ANONYMOUS'))); ?></span></legend>

						<input type="hidden" name="comment[id]" value="0" />
						<input type="hidden" name="comment[entry_id]" value="<?php echo $this->comment->get('entry_id'); ?>" />
						<input type="hidden" name="comment[parent]" value="<?php echo $this->comment->get('id'); ?>" />
						<input type="hidden" name="comment[created]" value="" />
						<input type="hidden" name="comment[created_by]" value="<?php echo $juser->get('id'); ?>" />
						<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
						<input type="hidden" name="id" value="<?php echo $this->member->get('uidNumber'); ?>" />
						<input type="hidden" name="active" value="blog" />
						<input type="hidden" name="task" value="view" />
						<input type="hidden" name="action" value="savecomment" />

						<label for="comment_<?php echo $this->comment->get('id'); ?>_content">
							<span class="label-text"><?php echo JText::_('PLG_MEMBERS_BLOG_FIELD_COMMENTS'); ?></span>
							<?php
							ximport('Hubzero_Wiki_Editor');
							echo Hubzero_Wiki_Editor::getInstance()->display('comment[content]', 'comment_' . $this->comment->get('id') . '_content', '', 'minimal no-footer', '35', '4');
							?>
						</label>

						<label id="comment-anonymous-label" for="comment-anonymous">
							<input class="option" type="checkbox" name="comment[anonymous]" id="comment-anonymous" value="1" />
							<?php echo JText::_('PLG_MEMBERS_BLOG_POST_ANONYMOUS'); ?>
						</label>

						<p class="submit">
							<input type="submit" value="<?php echo JText::_('PLG_MEMBERS_BLOG_SUBMIT'); ?>" /> 
						</p>
					</fieldset>
				</form>
			</div><!-- / .addcomment -->
		<?php } ?>
		</div><!-- / .comment-content -->
		<?php
		if ($this->depth < $this->config->get('comments_depth', 3)) 
		{
			$view = new Hubzero_Plugin_View(
				array(
					'folder'  => 'members',
					'element' => 'blog',
					'name'    => 'comments',
					'layout'  => '_list'
				)
			);
			$view->parent     = $this->comment->get('id');
			$view->option     = $this->option;
			$view->comments   = $this->comment->replies();
			$view->config     = $this->config;
			$view->depth      = $this->depth;
			$view->cls        = $cls;
			$view->base       = $this->base;
			$view->member     = $this->member;
			$view->display();
		}
		?>
	</li>