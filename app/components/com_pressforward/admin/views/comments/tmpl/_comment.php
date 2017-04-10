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

$name = $this->escape(stripslashes($this->comment->creator->get('name')));

$comment  = $this->comment->get('comment_content');
?>
	<li class="pressforward-comment <?php echo $cls; ?> comment-item depth-<?php echo $this->depth; ?>" id="comment-<?php echo $this->comment->get('comment_ID'); ?>">
		<img class="avatar avatar-50 photo" width="50" src="<?php echo $this->comment->creator->picture($this->comment->get('anonymous')); ?>" alt="" />

		<div class="post-comment-wrap">
			<h5 class="comment-meta">
				<?php echo $name; ?>
				<span class="meta"><?php echo Lang::txt('said on %s at %s', Date::of($this->comment->get('comment_date_gmt'))->toLocal('M d, Y'), Date::of($this->comment->get('comment_date_gmt'))->toLocal(Lang::txt('TIME_FORMAT_HZ1'))); ?></time></span>
			</h5>

			<div class="comment-content">
				<?php echo $comment; ?>
			</div>

			<p class="row-actions">
				<?php if ($this->config->get('access-delete-comment')) { ?>
					<a class="icon-delete delete" data-confirm="<?php echo Lang::txt('PF_CONFIRM_DELETE'); ?>" href="<?php echo Route::url($this->base . '&action=deletecomment&comment=' . $this->comment->get('comment_ID')); ?>"><!--
						--><?php echo Lang::txt('PF_DELETE'); ?><!--
					--></a>
				<?php } ?>
				<?php if ($this->config->get('access-edit-comment') || User::get('id') == $this->comment->get('created_by')) { ?>
					<a class="icon-edit edit" href="<?php echo Route::url($this->base . '&action=editcomment&comment=' . $this->comment->get('comment_ID')); ?>"><!--
						--><?php echo Lang::txt('PF_EDIT'); ?><!--
					--></a>
				<?php } ?>
				<?php if ($this->depth < $this->config->get('comments_depth', 3)) { ?>
					<?php if (Request::getInt('reply', 0) == $this->comment->get('comment_ID')) { ?>
						<a class="icon-reply reply active" data-txt-active="<?php echo Lang::txt('PF_CANCEL'); ?>" data-txt-inactive="<?php echo Lang::txt('PF_REPLY'); ?>" href="<?php echo Route::url($this->base); ?>" rel="ef-replyrow<?php echo $this->comment->get('comment_ID'); ?>"><!--
							--><?php echo Lang::txt('PF_CANCEL'); ?><!--
						--></a>
					<?php } else { ?>
						<a class="icon-reply reply" id="ef-comment_respond<?php echo $this->comment->get('comment_ID'); ?>" data-txt-active="<?php echo Lang::txt('PF_CANCEL'); ?>" data-txt-inactive="<?php echo Lang::txt('PF_REPLY'); ?>" href="<?php echo Route::url($this->base . '&reply=' . $this->comment->get('comment_ID')); ?>" rel="ef-replyrow<?php echo $this->comment->get('comment_ID'); ?>"><!--
							--><?php echo Lang::txt('PF_REPLY'); ?><!--
						--></a>
					<?php } ?>
				<?php } ?>
			</p>
		</div><!-- / .comment-content -->

		<?php if ($this->depth < $this->config->get('comments_depth', 3)) { ?>
			<div class="hide" id="ef-replyrow<?php echo $this->comment->get('comment_ID'); ?>">
				<form class="ef-reply" id="cform<?php echo $this->comment->get('comment_ID'); ?>" action="<?php echo Route::url($this->base); ?>" method="post" enctype="multipart/form-data">
					<fieldset>
						<legend><span><?php echo Lang::txt('PF_REPLYING_TO', $name); ?></span></legend>

						<input type="hidden" name="comment[comment_ID]"  value="0" />
						<input type="hidden" name="comment[comment_post_ID]" value="<?php echo $this->comment->get('comment_post_ID'); ?>" />
						<input type="hidden" name="comment[comment_parent]" id="ef-comment_parent<?php echo $this->comment->get('comment_ID'); ?>" value="<?php echo $this->comment->get('comment_ID'); ?>" />
						<!-- <input type="hidden" name="comment[comment_author]" value="<?php echo User::get('username'); ?>" />
						<input type="hidden" name="comment[comment_author_IP]" value="<?php echo Request::ip(); ?>" />
						<input type="hidden" name="comment[comment_author_email]" value="<?php echo User::get('email'); ?>" /> -->
						<input type="hidden" name="comment[comment_approved]" value="pressforward-comment" />
						<input type="hidden" name="comment[comment_type]" value="pressforward-comment" />
						<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
						<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
						<input type="hidden" name="no_html" value="1" />
						<input type="hidden" name="post_id" value="<?php echo $this->comment->get('comment_post_ID'); ?>" />
						<input type="hidden" name="task" value="save" />

						<?php echo Html::input('token'); ?>

						<label for="ef-replycontent<?php echo $this->comment->get('comment_ID'); ?>">
							<span class="label-text"><?php echo Lang::txt('PF_COMMENTS'); ?></span>
							<textarea name="comment[comment_content]" cols="35" rows="4" class="ef-replycontent" id="ef-replycontent<?php echo $this->comment->get('comment_ID'); ?>"></textarea>
						</label>

						<div class="error" style="display:none;"></div>

						<p id="ef-replysubmit<?php echo $this->comment->get('comment_ID'); ?>" class="ef-replysubmit">
							<a class="button ef-replycancel" rel="ef-replyrow<?php echo $this->comment->get('comment_ID'); ?>" href="#ef-comment_respond<?php echo $this->comment->get('comment_ID'); ?>"><?php echo Lang::txt('PF_CANCEL'); ?></a>
							<input type="submit" class="button ef-replysave" value="<?php echo Lang::txt('PF_SUBMIT_RESPONSE'); ?>" />
						</p>
					</fieldset>
				</form>
			</div><!-- / .addcomment -->
		<?php } ?>
	</li>
		<?php
		if ($this->depth < $this->config->get('comments_depth', 3))
		{
			$replies = $this->comment->replies()
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
