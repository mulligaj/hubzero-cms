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

//get the rows
$rows = $this->rows;

//feedlink
$feedlink  = $this->feedlink;
?>

<?php if(count($rows) > 0) : ?>
	<ul class="questions">
		<?php foreach($rows as $row) :?>
			<li>
				<h4>
					<a href="<?php echo JRoute::_('index.php?option=com_answers&task=question&id='.$row->id); ?>" title="Question: <?php echo $row->subject; ?>">
						<?php echo $row->subject; ?>
					</a>
				</h4>
				
					<?php
						if($row->anonymous) {
							$name = "<i>" . JText::_('MOD_RECENTQUESTIONS_ANONYMOUS') . "</i>";
						} else {
							$juser =& JUser::getInstance( $row->created_by );
							if (is_object($juser)) {
								$name = '<a href="'.JRoute::_('index.php?option=com_members&id='.$juser->get('id')).'">'.stripslashes($juser->get('name')).'</a>';
							}
						}
					?>
				<span class="question-author"><?php echo $name . ",&nbsp;"; ?></span>
				<span class="question-date"><?php echo date("F jS, Y, g:ia", strtotime($row->created)); ?></span>
				<span class="question-answers">
					<?php echo $row->rcount . " Response"; if($row->rcount > 1) { echo "s"; } ?>
				</span>
				
				<span class="question-question"><?php echo substr($row->question,0, 100); if(strlen($row->question) > 100) { echo "&hellip;"; } ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
<?php else : ?>
	<p><?php echo JText::_('MOD_RECENTQUESTIONS_NO_RESULTS'); ?></p>
<?php endif; ?>

<p class="more">
	<a href="<?php echo JRoute::_('index.php?option=com_answers'); ?>">More Questions &rsaquo;</a>
</p>

<?php if($feedlink == "yes") : ?>
	
	<a href="<?php echo JRoute::_('index.php?option=com_answers&task=latest.rss&m='.$modrecentquestions->id, true, -1); ?>" class="newsfeed" title="Latest Question's Feed">Latest Question's Feed</a>
	<br class="clear" />
<?php endif; ?>




