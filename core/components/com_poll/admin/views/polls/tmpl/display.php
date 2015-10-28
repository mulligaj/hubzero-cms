<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

defined('_HZEXEC_') or die();

Html::behavior('tooltip');

$canDo = \Components\Poll\Helpers\Permissions::getActions('component');

Toolbar::title(Lang::txt('COM_POLL'), 'poll.png');
if ($canDo->get('core.admin'))
{
	Toolbar::preferences('com_poll', '550');
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
	Toolbar::deleteList();
	Toolbar::spacer();
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
Toolbar::help('polls');
?>

<form action="<?php echo Route::url('index.php?option=' . $this->option); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="col width-50 fltlft">
			<label for="filter_search"><?php echo Lang::txt('JSEARCH_FILTER'); ?>:</label>
			<input type="text" name="search" id="filter_search" value="<?php echo $this->escape($this->lists['search']); ?>" placeholder="<?php echo Lang::txt('COM_POLL_SEARCH_PLACEHOLDER'); ?>" />

			<button onclick="this.form.submit();"><?php echo Lang::txt('COM_POLL_GO'); ?></button>
			<button onclick="document.getElementById('search').value='';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo Lang::txt('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="col width-50 fltrt">
			<?php echo $this->lists['state']; ?>
		</div>
	</fieldset>
	<div class="clr"></div>

	<table class="adminlist">
		<thead>
			<tr>
				<th scope="col">
					<?php echo Lang::txt('COM_POLL_COL_NUM'); ?>
				</th>
				<th scope="col">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
				</th>
				<th scope="col" class="title">
					<?php echo Html::grid('sort', 'COM_POLL_COL_TITLE', 'm.title', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
				<th scope="col">
					<?php echo Html::grid('sort', 'COM_POLL_COL_PUBLISHED', 'm.published', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
				<th scope="col" class="priority-2">
					<?php echo Html::grid('sort', 'COM_POLL_COL_OPEN', 'm.open', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
				<th scope="col" class="priority-3">
					<?php echo Html::grid('sort', 'COM_POLL_COL_VOTES', 'm.voters', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
				<th scope="col" class="priority-4">
					<?php echo Html::grid('sort', 'COM_POLL_COL_OPTIONS', 'numoptions', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
				<th scope="col" class="priority-4">
					<?php echo Html::grid('sort', 'COM_POLL_COL_LAG', 'm.lag', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
				<th scope="col" class="priority-5">
					<?php echo Html::grid('sort', 'COM_POLL_COL_ID', 'm.id', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="9">
					<?php
					$pagination = $this->pagination(
						$this->total,
						$this->limitstart,
						$this->limit
					);
					echo $pagination->render();
					?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php
		$k = 0;
		for ($i=0, $n=count( $this->items ); $i < $n; $i++)
		{
			$row = &$this->items[$i];

			$link = Route::url('index.php?option=' . $this->option . '&view=poll&task=edit&cid='. $row->id);

			$task  = $row->published ? 'unpublish' : 'publish';
			$class = $row->published ? 'published' : 'unpublished';
			$alt   = $row->published ? Lang::txt('JPUBLISHED') : Lang::txt('JUNPUBLISHED');

			$task2  = ($row->open == 1) ? 'close' : 'open';
			$class2 = ($row->open == 1) ? 'published' : 'unpublished';
			$alt2   = ($row->open == 1) ? Lang::txt('COM_POLL_OPEN') : Lang::txt('COM_POLL_CLOSED');
		?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<?php echo $pagination->getRowOffset($i); ?>
				</td>
				<td>
					<?php if (($row->checked_out && $row->checked_out != $this->user->get('id')) || !$canDo->get('core.edit')) { ?>
						<span> </span>
					<?php } else { ?>
						<input type="checkbox" name="cid[]" id="cb<?php echo $i; ?>" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked, this);" />
					<?php } ?>
				</td>
				<td>
					<?php if (($row->checked_out && $row->checked_out != $this->user->get('id')) || !$canDo->get('core.edit')) {
						echo $row->title;
					} else { ?>
						<span class="editlinktip hasTip" title="<?php echo $this->escape($row->title); ?>">
							<a href="<?php echo $link; ?>">
								<?php echo $this->escape($row->title); ?>
							</a>
						</span>
					<?php } ?>
				</td>
				<td>
					<?php if ($canDo->get('core.edit.state')) { ?>
						<a class="state <?php echo $class;?>" href="<?php echo Route::url('index.php?option=' . $this->option . '&task=' . $task . '&cid=' . $row->id . '&' . Session::getFormToken() . '=1'); ?>" title="<?php echo Lang::txt('COM_POLL_SET_TO', $task); ?>">
							<span><?php echo $alt; ?></span>
						</a>
					<?php } else { ?>
						<span class="state <?php echo $class; ?>">
							<span><?php echo $alt; ?></span>
						</span>
					<?php } ?>
				</td>
				<td class="priority-2">
					<?php if ($canDo->get('core.edit.state')) { ?>
						<a class="state <?php echo $class2; ?>" href="<?php echo Route::url('index.php?option=' . $this->option . '&task=' . $task2 . '&cid=' . $row->id . '&' . Session::getFormToken() . '=1'); ?>" title="<?php echo Lang::txt('COM_POLL_SET_TO', $task2); ?>">
							<span><?php echo $alt2; ?></span>
						</a>
					<?php } else { ?>
						<span class="state <?php echo $class2; ?>">
							<span><?php echo $alt2; ?></span>
						</span>
					<?php } ?>
				</td>
				<td class="priority-3">
					<?php echo $row->voters; ?>
				</td>
				<td class="priority-4">
					<?php echo $row->numoptions; ?>
				</td>
				<td class="priority-4">
					<?php echo $row->lag; ?>
				</td>
				<td class="priority-5">
					<?php echo $row->id; ?>
				</td>
			</tr>
			<?php
				$k = 1 - $k;
			}
			?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

	<?php echo Html::input('token'); ?>
</form>