<?php
// No direct access
defined('_HZEXEC_') or die();

// Get the permissions helper
$canDo = Components\PressForward\Helpers\Permissions::getActions('folder');

// Toolbar is a helper class to simplify the creation of Toolbar 
// titles, buttons, spacers and dividers in the Admin Interface.
//
// Here we'll had the title of the component and options
// for saving based on if the user has permission to
// perform such actions. Everyone gets a cancel button.
$text = ($this->task == 'edit' ? Lang::txt('JACTION_EDIT') : Lang::txt('JACTION_CREATE'));

Toolbar::title(Lang::txt('COM_PRESSFORWARD') . ': ' . Lang::txt('PF_FOLDERS') . ': ' . $text);
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
Joomla.submitbutton = function(pressbutton) {
	var form = document.adminForm;

	if (pressbutton == 'cancel') {
		Joomla.submitform(pressbutton, document.getElementById('item-form'));
		return;
	}

	// do field validation
	if ($('#field-name').val() == ''){
		alert("<?php echo Lang::txt('PF_ERROR_MISSING_NAME'); ?>");
	} else {
		Joomla.submitform(pressbutton, document.getElementById('item-form'));
	}
}
</script>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" class="editform" id="item-form">
	<div class="grid">
		<div class="col span7">
			<fieldset class="adminform">
				<legend><span><?php echo Lang::txt('JDETAILS'); ?></span></legend>

				<div class="input-wrap" data-hint="<?php echo Lang::txt('The name is how it appears on your site.'); ?>">
					<label for="field-name"><?php echo Lang::txt('PF_FIELD_NAME'); ?> <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label>
					<input type="text" name="folder[name]" id="field-name" size="35" value="<?php echo $this->escape($this->row->folder->get('name')); ?>" />
					<span class="hint"><?php echo Lang::txt('The name is how it appears on your site.'); ?></span>
				</div>

				<div class="input-wrap" data-hint="<?php echo Lang::txt('The &quot;slug&quot; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.'); ?>">
					<label for="field-slug"><?php echo Lang::txt('PF_FIELD_SLUG'); ?>:</label>
					<input type="text" name="folder[slug]" id="field-slug" size="35" value="<?php echo $this->escape($this->row->folder->get('slug')); ?>" />
					<span class="hint"><?php echo Lang::txt('The &quot;slug&quot; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.'); ?></span>
				</div>

				<div class="input-wrap">
					<label for="field-parent"><?php echo Lang::txt('PF_FIELD_PARENT'); ?>:</label>
					<select name="taxonomy[parent]" id="field-parent">
						<option value="0"><?php echo Lang::txt('PF_NONE'); ?></option>
						<?php foreach ($this->folders as $folder) { ?>
							<option value="<?php echo $this->escape($folder->get('term_taxonomy_id')); ?>"<?php if ($folder->get('term_taxonomy_id') == $this->row->get('parent')) { echo ' selected="selected"'; } ?>><?php echo $this->escape($folder->get('name')); ?></option>
						<?php } ?>
					</select>
				</div>

				<div class="input-wrap" data-hint="<?php echo Lang::txt('The description is not prominent by default; however, some themes may show it.'); ?>">
					<label for="field-description"><?php echo Lang::txt('PF_FIELD_DESCRIPTION'); ?>:</label>
					<textarea name="taxonomy[description]" id="field-description" cols="50" rows="3"><?php echo $this->escape($this->row->get('description')); ?></textarea>
					<span class="hint"><?php echo Lang::txt('The description is not prominent by default; however, some themes may show it.'); ?></span>
				</div>
			</fieldset>
		</div>
		<div class="col span5">
			<table class="meta">
				<tbody>
					<tr>
						<th><?php echo Lang::txt('PF_FIELD_ID'); ?>:</th>
						<td>
							<?php echo $this->row->get('term_id', 0); ?>
							<input type="hidden" name="folder[term_id]" value="<?php echo $this->row->get('term_id'); ?>" />
							<input type="hidden" name="taxonomy[term_taxonomy_id]" value="<?php echo $this->row->get('term_taxonomy_id'); ?>" />
						</td>
					</tr>
					<tr>
						<th><?php echo Lang::txt('PF_FIELD_COUNT'); ?>:</th>
						<td>
							<?php echo $this->row->get('count', 0); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="save" />

	<?php echo Html::input('token'); ?>
</form>