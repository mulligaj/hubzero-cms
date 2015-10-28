<?php defined('_HZEXEC_') or die(); ?>

<?php
Html::behavior('keepalive');
Html::behavior('formvalidation');
?>

<header id="content-header">
	<h2><?php echo Lang::txt('Reset your Password'); ?></h2>
</header>

<section class="main section">
	<form action="<?php echo Route::url( 'index.php?option=com_users&task=reset.complete' ); ?>" method="post" class="josForm form-validate" name="hubForm" id="hubForm">
		<fieldset>
			<legend><?php echo Lang::txt('New Password'); ?></legend>

		<?php foreach ($this->form->getFieldsets() as $fieldset): ?>
				<p><?php echo Lang::txt($fieldset->label); ?></p>
				<?php foreach ($this->form->getFieldset($fieldset->name) as $name => $field): ?>
					<?php echo $field->label; ?>
					<?php echo $field->input; ?>
				<?php endforeach; ?>
			</fieldset>
		<?php endforeach; ?>

			<?php
			// Add password rules if they apply
			if (count($this->password_rules) > 0)
			{
				echo "\t\t<ul id=\"passrules\">\n";
				foreach ($this->password_rules as $rule)
				{
					if (!empty($rule))
					{
						echo "\t\t\t<li>".$rule."</li>\n";
					}
				}
				echo "\t\t\t</ul>\n";
			}
			?>

		</fieldset>
		<div class="clear"></div>

		<input type="hidden" id="pass_no_html" name="no_html" value="0" />
		<input type="hidden" name="change" value="1" />
		<p class="submit"><button type="submit" id="password-change-save" class="validate"><?php echo Lang::txt('Submit'); ?></button></p>
		<?php echo Html::input('token'); ?>
	</form>
</section><!-- / .main section -->