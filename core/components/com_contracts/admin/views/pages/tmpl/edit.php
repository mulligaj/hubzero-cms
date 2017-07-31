<?php
// No direct access
defined('_HZEXEC_') or die();

// Get the permissions helper
$canDo = \Components\Drwho\Helpers\Permissions::getActions('season');

// Toolbar is a helper class to simplify the creation of Toolbar 
// titles, buttons, spacers and dividers in the Admin Interface.
//
// Here we'll had the title of the component and options
// for saving based on if the user has permission to
// perform such actions. Everyone gets a cancel button.
$text = ($this->task == 'edit' ? Lang::txt('JACTION_EDIT') : Lang::txt('JACTION_CREATE'));

Toolbar::title(Lang::txt('COM_DRWHO') . ': ' . Lang::txt('COM_DRWHO_SEASONS') . ': ' . $text);
if ($canDo->get('core.edit'))
{
	Toolbar::apply();
	Toolbar::save();
	Toolbar::spacer();
}
Toolbar::cancel();
Toolbar::spacer();
Toolbar::help('entry');
?>
<script type="text/javascript">
Joomla.submitbutton = function(pressbutton) {
	var form = document.adminForm;

	if (pressbutton == 'cancel') {
		Joomla.submitform(pressbutton, document.getElementById('item-form'));
		return;
	}

	// do field validation
	if ($('#field-title').val() == ''){
		alert("<?php echo Lang::txt('COM_DRWHO_ERROR_MISSING_TITLE'); ?>");
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

				<div class="grid">
					<div class="col span6">
						<div class="input-wrap">
							<label for="field-name"><?php echo Lang::txt('COM_DRWHO_FIELD_TITLE'); ?> <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label>
							<input type="text" name="fields[title]" id="field-title" size="35" value="<?php echo $this->escape($this->row->get('title')); ?>" />
						</div>
					</div>
					<div class="col span6">
						<div class="input-wrap">
							<label for="field-doctor_id"><?php echo Lang::txt('COM_DRWHO_FIELD_DOCTOR'); ?></label>
							<select name="fields[doctor_id]" id="field-doctor_id">
								<?php foreach ($this->doctors as $doctor) { ?>
									<option<?php if ($this->row->get('doctor_id') == $doctor->get('id')) { echo ' selected="selected"'; } ?> vlaue="<?php echo $this->escape($doctor->get('id')); ?>"><?php echo $this->escape($doctor->get('name')); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
				</div>

				<div class="grid">
					<div class="col span6">
						<div class="input-wrap" data-hint="YYYY-MM-DD mm:hh:ss">
							<label for="field-premiere_date"><?php echo Lang::txt('COM_DRWHO_FIELD_PREMIERE'); ?></label>
							<input type="text" name="fields[premiere_date]" id="field-premiere_date" value="<?php echo $this->escape($this->row->get('premiere_date')); ?>" placeholder="YYYY-MM-DD mm:hh:ss" />
							<span class="hint">YYYY-MM-DD mm:hh:ss</span>
						</div>
					</div>
					<div class="col span6">
						<div class="input-wrap" data-hint="YYYY-MM-DD mm:hh:ss">
							<label for="field-finale_date"><?php echo Lang::txt('COM_DRWHO_FIELD_FINALE'); ?></label>
							<input type="text" name="fields[finale_date]" id="field-finale_date" value="<?php echo $this->escape($this->row->get('finale_date')); ?>" placeholder="YYYY-MM-DD mm:hh:ss" />
							<span class="hint">YYYY-MM-DD mm:hh:ss</span>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="col span5">
			<table class="meta">
				<tbody>
					<tr>
						<th><?php echo Lang::txt('COM_DRWHO_FIELD_ID'); ?>:</th>
						<td>
							<?php echo $this->row->get('id', 0); ?>
							<input type="hidden" name="fields[id]" value="<?php echo $this->row->get('id'); ?>" />
						</td>
					</tr>
					<?php if ($this->row->get('id')) { ?>
						<tr>
							<th><?php echo Lang::txt('COM_DRWHO_FIELD_CREATOR'); ?>:</th>
							<td>
								<?php
								$editor = User::getInstance($this->row->get('created_by'));
								echo $this->escape(stripslashes($editor->get('name')));
								?>
								<input type="hidden" name="fields[created_by]" id="field-created_by" value="<?php echo $this->escape($this->row->get('created_by')); ?>" />
							</td>
						</tr>
						<tr>
							<th><?php echo Lang::txt('COM_DRWHO_FIELD_CREATED'); ?>:</th>
							<td>
								<?php echo $this->row->get('created'); ?>
								<input type="hidden" name="fields[created]" id="field-created" value="<?php echo $this->escape($this->row->get('created')); ?>" />
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>

			<fieldset class="adminform">
				<legend><span><?php echo Lang::txt('JGLOBAL_FIELDSET_PUBLISHING'); ?></span></legend>

				<div class="input-wrap">
					<label for="field-state"><?php echo Lang::txt('COM_DRWHO_FIELD_STATE'); ?>:</label>
					<select name="fields[state]" id="field-state">
						<option value="0"<?php if ($this->row->get('state') == 0) { echo ' selected="selected"'; } ?>><?php echo Lang::txt('JUNPUBLISHED'); ?></option>
						<option value="1"<?php if ($this->row->get('state') == 1) { echo ' selected="selected"'; } ?>><?php echo Lang::txt('JPUBLISHED'); ?></option>
						<option value="2"<?php if ($this->row->get('state') == 2) { echo ' selected="selected"'; } ?>><?php echo Lang::txt('JTRASHED'); ?></option>
					</select>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="save" />

	<?php echo Html::input('token'); ?>
</form>