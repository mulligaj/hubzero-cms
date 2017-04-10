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

$canDo = Components\Blog\Admin\Helpers\Permissions::getActions('entry');

$text = ($this->task == 'edit' ? Lang::txt('JACTION_EDIT') : Lang::txt('JACTION_CREATE'));

Toolbar::title(Lang::txt('COM_PRESSFORWARD') . ': ' . Lang::txt('PF_COMMENTS') . ': ' . $text);
if ($canDo->get('core.edit'))
{
	Toolbar::apply();
	Toolbar::save();
	Toolbar::spacer();
}
Toolbar::cancel();
Toolbar::spacer();
Toolbar::appendButton('Link', 'help', 'help', 'https://github.com/PressForward/pressforward/wiki');
?>
<script type="text/javascript">
function submitbutton(pressbutton)
{
	var form = document.adminForm;

	if (pressbutton == 'cancel') {
		submitform(pressbutton);
		return;
	}

	// do field validation
	if ($('#field-content').val() == ''){
		alert("<?php echo Lang::txt('PF_ERROR_MISSING_CONTENT'); ?>");
	} else {
		submitform(pressbutton);
	}
}
</script>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" class="editform" id="item-form">
	<div class="grid">
		<div class="col span7">
			<fieldset class="adminform">
				<legend><span><?php echo Lang::txt('JDETAILS'); ?></span></legend>

				<div class="input-wrap">
					<label for="field-content"><?php echo Lang::txt('PF_COMMENTS'); ?> <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label><br />
					<textarea name="comment[comment_content]" cols="35" rows="5"><?php echo $this->escape($this->row->get('comment_content')); ?></textarea>
				</div>
			</fieldset>
		</div>
		<div class="col span5">
			<table class="meta">
				<tbody>
					<tr>
						<th><?php echo Lang::txt('PF_FIELD_ID'); ?>:</th>
						<td>
							<?php echo $this->row->get('id', 0); ?>
							<input type="hidden" name="comment[comment_ID]" value="<?php echo $this->row->get('comment_ID'); ?>" />
							<input type="hidden" name="id" value="<?php echo $this->row->get('comment_ID'); ?>" />
						</td>
					</tr>
					<tr>
						<th><?php echo Lang::txt('COM_BLOG_FIELD_CREATOR'); ?>:</th>
						<td>
							<?php
							$editor = User::getInstance($this->row->get('comment_author'));
							echo $this->escape($editor->get('name'));
							?>
							<input type="hidden" name="comment[comment_author]" id="field-created_by" value="<?php echo $this->escape($this->row->get('comment_author')); ?>" />
						</td>
					</tr>
					<tr>
						<th><?php echo Lang::txt('PF_FIELD_CREATED'); ?>:</th>
						<td>
							<?php echo $this->row->get('comment_date_gmt'); ?>
							<input type="hidden" name="comment[comment_date_gmt]" id="field-created" value="<?php echo $this->escape($this->row->get('comment_date_gmt')); ?>" />
						</td>
					</tr>
					<tr>
						<th><?php echo Lang::txt('PF_FIELD_ENTRY'); ?>:</th>
						<td>
							<?php echo $this->row->get('entry_id'); ?>
							<input type="hidden" name="comment[comment_post_ID]" id="field-entry_id" value="<?php echo $this->escape($this->row->get('comment_post_ID')); ?>" />
							<input type="hidden" name="post_id" value="<?php echo $this->escape($this->row->get('comment_post_ID')); ?>" />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<input type="hidden" name="comment[comment_approved]" value="pressforward-comment" />
	<input type="hidden" name="comment[comment_type]" value="pressforward-comment" />
	<input type="hidden" name="comment[comment_parent]" value="<?php echo $this->row->get('comment_parent'); ?>" />

	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="save" />

	<?php echo Html::input('token'); ?>
</form>