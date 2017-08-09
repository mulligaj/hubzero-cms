<?php
// No direct access
defined('_HZEXEC_') or die();

// Get the permissions helper
$canDo = \Components\Contracts\Helpers\Permissions::getActions('character');

// Toolbar is a helper class to simplify the creation of Toolbar 
// titles, buttons, spacers and dividers in the Admin Interface.
//
// Here we'll had the title of the component and options
// for saving based on if the user has permission to
// perform such actions. Everyone gets a cancel button.
$text = ($this->task == 'edit' ? Lang::txt('JACTION_EDIT') : Lang::txt('JACTION_CREATE'));

Toolbar::title(Lang::txt('COM_CONTRACTS') . ': ' . Lang::txt('COM_CONTRACT_AGREEMENTS') . ': ' . $text);
if ($canDo->get('core.edit'))
{
	Toolbar::apply();
	Toolbar::save();
	Toolbar::spacer();
}
Toolbar::cancel();
Toolbar::spacer();
Toolbar::help('character');
$this->css('contracts');
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
	if ($('#field-title').val() == ''){
		alert("<?php echo Lang::txt('COM_CONTRACTS_ERROR_MISSING_NAME'); ?>");
	} else {
		<?php echo $this->editor()->save('text'); ?>

		submitform(pressbutton);
	}
}
</script>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" class="editform" id="item-form">
	<div class="grid">
		<div class="col span7">
			<fieldset class="adminform">
				<legend><span><?php echo Lang::txt('COM_CONTRACTS_ORGANIZATION_AREA'); ?></span></legend>
				<div class="input-wrap">
					<label for="field-organization_name"><?php echo Lang::txt('COM_CONTRACTS_FIELD_ORGANIZATION'); ?> <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label>
					<input type="text" name="fields[organization_name]" id="field-organization_name" size="35" value="<?php echo $this->escape($this->row->get('organization_name')); ?>" />
				</div>
				<div class="input-wrap">
					<label for="field-organization_address"><?php echo Lang::txt('COM_CONTRACTS_FIELD_ORGANIZATION_ADDRESS'); ?> <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label>
					<textarea name="fields[organization_address]" rows="5" id="field-organization_address"><?php echo $this->escape($this->row->get('organization_address')); ?></textarea>
				</div>
			</fieldset>
		</div>
		<div class="col span5">
			<table class="meta">
				<tbody>
					<tr>
						<th><?php echo Lang::txt('COM_CONTRACTS_FIELD_ID'); ?></th>
						<td>
							<?php echo $this->row->get('id', 0); ?>
							<input type="hidden" name="fields[id]" id="field-id" value="<?php echo $this->escape($this->row->get('id')); ?>" />
						</td>
					</tr>
					<tr>
						<th><?php echo Lang::txt('COM_CONTRACTS_FIELD_CONTRACT'); ?></th>
						<td>
							<?php echo $this->row->contract->title; ?>
						</td>
					</tr>
					<tr>
						<th><?php echo Lang::txt('COM_CONTRACTS_FIELD_ACCEPTED'); ?></th>
						<td>
							<?php echo $this->row->accepted; ?>
						</td>
					</tr>
				<?php if ($this->row->get('created')) { ?>
					<tr>
						<th><?php echo Lang::txt('COM_CONTRACTS_FIELD_CREATED'); ?></th>
						<td>
							<?php echo Date::of($this->row->get('created'))->toLocal('M jS, Y @ g:i A'); ?>
						</td>
					</tr>
				<?php } ?>
					<tr>
						<th><?php echo Lang::txt('COM_CONTRACTS_FIELD_AUTHORITY'); ?></th>
						<td>
							<?php echo $this->row->authority; ?>
						</td>
					</tr>
				</tbody>
			</table>
			<fieldset class="adminform">
				<legend><span><?php echo Lang::txt('COM_CONTRACTS_CONTACT_AREA'); ?></span></legend>
				<div class="input-wrap">
					<label for="field-firstname"><?php echo Lang::txt('COM_CONTRACTS_FIELD_FIRSTNAME'); ?> <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label>
					<input type="text" name="fields[firstname]" id="field-firstname" size="35" value="<?php echo $this->escape($this->row->get('firstname')); ?>" />
				</div>
				<div class="input-wrap">
					<label for="field-lastname"><?php echo Lang::txt('COM_CONTRACTS_FIELD_LASTNAME'); ?> <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label>
					<input type="text" name="fields[lastname]" id="field-lastname" size="35" value="<?php echo $this->escape($this->row->get('lastname')); ?>" />
				</div>
				<div class="input-wrap">
					<label for="field-email"><?php echo Lang::txt('COM_CONTRACTS_FIELD_EMAIL'); ?> <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label>
					<input type="text" name="fields[email]" id="field-email" size="35" value="<?php echo $this->escape($this->row->get('email')); ?>" />
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="save" />
	<?php echo Html::input('token'); ?>
	</form>
