<?php
// No direct access
defined('_HZEXEC_') or die();

// Get the permissions helper
$canDo = \Components\PressForward\Helpers\Permissions::getActions('character');

$text = ($this->task == 'edit' ? Lang::txt('JACTION_EDIT') : Lang::txt('JACTION_CREATE'));

Toolbar::title(Lang::txt('COM_DRWHO') . ': ' . Lang::txt('COM_DRWHO_CHARACTERS') . ': ' . $text);
if ($canDo->get('core.edit'))
{
	Toolbar::apply();
	Toolbar::save();
	Toolbar::spacer();
}
Toolbar::cancel();
Toolbar::spacer();
Toolbar::appendButton('Link', 'help', 'help', 'https://github.com/PressForward/pressforward/wiki');

$species = array(
	'???'      => '???',
	'human'    => 'Human',
	'timelord' => 'Time Lord',
	'dalek'    => 'Dalek',
	'cyberman' => 'Cyberman',
	'sontaran' => 'Sontaran',
);
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
	if ($('#field-name').val() == ''){
		alert("<?php echo Lang::txt('COM_DRWHO_ERROR_MISSING_NAME'); ?>");
	} else {
		<?php echo $this->editor()->save('text'); ?>

		submitform(pressbutton);
	}
}
</script>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" class="editform" id="item-form">
	<div class="col width-60 fltlft">
		<fieldset class="adminform">
			<legend><span><?php echo Lang::txt('JDETAILS'); ?></span></legend>

			<div class="input-wrap">
				<label for="field-name"><?php echo Lang::txt('COM_DRWHO_FIELD_NAME'); ?> <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label>
				<input type="text" name="fields[name]" id="field-name" size="35" value="<?php echo $this->escape($this->row->get('name')); ?>" />
			</div>

			<div class="input-wrap">
				<label for="field-species"><?php echo Lang::txt('COM_DRWHO_FIELD_SPECIES'); ?></label>
				<select name="fields[species]" id="field-species">
					<?php foreach ($species as $val => $title) { ?>
						<option<?php if ($this->row->get('species') == $val) { echo ' selected="selected"'; } ?> vlaue="<?php echo $this->escape($val); ?>"><?php echo $this->escape($title); ?></option>
					<?php } ?>
				</select>
			</div>

			<div class="col width-30 fltlft">
				<div class="input-wrap">
					<input class="option" type="checkbox" name="fields[friend]" id="field-friend" value="1" <?php if ($this->row->get('friend', 0)) { echo ' checked="checked"'; } ?> />
					<label for="field-friend"><?php echo Lang::txt('COM_DRWHO_FIELD_FRIEND'); ?></label>
				</div>
			</div>
			<div class="col width-30 fltlft">
				<div class="input-wrap">
					<input class="option" type="checkbox" name="fields[enemy]" id="field-enemy" value="1" <?php if ($this->row->get('enemy', 0)) { echo ' checked="checked"'; } ?> />
					<label for="field-friend"><?php echo Lang::txt('COM_DRWHO_FIELD_ENEMY'); ?></label>
				</div>
			</div>
			<div class="col width-30 fltlft">
				<div class="input-wrap">
					<input class="option" type="checkbox" name="fields[doctor]" id="field-doctor" value="1" <?php if ($this->row->get('doctor', 0)) { echo ' checked="checked"'; } ?> />
					<label for="field-doctor"><?php echo Lang::txt('COM_DRWHO_FIELD_DOCTOR'); ?></label>
				</div>
			</div>
			<div class="clr"></div>

			<div class="input-wrap">
				<label for="field-bio"><?php echo Lang::txt('COM_DRWHO_FIELD_BIO'); ?> <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label>
				<?php echo $this->editor('fields[bio]', $this->escape($this->row->bio('raw')), 50, 15, 'field-bio', array('class' => 'minimal no-footer', 'buttons' => false)); ?>
			</div>
		</fieldset>

		<fieldset class="adminform">
			<legend><span><?php echo Lang::txt('COM_DRWHO_SEASONS'); ?></span></legend>

			<?php
			foreach ($this->seasons as $season) { ?>
				<?php
				$check = false;
				if ($this->row->get('id'))
				{
					foreach ($this->row->seasons as $s)
					{
						if ($s->get('id') == $season->get('id'))
						{
							$check = true;
						}
					}
				}
				?>
				<div class="input-wrap">
					<input class="option" type="checkbox" name="seasons[]" id="season<?php echo $season->get('id'); ?>" <?php if ($check) { echo ' checked="checked'; } ?> value="<?php echo $season->get('id'); ?>" />
					<label for="season<?php echo $season->get('id'); ?>"><?php echo $this->escape($season->get('title')); ?></label>
				</div>
			<?php } ?>
		</fieldset>
	</div>
	<div class="col width-40 fltrt">
		<table class="meta">
			<tbody>
				<tr>
					<th><?php echo Lang::txt('COM_DRWHO_FIELD_ID'); ?>:</th>
					<td>
						<?php echo $this->row->get('id', 0); ?>
						<input type="hidden" name="fields[id]" id="field-id" value="<?php echo $this->escape($this->row->get('id')); ?>" />
					</td>
				</tr>
				<?php if ($this->row->get('state')) { ?>
					<tr>
						<th><?php echo Lang::txt('COM_DRWHO_FIELD_CREATOR'); ?>:</th>
						<td>
							<?php
							$editor = User::getInstance($this->row->get('created_by'));
							echo $this->escape($editor->get('name'));
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
				<label for="field-state"><?php echo Lang::txt('COM_DRWHO_FIELD_STATE'); ?>:</label><br />
				<select name="fields[state]" id="field-state">
					<option value="0"<?php if ($this->row->get('state') == 0) { echo ' selected="selected"'; } ?>><?php echo Lang::txt('JUNPUBLISHED'); ?></option>
					<option value="1"<?php if ($this->row->get('state') == 1) { echo ' selected="selected"'; } ?>><?php echo Lang::txt('JPUBLISHED'); ?></option>
					<option value="2"<?php if ($this->row->get('state') == 2) { echo ' selected="selected"'; } ?>><?php echo Lang::txt('JTRASHED'); ?></option>
				</select>
			</div>
		</fieldset>
	</div>
	<div class="clr"></div>

	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="save" />

	<?php echo Html::input('token'); ?>
</form>