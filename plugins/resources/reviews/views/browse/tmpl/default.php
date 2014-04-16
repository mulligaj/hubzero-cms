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

if (!isset($this->reviews) || !$this->reviews) 
{
	$this->reviews = array();
}

foreach ($this->reviews as $k => $review)
{
	$this->reviews[$k] = new ResourcesModelReview($review);
}
$this->reviews = new \Hubzero\Base\ItemList($this->reviews);

$juser = JFactory::getUser();
?>
<h3 class="section-header">
	<?php echo JText::_('PLG_RESOURCES_REVIEWS'); ?>
</h3>
<p class="section-options">
	<?php if ($juser->get('guest')) { ?>
			<a href="<?php echo JRoute::_('index.php?option=com_login&return=' . base64_encode(JRoute::_('index.php?option=' . $this->option . '&id=' . $this->resource->id . '&active=reviews&action=addreview#reviewform'))); ?>" class="icon-add add btn">
				<?php echo JText::_('PLG_RESOURCES_REVIEWS_WRITE_A_REVIEW'); ?>
			</a>
	<?php } else { ?>
			<a href="<?php echo JRoute::_('index.php?option=' . $this->option . '&id=' . $this->resource->id . '&active=reviews&action=addreview#reviewform'); ?>" class="icon-add add btn">
				<?php echo JText::_('PLG_RESOURCES_REVIEWS_WRITE_A_REVIEW'); ?>
			</a>
	<?php } ?>
</p>

<?php if ($this->getError()) { ?>
	<p class="warning"><?php echo implode('<br />', $this->getErrors()); ?></p>
<?php } ?>

<?php
if ($this->reviews->total() > 0)
{
	$view = new \Hubzero\Plugin\View(
		array(
			'folder'  => 'resources',
			'element' => 'reviews',
			'name'    => 'browse',
			'layout'  => '_list'
		)
	);
	$view->parent     = 0;
	$view->cls        = 'odd';
	$view->depth      = 0;
	$view->option     = $this->option;
	$view->resource   = $this->resource;
	$view->comments   = $this->reviews;
	$view->config     = $this->config;
	$view->base       = 'index.php?option=' . $this->option . '&id=' . $this->resource->id . '&active=reviews';
	$view->display();
}
else
{
	echo '<p>'.JText::_('PLG_RESOURCES_REVIEWS_NO_REVIEWS_FOUND').'</p>'."\n";
}

// Display the review form if needed
if (!$juser->get('guest')) 
{
	$myreview = $this->h->myreview;
	if (is_object($myreview)) 
	{
		$view = new \Hubzero\Plugin\View(
			array(
				'folder'  => 'resources',
				'element' => 'reviews',
				'name'    => 'review'
			)
		);
		$view->option   = $this->option;
		$view->review   = $this->h->myreview;
		$view->banking  = $this->banking;
		$view->infolink = $this->infolink;
		$view->resource = $this->resource;
		$view->juser    = $juser;
		$view->display();
	}
}
