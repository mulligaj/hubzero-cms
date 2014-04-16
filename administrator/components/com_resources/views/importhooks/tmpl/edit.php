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
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// set title
$title  = ($this->hook->get('id')) ? JText::_('COM_RESOURCES_IMPORTHOOK_TITLE_EDIT') : JText::_('COM_RESOURCES_IMPORTHOOK_TITLE_ADD');

JToolBarHelper::title(JText::_($title), 'import.png');
JToolBarHelper::save();
JToolBarHelper::cancel();
?>

<script type="text/javascript">
function submitbutton(pressbutton) 
{
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}
	// do field validation
	submitform( pressbutton );
}
</script>

<?php foreach ($this->getErrors() as $error) : ?>
	<p class="error"><?php echo $error; ?></p>
<?php endforeach; ?>

<form action="index.php?option=com_resources&amp;controller=importhooks&amp;task=save" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<div class="col width-70 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_RESOURCES_IMPORTHOOK_EDIT_FIELDSET_DETAILS'); ?></legend>
			<table class="admintable">
				<tbody>
					<tr>
						<td class="key" width="200px">
							<label for="field-type">
								<?php echo JText::_('COM_RESOURCES_IMPORTHOOK_EDIT_FIELD_TYPE'); ?>
							</label>
						</td>
						<td>
							<select name="hook[type]" id="field-type">
								<option value="postparse">
									<?php echo JText::_('COM_RESOURCES_IMPORTHOOK_EDIT_FIELD_TYPE_POSTPARSE'); ?>
								</option>
								<option <?php if ($this->hook->get('type') == 'postmap') { echo 'selected="selected"'; } ?> value="postmap">
									<?php echo JText::_('COM_RESOURCES_IMPORTHOOK_EDIT_FIELD_TYPE_POSTMAP'); ?>
								</option>
								<option <?php if ($this->hook->get('type') == 'postconvert') { echo 'selected="selected"'; } ?> value="postconvert">
									<?php echo JText::_('COM_RESOURCES_IMPORTHOOK_EDIT_FIELD_TYPE_POSTCONVERT'); ?>
								</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="key" width="200px">
							<label for="field-name">
								<?php echo JText::_('COM_RESOURCES_IMPORTHOOK_EDIT_FIELD_NAME'); ?>
							</label>
						</td>
						<td>
							<input type="text" name="hook[name]" id="field-name" value="<?php echo $this->escape($this->hook->get('name')); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="field-notes">
								<?php echo JText::_('COM_RESOURCES_IMPORTHOOK_EDIT_FIELD_NOTES'); ?>
							</label>
						</td>
						<td>
							<textarea name="hook[notes]" id="field-notes" rows="5"><?php echo $this->escape($this->hook->get('notes')); ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_RESOURCES_IMPORTHOOK_EDIT_FIELDSET_FILE'); ?></legend>
			<table class="admintable">
				<tbody>
					<tr>
						<td class="key" width="200px">
							<label for="field-name">
								<?php echo JText::_('COM_RESOURCES_IMPORTHOOK_EDIT_FIELD_SCRIPT'); ?>
							</label>
						</td>
						<td>
							<?php
								if ($this->hook->get('file'))
								{
									echo JText::sprintf('COM_RESOURCES_IMPORTHOOK_EDIT_FIELD_SCRIPT_CURRENT', $this->hook->get('file'));
									echo ' &mdash; <a target="_blank" href="' . JRoute::_('index.php?option=com_resources&controller=importhooks&task=raw&id[]='.$this->hook->get('id')) . '">'.JText::_('COM_RESOURCES_IMPORTHOOK_EDIT_FIELD_SCRIPT_VIEWRAW').'</a><br />';
								}
							?>
							<input type="file" name="file" />
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		
	</div>
	<div class="col width-30 fltrt">
		<?php if ($this->hook->get('id')) : ?>
			<table class="meta" summary="Metadata">
				<tbody>
					<tr>
						<th><?php echo JText::_('COM_RESOURCES_IMPORTHOOK_EDIT_FIELD_ID'); ?></th>
						<td><?php echo $this->hook->get('id'); ?></td>
					</tr>
					<tr>
						<th><?php echo JText::_('COM_RESOURCES_IMPORTHOOK_EDIT_FIELD_CREATEDBY'); ?></th>
						<td>
							<?php 
								if ($created_by = Hubzero\User\Profile::getInstance($this->hook->get('created_by')))
								{
									echo $created_by->get('name');
								}
							?>
						</td>
					</tr>
					<tr>
						<th><?php echo JText::_('COM_RESOURCES_IMPORTHOOK_EDIT_FIELD_CREATEDON'); ?></th>
						<td>
							<?php
								echo JHTML::_('date', $this->hook->get('created_at'), 'm/d/Y @ g:i a');
							?>
						</td>
					</tr>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<input type="hidden" name="option" value="<?php echo $this->option ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>">
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="hook[id]" value="<?php echo $this->hook->get('id'); ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>