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
 * @author    Christopher Smoak <csmoak@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');                              

//set title
JToolBarHelper::title('<a href="index.php?option='.$this->option.'">' . JText::_( 'Newsletter Mailings' ) . '</a>', 'mailing.png');

//add buttons to toolbar
//JToolBarHelper::custom('process', 'options', '', 'Process', false);
//JToolBarHelper::custom('processips', 'refresh', '', 'IPs', false);
JToolBarHelper::spacer();
JToolBarHelper::custom('tracking', 'stats', '', 'Stats');
JToolBarHelper::custom('stop', 'trash', '', 'Stop/Cancel');
JToolBarHelper::spacer();
JToolBarHelper::preferences($this->option, '550');
?>

<script type="text/javascript">
function submitbutton(pressbutton) 
{
	if (pressbutton == 'stop')
	{
		var message = 'Are you sure you want to stop the mailing?\n\nThis will remove any remaining emails "queued" from processing and stop any scheduled emails.\n';
		if (!confirm( message ))
		{
			return;
		}
	}
	
	
	// do field validation
	submitform( pressbutton );
}
</script>

<?php
	if ($this->getError())
	{
		echo '<p class="error">' . $this->getError() . '</p>';
	}
?>

<form action="index.php" method="post" name="adminForm">
	<table class="adminlist">
		<thead>
			<tr>
				<th width="30"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->mailings); ?>);" /></th>
				<th><?php echo JText::_('Newsletter'); ?></th>
				<th><?php echo JText::_('Date Sent/Scheduled'); ?></th>
				<th><?php echo JText::_('% Complete'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if (count($this->mailings) > 0) : ?>
				<?php foreach ($this->mailings as $k => $mailing) : ?>
					<tr>
						<td>
							<input type="checkbox" name="id[]" id="cb<?php echo $k;?>" value="<?php echo $mailing->mailing_id; ?>" onclick="isChecked(this.checked);" />
						</td>
						<td>
							<?php echo $mailing->newsletter_name; ?>
						</td>
						<td>
							<?php echo JHTML::_('date', $mailing->mailing_date, "F d, Y @ g:ia"); ?>
						</td>
						<td>
							<?php echo ($mailing->emails_sent/$mailing->emails_total) * 100; ?>% 
							(<?php echo number_format($mailing->emails_sent); ?> of <?php echo number_format($mailing->emails_total); ?> emails sent)
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="4">
						Currently there are no newsletter mailings.
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
</form>