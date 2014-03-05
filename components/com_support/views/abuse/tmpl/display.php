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
defined('_JEXEC') or die( 'Restricted access' );
?>
<div id="content-header" class="full">
	<h2><?php echo $this->title; ?></h2>
</div><!-- / #content-header -->

<div class="main section">
<?php
if ($this->report) {
	$name = JText::_('ANONYMOUS');
	if ($this->report->anon == 0) {
		$juser = JUser::getInstance($this->report->author);
		$name = JText::_('UNKNOWN');
		if (is_object($juser)) {
			$name = $juser->get('name');
		}
	}
?>
	<?php if ($this->getError()) { ?>
	<p class="error"><?php echo $this->getError(); ?></p>
	<?php } ?>
	<form action="<?php echo JRoute::_('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=reportabuse'); ?>" method="post" id="hubForm">
		<div class="explaination">
			<p><?php echo JText::_('REPORT_ABUSE_EXPLANATION'); ?></p>
			<p><?php echo JText::_('REPORT_ABUSE_DESCRIPTION_HINT'); ?></p>
		</div>
		<fieldset>
			<legend><?php echo JText::_('REPORT_ABUSE'); ?></legend>

			<div class="field-wrap">
				<div class="abuseitem">
					<h4><?php 
						echo ($this->report->href) ? '<a href="' . $this->report->href . '">': '';
						echo ucfirst($this->cat) . ' by ';
						echo ($this->report->anon != 0) ? JText::_('ANONYMOUS') : $name;
						echo ($this->report->href) ? '</a>': '';
					?></h4>
					<?php echo ($this->report->subject) ? '<p><strong>'.stripslashes($this->report->subject).'</strong></p>' : ''; ?>
					<blockquote cite="<?php echo ($this->report->anon != 0) ? JText::_('ANONYMOUS') : $name; ?>">
						<p><?php echo $this->escape(stripslashes($this->report->text)); ?></p>
					</blockquote>
				</div>
			</div>

			<p class="multiple-option">
				<label class="option" for="subject1"><input type="radio" class="option" name="subject" id="subject1" value="<?php echo JText::_('OFFENSIVE_CONTENT'); ?>" checked="checked" /> <?php echo JText::_('OFFENSIVE_CONTENT'); ?></label>
				<label class="option" for="subject2"><input type="radio" class="option" name="subject" id="subject2" value="<?php echo JText::sprintf('STUPID', ' '); ?>" /> <?php echo JText::sprintf('STUPID',' '); ?></label>
				<label class="option" for="subject3"><input type="radio" class="option" name="subject" id="subject3" value="<?php echo JText::_('SPAM'); ?>" /> <?php echo JText::_('SPAM'); ?></label>
				<label class="option" for="subject4"><input type="radio" class="option" name="subject" id="subject4" value="<?php echo JText::_('OTHER'); ?>" /> <?php echo JText::_('OTHER'); ?></label>
			</p>

			<input type="hidden" name="option" value="<?php echo $this->escape($this->option); ?>" />
			<input type="hidden" name="controller" value="<?php echo $this->escape($this->controller); ?>" />
			<input type="hidden" name="task" value="save" />
			<input type="hidden" name="category" value="<?php echo $this->escape($this->cat); ?>" />
			<input type="hidden" name="referenceid" value="<?php echo $this->escape($this->refid); ?>" />
			<input type="hidden" name="link" value="<?php echo $this->escape($this->report->href); ?>" />

			<?php echo JHTML::_('form.token'); ?>

			<label for="field-report">
				<?php echo JText::_('REPORT_ABUSE_DESCRIPTION'); ?>
				<textarea name="report" id="field-report" rows="10" cols="50"></textarea>
			</label>
		</fieldset>
		<p class="submit"><input type="submit" value="<?php echo JText::_('SUBMIT'); ?>" /></p>
	</form>
	<div class="clear"></div>
<?php } else { ?>
	<?php if ($this->getError()) { ?>
		<p class="error"><?php echo $this->getError(); ?></p>
	<?php } else { ?>
		<p class="warning"><?php echo JText::_('ERROR_NO_INFO_ON_REPORTED_ITEM'); ?></p>
	<?php } ?>
<?php } ?>
</div><!-- / .main section -->

