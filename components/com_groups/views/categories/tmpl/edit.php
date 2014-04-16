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

$title = 'Add Page Category';
if ($this->category->get('id'))
{
	$title = 'Edit Page Category';
}
?>
<?php if (!JRequest::getInt('no_html', 0)) : ?>
<div id="content-header" class="full">
	<h2><?php echo JText::_($title); ?></h2>
</div>
<div id="content-header-extra">
	<ul id="useroptions">
		<li><a class="icon-prev prev btn" href="<?php echo JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=pages#pagecategories'); ?>">Back to Manage Pages</a></li>
	</ul>
</div>
<?php endif; ?>

<div class="main section">
	<?php foreach ($this->notifications as $notification) { ?>
		<p class="<?php echo $notification['type']; ?>"><?php echo $notification['message']; ?></p>
	<?php } ?>

	<form action="<?php echo JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=categories&task=savecategory'); ?>" method="POST" id="hubForm" class="full editcategory">
		<fieldset>
			<legend><?php echo JText::_('Details')?></legend>
			
			<label for="field-category-title">
				<strong>Title:</strong> <span class="required">Required</span>
				<input type="text" name="category[title]" id="field-category-title" value="<?php echo $this->escape($this->category->get('title')); ?>" />
			</label>
			
			<label for="field-category-color">
				<strong>Color:</strong> <span class="optional">Optional</span>
				<input type="text" maxlength="6" name="category[color]" id="field-category-color" value="<?php echo $this->escape($this->category->get('color')); ?>" />
			</label>
		</fieldset>
		
		<p class="submit">
			<button type="submit" class="btn btn-info save icon-save"><?php echo JText::_('Save Category'); ?></button>
		</p>
		<input type="hidden" name="option" value="com_groups" />
		<input type="hidden" name="controller" value="categories" />
		<input type="hidden" name="task" value="save" />
		<input type="hidden" name="category[id]" value="<?php echo $this->category->get('id'); ?>" />
	</form>
</div>