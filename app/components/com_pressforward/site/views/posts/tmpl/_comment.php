<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

defined('_HZEXEC_') or die();

$cls = isset($this->cls) ? $this->cls : 'odd';

$name = Lang::txt('PF_ANONYMOUS');
if (!$this->comment->get('anonymous'))
{
	$name = $this->escape(stripslashes($this->comment->creator->get('name', $name)));
	if (in_array($this->comment->creator->get('access'), User::getAuthorisedViewLevels()))
	{
		$name = '<a href="' . Route::url($this->comment->creator->link()) . '">' . $name . '</a>';
	}
}

$comment = $this->comment->get('comment_content');
?>
	<li class="comment <?php echo $cls; ?>" id="c<?php echo $this->comment->get('comment_ID'); ?>">
		<p class="comment-member-photo">
			<img src="<?php echo $this->comment->creator->picture($this->comment->get('anonymous')); ?>" alt="" />
		</p>
		<div class="comment-content">
			<p class="comment-title">
				<strong><?php echo $name; ?></strong>
				<a class="permalink" href="<?php echo Route::url($this->base . '#c' . $this->comment->get('comment_ID')); ?>" title="<?php echo Lang::txt('PF_PERMALINK'); ?>">
					<span class="comment-date-at"><?php echo Lang::txt('PF_AT'); ?></span>
					<span class="time"><time datetime="<?php echo $this->comment->get('comment_date_gmt'); ?>"><?php echo $this->comment->created('time'); ?></time></span>
					<span class="comment-date-on"><?php echo Lang::txt('PF_ON'); ?></span>
					<span class="date"><time datetime="<?php echo $this->comment->get('comment_date_gmt'); ?>"><?php echo $this->comment->created('date'); ?></time></span>
				</a>
			</p>

			<div class="comment-body">
				<?php echo $comment; ?>
			</div>

			<p class="comment-options">
				<?php if ($this->config->get('access-delete-comment')) { ?>
					<a class="icon-delete delete" data-confirm="<?php echo Lang::txt('PF_CONFIRM_DELETE'); ?>" href="<?php echo Route::url($this->base . '&action=deletecomment&comment=' . $this->comment->get('comment_ID')); ?>"><!--
						--><?php echo Lang::txt('PF_DELETE'); ?><!--
					--></a>
				<?php } ?>
				<?php if ($this->depth < $this->config->get('comments_depth', 3)) { ?>
					<a class="icon-reply reply" data-txt-active="<?php echo Lang::txt('PF_CANCEL'); ?>" data-txt-inactive="<?php echo Lang::txt('PF_REPLY'); ?>" href="<?php echo Route::url($this->base . '&reply=' . $this->comment->get('comment_ID')); ?>" rel="comment-form<?php echo $this->comment->get('comment_ID'); ?>"><!--
					--><?php echo Lang::txt('PF_REPLY'); ?><!--
				--></a>
				<?php } ?>
				<a class="icon-abuse abuse" data-txt-flagged="<?php echo Lang::txt('PF_COMMENT_REPORTED_AS_ABUSIVE'); ?>" href="<?php echo Route::url('index.php?option=com_support&task=reportabuse&category=pfcomment&id=' . $this->comment->get('comment_ID') . '&parent=' . $this->comment->get('comment_post_ID')); ?>"><!--
					--><?php echo Lang::txt('PF_REPORT_ABUSE'); ?><!--
				--></a>
			</p>

			<?php if ($this->depth < $this->config->get('comments_depth', 3)) { ?>
				<div class="addcomment comment-add hide" id="comment-form<?php echo $this->comment->get('comment_ID'); ?>">
					<form id="cform<?php echo $this->comment->get('comment_ID'); ?>" action="<?php echo Route::url($this->base); ?>" method="post" enctype="multipart/form-data">
						<fieldset>
							<legend><span><?php echo Lang::txt('PF_REPLYING_TO', (!$this->comment->get('anonymous') ? $name : Lang::txt('PF_ANONYMOUS'))); ?></span></legend>

							<input type="hidden" name="comment[comment_ID]" value="0" />
							<input type="hidden" name="comment[comment_post_ID]" value="<?php echo $this->comment->get('comment_post_ID'); ?>" />
							<input type="hidden" name="comment[comment_parent]" value="<?php echo $this->comment->get('comment_ID'); ?>" />
							<input type="hidden" name="comment[user_id]" value="<?php echo User::get('id'); ?>" />
							<input type="hidden" name="comment[comment_approved]" value="1" />
							<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
							<input type="hidden" name="task" value="savecomment" />

							<?php echo Html::input('token'); ?>

							<label for="comment_<?php echo $this->comment->get('id'); ?>_content">
								<span class="label-text"><?php echo Lang::txt('PF_FIELD_COMMENTS'); ?></span>
								<?php
								echo $this->editor('comment[content]', '', 35, 4, 'comment_' . $this->comment->get('comment_ID') . '_content', array('class' => 'minimal no-footer'));
								?>
							</label>

							<label id="comment-anonymous-label" for="comment-anonymous">
								<input class="option" type="checkbox" name="comment[anonymous]" id="comment-anonymous" value="1" />
								<?php echo Lang::txt('PF_POST_ANONYMOUS'); ?>
							</label>

							<p class="submit">
								<input type="submit" value="<?php echo Lang::txt('PF_SUBMIT'); ?>" />
							</p>
						</fieldset>
					</form>
				</div><!-- / .addcomment -->
			<?php } ?>
		</div><!-- / .comment-content -->
		<?php
		if ($this->depth < $this->config->get('comments_depth', 3))
		{
			$replies = $this->comment->replies()
				->whereEquals('comment_approved', 1)
				->ordered()
				->rows();

			$this->view('_list')
				->set('parent', $this->comment->get('comment_ID'))
				->set('option', $this->option)
				->set('comments', $replies)
				->set('config', $this->config)
				->set('depth', $this->depth)
				->set('cls', $cls)
				->set('base', $this->base)
				->display();
		}
		?>
	</li>