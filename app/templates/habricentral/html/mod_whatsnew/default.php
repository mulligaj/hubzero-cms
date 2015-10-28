<?php
/**
 * @package     hubzero-cms
 * @author      Shawn Rice <zooley@purdue.edu>
 * @copyright   Copyright 2005-2011 Purdue University. All rights reserved.
 * @license     http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
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
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$rows = $this->rows;

?>

<?php if(count($rows) > 0) : ?>
	<ul class="new">
		<?php foreach($rows as $row) : ?>
			<li><div class="item">
				<h4 class="new-title">
					<a href="<?php echo JRoute::_($row->href); ?>"><?php echo Hubzero\Utility\String::truncate(stripslashes($row->title), 200); ?></a>
				</h4>
				<p>
				<span class="new-details">
					<?php 
						if($row->area) {
							echo "<a href=\"" . JRoute::_('index.php?option=com_resources&type=' . str_replace(" ", "", strtolower($row->area))) . "\">" . JText::_($row->area) . "</a>";
						} else {
							echo JText::_(strtoupper($row->section));
						} 
					?>
				</span>
				<span class="new-date"><?php echo date("F jS, Y", strtotime($row->publish_up)); ?></span>
				</p>
			</div></li>
		<?php endforeach; ?>
	</ul>
<?php else : ?>
	<p>
		<?php echo JText::_('MOD_WHATSNEW_NO_RESULTS'); ?>
	</p>
<?php endif; ?>

<?php /*
<p class="more">
	<a href="<?php echo JRoute::_('index.php?option=com_whatsnew&period='.$this->area.':'.$this->period); ?>">More Resources &rsaquo;</a>
</p>

<?php if($this->feed) : ?>
	<a class="newsfeed" href="<?php echo $this->feedlink; ?>" title="<?php echo JText::_('MOD_WHATSNEW_SUBSCRIBE'); ?>">
		<?php echo JText::_('MOD_WHATSNEW_NEWS_FEED'); ?>
	</a>
<?php endif;*/ ?>
