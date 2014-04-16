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

//this is a quick and dirty way to get the one single object
if(isset($this->feed) == TRUE)
{
	$feed = $this->feed;
}
else
{
	$feed = '';
}
//load js
$this->js('feeds');
?>

<div id="content-header" class="full">
	<h2><?php echo $this->title; ?></h2>
</div><!-- / #content-header -->

<div class="main section">

<?php if ($this->getErrors()) { ?>
	<p class="error"><?php echo implode('<br />', $this->getErrors()); ?></p>
<?php } ?>
<form method="post" id="hubForm" action="<?php echo JRoute::_('index.php?option=' . $this->option . ' &task=save'); ?>">
<div class="explaination">
<p>
<?php echo JText::_('COM_FEEDAGGREGATOR_FEED_INFO_ASIDE')?>
</p>
</div>
	<fieldset>
		<legend>Feed Information</legend>
		<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
		<input type="hidden" name="controller" value="<?php echo $this->controller; ?>">
		<input type="hidden" name="id" value="<?php echo (is_object($feed) ? $feed->id : ''); ?>">
		<input type="hidden" name="enabled" value="<?php echo (isset($feed->enabled) ? $feed->enabled : '1'); ?>">
		<input type="hidden" name="task" value="save" />

			<label for="feed-title">
				<?php echo JText::_('COM_FEEDAGGREGATOR_LABEL_FEEDNAME'); ?>
				<span class="required">required</span>
				<input type="text" class="required-field" name="name" id="feedTitle" size="25" value="<?php echo (is_object($feed) ? $feed->name : ''); ?>"/>
			</label>

			<label for="url">
				<?php //echo JText::_('COM_GROUPS_CN'); ?>Feed URL
				<span class="required">required</span>
				<input type="text" class="required-field" name="url" id="feedURL" size="50" value="<?php echo (is_object($feed) ? $feed->url : ''); ?>" />
			</label>

			<label for="description">
				<?php //echo JText::_('COM_GROUPS_CN'); ?>Description
				<input type="text" name="description" id="feedDescription" size="50" value="<?php echo (is_object($feed) ? $feed->description : ''); ?>" />
			</label>
			<!--  <span><p style="float:left";>Auto Approve<input type="checkbox" id="autoApproveCheckbox" name="autoapprove">
			</p></span> -->
		</fieldset>
	<p class="submit">
		<input type="submit" id="submitBtn" class="btn" name="formsubmitBtn" value="Submit<?php //echo JText::_('COM_SUPPORT_SUBMIT'); ?>" />
	</p>
<?php echo JHTML::_('form.token'); ?>
</form>
</div><!-- / .main section -->
