<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');

// Menu
JToolBarHelper::title(JText::_('COM_BILLBOARDS_MANAGER') . ': ' . JText::_('COM_BILLBOARDS'), 'addedit.png');
JToolBarHelper::preferences($this->option, '200', '500');
JToolBarHelper::spacer();
JToolBarHelper::publishList();
JToolBarHelper::unpublishList();
JToolBarHelper::spacer();
JToolBarHelper::addNew();
JToolBarHelper::editList();
JToolBarHelper::spacer();
JToolBarHelper::deleteList(JText::_('COM_BILLBOARDS_CONFIRM_DELETE'));
JToolBarHelper::spacer();
JToolBarHelper::help('billboards');
?>

<form action="<?php echo JRoute::_('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" id="adminForm">
	<table class="adminlist">
		<thead>
			<tr>
				<th><input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo $this->rows->count(); ?>);" /></th>
				<th scope="col"><?php echo JText::_('COM_BILLBOARDS_COL_ID'); ?></th>
				<th scope="col"><?php echo JText::_('COM_BILLBOARDS_COL_NAME'); ?></th>
				<th scope="col"><?php echo JText::_('COM_BILLBOARDS_COL_COLLECTION'); ?></th>
				<th scope="col"><?php echo JText::_('COM_BILLBOARDS_COL_ORDERING') . JHTML::_('grid.order', $this->rows->toArray()); ?></th>
				<th scope="col"><?php echo JText::_('COM_BILLBOARDS_COL_PUBLISHED'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="6"><?php echo $this->rows->pagination; ?></td>
			</tr>
		</tfoot>
		<tbody>
<?php
	$i = 0;
	foreach ($this->rows as $row)
	{
		// See if the billboard is being edited by someone else
		if ($row->checked_out || $row->checked_out_time != '0000-00-00 00:00:00')
		{
			$checked = JHtml::_('jgrid.checkedout', $row, JFactory::getUser($row->checked_out)->get('name'), $row->checked_out_time);
		}
		else
		{
			$checked = JHTML::_('grid.id', $i, $row->id, false, 'cid');
		}

		$task  = $row->published ? 'unpublish' : 'publish';
		$class = $row->published ? 'publish' : 'unpublish';
		$alt   = $row->published ? JText::_('JPUBLISHED') : JText::_('JUNPUBLISHED');
?>
			<tr class="<?php echo "row$i"; ?>">
				<td>
					<?php echo $checked; ?>
				</td>
				<td>
					<?php echo $row->id; ?>
				</td>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=edit&cid=' . $row->id); ?>"><?php echo $row->name; ?></a>
				</td>
				<td>
					<?php echo $row->collection->name; ?>
				</td>
				<td class="order">
					<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
				</td>
				<td>
					<a class="state <?php echo $class;?>" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=' . $task . '&cid=' . $row->id . '&' . JUtility::getToken() . '=1'); ?>" title="<?php echo JText::sprintf('COM_BILLBOARDS_SET_TO', $task); ?>">
						<span><?php echo $alt; ?></span>
					</a>
				</td>
			</tr>
<?php $i++; } ?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_('form.token'); ?>
</form>