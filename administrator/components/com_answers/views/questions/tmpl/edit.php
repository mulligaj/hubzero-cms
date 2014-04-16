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

$canDo = AnswersHelper::getActions('question');

$text = ($this->task == 'edit' ? JText::_('Edit') : JText::_('New'));

JToolBarHelper::title(JText::_('Answers Manager') . ': ' . JText::_('Question') . ': ' . $text, 'answers.png');
if ($canDo->get('core.edit')) 
{
	JToolBarHelper::apply();
	JToolBarHelper::save();
	JToolBarHelper::spacer();
}
JToolBarHelper::cancel();
JToolBarHelper::spacer();
JToolBarHelper::help('question.html', true);

jimport('joomla.html.editor');
$editor = JEditor::getInstance();
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
	if (document.getElementById('q_subject').value == ''){
		alert( 'Question must have a subject' );
	} else if (document.getElementById('q_tags').value == ''){
		alert( 'Question must have at least one tag' );
	} else {
		submitform( pressbutton );
	}
}
</script>

<form action="index.php" method="post" name="adminForm" id="item-form">
	<div class="col width-60 fltlft">
		<fieldset class="adminform">
			<legend><span>Details</span></legend>
			<table class="admintable">
				<tbody>
					<tr>
						<td>
							<input type="checkbox" name="question[anonymous]" id="anonymous" value="1" <?php echo ($this->row->get('anonymous')) ? 'checked="checked"' : ''; ?> /> <label for="anonymous">Anonymous</label>
						</td>
						<td>
							<input type="checkbox" name="question[email]" id="email" value="1" <?php echo ($this->row->get('email')) ? 'checked="checked"' : ''; ?> /> <label for="email">Notify of responses</label>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<label for="q_subject">Subject: <span class="required">*</span></label><br />
							<input type="text" name="question[subject]" id="q_subject" size="30" maxlength="250" value="<?php echo $this->escape($this->row->subject('raw')); ?>" />
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<label for="question[question]">Question:</label><br />
							<?php echo $editor->display('question[question]', $this->row->content('raw'), '100%', 'auto', '50', '15'); ?>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<label for="q_tags">Tags: <span class="required">*</span></label><br />
							<textarea name="question[tags]" id="q_tags" cols="50" rows="3"><?php echo $this->escape(stripslashes($this->row->tags('string'))); ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	</div>
	<div class="col width-40 fltrt">
		<table class="meta" summary="Metadata for this entry">
			<tbody>
				<tr>
					<th>ID:</th>
					<td>
						<?php echo $this->row->get('id', 0); ?>
						<input type="hidden" name="question[id]" value="<?php echo $this->row->get('id'); ?>" />
					</td>
				</tr>
<?php if ($this->row->get('id')) { ?>
				<tr>
					<th>Created:</th>
					<td><?php echo $this->row->get('created'); ?></td>
				</tr>
				<tr>
					<th>Created by:</th>
					<td><?php echo $this->escape(stripslashes($this->row->creator('name'))); ?></td>
				</tr>
<?php } ?>
			</tbody>
		</table>
		<fieldset class="adminform">
			<legend><span>Parameters</span></legend>

			<table class="admintable">
				<tbody>
					<tr>
						<td class="key"><label for="created_by">Change Creator:</label></td>
						<td><input type="text" name="question[created_by]" id="created_by" size="25" maxlength="50" value="<?php echo $this->row->get('created_by', JFactory::getUser()->get('id')); ?>" /></td>
					</tr>
					<tr>
						<td class="key"><label for="created">Created Date:</label></td>
						<td><?php echo JHTML::_('calendar', $this->row->get('created', JFactory::getDate()->toSql()), 'question[created]', 'created', 'Y-m-d H:i:s', array('class' => 'calendar-field')); ?></td>
					</tr>
					<tr>
						<td class="key"><label for="state">State:</label></td>
						<td>
							<select name="question[state]" id="state">
								<option value="0"<?php echo ($this->row->get('state') == 0) ? ' selected="selected"' : ''; ?>>Open</option>
								<option value="1"<?php echo ($this->row->get('state') == 1) ? ' selected="selected"' : ''; ?>>Closed</option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	</div>
	<div class="clr"></div>

	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="save" />

	<?php echo JHTML::_('form.token'); ?>
</form>
