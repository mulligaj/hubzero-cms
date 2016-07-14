<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_HZEXEC_') or die();
Html::behavior('framework');
?>
<form action="<?php echo Route::url('index.php?option=com_messages'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset>
		<ul class="adminformlist">
			<li><?php echo Lang::txt('COM_MESSAGES_FIELD_USER_ID_FROM_LABEL'); ?>
			<?php echo $this->item->get('from_user_name');?></li>

			<li><?php echo Lang::txt('COM_MESSAGES_FIELD_DATE_TIME_LABEL'); ?>
			<?php echo Date::of($this->item->date_time)->toSql();?></li>

			<li><?php echo Lang::txt('COM_MESSAGES_FIELD_SUBJECT_LABEL'); ?>
			<?php echo $this->item->subject;?></li>

			<li><?php echo Lang::txt('COM_MESSAGES_FIELD_MESSAGE_LABEL'); ?>
			<pre class="pre_message"><?php echo $this->escape($this->item->message);?></pre></li>
		</ul>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="reply_id" value="<?php echo $this->item->message_id; ?>" />
		<?php echo Html::input('token'); ?>
	</fieldset>
</form>
