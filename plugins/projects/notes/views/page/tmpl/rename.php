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
	<div id="<?php echo ($this->sub) ? 'sub-content-header' : 'content-header'; ?>">
		<h2><?php echo $this->escape($this->title); ?></h2>
		<?php
		if (!$this->page->isStatic()) 
		{
			$view = new JView(array(
				'base_path' => $this->base_path, 
				'name'      => 'page',
				'layout'    => 'authors'
			));
			$view->page   = $this->page;
			$view->display();
		}
		?>
	</div><!-- /#content-header -->

<?php if ($this->getError()) { ?>
	<p class="error"><?php echo $this->getError(); ?></p>
<?php } ?>
<?php if ($this->message) { ?>
	<p class="passed"><?php echo $this->message; ?></p>
<?php } ?>

<?php
if ($this->page->exists()) 
{
	$view = new JView(array(
		'base_path' => $this->base_path, 
		'name'      => 'page',
		'layout'    => 'submenu'
	));
	$view->option = $this->option;
	$view->controller = $this->controller;
	$view->page   = $this->page;
	$view->task   = $this->task;
	$view->config = $this->config;
	$view->sub    = $this->sub;
	$view->display();
}
?>

<div class="main section">
<?php if ($this->page->isLocked() && !$this->page->access('manage')) { ?>
	<p class="warning"><?php echo JText::_('COM_WIKI_WARNING_NOT_AUTH_EDITOR'); ?></p>
<?php } else { ?>

	<form action="<?php echo JRoute::_('index.php?option='.$this->option.'&scope='.$this->page->get('scope')); ?>" method="post" id="hubForm">
		<div class="explaination">
			<p><?php echo JText::_('COM_WIKI_PAGENAME_EXPLANATION'); ?></p>
		</div>
		<fieldset>
			<h3><?php echo JText::_('COM_WIKI_CHANGE_PAGENAME'); ?></h3>
			<label>
				<?php echo JText::_('COM_WIKI_FIELD_PAGENAME'); ?>:
				<input type="text" name="newpagename" id="newpagename" value="<?php echo $this->escape($this->page->get('pagename')); ?>" size="38" />
				<span><?php echo JText::_('COM_WIKI_FIELD_PAGENAME_HINT'); ?></span>
			</label>

			<input type="hidden" name="oldpagename" value="<?php echo $this->escape($this->page->get('pagename')); ?>" />
			<input type="hidden" name="scope" value="<?php echo $this->escape($this->page->get('scope')); ?>" />
			<input type="hidden" name="pageid" value="<?php echo $this->escape($this->page->get('id')); ?>" />
			<input type="hidden" name="option" value="<?php echo $this->escape($this->option); ?>" />
			<input type="hidden" name="action" value="saverename" />
			<input type="hidden" name="active" value="<?php echo $this->sub; ?>" />

			<?php echo JHTML::_('form.token'); ?>
		</fieldset>
		<div class="clear"></div>
		<p class="submit"><input type="submit" value="<?php echo JText::_('SUBMIT'); ?>" /></p>
	</form>
<?php } ?>
</div><!-- / .main section -->
<div class="clear"></div>
