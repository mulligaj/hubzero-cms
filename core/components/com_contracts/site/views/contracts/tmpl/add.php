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
$this->js();

// Set the document title
//
// This sets the <title> tag of the document and will overwrite any previous
// title set. To append or modify an existing title, it must be retrieved first
// with $title = Document::getTitle();
Document::setTitle(Lang::txt('COM_CONTRACTS'));

// Set the pathway (breadcrumbs)
//
// Breadcrumbs are displayed via a breadcrumbs module and may or may not be enabled for
// all hubs and/or templates. In general, it's good practice to set the pathway
// even if it's unknown if hey will be displayed or not.
Pathway::append(
	Lang::txt('COM_CONTRACTS'),  // Text to display
	'index.php?option=' . $this->option  // Link. Route::url() not needed.
);
?>
<header id="content-header">
	<h2><?php echo Lang::txt('COM_CONTRACTS'); ?></h2>
</header>

<section class="main section">
	<form id="hubForm" action="<?php echo Route::url('index.php?option=' . $this->option . '&task=save' . '&alias=' . $this->agreement->contract->alias);?>" method="post">
	<fieldset>
		<legend>Contact Information</legend>
		<div class="grid">
			<div class="col span6">
				<label for="firstname">
					First Name
					<input type="text" name="firstname" id="firstname" value="<?php echo $this->escape($this->agreement->firstname); ?>"/>
				</label>
			</div>
			<div class="col span6 omega">
				<label for="lastname">
					Last Name
					<input type="text" name="lastname" id="lastname"  value="<?php echo $this->escape($this->agreement->lastname); ?>" />
				</label>
			</div>
			<div class="col span6 omega">
				<label for="email">
					E-mail
					<input type="text" name="email" id="email" value="<?php echo $this->escape($this->agreement->email); ?>" />
				</label>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Organization Information</legend>
		<div class="grid">
			<div class="col span12 omega">
				<label for="organization_name">
					Organization Name
					<input type="text" name="organization_name" id="organization_name" value="<?php echo $this->escape($this->agreement->organization_name); ?>" />
				</label>
			</div>
			<div class="col span12 omega">
				<label for="organization_address">
					Organization Address
					<textarea name="organization_address" id="organization_address" rows="5"><?php echo $this->escape($this->agreement->organization_address); ?></textarea>
				</label>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Contract Agreement</legend>
		<div class="grid">
			<?php echo Html::input('token'); ?>
			<fieldset class="radio-options">
				<p>Do you have authority to authorize contract agreements on behalf of this organization?</p>
				<input type="radio" class="option-hidden" name="authority" value="" id="authorized-none" <?php echo !is_numeric($this->agreement->get('authority')) ? 'checked="checked"' : '';?> />
				<label for="authorized-none">No Option Selected</label>
				<input type="radio" class="option" name="authority" value="1" id="authorized-yes" <?php echo $this->agreement->get('authority') == 1 ? 'checked="checked"' : '';?> />
				<label for="authorized-yes">Yes, I have authority and would like to review the agreement now.</label>
				<input type="radio" class="option" name="authority" value="0" id="authorized-no" <?php echo is_numeric($this->agreement->get('authority')) && $this->agreement->get('authority') == 0 ? 'checked="checked"' : '';?> />
				<label for="authorized-no">No, I would like to recieve an electronic copy via email for further review and/or signature by someone else.</label>
				<p> If you select "Yes", the agreement will appear below. Please read the entire agreement. When finished reading, select an agreement option at the end of the agreement.</p>
			</fieldset>
			<div class="col span12 omega">
					<section class="contract" id="contract-section">
						<?php if ($this->agreement->documentViewable()): ?>
							<?php echo $this->loadTemplate('agreement'); ?>				
						<?php endif; ?>
					</section>
			</div>
		</div>
	</fieldset>
	<p class="submit">
		<input type="hidden" name="contract_id" value="<?php echo $this->agreement->contract->id; ?>" />
		<input type="submit" value="Continue" class="btn" />
	</p>
	</form>
</section>
