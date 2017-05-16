<?php
// No direct access
defined('_HZEXEC_') or die();

// Get the permissions helper
$canDo = Components\PressForward\Helpers\Permissions::getActions('post');

Toolbar::title(Lang::txt('COM_PRESSFORWARD') . ': ' . Lang::txt('PF_POSTS'));
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
/*if ($canDo->get('core.create'))
{
	Toolbar::addNew();
}*/
if ($canDo->get('core.edit'))
{
	Toolbar::editList();
}
if ($canDo->get('core.delete'))
{
	Toolbar::deleteList();
}
Toolbar::spacer();
Toolbar::appendButton('Link', 'help', 'help', 'https://github.com/PressForward/pressforward/wiki');

Html::behavior('framework');
?>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm">
	<fieldset id="filter-bar">
		<div class="grid">
			<div class="col span6">
				<label for="filter_search"><?php echo Lang::txt('JSEARCH_FILTER'); ?>:</label>
				<input type="text" name="search" id="filter_search" value="<?php echo $this->escape($this->filters['search']); ?>" placeholder="<?php echo Lang::txt('JSEARCH_FILTER'); ?>" />

				<input type="submit" value="<?php echo Lang::txt('PF_GO'); ?>" />
				<button type="button" onclick="$('#filter_search').val('');this.form.submit();"><?php echo Lang::txt('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>
			<div class="col span6">
				<label for="filter-status"><?php echo Lang::txt('PF_FILTER'); ?>:</label>
				<select name="status" id="filter-status" onchange="this.form.submit()">
					<option value="">All</option>
					<option value="draft"<?php if ($this->filters['status'] == 'draft') { echo ' selected="selected"'; } ?>>Drafts</option>
					<option value="publish"<?php if ($this->filters['status'] == 'publish') { echo ' selected="selected"'; } ?>>Published</option>
					<option value="trash"<?php if ($this->filters['status'] == 'trash') { echo ' selected="selected"'; } ?>>Trash</option>
				</select>

				<label for="filter-folder"><?php echo Lang::txt('PF_FOLDER'); ?>:</label>
				<select name="folder" id="filter-folder">
					<option value="0">— All Categories —</option>
					<?php foreach ($this->folders as $folder) { ?>
						<option value="<?php echo $folder->get('term_taxonomy_id'); ?>"<?php if ($this->filters['folder'] == $folder->get('term_taxonomy_id')) { echo ' selected="selected"'; } ?>><?php echo $folder->get('treename') . $folder->get('name'); ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
	</fieldset>
	<div class="clr"></div>

	<table class="adminlist">
		<thead>
			<tr>
				<th><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo $this->rows->count(); ?>);" /></th>
				<th scope="col"><?php echo Html::grid('sort', 'PF_COL_TITLE', 'title', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col" class="priority-4"><?php echo Html::grid('sort', 'PF_COL_AUTHOR', 'author', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col" class="priority-3"><?php echo Lang::txt('PF_COL_CATEGORIES'); ?></th>
				<th scope="col" class="priority-4"><?php echo Lang::txt('PF_COL_TAGS'); ?></th>
				<th scope="col" class="priority-3"><?php echo Lang::txt('PF_COL_COMMENTS'); ?></th>
				<th scope="col" class="priority-2"><?php echo Html::grid('sort', 'PF_COL_DATE', 'date', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="7"><?php
				// Initiate paging
				echo $this->rows->pagination;
				?></td>
			</tr>
		</tfoot>
		<tbody>
		<?php
		$k = 0;
		$i = 0;
		foreach ($this->rows as $row) :
			?>
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
					<?php if ($row->get('post_status') != 'publish') { ?>
						&mdash; <span class="post-state"><?php echo $this->escape($row->get('post_status')); ?></span>
					<?php } ?>
				</td>
				<td class="priority-4">
					<?php echo $this->escape($row->author->get('name', '&mdash;')); // authors ?>
				</td>
				<td class="priority-3">
					<?php echo '&mdash;'; // categories ?>
				</td>
				<td class="priority-4">
					<?php echo ($row->tags('string') ? $row->tags('string') : '&mdash;'); ?>
				</td>
				<td class="priority-3">
					<?php echo $row->comments()->total(); // comments ?>
				</td>
				<td class="priority-2">
					<?php
					if ($row->get('post_status') == 'publish')
					{
						echo Lang::txt('Published %s', Date::of($row->get('post_date_gmt'))->format('Y/m/d'));
					}
					else if ($row->get('post_modified_gmt') != '0000-00-00 00:00:00')
					{
						echo Lang::txt('Last Modified %s', Date::of($row->get('post_modified_gmt'))->format('Y/m/d'));
					}
					else
					{
						echo Lang::txt('Created %s', Date::of($row->get('post_date_gmt'))->format('Y/m/d'));
					}
					?>
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
