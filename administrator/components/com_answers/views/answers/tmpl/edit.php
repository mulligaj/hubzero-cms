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

$canDo = AnswersHelper::getActions('answer');

$text = ($this->task == 'edit' ? JText::_('Edit') : JText::_('New'));

JToolBarHelper::title(JText::_('Answers Manager') . ': ' . JText::_('Response') . ': ' . $text, 'answers.png');
if ($canDo->get('core.edit')) 
{
	JToolBarHelper::apply();
	JToolBarHelper::save();
	JToolBarHelper::spacer();
}
JToolBarHelper::cancel();

jimport('joomla.html.editor');
$editor = JEditor::getInstance();
?>
<script type="text/javascript">
function submitbutton(pressbutton) 
{
	var form = document.adminForm;

	if (pressbutton =='resethelpful') {
		if (confirm('Are you sure you want to reset the Helpful counts to zero? \nAny unsaved changes to this content will be lost.')){
			submitform( pressbutton );
			return;
		} else {
			return;
		}
	}

	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}

	// do field validation
	/*if (form.answer.value == ''){
		alert( 'Answer must have a response' );
	} else {*/
		submitform( pressbutton );
	//}
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
							<input type="checkbox" name="answer[anonymous]" id="anonymous" value="1" <?php echo ($this->row->get('anonymous')) ? 'checked="checked"' : ''; ?> />
							<label for="anonymous">Anonymous:</label>
						</td>
					</tr>
					<tr>
						<td>
							<span>Question:</span><br />
							<?php echo $this->escape($this->question->subject('clean')); ?>
						</td>
					</tr>
					<tr>
						<td>
							<label>Answer:</label><br />
							<?php echo $editor->display('answer[answer]', $this->escape($this->row->content('raw')), '100%', 'auto', '50', '15'); ?>
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
					<td><?php echo $this->row->get('id'); ?></td>
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
						<td class="key"><label for="state">Accept:</label></td>
						<td><input type="checkbox" name="answer[state]" id="state" value="1" <?php echo $this->row->get('state') ? 'checked="checked"' : ''; ?> /> (<?php echo ($this->row->get('state') == 1) ? 'Accepted answer' : 'Unaccepted'; ?>)</td>
					</tr>
					<tr>
						<td class="key"><label for="created_by">Change Creator:</label></td>
						<td><input type="text" name="answer[created_by]" id="created_by" size="25" maxlength="50" value="<?php echo $this->escape($this->row->get('created_by', JFactory::getUser()->get('id'))); ?>" /></td>
					</tr>
					<tr>
						<td class="key"><label for="created">Created Date:</label></td>
						<td><?php echo JHTML::_('calendar', $this->row->get('created', JFactory::getDate()->toSql()), 'answer[created]', 'created', 'Y-m-d H:i:s', array('class' => 'calendar-field')); ?></td>
					</tr>
					<tr>
						<td class="key">Helpful:</td>
						<td>
							<span style="color:green;">+<?php echo $this->row->get('helpful'); ?></span> 
							<span style="color:red;">-<?php echo $this->row->get('nothelpful'); ?></span> 
							<?php if ( $this->row->get('helpful') > 0 || $this->row->get('nothelpful') > 0 ) { ?>
								<input type="button" name="reset_helpful" value="Reset Helpful" onclick="submitbutton('reset');" />
							<?php } ?>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	</div>
	<div class="clr"></div>
	
	<input type="hidden" name="answer[question_id]" value="<?php echo $this->question->get('id'); ?>" />
	<input type="hidden" name="answer[id]" value="<?php echo $this->row->get('id'); ?>" />
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="save" />
	
	<?php echo JHTML::_('form.token'); ?>
</form>
