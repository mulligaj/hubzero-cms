<?php
// No direct access
defined('_HZEXEC_') or die();

// Get the permissions helper
$canDo = Components\PressForward\Helpers\Permissions::getActions('feed');

// Toolbar is a helper class to simplify the creation of Toolbar 
// titles, buttons, spacers and dividers in the Admin Interface.
//
// Here we'll had the title of the component and various options
// for adding/editing/etc based on if the user has permission to
// perform such actions.
Toolbar::title(Lang::txt('COM_PRESSFORWARD') . ': ' . Lang::txt('PF_FEEDS'));
if ($canDo->get('core.admin'))
{
	Toolbar::preferences($this->option);
	Toolbar::spacer();
}
if ($canDo->get('core.edit.state'))
{
	Toolbar::publishList();
	Toolbar::unpublishList();
	Toolbar::spacer();
}
if ($canDo->get('core.delete'))
{
	Toolbar::deleteList('', 'delete');
}
if ($canDo->get('core.edit'))
{
	Toolbar::editList();
}
if ($canDo->get('core.create'))
{
	Toolbar::addNew();
}
Toolbar::spacer();
Toolbar::appendButton('Link', 'help', 'help', 'https://github.com/PressForward/pressforward/wiki');

Html::behavior('framework');

$this->css('pressforward.css');
?>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="grid">
			<div class="col span6">
				<label for="filter_search"><?php echo Lang::txt('JSEARCH_FILTER'); ?>:</label>
				<input type="text" name="search" id="filter_search" value="<?php echo $this->escape($this->filters['search']); ?>" placeholder="<?php echo Lang::txt('JSEARCH_FILTER'); ?>" />

				<input type="submit" value="<?php echo Lang::txt('PF_GO'); ?>" />
				<button type="button" onclick="$('#filter_search').val('');$('#filter-state').val('-1');this.form.submit();"><?php echo Lang::txt('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>
			<div class="col span6">
				<label for="filter-date"><?php echo Lang::txt('PF_FILTER_DATES'); ?>:</label>
				<select name="date" id="filter-date" onchange="this.form.submit();">
					<option value=""<?php if (!$this->filters['date']) { echo ' selected="selected"'; } ?>><?php echo Lang::txt('PF_FILTER_DATES_ALL'); ?></option>
					<?php foreach ($this->dates as $dt => $val) { ?>
						<option value="<?php echo $dt; ?>"<?php if ($this->filters['date'] == $dt) { echo ' selected="selected"'; } ?>><?php echo $val; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
	</fieldset>

	<table class="adminlist widefat">
		<thead>
			<tr>
				<th><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo $this->rows->count(); ?>);" /></th>
				<th scope="col"><?php echo Html::grid('sort', 'PF_COL_TITLE', 'title', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col" class="priority-4"><?php echo Html::grid('sort', 'PF_COL_CREATEDBY', 'created_by', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col" class="priority-5"><?php echo Lang::txt('PF_COL_TAGS'); ?></th>
				<th scope="col" class="priority-5"><?php echo Lang::txt('PF_COL_CATEGORIES'); ?></th>
				<th scope="col" class="priority-4"><?php echo Html::grid('sort', 'PF_COL_CREATED', 'created', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col" class="priority-3"><?php echo Html::grid('sort', 'PF_COL_LASTCHECKED', 'created', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col" class="priority-3"><?php echo Html::grid('sort', 'PF_COL_LASTRETRIEVED', 'created', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col" class="priority-2"><?php echo Lang::txt('PF_COL_ITEMS'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10"><?php echo $this->rows->pagination; ?></td>
			</tr>
		</tfoot>
		<tbody>
		<?php
		$k = 0;
		$i = 0;

		foreach ($this->rows as $row) : ?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<input type="checkbox" name="id[]" id="cb<?php echo $i; ?>" value="<?php echo $row->get('ID') ?>" onclick="isChecked(this.checked, this);" />
				</td>
				<td>
					<?php if ($canDo->get('core.edit')) { ?>
						<a href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=edit&id=' . $row->get('ID')); ?>">
							<?php echo $this->escape($row->get('post_title')); ?>
						</a>
					<?php } else { ?>
						<span>
							<?php echo $this->escape($row->get('post_title')); ?>
						</span>
					<?php } ?>
					<div class="row-actions">
						<span class="pf-url"><?php echo $this->escape($row->get('guid')); ?></span>
						<?php if ($canDo->get('core.create')) { ?>
							<span class="pf-refresh">
								<a href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=import&id=' . $row->get('ID')); ?>">
									<?php echo Lang::txt('Refresh Feed Items'); ?>
								</a>
							</span>
						<?php } ?>
					</div>
				</td>
				<td class="priority-4">
					<?php echo $this->escape(stripslashes($row->author->get('username'))); ?>
				</td>
				<td class="priority-5">
					--
				</td>
				<td class="priority-5">
					--
				</td>
				<td class="priority-4">
					<time datetime="<?php echo $row->get('post_date_gmt'); ?>">
						<?php echo $row->get('post_date_gmt'); ?>
					</time>
				</td>
				<td class="priority-3">
					<time datetime="<?php echo $row->get('post_modified_gmt'); ?>">
						<?php echo $row->get('post_modified_gmt'); ?>
					</time>
				</td>
				<td class="priority-3">
					<time datetime="<?php echo $row->get('post_modified_gmt'); ?>">
						<?php echo $row->get('post_modified_gmt'); ?>
					</time>
				</td>
				<td class="priority-2">
					<?php echo $row->children()->total(); ?>
				</td>
			</tr>
			<?php
			$i++;
			$k = 1 - $k;
		endforeach;
		?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="<?php echo $this->option ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->escape($this->filters['sort']); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->filters['sort_Dir']); ?>" />

	<?php echo Html::input('token'); ?>
</form>
