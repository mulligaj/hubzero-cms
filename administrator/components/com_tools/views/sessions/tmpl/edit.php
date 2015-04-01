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
defined('_JEXEC') or die( 'Restricted access' );

$text = ($this->task == 'edit' ? JText::_('JACTION_EDIT') : JText::_('JACTION_CREATE'));

JToolBarHelper::title(JText::_('COM_TOOLS_SESSION_CLASSES') . ': ' . $text, 'user.png');
JToolBarHelper::apply();
JToolBarHelper::save();
JToolBarHelper::spacer();
JToolBarHelper::cancel('cancelclass');
?>

<script type="text/javascript">
function submitbutton(pressbutton)
{
	var form = document.adminForm;

	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}

	submitform( pressbutton );
}
</script>

<?php if ($this->getError()) : ?>
	<p class="error"><?php echo $this->getError(); ?></p>
<?php endif; ?>

<form action="<?php echo JRoute::_('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" id="item-form">
	<div class="col width-60 fltlft">
		<fieldset class="adminform">
			<legend><span><?php echo JText::_('COM_TOOLS_SESSION_CLASS_LEGEND'); ?></span></legend>

			<div class="input-wrap">
				<label for="field-alias"><?php echo JText::_('COM_TOOLS_SESSION_CLASS_ALIAS'); ?>:</label>
				<input <?php echo ($this->row->alias == 'default') ? 'readonly' : ''; ?> type="text" name="fields[alias]" id="field-alias" value="<?php echo $this->escape(stripslashes($this->row->alias)); ?>" />
			</div>
			<div class="input-wrap">
				<label for="field-jobs"><?php echo JText::_('COM_TOOLS_SESSION_CLASS_JOBS'); ?>:</label>
				<input type="text" name="fields[jobs]" id="field-jobs" value="<?php echo $this->escape(stripslashes($this->row->jobs)); ?>" />
			</div>
		</fieldset>
		<fieldset class="adminform">
			<legend><span><?php echo JText::_('COM_TOOLS_SESSION_CLASS_USERGROUPS_LEGEND'); ?></span></legend>
			<p><?php echo JText::_('COM_TOOLS_SESSION_CLASS_USERGROUPS_DESC'); ?></p>
			<?php
			// Include the component HTML helpers.
			JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/helpers/html');
			?>
			<div class="input-wrap">
				<?php echo JHtml::_('access.usergroups', 'fields[groups]', $this->row->getGroupIds(), true); ?>
			</div>
		</fieldset>
	</div>
	<div class="col width-40 fltrt">
		<table class="meta">
			<tbody>
				<tr>
					<th><?php echo JText::_('COM_TOOLS_SESSION_CLASS_ID'); ?></th>
					<td><?php echo $this->row->id; ?></td>
				</tr>
				<tr>
					<th><?php echo JText::_('COM_TOOLS_SESSION_CLASS_USER_COUNT'); ?></th>
					<td><?php echo ($this->row->id) ? $this->row->userCount() : 0; ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="clr"></div>

	<input type="hidden" name="fields[id]" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="saveClass" />

	<?php echo JHTML::_('form.token'); ?>
</form>