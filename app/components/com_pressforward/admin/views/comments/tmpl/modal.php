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

// No direct access
defined('_HZEXEC_') or die();
?>
<h2 class="modal-title"><?php echo Lang::txt('Comments'); ?></h2>

<div id="ef-comments_wrapper">
	<?php
	if ($this->rows->count() > 0)
	{
		$this->view('_list')
			->set('parent', 0)
			->set('option', $this->option)
			->set('comments', $this->rows)
			->set('config', $this->config)
			->set('depth', 0)
			->set('cls', 'odd')
			->set('base', 'index.php?option=' . $this->option . '&controller=' . $this->controller)
			->display();
	}
	else
	{
		echo '<p>No comments found.</p>';
	}
	?>

	<p class="ef-replysubmit">
		<a href="#ef-replyrow" id="ef-comment_respond" class="button button-primary alignright hide-if-no-js reply" rel="ef-replyrow"><span><?php echo Lang::txt('Add Comment'); ?></span></a>
	</p>

	<div class="hide" id="ef-replyrow">
		<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" class="ef-reply" id="ef-reply">
			<fieldset>
				<legend><span><?php echo Lang::txt('PF_ADD_COMMENT'); ?></span></legend>

				<input type="hidden" name="comment[comment_ID]"  value="0" />
				<input type="hidden" name="comment[comment_post_ID]" value="<?php echo $this->filters['post_id']; ?>" />
				<input type="hidden" name="comment[comment_parent]" value="0" />
				<!--<input type="hidden" name="comment[comment_author]" value="<?php echo User::get('username'); ?>" />
				<input type="hidden" name="comment[comment_author_IP]" value="<?php echo Request::ip(); ?>" />
				<input type="hidden" name="comment[comment_author_email]" value="<?php echo User::get('email'); ?>" />-->
				<input type="hidden" name="comment[comment_approved]" value="pressforward-comment" />
				<input type="hidden" name="comment[comment_type]" value="pressforward-comment" />
				<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
				<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
				<input type="hidden" name="no_html" value="1" />
				<input type="hidden" name="post_id" value="<?php echo $this->filters['post_id']; ?>" />
				<input type="hidden" name="task" value="save" />

				<?php echo Html::input('token'); ?>

				<label for="ef-replycontent">
					<span class="label-text"><?php echo Lang::txt('PF_COMMENTS'); ?></span>
					<textarea name="comment[comment_content]" class="ef-replycontent" cols="35" rows="4" id="ef-replycontent"></textarea>
				</label>

				<div class="error" style="display:none;"></div>

				<p id="ef-replysubmit" class="ef-replysubmit">
					<a class="button ef-replycancel" rel="ef-replyrow" href="#ef-comment_respond"><?php echo Lang::txt('PF_CANCEL'); ?></a>
					<input type="submit" class="button ef-replysave" value="<?php echo Lang::txt('PF_SUBMIT_RESPONSE'); ?>" />
				</p>
			</fieldset>
		</form>
	</div>
</div>
