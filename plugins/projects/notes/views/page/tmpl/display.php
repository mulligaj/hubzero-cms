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

$project = JRequest::getVar( 'project', '', 'request', 'object' );

$html = $this->revision->get('pagehtml');

// Parse text for project file references
$html = projectsHelper::parseProjectFileRefs($this->page, $this->revision->get('pagetext'), $project->id, $project->alias, NULL, $html );

// Fix up images
$html = projectsHelper::wikiFixImages($this->page, $this->revision->get('pagetext'), $project->id, $project->alias, NULL, $html, true);

?>
	<header id="<?php echo ($this->sub) ? 'sub-content-header' : 'content-header'; ?>">
		<h2><?php echo $this->title; ?></h2>
	</header><!-- /#content-header -->

<?php echo $this->page->event->afterDisplayTitle; ?>

<?php if ($this->page->isStatic() && $this->page->access('admin') && $this->controller == 'page' && $this->task == 'display') { ?>
	<div id="<?php echo ($this->sub) ? 'sub-content-header' : 'content-header'; ?>-extra">
		<ul id="<?php echo ($this->sub) ? 'section-useroptions' : 'useroptions'; ?>">
			<li><a class="edit btn" href="<?php echo JRoute::_('index.php?option='.$this->option.'&scope='.$this->page->get('scope').'&pagename='.$this->page->get('pagename').'&task=edit'); ?>">Edit</a></li>
			<li class="last"><a class="history btn" href="<?php echo JRoute::_('index.php?option='.$this->option.'&scope='.$this->page->get('scope').'&pagename='.$this->page->get('pagename').'&task=history'); ?>">History</a></li>
		</ul>
	</div><!-- /#content-header-extra -->
<?php } ?>

<?php if ($this->getError()) { ?>
	<p class="error"><?php echo $this->getError(); ?></p>
<?php } ?>

<?php if ($this->message) { ?>
	<p class="passed"><?php echo $this->message; ?></p>
<?php } ?>

<?php
echo $this->page->event->beforeDisplayContent;

if (!$this->page->isStatic()) {
	$view = new JView(array(
		'base_path' => $this->base_path,
		'name'      => 'page',
		'layout'    => 'submenu'
	));
	$view->option = $this->option;
	$view->controller = $this->controller;
	$view->page   = $this->page;
	$view->task   = $this->task;
	//$view->config = $this->config;
	$view->sub    = $this->sub;
	$view->display();
?>
<section class="main section">
		<div class="wikipage">
			<?php echo $html; ?>
		</div>
		<p class="timestamp">
			<?php echo JText::_('COM_WIKI_PAGE_CREATED').' <time datetime="' . $this->page->created() . '">'.$this->page->created('date') . '</time>, '.JText::_('COM_WIKI_PAGE_LAST_MODIFIED').' <time datetime="' . $this->revision->created() . '">' . $this->revision->created('date') . '</time>'; ?>
			<?php /*if ($stats = $this->page->getMetrics()) { ?>
			<span class="article-usage">
				<?php echo $stats['visitors']; ?> Visitors, <?php echo $stats['visits']; ?> Visits
			</span>
			<?php }*/ ?>
		</p>
		<?php if ($tags = $this->page->tags('cloud')) { ?>
		<div class="article-tags">
			<h3><?php echo JText::_('COM_WIKI_PAGE_TAGS'); ?></h3>
			<?php echo $tags; ?>
		</div>
		<?php } ?>
</section><!-- / .main section -->
<?php
} else {
	echo $this->revision->get('pagehtml');
}

echo $this->page->event->afterDisplayContent;
?>