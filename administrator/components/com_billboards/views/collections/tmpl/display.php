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

// Add the toolbar items
JToolBarHelper::title(JText::_('COM_BILLBOARDS_MANAGER') . ': ' . JText::_('COM_BILLBOARDS_COLLECTIONS'), 'addedit.png');
JToolBarHelper::addNew();
JToolBarHelper::editList();
JToolBarHelper::spacer();
JToolBarHelper::deleteList(JText::_('COM_BILLBOARDS_CONFIRM_DELETE'));
JToolBarHelper::spacer();
JToolBarHelper::help('collections');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="adminlist">
		<thead>
			<tr>
				<th><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->rows);?>);" /></th>
				<th scope="col"><?php echo JText::_('COM_BILLBOARDS_COL_ID'); ?></th>
				<th scope="col"><?php echo JText::_('COM_BILLBOARDS_COL_COLLECTION'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="3"><?php echo $this->pageNav->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>

<?php
	$k = 0;
	for ($i=0, $n=count($this->rows); $i < $n; $i++)
	{
		$row =& $this->rows[$i];
?>

			<tr>
				<td><input type="checkbox" name="id[]" id="cb<?php echo $i; ?>" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked, this);" /></td>
				<td><?php echo $row->id; ?></td>
				<td><a href="<?php echo JRoute::_('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=edit&id=' . $row->id); ?>"><?php echo $this->escape(stripslashes($row->name)); ?></a></td>
			</tr>

<?php
		$k = 1 - $k;
	}
?>

		</tbody>
	</table>

	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />

	<?php echo JHTML::_('form.token'); ?>
</form>