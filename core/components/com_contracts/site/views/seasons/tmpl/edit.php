<?php
// Push CSS to the document
//
// The css() method provides a quick and convenient way to attach stylesheets. 
// 
// 1. The name of the stylesheet to be pushed to the document (file extension is optional). 
//    If no name is provided, the name of the component or plugin will be used. For instance, 
//    if called within a view of the component com_tags, the system will look for a stylesheet named tags.css.
// 
// 2. The name of the extension to look for the stylesheet. For components, this will be 
//    the component name (e.g., com_tags). For plugins, this is the name of the plugin folder 
//    and requires the third argument be passed to the method.
//
// Method chaining is also allowed.
// $this->css()  
//      ->css('another');

$this->css();

// Similarly, a js() method is available for pushing javascript assets to the document.
// The arguments accepted are the same as the css() method described above.
//
// $this->js();

// Set the document title
//
// This sets the <title> tag of the document and will overwrite any previous
// title set. To append or modify an existing title, it must be retrieved first
// with $title = Document::getTitle();
Document::setTitle(Lang::txt('COM_DRWHO') . ': ' . Lang::txt('COM_DRWHO_SEASONS') . ': ' . ($this->entry->get('id') ? Lang::txt('JACTION_EDIT') : Lang::txt('JACTION_NEW')));

// Set the pathway (breadcrumbs)
//
// Breadcrumbs are displayed via a breadcrumbs module and may or may not be enabled for
// all hubs and/or templates. In general, it's good practice to set the pathway
// even if it's unknown if hey will be displayed or not.
Pathway::append(
	Lang::txt('COM_DRWHO'),  // Text to display
	'index.php?option=' . $this->option  // Link. Route::url() not needed.
);
Pathway::append(
	Lang::txt('COM_DRWHO_SEASONS'),
	'index.php?option=' . $this->option . '&controller=' . $this->controller
);
?>
<header id="content-header">
	<h2><?php echo Lang::txt('COM_DRWHO'); ?>: <?php echo Lang::txt('COM_DRWHO_SEASONS'); ?></h2>
</header>
<section class="main section">
	<?php if ($this->getError()) { ?>
		<p class="error"><?php echo $this->getError(); ?></p>
	<?php } ?>

	<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=save'); ?>" method="post" id="hubForm">
		<div class="explaination">
			<p><?php echo Lang::txt('COM_DRWHO_HELP'); ?></p>
		</div>
		<fieldset>
			<legend><?php echo Lang::txt('COM_DRWHO_DETAILS'); ?></legend>

			<label for="field-name">
				<?php echo Lang::txt('COM_DRWHO_TITLE'); ?> <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span>
				<input type="text" name="entry[title]" id="field-title" size="35" value="<?php echo $this->escape($this->entry->get('title')); ?>" />
			</label>

			<label for="field-doctor_id">
				<?php echo Lang::txt('COM_DRWHO_DOCTOR'); ?>
				<select name="entry[doctor_id]" id="field-doctor_id">
					<?php foreach ($this->doctors as $doctor) { ?>
						<option<?php if ($this->entry->get('doctor_id') == $doctor->get('id')) { echo ' selected="selected"'; } ?> vlaue="<?php echo $this->escape($doctor->get('id')); ?>"><?php echo $this->escape($doctor->get('name')); ?></option>
					<?php } ?>
				</select>
			</label>

			<div class="grid">
				<div class="col span6">
					<label for="field-premiere_date">
						<?php echo Lang::txt('COM_DRWHO_PREMIERE_DATE'); ?>
						<input type="text" name="entry[premiere_date]" id="field-premiere_date" value="<?php echo $this->escape($this->entry->get('premiere_date')); ?>" />
						<span class="hint">YYYY-MM-DD mm:hh:ss</span>
					</label>
				</div>

				<div class="col span6 omega">
					<label for="field-finale_date">
						<?php echo Lang::txt('COM_DRWHO_FINALE_DATE'); ?>
						<input type="text" name="entry[finale_date]" id="field-finale_date" value="<?php echo $this->escape($this->entry->get('finale_date')); ?>" />
						<span class="hint">YYYY-MM-DD mm:hh:ss</span>
					</label>
				</div>
			</div>
		</fieldset>
		<div class="clear"></div>

		<input type="hidden" name="id" value="<?php echo $this->escape($this->entry->get('created_by')); ?>" />
		<input type="hidden" name="entry[id]" value="<?php echo $this->escape($this->entry->get('id')); ?>" />
		<input type="hidden" name="entry[state]" value="<?php echo $this->escape($this->entry->get('state', 1)); ?>" />
		<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
		<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
		<input type="hidden" name="task" value="save" />

		<?php echo Html::input('token'); ?>

		<p class="submit">
			<input class="btn btn-success" type="submit" value="<?php echo Lang::txt('COM_DRWHO_SAVE'); ?>" />

			<a class="btn btn-secondary" href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>">
				<?php echo Lang::txt('COM_DRWHO_CANCEL'); ?>
			</a>
		</p>
	</form>
</section>