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

$this->css();
?>
	<h3 class="section-header">
		<?php echo JText::_('PLG_PUBLICATIONS_WISHLIST'); ?>
	</h3>
	<div class="container">
		<p class="section-options">
			<a class="icon-add add btn" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&task=add&category=' . $this->wishlist->category . '&rid=' . $this->wishlist->referenceid); ?>">
				<?php echo JText::_('PLG_PUBLICATIONS_WISHLIST_ADD_NEW_WISH'); ?>
			</a>
		</p>
		<table class="ideas entries">
			<caption>
				<?php echo $this->title; ?>
			</caption>
			<tbody>
<?php
	if ($this->wishlist->items) {
		foreach ($this->wishlist->items as $item)
		{
			$item->subject = $this->escape(stripslashes($item->subject));

			$item->bonus = $this->config->get('banking') ? $item->bonus : 0;

			if ($item->status == 7) {
				$status = 'outstanding';
			} else if (isset($item->ranked) && !$item->ranked && $item->status!=1 && $item->status!=3 && $item->status!=4 && ($this->admin==2 or $this->admin==3))  {
				$status = 'unranked';
			} else {
				$status = 'outstanding';
			}

			$state  = (isset($item->ranked) && !$item->ranked && $item->status!=1 && ($this->admin==2 or $this->admin==3)) ? 'new' : '' ;
			$state .= ($item->private) ? ' private' : '' ;
			switch ($item->status)
			{
				case 3:
					$state .= ' rejected';
				break;
				case 2:
					$state .= '';
				break;
				case 1:
					$state .= ' granted';
				break;
				case 0:
				default:
					if ($item->accepted == 1) {
						$state .= ' accepted';
					} else {
						$state .= ' pending';
					}
				break;
			}

			$name = JText::_('COM_WISHLIST_ANONYMOUS');
			if (!$item->anonymous) {
				$name = '<a href="'.JRoute::_('index.php?option=com_members&id='.$item->proposed_by).'">' . $this->escape($item->authorname) . '</a>';
			}
?>
				<tr class="<?php echo $state; ?>">
					<th class="<?php echo $status; ?>">
						<span class="entry-id"><?php echo $item->id; ?></span>
					</th>
					<td>
					<?php if ($item->status != 7) { ?>
						<a class="entry-title" href="<?php echo JRoute::_('index.php?option='.$this->option.'&task=wish&category='.$this->wishlist->category.'&rid='.$this->wishlist->referenceid.'&wishid='.$item->id.'&filterby='.$this->filters['filterby'].'&sortby='.$this->filters['sortby'].'&tags='.$this->filters['tag']); ?>"><?php echo $item->subject; ?></a><br />
						<span class="entry-details">
							<?php echo JText::_('COM_WISHLIST_WISH_PROPOSED_BY'); ?> <?php echo $name; ?> @
							<span class="entry-time"><time datetime="<?php echo $item->proposed; ?>"><?php echo JHTML::_('date', $item->proposed, JText::_('TIME_FORMAT_HZ1')); ?></time></span> <?php echo JText::_('COM_WISHLIST_on'); ?>
							<span class="entry-date"><time datetime="<?php echo $item->proposed; ?>"><?php echo JHTML::_('date', $item->proposed, JText::_('DATE_FORMAT_HZ1')); ?></time></span>
							<span class="entry-details-divider">&bull;</span>
							<span class="entry-comments"><a href="<?php echo JRoute::_('index.php?option='.$this->option.'&task=wish&category='.$this->wishlist->category.'&rid='.$this->wishlist->referenceid.'&wishid='.$item->id.'&com=1&filterby='.$this->filters['filterby'].'&sortby='.$this->filters['sortby'].'&tags='.$this->filters['tag'].'#comments'); ?>" title="<?php echo $item->numreplies; ?> <?php echo JText::_('COM_WISHLIST_COMMENTS'); ?>"><?php echo $item->numreplies; ?></a></span>
						</span>
					<?php } else { ?>
						<span class="warning adjust"><?php echo JText::_('COM_WISHLIST_NOTICE_POSTING_REPORTED'); ?></span>
					<?php } ?>
					</td>
				<?php if ($this->config->get('banking')) { ?>
					<td class="reward">
						<span class="entry-reward">
						<?php if (isset($item->bonus) && $item->bonus > 0 && ($item->status==0 or $item->status==6)) { ?>
							<a class="bonus tooltips" href="<?php echo JRoute::_('index.php?option='.$this->option.'&task=wish&category='.$this->wishlist->category.'&rid='.$this->wishlist->referenceid.'&wishid='.$item->id.'&action=addbonus&filterby='.$this->filters['filterby'].'&sortby='.$this->filters['sortby'].'&tags='.$this->filters['tag'].'#action'); ?>" title="<?php echo JText::_('COM_WISHLIST_WISH_ADD_BONUS').' ::'.$item->bonusgivenby.' '.JText::_('COM_WISHLIST_MULTIPLE_USERS').' '.JText::_('COM_WISHLIST_WISH_BONUS_CONTRIBUTED_TOTAL').' '.$item->bonus.' '.JText::_('COM_WISHLIST_POINTS').' '.JText::_('COM_WISHLIST_WISH_BONUS_AS_BONUS'); ?>"><?php echo $item->bonus; ?> <span><?php echo JText::_('COM_WISHLIST_POINTS'); ?></span></a>
						<?php } else if ($item->status == 0 || $item->status == 6) { ?>
							<a class="nobonus tooltips" href="<?php echo JRoute::_('index.php?option='.$this->option.'&task=wish&category='.$this->wishlist->category.'&rid='.$this->wishlist->referenceid.'&wishid='.$item->id.'&action=addbonus&filterby='.$this->filters['filterby'].'&sortby='.$this->filters['sortby'].'&tags='.$this->filters['tag'].'#action'); ?>" title="<?php echo JText::_('COM_WISHLIST_WISH_ADD_BONUS').' :: '.JText::_('COM_WISHLIST_WISH_BONUS_NO_USERS_CONTRIBUTED'); ?>"><?php echo $item->bonus; ?> <span><?php echo JText::_('COM_WISHLIST_POINTS'); ?></span></a>
						<?php } else { ?>
							<span class="inactive" title="<?php echo JText::_('COM_WISHLIST_WISH_BONUS_NOT_ACCEPTED'); ?>">&nbsp;</span>
						<?php } ?>
						</span>
					</td>
				<?php } ?>
				<?php if ($item->status != 7) { ?>
					<td class="voting">
					<?php
						$view = new \Hubzero\Component\View(array(
							'name'      =>'wishlist',
							'base_path' => JPATH_ROOT . DS . 'components' . DS . $this->option,
							'layout'    => '_vote'
						));
						$view->set('option', 'com_wishlist')
						     ->set('item',  new WishlistModelWish($item))
						     ->set('listid', $this->wishlist->id)
						     ->set('plugin', 0)
						     ->set('admin', 0)
						     ->set('page', 'wishlist')
						     ->set('filters', $this->filters)
						     ->display();
					?>
					</td>
					<td class="ranking">
					<?php /*if ($this->admin
						|| $item->status == 1
						|| ($item->status == 0 && $item->accepted == 1)
						|| $item->status == 3
						|| $item->status == 4
					) { */
						$html = '';
						switch ($item->status)
						{
							case 0:
								if (isset($item->ranked) && !$item->ranked && ($this->admin==2 or $this->admin==3)) {
									$html .= '<a class="rankit" href="index.php?option='.$this->option.'&task=wish&category='.$this->wishlist->category.'&rid='.$this->wishlist->referenceid.'&wishid='.$item->id.'&filterby='.$this->filters['filterby'].'&sortby='.$this->filters['sortby'].'&tags='.$this->filters['tag'].'">'.JText::_('COM_WISHLIST_WISH_RANK_THIS').'</a>'."\n";
								} else if (isset($item->ranked) && $item->ranked) {
									//$html .= JText::_('COM_WISHLIST_WISH_PRIORITY').': <span class="priority">'.$item->ranking.'</span>'."\n";
									$html .= '<span class="priority-level-base">
										<span class="priority-level" style="width: '.(($item->ranking/50)*100).'%">
											<span>'.JText::_('COM_WISHLIST_WISH_PRIORITY').': '.$item->ranking.'</span>
										</span>
									</span>';
								}
								if ($item->accepted == 1) {
									$html .= '<span class="accepted">'.JText::_('COM_WISHLIST_WISH_STATUS_ACCEPTED').'</span>';
								}
							break;
							case 1:
								$html .= '<span class="granted">'.JText::_('COM_WISHLIST_WISH_STATUS_GRANTED').'</span>';
								/*if ($item->granted != '0000-00-00 00:00:00') {
									$html .= ' <span class="mini">'.strtolower(JText::_('COM_WISHLIST_ON')).' '.JHTML::_('date',$item->granted, JText::_('COM_WISHLIST_DATE_FORMAT_HZ1')).'</span>';
								}*/
							break;
							case 3:
								$html .= '<span class="rejected">'.JText::_('COM_WISHLIST_WISH_STATUS_REJECTED').'</span>';
							break;
							case 4:
								$html .= '<span class="withdrawn">'.JText::_('COM_WISHLIST_WISH_STATUS_WITHDRAWN').'</span>';
							break;
						}
						echo $html;
					//} ?>
					</td>
				<?php } ?>
				</tr>
<?php
		} // end foreach
	}
?>
			</tbody>
		</table>
	</div><!-- / .container -->
