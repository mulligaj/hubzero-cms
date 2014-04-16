<?php 
defined('_JEXEC') or die('Restricted access');

	$juser = JFactory::getUser();

	$cls = isset($this->cls) ? $this->cls : 'odd';

	if ($this->article->get('created_by') == $this->comment->get('created_by')) 
	{
		$cls .= ' author';
	}
	$cls .= ($this->comment->isReported()) ? ' abusive' : '';
	if ($this->comment->get('state') == 1)
	{
		$cls .= ' chosen';
	}

	$name = JText::_('COM_KB_ANONYMOUS');
	$huser = new \Hubzero\User\Profile;
	if (!$this->comment->get('anonymous')) 
	{
		$huser = $this->comment->creator();
		if (is_object($huser) && $huser->get('name')) 
		{
			$name = '<a href="' . JRoute::_('index.php?option=com_members&id=' . $huser->get('uidNumber')) . '">' . $this->escape(stripslashes($huser->get('name'))) . '</a>';
		}
	}

	if ($this->comment->isReported())
	{
		$comment = '<p class="warning">' . JText::_('COM_KB_COMMENT_REPORTED_AS_ABUSIVE') . '</p>';
	}
	else
	{
		$comment  = $this->comment->content('parsed');
	}

	//$this->comment->set('category', 'kb');
?>
	<li class="comment <?php echo $cls; ?>" id="c<?php echo $this->comment->get('id'); ?>">
		<p class="comment-member-photo">
			<img src="<?php echo \Hubzero\User\Profile\Helper::getMemberPhoto($huser, $this->comment->get('anonymous')); ?>" alt="" />
		</p>
		<div class="comment-content">
		<?php if (!$this->comment->isReported() && $this->comment->get('entry_id')) { ?>
			<p class="comment-voting voting" id="answers_<?php echo $this->comment->get('id'); ?>">
				<?php
				$view = new JView(array(
					'name'   => 'articles', 
					'layout' => '_vote'
				));
				$view->option = $this->option;
				$view->item   = $this->comment;
				$view->type   = 'comment';
				$view->vote   = '';
				$view->id     = '';
				if (!$juser->get('guest')) 
				{
					$view->vote = $this->comment->get('vote');
					if ($this->comment->get('created_by') == $juser->get('id')) 
					{
						$view->id   = $this->comment->get('id');
					}
				}
				$view->display();
				?>
			</p><!-- / .comment-voting -->
		<?php } ?>

			<p class="comment-title">
				<strong><?php echo $name; ?></strong> 
				<a class="permalink" href="<?php echo JRoute::_($this->base . '#c' . $this->comment->get('id')); ?>" title="<?php echo JText::_('COM_KB_PERMALINK'); ?>">
					<span class="comment-date-at"><?php echo JText::_('COM_KB_DATETIME_AT'); ?></span> 
					<span class="time"><time datetime="<?php echo $this->comment->created(); ?>"><?php echo $this->comment->created('time'); ?></time></span> 
					<span class="comment-date-on"><?php echo JText::_('COM_KB_DATETIME_ON'); ?></span> 
					<span class="date"><time datetime="<?php echo $this->comment->created(); ?>"><?php echo $this->comment->created('date'); ?></time></span>
				</a>
			</p>

			<?php echo $comment; ?>

			<p class="comment-options">
			<?php /*if ($this->config->get('access-edit-thread')) { // || $juser->get('id') == $this->comment->created_by ?>
				<?php if ($this->config->get('access-delete-thread')) { ?>
					<a class="icon-delete delete" href="<?php echo JRoute::_($this->base . '&action=delete&comment=' . $this->comment->get('id')); ?>"><!-- 
						--><?php echo JText::_('COM_KB_DELETE'); ?><!-- 
					--></a>
				<?php } ?>
				<?php if ($this->config->get('access-edit-thread')) { ?>
					<a class="icon-edit edit" href="<?php echo JRoute::_($this->base . '&action=edit&comment=' . $this->comment->get('id')); ?>"><!-- 
						--><?php echo JText::_('COM_KB_EDIT'); ?><!-- 
					--></a>
				<?php } ?>
			<?php }*/ ?>
			<?php if (!$this->comment->get('reports')) { ?>
				<?php if ($this->depth < $this->article->param('comments_depth', 3) && $this->article->commentsOpen()) { ?>
					<?php if (JRequest::getInt('reply', 0) == $this->comment->get('id')) { ?>
					<a class="icon-reply reply active" data-txt-active="<?php echo JText::_('COM_KB_CANCEL'); ?>" data-txt-inactive="<?php echo JText::_('COM_KB_REPLY'); ?>" href="<?php echo JRoute::_($this->comment->link()); ?>" data-rel="comment-form<?php echo $this->comment->get('id'); ?>"><!-- 
					--><?php echo JText::_('COM_KB_CANCEL'); ?><!-- 
				--></a>
					<?php } else { ?>
					<a class="icon-reply reply" data-txt-active="<?php echo JText::_('COM_KB_CANCEL'); ?>" data-txt-inactive="<?php echo JText::_('COM_KB_REPLY'); ?>" href="<?php echo JRoute::_($this->comment->link('reply')); ?>" data-rel="comment-form<?php echo $this->comment->get('id'); ?>"><!-- 
					--><?php echo JText::_('COM_KB_REPLY'); ?><!-- 
				--></a>
					<?php } ?>
				<?php } ?>
					<a class="icon-abuse abuse" href="<?php echo JRoute::_($this->comment->link('report')); ?>" data-rel="comment-form<?php echo $this->comment->get('id'); ?>"><!-- 
					--><?php echo JText::_('COM_KB_REPORT_ABUSE'); ?><!-- 
				--></a>
			<?php } ?>
			</p>

		<?php if ($this->depth < $this->article->param('comments_depth', 3) && $this->article->commentsOpen()) { ?>
			<div class="addcomment comment-add<?php if (JRequest::getInt('reply', 0) != $this->comment->get('id')) { echo ' hide'; } ?>" id="comment-form<?php echo $this->comment->get('id'); ?>">
				<?php if ($juser->get('guest')) { ?>
				<p class="warning">
					<?php echo JText::sprintf('COM_KB_PLEASE_LOGIN_TO_ANSWER', '<a href="' . JRoute::_('index.php?option=com_login&return=' . base64_encode(JRoute::_($this->base, false, true))) . '">' . JText::_('COM_KB_LOGIN') . '</a>'); ?>
				</p>
				<?php } else { ?>
				<form id="cform<?php echo $this->comment->get('id'); ?>" action="<?php echo JRoute::_($this->base); ?>" method="post" enctype="multipart/form-data">
					<fieldset>
						<legend><span><?php echo JText::sprintf('COM_KB_REPLYING_TO', (!$this->comment->get('anonymous') ? $name : JText::_('COM_KB_ANONYMOUS'))); ?></span></legend>

						<input type="hidden" name="comment[id]" value="0" />
						<input type="hidden" name="comment[parent]" value="<?php echo $this->escape($this->comment->get('id')); ?>" />
						<input type="hidden" name="comment[entry_id]" value="<?php echo $this->escape($this->comment->get('entry_id')); ?>" />
						<input type="hidden" name="comment[created]" value="" />
						<input type="hidden" name="comment[created_by]" value="<?php echo $this->escape($juser->get('id')); ?>" />
						<input type="hidden" name="comment[state]" value="1" />

						<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
						<input type="hidden" name="controller" value="articles" />
						<input type="hidden" name="section" value="<?php echo $this->escape($this->article->get('salias')); ?>" />
						<input type="hidden" name="category" value="<?php echo $this->escape($this->article->get('calias')); ?>" />
						<input type="hidden" name="alias" value="<?php echo $this->escape($this->article->get('alias')); ?>" />
						<input type="hidden" name="task" value="savecomment" />

						<?php echo JHTML::_('form.token'); ?>

						<label for="comment_<?php echo $this->comment->get('id'); ?>_content">
							<span class="label-text"><?php echo JText::_('COM_KB_ENTER_COMMENTS'); ?></span>
							<?php
							echo JFactory::getEditor()->display('comment[content]', '', '', '', 35, 4, false, 'comment_' . $this->comment->get('id') . '_content', null, null, array('class' => 'minimal no-footer'));
							?>
						</label>

						<label id="comment-anonymous-label" for="comment-anonymous">
							<input class="option" type="checkbox" name="comment[anonymous]" id="comment-anonymous" value="1" />
							<?php echo JText::_('COM_KB_FIELD_ANONYMOUS'); ?>
						</label>

						<p class="submit">
							<input type="submit" value="<?php echo JText::_('COM_KB_SUBMIT'); ?>" /> 
						</p>
					</fieldset>
				</form>
				<?php } ?>
			</div><!-- / .addcomment -->
		<?php } ?>
		</div><!-- / .comment-content -->
		<?php
		if ($this->depth < $this->article->param('comments_depth', 3)) 
		{
			$view = new JView(
				array(
					'name'    => 'articles',
					'layout'  => '_list'
				)
			);
			$view->parent     = $this->comment->get('id');
			$view->article    = $this->article;
			$view->option     = $this->option;
			$view->comments   = $this->comment->replies();
			$view->depth      = $this->depth;
			$view->cls        = $cls;
			$view->base       = $this->base;
			$view->display();
		}
		?>
	</li>