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

$canDo = GroupsHelper::getActions('group');

JToolBarHelper::title(JText::_('COM_GROUPS'), 'groups.png');

if ($canDo->get('core.admin'))
{
	JToolBarHelper::preferences('com_groups', '550');
	JToolBarHelper::spacer();

	if ($this->config->get('super_gitlab', 0))
	{
		JToolBarHelper::custom('update', 'refresh', '', 'COM_GROUPS_UPDATE_CODE');
		JToolBarHelper::spacer();
	}

}
if ($canDo->get('core.create'))
{
	JToolBarHelper::addNew();
}
if ($canDo->get('core.edit'))
{
	JToolBarHelper::editList();
}
if ($canDo->get('core.delete'))
{
	JToolBarHelper::deleteList('COM_GROUPS_DELETE_CONFIRM', 'delete');
}
JToolBarHelper::spacer();
JToolBarHelper::help('groups');

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

<form action="index.php?option=<?php echo $this->option ?>&amp;controller=<?php echo $this->controller; ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<label for="filter_search"><?php echo JText::_('COM_GROUPS_SEARCH'); ?>:</label>
		<input type="text" name="search" id="filter_search" value="<?php echo $this->filters['search']; ?>" placeholder="<?php echo JText::_('COM_GROUPS_SEARCH'); ?>" />

		<label for="filter-type"><?php echo JText::_('COM_GROUPS_TYPE'); ?>:</label>
		<select name="type" id="filter-type">
			<option value="all"<?php echo ($this->filters['type'][0] == 'all') ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_GROUPS_TYPE'); ?></option>
			<option value="hub"<?php echo ($this->filters['type'][0] == 'hub') ? ' selected="selected"' : ''; ?>>Hub</option>
			<option value="super"<?php echo ($this->filters['type'][0] == 'super') ? ' selected="selected"' : ''; ?>>Super</option>
			<?php if ($canDo->get('core.admin')) { ?>
				<option value="system"<?php echo ($this->filters['type'][0] == 'system') ? ' selected="selected"' : ''; ?>>System</option>
			<?php } ?>
			<option value="project"<?php echo ($this->filters['type'][0] == 'project') ? ' selected="selected"' : ''; ?>>Project</option>
			<option value="course"<?php echo ($this->filters['type'][0] == 'course') ? ' selected="selected"' : ''; ?>>Course</option>
		</select>

		<label for="filter-discoverability"><?php echo JText::_('COM_GROUPS_DISCOVERABILITY'); ?>:</label>
		<select name="discoverability" id="filter-discoverability">
			<option value=""<?php echo ($this->filters['discoverability'] == null) ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_GROUPS_DISCOVERABILITY'); ?></option>
			<option value="0"<?php echo ($this->filters['discoverability'] == 0 && $this->filters['discoverability'] != null) ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_GROUPS_DISCOVERABILITY_VISIBLE'); ?></option>
			<option value="1"<?php echo ($this->filters['discoverability'] == 1) ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_GROUPS_DISCOVERABILITY_HIDDEN'); ?></option>
		</select>

		<label for="filter-policy"><?php echo JText::_('COM_GROUPS_JOIN_POLICY'); ?>:</label>
		<select name="policy" id="filter-policy">
			<option value=""<?php echo ($this->filters['policy'] == '') ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_GROUPS_JOIN_POLICY'); ?></option>
			<option value="open"<?php echo ($this->filters['policy'] == 'open') ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_GROUPS_JOIN_POLICY_PUBLIC'); ?></option>
			<option value="restricted"<?php echo ($this->filters['policy'] == 'restricted') ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_GROUPS_JOIN_POLICY_RESTRICTED'); ?></option>
			<option value="invite"<?php echo ($this->filters['policy'] == 'invite') ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_GROUPS_JOIN_POLICY_INVITE'); ?></option>
			<option value="closed"<?php echo ($this->filters['policy'] == 'closed') ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_GROUPS_JOIN_POLICY_CLOSED'); ?></option>
		</select>

		<input type="submit" value="<?php echo JText::_('COM_GROUPS_GO'); ?>" />
		|
		<a class="button" href="/administrator/index.php?option=com_groups&amp;controller=manage&amp;type=hub&amp;discoverability=&amp;policy=&amp;approved=&amp;published=&amp;created="><?php echo JText::_('COM_GROUPS_RESET'); ?></a>
	</fieldset>
	<div class="clr"></div>

	<table class="adminlist">
		<thead>
		 	<tr>
				<th scope="col"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->rows);?>);" /></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'COM_GROUPS_ID', 'gidNumber', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'COM_GROUPS_NAME', 'description', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'COM_GROUPS_CN', 'cn', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'COM_GROUPS_TYPE', 'type', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'COM_GROUPS_PUBLISHED', 'published', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo JHTML::_('grid.sort', 'COM_GROUPS_APPROVED', 'approved', @$this->filters['sort_Dir'], @$this->filters['sort']); ?></th>
				<th scope="col"><?php echo JText::_('COM_GROUPS_MEMBERS'); ?></th>
				<th scope="col"><?php echo JText::_('COM_GROUPS_PAGES'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="9"><?php echo $this->pageNav->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>
<?php
$database = JFactory::getDBO();
$k = 0;
for ($i=0, $n=count($this->rows); $i < $n; $i++)
{
	$row = &$this->rows[$i];

	$group = new \Hubzero\User\Group();
	//$group->gidNumber = $row->gidNumber;
	//$group->cn = $row->cn;
	$group->read($row->gidNumber);

	switch ($row->type)
	{
		case '0': $type = JText::_('COM_GROUPS_TYPE_SYSTEM');  break;
		case '1': $type = JText::_('COM_GROUPS_TYPE_HUB');     break;
		case '2': $type = JText::_('COM_GROUPS_TYPE_PROJECT'); break;
		case '3': $type = JText::_('COM_GROUPS_TYPE_SUPER');   break;
		case '4': $type = JText::_('COM_GROUPS_TYPE_COURSE');  break;
	}

	//get group invite emails
	$hubzeroGroupInviteEmail = new \Hubzero\User\Group\InviteEmail($database);
	$inviteemails = $hubzeroGroupInviteEmail->getInviteEmails($group->get('gidNumber'));

	//get group membership
	$members    = $group->get('members');
	$managers   = $group->get('managers');
	$applicants = $group->get('applicants');
	$invitees   = $group->get('invitees');

	//remove any managers from members list
	$true_members = array_diff($members, $managers);

	//build membership tooltip
	$tip  = '<table><tbody>';
	$tip .= '<tr><th>' . JText::_('COM_GROUPS_MEMBERS') . '</th><td>' . count($true_members) . '</td></tr>';
	$tip .= '<tr><th>' . JText::_('COM_GROUPS_MANAGERS') . '</th><td>' . count($managers) . '</td></tr>';
	$tip .= '<tr><th>' . JText::_('COM_GROUPS_APPLICANTS') . '</th><td>' . count($applicants) . '</td></tr>';
	$tip .= '<tr><th>' . JText::_('COM_GROUPS_INVITEES') . '</th><td>' . (count($invitees) + count($inviteemails)) . '</td></tr>';
	$tip .= '</tbody></table>';
?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<input type="checkbox" name="id[]" id="cb<?php echo $i;?>" value="<?php echo $row->cn ?>" onclick="isChecked(this.checked);" />
				</td>
				<td>
					<?php echo $this->escape($row->gidNumber); ?>
				</td>
				<td>
				<?php if ($canDo->get('core.edit')) { ?>
					<a href="index.php?option=<?php echo $this->option ?>&amp;controller=<?php echo $this->controller; ?>&amp;task=edit&amp;id=<?php echo $row->cn; ?>">
						<?php echo $this->escape(stripslashes($row->description)); ?>
					</a>
				<?php } else { ?>
					<span>
						<?php echo $this->escape(stripslashes($row->description)); ?>
					</span>
				<?php } ?>
				</td>
				<td>
				<?php if ($canDo->get('core.edit')) { ?>
					<a href="index.php?option=<?php echo $this->option ?>&amp;controller=<?php echo $this->controller; ?>&amp;task=edit&amp;id=<?php echo $row->cn; ?>">
						<?php echo $this->escape($row->cn); ?>
					</a>
				<?php } else { ?>
					<?php echo $this->escape($row->cn); ?>
				<?php } ?>
				</td>
				<td>
					<?php echo $type; ?>
				</td>
				<td>
				<?php if ($canDo->get('core.edit.state')) { ?>
					<?php if ($row->published) { ?>
					<a class="jgrid" href="index.php?option=<?php echo $this->option ?>&amp;controller=<?php echo $this->controller; ?>&amp;task=unpublish&amp;id=<?php echo $row->cn; ?>" title="<?php echo JText::_('COM_GROUPS_UNPUBLISH'); ?>">
						<span class="state publish">
							<span class="text"><?php echo JText::_('COM_GROUPS_PUBLISHED'); ?></span>
						</span>
					</a>
					<?php } else { ?>
					<a class="jgrid" href="index.php?option=<?php echo $this->option ?>&amp;controller=<?php echo $this->controller; ?>&amp;task=publish&amp;id=<?php echo $row->cn; ?>" title="<?php echo JText::_('COM_GROUPS_PUBLISH'); ?>">
						<span class="state unpublish">
							<span class="text"><?php echo JText::_('COM_GROUPS_UNPUBLISHED'); ?></span>
						</span>
					</a>
					<?php } ?>
				<?php } ?>
				</td>
				<td>
				<?php if ($canDo->get('core.edit.state')) { ?>
					<?php if (!$group->get('approved')) { ?>
					<a class="jgrid state no" href="index.php?option=<?php echo $this->option ?>&amp;controller=<?php echo $this->controller; ?>&amp;task=approve&amp;id=<?php echo $row->cn; ?>" title="<?php echo JText::_('COM_GROUPS_APPROVE'); ?>">
						<span class="not-approved">
							<span class="text"><?php echo JText::_('COM_GROUPS_APPROVE'); ?></span>
						</span>
					</a>
					<?php } else { ?>
						<span class="state yes approved">
							<span class="text"><?php echo JText::_('COM_GROUPS_APPROVED'); ?></span>
						</span>
					<?php } ?>
				<?php } ?>
				</td>
				<td>
				<?php if ($canDo->get('core.manage')) { ?>
					<a class="glyph member hasTip" href="index.php?option=<?php echo $this->option ?>&amp;controller=membership&amp;gid=<?php echo $row->cn; ?>" title="<?php echo JText::_('COM_GROUPS_MANAGE_MEMBERSHIP') . '::' . $tip; ?>">
						<?php echo count($members); ?>
					</a>
				<?php } else { ?>
					<span class="glyph member" title="<?php echo JText::_('COM_GROUPS_MANAGE_MEMBERSHIP') . '::' . $tip; ?>">
						<?php echo count($members); ?>
					</span>
				<?php } ?>
				</td>
				<td>
					<?php if ($canDo->get('core.manage')) { ?>
						<a href="index.php?option=<?php echo $this->option ?>&amp;controller=pages&amp;gid=<?php echo $row->cn; ?>"><?php echo JText::_('COM_GROUPS_PAGES'); ?></a>
					<?php } ?>
				</td>
			</tr>
<?php
	$k = 1 - $k;
}
?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="<?php echo $this->option ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->filters['sort']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->filters['sort_Dir']; ?>" />

	<?php echo JHTML::_('form.token'); ?>
</form>