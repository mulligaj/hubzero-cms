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

JToolBarHelper::title(JText::_('COM_EVENTS_MANAGER'), 'event.png');
JToolBarHelper::custom('addpage', 'new', 'Add Page', 'Add Page', true, false);
JToolBarHelper::custom('respondents', 'user', JText::_('COM_EVENTS_VIEW_RESPONDENTS'), JText::_('COM_EVENTS_VIEW_RESPONDENTS'), true, false);
JToolBarHelper::spacer();
JToolBarHelper::publishList();
JToolBarHelper::unpublishList();
JToolBarHelper::spacer();
JToolBarHelper::addNew();
JToolBarHelper::editList();
JToolBarHelper::deleteList();

$juser = JFactory::getUser();

JHTML::_('behavior.tooltip');
?>

<script type="text/javascript">
function submitbutton(pressbutton) 
{
	var form = document.getElementById('adminForm');
	if (pressbutton == 'cancel') {
		submitform(pressbutton);
		return;
	}
	// do field validation
	submitform(pressbutton);
}
</script>

<form action="index.php?option=<?php echo $this->option; ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<label for="filter_search"><?php echo JText::_('COM_EVENTS_SEARCH'); ?>:</label>
		<input type="text" name="search" id="filter_search" value="<?php echo $this->escape($this->filters['search']); ?>" />

		<?php echo $this->clist; ?>
		<?php echo $this->glist; ?>

		<input type="submit" name="submitsearch" value="<?php echo JText::_('GO'); ?>" />
	</fieldset>
	<div class="clr"></div>

	<table class="adminlist">
		<thead>
			<tr>
				<th scope="col"><?php echo JText::_('COM_EVENTS_CAL_LANG_EVENT_ID'); ?></th>
				<th scope="col"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->rows); ?>);" /></th>
				<th scope="col"><?php echo JText::_('COM_EVENTS_CAL_LANG_EVENT_TITLE'); ?></th>
				<th scope="col"><?php echo JText::_('COM_EVENTS_CAL_LANG_EVENT_CATEGORY'); ?></th>
				<th scope="col"><?php echo JText::_('COM_EVENTS_CAL_LANG_EVENT_STATE'); ?></th>
				<th scope="col"><?php echo JText::_('COM_EVENTS_CAL_LANG_EVENT_TIMESHEET'); ?></th>
				<th scope="col"><?php echo JText::_('COM_EVENTS_CAL_LANG_EVENT_ACCESS'); ?></th>
				<th scope="col"><?php echo JText::_('Pages'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10"><?php echo $this->pageNav->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>
<?php
$k = 0;
$database = JFactory::getDBO();
$p = new EventsPage($database);
for ($i=0, $n=count($this->rows); $i < $n; $i++)
{
$row = &$this->rows[$i];
?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<?php echo $row->id; ?>
				</td>
				<td>
				<?php if ($row->checked_out && $row->checked_out != $juser->get('id')) { ?>
					&nbsp;
				<?php } else { ?>
					<input type="checkbox" id="cb<?php echo $i;?>" name="id[]" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked);" />
				<?php } ?>
				</td>
				<td>
<?php if ($row->checked_out && $row->checked_out != $juser->get('id')) { ?>
					<span class="checkedout hasTip" title="Checked out::<?php echo $this->escape(stripslashes($row->editor)); ?>">
						<?php echo $this->escape(html_entity_decode(stripslashes($row->title))); ?>
					</span>
<?php } else { ?>
					<a href="index.php?option=<?php echo $this->option; ?>&amp;controller=<?php echo $this->controller; ?>&amp;task=edit&amp;id[]=<?php echo $row->id; ?>">
						<?php echo $this->escape(html_entity_decode(stripslashes($row->title))); ?>
					</a>
<?php } ?>
				</td>
				<td>
					<span>
						<?php echo $this->escape($row->category); ?>
					</span>
				</td>
				<td>
<?php
	$now = JFactory::getDate()->toSql();
	$alt = JText::_('Unpublished');
	if ($now <= $row->publish_up && $row->state == "1") {
		$alt = JText::_('Pending');
	} else if (($now <= $row->publish_down || $row->publish_down == "0000-00-00 00:00:00") && $row->state == "1") {
		$alt = JText::_('Published');
	} else if ($now > $row->publish_down && $row->state == "1") {
		$alt = JText::_('Expired');
	} elseif ($row->state == "0") {
		$alt = JText::_('Unpublished');
	}

	$times = '';
	if (isset($row->publish_up)) {
		if ($row->publish_up == '0000-00-00 00:00:00') {
			$times .= JText::_('COM_EVENTS_CAL_LANG_FROM').' : '.JText::_('COM_EVENTS_CAL_LANG_ALWAYS').'<br />';
		} else {
			$times .= JText::_('COM_EVENTS_CAL_LANG_FROM').' : '.JHTML::_('date', $row->publish_up, 'Y-m-d H:i:s').'<br />';
		}
	}
	if (isset($row->publish_down)) {
		if ($row->publish_down == '0000-00-00 00:00:00') {
			$times .= JText::_('COM_EVENTS_CAL_LANG_TO').' : '.JText::_('COM_EVENTS_CAL_LANG_NEVER').'<br />';
		} else {
			$times .= JText::_('COM_EVENTS_CAL_LANG_TO').' : '.JHTML::_('date', $row->publish_down, 'Y-m-d H:i:s').'<br />';
		}
	}

	$pages = $p->getCount(array('event_id'=>$row->id));

	if ($times) { 
?>
					<a class="state <?php echo $row->state ? 'publish' : 'unpublish' ?> hasTip" href="javascript:void(0);" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $row->state ? 'unpublish' : 'publish' ?>')" title="<?php echo JText::_('Publish Information');?>::<?php echo $times; ?>">
						<span><?php echo $alt; ?></span>
					</a>
<?php } ?>
				</td>
				<td style="white-space: nowrap;">
					<?php echo $times; ?>
				</td>
				<td>
					
					<?php if($row->scope == 'group') : ?>
						<?php
							$group = \Hubzero\User\Group::getInstance( $row->scope_id );
							if (is_object($group))
							{
								echo "Group: <a href='" . JRoute::_('index.php?option=com_events&group_id=' . $group->get('gidNumber')) . "'>" . $group->get('description') . "</a>";
							}
							else
							{
								echo "Group: NOT FOUND({$row->scope_id})";
							}
						?>
					<?php else : ?>
						<span>
							<?php echo $this->escape(stripslashes($row->groupname)); ?>
						</span>
					<?php endif; ?>
				</td>
				<td style="white-space: nowrap;">
					<a href="index.php?option=<?php echo $this->option ?>&amp;controller=pages&amp;id[]=<?php echo $row->id; ?>">
						<?php echo JText::sprintf('%s page(s)', $pages); ?>
					</a>
				</td>
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