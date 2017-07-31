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
Document::setTitle(Lang::txt('COM_DRWHO') . ': ' . Lang::txt('COM_DRWHO_CHARACTERS'));

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
	Lang::txt('COM_DRWHO_CHARACTERS'),
	'index.php?option=' . $this->option . '&controller=' . $this->controller
);
?>
<header id="content-header">
	<h2><?php echo Lang::txt('COM_DRWHO'); ?>: <?php echo Lang::txt('COM_DRWHO_CHARACTERS'); ?></h2>

	<div id="content-header-extra">
		<p>
			<a class="icon-prev btn" href="<?php echo Route::url('index.php?option=' . $this->option); ?>"><?php echo Lang::txt('COM_DRWHO_MAIN'); ?></a>
		</p>
	</div>
</header>

<section class="main section">
	<form class="section-inner" action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="get">
		<div class="subject">
			<table class="entries">
				<caption><?php echo Lang::txt('COM_DRWHO_CHARACTERS'); ?></caption>
				<thead>
					<tr>
						<th><?php echo Lang::txt('COM_DRWHO_COL_ID'); ?></th>
						<th><?php echo Lang::txt('COM_DRWHO_COL_NAME'); ?></th>
						<th><?php echo Lang::txt('COM_DRWHO_COL_FRIEND'); ?></th>
						<th><?php echo Lang::txt('COM_DRWHO_COL_ENEMY'); ?></th>
						<th><?php echo Lang::txt('COM_DRWHO_COL_THE_DOCTOR'); ?></th>
						<?php if ($this->model->access('edit')) { ?>
							<th></th>
						<?php } ?>
					</tr>
				</thead>
				<?php if ($this->model->access('create')) { ?>
					<tfoot>
						<tr>
							<td colspan="<?php echo ($this->model->access('edit') ? '6' : '5'); ?>">
								<a class="icon-add btn" href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=add'); ?>">
									<?php echo Lang::txt('COM_DRWHO_NEW'); ?>
								</a>
							</td>
						</tr>
					</tfoot>
				<?php } ?>
				<tbody>
					<?php foreach ($this->records as $record) { ?>
						<tr>
							<th>
								<?php echo $this->escape($record->get('id')); ?>
							</th>
							<td>
								<a href="<?php echo Route::url($record->link()); ?>">
									<?php echo $this->escape($record->get('name')); ?>
								</a>
							</td>
							<td>
								<?php echo $record->isFriend() ? '<span class="icon-yes">' . Lang::txt('JYES') . '</span>' : ''; ?>
							</td>
							<td>
								<?php echo $record->isEnemy() ? '<span class="icon-yes">' . Lang::txt('JYES') . '</span>' : ''; ?>
							</td>
							<td>
								<?php echo $record->isDoctor() ? '<span class="icon-yes">' . Lang::txt('JYES') . '</span>' : '<span class="icon-no">' . Lang::txt('JNO') . '</span>'; ?>
							</td>
							<?php if ($this->model->access('edit')) { ?>
								<td>
									<a class="icon-edit btn" href="<?php echo Route::url($record->link('edit')); ?>">
										<?php echo Lang::txt('JACTION_EDIT'); ?>
									</a>
									<a class="icon-delete btn" href="<?php echo Route::url($record->link('delete')); ?>">
										<?php echo Lang::txt('JACTION_DELETE'); ?>
									</a>
								</td>
							<?php } ?>
						</tr>
					<?php } ?>
				</tbody>
			</table>

			<?php 
			echo $this
				->records
				->pagination
				->setAdditionalUrlParam('season', $this->filters['season']);

			$results = Event::trigger('drwho.onAfterDisplay');
			echo implode("\n", $results);
			?>
		</div>
		<aside class="aside">
			<fieldset>
				<select name="season">
					<option value=""><?php echo Lang::txt('COM_DRWHO_SEASONS_ALL'); ?></option>
					<?php foreach (\Components\Drwho\Models\Season::all()->rows() as $season) { ?>
						<option<?php if ($this->filters['season'] == $season->get('id')) { echo ' selected="selected"'; } ?> value="<?php echo $this->escape($season->get('alias')); ?>"><?php echo $this->escape($season->get('title')); ?></option>
					<?php } ?>
				</select>
				<input type="submit" value="<?php echo Lang::txt('COM_DRWHO_GO'); ?>" />
			</fieldset>

			<p><a class="btn" href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=seasons'); ?>"><?php echo Lang::txt('COM_DRWHO_SEASONS'); ?></a></p>
		</aside>
	</form>
</section>