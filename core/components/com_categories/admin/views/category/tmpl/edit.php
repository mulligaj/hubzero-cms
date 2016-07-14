<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_HZEXEC_') or die();

// Include the component HTML helpers.
Html::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
Html::behavior('tooltip');
Html::behavior('formvalidation');
Html::behavior('keepalive');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'category.cancel' || document.formvalidator.isValid($('#item-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('item-form'));
		} else {
			alert('<?php echo $this->escape(Lang::txt('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo Route::url('index.php?option=com_categories&extension='.Request::getCmd('extension', 'com_content').'&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="grid">
		<div class="col span7">
			<fieldset class="adminform">
				<legend><span><?php echo Lang::txt('COM_CATEGORIES_FIELDSET_DETAILS');?></span></legend>

				<div class="input-wrap">
					<?php echo $this->form->getLabel('title'); ?>
					<?php echo $this->form->getInput('title'); ?>
				</div>

				<div class="input-wrap">
					<?php echo $this->form->getLabel('alias'); ?>
					<?php echo $this->form->getInput('alias'); ?>
				</div>

				<?php echo $this->form->getInput('extension'); ?>
				<?php
				/*<div class="input-wrap">
					<?php echo $this->form->getLabel('extension'); ?>
					<?php echo $this->form->getInput('extension'); ?>
				</div>*/
				?>

				<div class="input-wrap">
					<?php echo $this->form->getLabel('parent_id'); ?>
					<?php echo $this->form->getInput('parent_id'); ?>
				</div>

				<div class="grid">
					<div class="col span6">
						<div class="input-wrap">
							<?php echo $this->form->getLabel('published'); ?>
							<?php echo $this->form->getInput('published'); ?>
						</div>
					</div>
					<div class="col span6">
						<div class="input-wrap">
							<?php echo $this->form->getLabel('access'); ?>
							<?php echo $this->form->getInput('access'); ?>
						</div>
					</div>
				</div>

					<?php /*if ($this->canDo->get('core.admin')): ?>
						<div class="input-wrap">
							<span class="faux-label"><?php echo Lang::txt('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></span>
							<div class="button2-left">
								<div class="blank">
									<button type="button" onclick="document.location.href='#access-rules';">
										<?php echo Lang::txt('JGLOBAL_PERMISSIONS_ANCHOR'); ?>
									</button>
								</div>
							</div>
						</div>
					<?php endif;*/ ?>

				<div class="input-wrap">
					<?php echo $this->form->getLabel('language'); ?>
					<?php echo $this->form->getInput('language'); ?>
				</div>

				<div class="input-wrap">
					<?php echo $this->form->getLabel('id'); ?>
					<?php echo $this->form->getInput('id'); ?>
				</div>

				<div class="input-wrap">
					<?php echo $this->form->getLabel('description'); ?>
					<?php echo $this->form->getInput('description'); ?>
				</div>
			</fieldset>
		</div>

		<div class="col span5">
			<?php echo Html::sliders('start', 'categories-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
				<?php echo $this->loadTemplate('options'); ?>
				<div class="clr"></div>

				<?php echo Html::sliders('panel', Lang::txt('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'meta-options'); ?>
				<fieldset class="panelform">
					<?php echo $this->loadTemplate('metadata'); ?>
				</fieldset>

				<?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
				<?php foreach ($fieldSets as $name => $fieldSet) : ?>
					<?php $label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_CATEGORIES_'.$name.'_FIELDSET_LABEL'; ?>
					<?php if ($name != 'editorConfig' && $name != 'basic-limited') : ?>
						<?php echo Html::sliders('panel', Lang::txt($label), $name.'-options'); ?>
						<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
							<p class="tip"><?php echo $this->escape(Lang::txt($fieldSet->description));?></p>
						<?php endif; ?>
						<fieldset class="panelform">
							<?php foreach ($this->form->getFieldset($name) as $field) : ?>
								<div class="input-wrap">
									<?php echo $field->label; ?>
									<?php echo $field->input; ?>
								</div>
							<?php endforeach; ?>
						</fieldset>
					<?php endif ?>
				<?php endforeach; ?>
			<?php echo Html::sliders('end'); ?>
		</div>
	</div>

	<?php if ($this->canDo->get('core.admin')): ?>
		<div class="width-100">

			<?php //echo Html::sliders('start', 'permissions-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

			<?php //echo Html::sliders('panel', Lang::txt('COM_CATEGORIES_FIELDSET_RULES'), 'access-rules'); ?>
			<fieldset class="panelform">

				<?php echo $this->form->getLabel('rules'); ?>
				<?php echo $this->form->getInput('rules'); ?>
			</fieldset>

			<?php //echo Html::sliders('end'); ?>
		</div>
	<?php endif; ?>

	<input type="hidden" name="task" value="" />
	<?php echo Html::input('token'); ?>
</form>
