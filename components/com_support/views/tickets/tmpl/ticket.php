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

$juser = JFactory::getUser();

JPluginHelper::importPlugin('hubzero');
$dispatcher = JDispatcher::getInstance();

$status = SupportHtml::getStatus($this->row->open, $this->row->status);

$unknown = 1;
$name = JText::_('Unknown');
$usertype = JText::_('Unknown');

$submitter = new \Hubzero\User\Profile();
if ($this->row->login) 
{
	//$juseri = JUser::getInstance($comment->created_by);
	$submitter->load($this->row->login);
	if (is_object($submitter) && $submitter->get('name')) 
	{
		if (version_compare(JVERSION, '1.6', 'lt'))
		{
			$usertype = JUser::getInstance($submitter->get('uidNumber'))->get('usertype');
		} 
		else
		{
			jimport( 'joomla.user.helper' );
			$usertype = implode(', ', JUserHelper::getUserGroups($submitter->get('uidNumber')));
		}

		$name = '<a rel="profile" href="' . JRoute::_('index.php?option=com_members&id=' . $submitter->get('uidNumber')) . '">' . $this->escape(stripslashes($submitter->get('name'))) . ' (' . $this->escape(stripslashes($this->row->login)) . ')</a>';
		$unknown = 0;
	} 
	else 
	{
		$name  = '<a rel="email" href="mailto:'. $this->row->email .'">';
		$name .= ($this->row->login) ? $this->escape(stripslashes($this->row->name)) . ' (' . $this->escape(stripslashes($this->row->login)) . ')' : $this->escape(stripslashes($this->row->name));
		$name .= '</a>';
	}
} 
else 
{
	$name  = '<a rel="email" href="mailto:'. $this->row->email .'">';
	$name .= ($this->row->login) ? $this->escape(stripslashes($this->row->name)) . ' (' . $this->escape(stripslashes($this->row->login)) . ')' : $this->escape(stripslashes($this->row->name));
	$name .= '</a>';
}

$prev = null;
$next = null;

$sq = new SupportQuery(JFactory::getDBO());
$sq->load($this->filters['show']);
if ($sq->conditions)
{
	$sq->query = $sq->getQuery($sq->conditions);

	$this->filters['sort']    = $sq->sort;
	$this->filters['sortdir'] = $sq->sort_dir;
	if ($rows = $this->row->getRecords($sq->query, $this->filters))
	{
		foreach ($rows as $key => $row)
		{
			if ($row->id == $this->row->id)
			{
				if (isset($rows[$key - 1]))
				{
					$next = $rows[$key - 1];
				}
				if (isset($rows[$key + 1]))
				{
					$prev = $rows[$key + 1];
				}
				break;
			}
		}
		unset($rows);
	}
}

if (!trim($this->row->report))
{
	$this->row->report = JText::_('(no content found)');
}

$cc = array();
?>
<div id="content-header">
	<h2><?php echo $this->title; ?></h2>
</div><!-- / #content-header -->

<div id="content-header-extra">
	<ul id="useroptions">
<?php if ($prev) { ?>
		<li>
			<a class="icon-prev prev btn" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&task=ticket&id=' . $prev->id . '&show=' . $this->filters['show'] . '&search=' . $this->filters['search'] . '&limit='.$this->filters['limit'] . '&limitstart=' . $this->filters['start']); ?>">
				<?php echo JText::_('COM_SUPPORT_PREV'); ?>
			</a>
		</li>
<?php } ?>
		<li>
			<a class="browse btn" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&task=tickets&show=' . $this->filters['show'] . '&search=' . $this->filters['search'] . '&limit='.$this->filters['limit'] . '&limitstart=' . $this->filters['start']); ?>">
				<?php echo JText::_('COM_SUPPORT_TICKETS'); ?>
			</a>
		</li>
<?php if ($next) { ?>
		<li>
			<a class="icon-next next opposite btn" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&task=ticket&id=' . $next->id . '&show=' . $this->filters['show'] . '&search=' . $this->filters['search'] . '&limit='.$this->filters['limit'] . '&limitstart=' . $this->filters['start']); ?>">
				<?php echo JText::_('COM_SUPPORT_NEXT'); ?>
			</a>
		</li>
<?php } ?>
	</ul>
</div><!-- / #content-header-extra -->

<?php if ($this->getError()) { ?>
<p class="error"><?php echo implode('<br />', $this->getErrors()); ?></p>
<?php } ?>

<?php
	$watching = new SupportTableWatching(JFactory::getDBO());
	/*$res = $watching->count(array(
		'ticket_id' => $this->row->id,
		'user_id'   => $juser->get('id')
	));*/
	$watching->load($this->row->id, $juser->get('id'));
	/*if ($watching->id)
	{
	?>
	<div id="watching">
		<p>This ticket is saved in your watch list. <a class="stop-watching" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&task=ticket&id=' . $this->row->id . '&show=' . $this->filters['show'] . '&search=' . $this->filters['search'] . '&limit='.$this->filters['limit'] . '&limitstart=' . $this->filters['start'] . '&watch=stop'); ?>">Remove it</a></p>
	</div>
	<?php
	}*/
?>

<div class="main section">

	<div class="aside">
		<div class="ticket-status">
			<p class="<?php echo (!$this->row->open) ? 'closed' : 'open'; ?>"><strong><?php echo (!$this->row->open) ? JText::_('TICKET_STATUS_CLOSED_TICKET') : JText::_('TICKET_STATUS_OPEN_TICKET'); ?></strong></p>
<?php if (!$this->row->open) { ?>
			<p><strong>Note:</strong> To reopen this issue, add a comment below.</p>
<?php } ?>
			<!-- <p class="entry-number">#<strong><?php echo $this->row->id; ?></strong></p> -->
		</div><!-- / .entry-status -->
	
		<div class="ticket-watch">
		<?php if ($watching->id) { ?>
			<div id="watching">
				<p>This ticket is saved in your watch list.</p>
				<p><a class="stop-watching btn" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&task=ticket&id=' . $this->row->id . '&show=' . $this->filters['show'] . '&search=' . $this->filters['search'] . '&limit='.$this->filters['limit'] . '&limitstart=' . $this->filters['start'] . '&watch=stop'); ?>">Stop watching</a></p>
			</div>
		<?php } ?>
		<?php if (!$watching->id) { ?>
			<p><a class="start-watching btn" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&task=ticket&id=' . $this->row->id . '&show=' . $this->filters['show'] . '&search=' . $this->filters['search'] . '&limit='.$this->filters['limit'] . '&limitstart=' . $this->filters['start'] . '&watch=start'); ?>">Watch ticket</a></p>
		<?php } ?>
			<p>When watching a ticket, you will be notified of any comments added or changes made. You may stop watching at any time.</p>
		</div>
	</div><!-- / .aside -->

	<div class="subject">
		<div class="ticket entry" id="t<?php echo $this->row->id; ?>">
			<p class="entry-member-photo">
				<span class="entry-anchor"><a name="ticket"></a></span>
				<img src="<?php echo \Hubzero\User\Profile\Helper::getMemberPhoto($submitter, $unknown); ?>" alt="" />
			</p><!-- / .entry-member-photo -->
			<div class="entry-content">
				<p class="entry-title">
					<strong><?php echo $name; ?></strong> 
					<a class="permalink" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&task=ticket&id=' . $this->row->id); ?>" title="<?php echo JText::_('COM_SUPPORT_PERMALINK'); ?>"><span class="entry-date-at">@</span> 
						<span class="time"><time datetime="<?php echo $this->row->created; ?>"><?php echo JHTML::_('date', $this->row->created, JText::_('TIME_FORMAT_HZ1')); ?></time></span> <span class="entry-date-on"><?php echo JText::_('on'); ?></span> 
						<span class="date"><time datetime="<?php echo $this->row->created; ?>"><?php echo JHTML::_('date', $this->row->created, JText::_('DATE_FORMAT_HZ1')); ?></time></span>
					</a>
				</p><!-- / .entry-title -->
				<div class="entry-body" cite="<?php echo ($this->row->login) ? $this->escape(stripslashes($this->row->login)) : $this->escape(stripslashes($this->row->name)); ?>">
					<p><?php echo preg_replace('/  /', ' &nbsp;', $this->row->report); ?></p>
				</div><!-- / .entry-body -->
			</div><!-- / .entry-content -->
<?php if ($this->acl->check('update', 'tickets') > 0) { ?>
				<div class="entry-details">
					<table summary="<?php echo JText::_('TICKET_DETAILS_TBL_SUMMARY'); ?>">
						<tbody>
							<tr>
								<th scope="row"><?php echo JText::_('TICKET_DETAILS_EMAIL'); ?>:</th>
								<td><a href="mailto:<?php echo $this->row->email; ?>"><?php echo $this->escape($this->row->email); ?></a></td>
							</tr>
							<tr>
								<th scope="row"><?php echo JText::_('TICKET_DETAILS_USERTYPE'); ?>:</th>
								<td><?php echo $this->escape($usertype); ?></td>
							</tr>
							<tr>
								<th scope="row"><?php echo JText::_('TICKET_DETAILS_OS'); ?>:</th>
								<td><?php echo $this->escape($this->row->os); ?> / <?php echo $this->escape($this->row->browser); ?> (<?php echo ($this->row->cookies) ? JText::_('COOKIES_ENABLED') : JText::_('COOKIES_DISABLED'); ?>)</td>
							</tr>
							<tr>
								<th scope="row"><?php echo JText::_('TICKET_DETAILS_IP'); ?>:</th>
								<td><?php echo $this->escape($this->row->ip); ?> (<?php echo  $this->escape($this->row->hostname); ?>)</td>
							</tr>
							<tr>
								<th scope="row"><?php echo JText::_('TICKET_DETAILS_REFERRER'); ?>:</th>
								<td><?php echo ($this->row->referrer) ? $this->escape($this->row->referrer) : ' '; ?></td>
							</tr>
							<tr>
								<th scope="row"><?php echo JText::_('TICKET_DETAILS_INSTANCES'); ?>:</th>
								<td><?php echo $this->escape($this->row->instances); ?></td>
							</tr>
<?php 			if ($this->row->uas) { ?>
							<tr>
								<td colspan="2"><?php echo $this->escape($this->row->uas); ?></td>
							</tr>
<?php 			} ?>
						</tbody>
					</table>
				</div><!-- / .entry-details -->
<?php } ?>
		</div><!-- / .ticket -->
	</div><!-- / .subject -->
</div><!-- / .main section -->

<?php if ($this->acl->check('read', 'comments')) { ?>
<div class="below section">
	<h3><a name="comments"></a><?php echo JText::_('TICKET_COMMENTS'); ?></h3>
			
	<div class="aside">
<?php if ($this->acl->check('create', 'comments')) { ?>
		<p>
			<a class="icon-add add btn" href="#commentform"><?php echo JText::_('ADD_COMMENT'); ?></a>
		</p>
<?php } ?>
	</div><!-- / .aside -->

	<div class="subject">
	<?php if (count($this->comments) > 0) { ?>
		<ol class="comments">
		<?php
		$o = 'even';
		$i = 0;
		foreach ($this->comments as $comment)
		{
			// Is the comment private?
			// If so, does the user have access to read private comments?
			//   If not, skip it
			if (!$this->acl->check('read', 'private_comments') && $comment->isPrivate()) 
			{
				continue;
			}
			$i++;

			// Set the CSS class
			if ($comment->isPrivate()) 
			{
				$access = 'private';
			} 
			else 
			{
				$access = 'public';
			}
			if ($comment->get('created_by') == $this->row->login && !$comment->isPrivate()) 
			{
				$access = 'submitter';
			}

			$name = JText::_('Unknown');
			$cite = $name;

			if ($comment->creator()) 
			{
				$cite = $this->escape(stripslashes($comment->creator('name')));
				$name = '<a href="' . JRoute::_('index.php?option=com_members&id=' . $comment->creator('id')) . '">' . $cite . '</a>';
			}

			$o = ($o == 'odd') ? 'even' : 'odd';

			if ($comment->changelog()->format() != 'html')
			{
				$cc = $comment->changelog()->get('cc');
			}
			?>
			<li class="comment <?php echo $access . ' ' . $o; ?>" id="c<?php echo $comment->get('id'); ?>">
				<p class="comment-member-photo">
					<img src="<?php echo $comment->creator('picture'); ?>" alt="" />
				</p>
				<div class="comment-content">
					<p class="comment-head">
						<strong>
							<?php echo $name; ?>
						</strong>
						<a class="permalink" href="<?php echo JRoute::_($comment->link()); ?>" title="<?php echo JText::_('COM_SUPPORT_PERMALINK'); ?>">
							<span class="comment-date-at">@</span> 
							<span class="time"><time datetime="<?php echo $this->escape($comment->created()); ?>"><?php echo $comment->created('time'); ?></time></span> 
							<span class="comment-date-on"><?php echo JText::_('on'); ?></span> 
							<span class="date"><time datetime="<?php echo $this->escape($comment->created()); ?>"><?php echo $comment->created('date'); ?></time></span>
						</a>
					</p><!-- / .comment-head -->
				<?php if ($content = $comment->content('parsed')) { ?>
					<div class="comment-body" cite="<?php echo $cite; ?>">
						<p><?php echo $content; ?></p>
					</div><!-- / .comment-body -->
				<?php } ?>
				<?php if ($comment->attachments()->total()) { ?>
					<div class="comment-attachments">
						<?php
						foreach ($comment->attachments() as $attachment)
						{
							if (!trim($attachment->get('description')))
							{
								$attachment->set('description', $attachment->get('filename'));
							}

							if ($attachment->isImage()) 
							{
								if ($attachment->width() > 400) 
								{
									$img = '<p><a href="' . JRoute::_($attachment->link()) . '"><img src="' . JRoute::_($attachment->link()) . '" alt="' . $attachment->get('description') . '" width="400" /></a></p>';
								} 
								else 
								{
									$img = '<p><img src="' . JRoute::_($attachment->link()) . '" alt="' . $attachment->get('description') . '" /></p>';
								}
								echo $img;
							} 
							else 
							{
								echo '<p class="attachment"><a href="' . JRoute::_($attachment->link()) . '" title="' . $attachment->get('description') . '">' . $attachment->get('description') . '</a></p>';
							}
						}
						?>
					</div><!-- / .comment-body -->
				<?php } ?>
				</div><!-- / .comment-content -->
				<div class="comment-changelog">
					<?php echo $comment->changelog()->render(); ?>
				</div><!-- / .changelog -->
			</li>
		<?php } ?>
		</ol>
	<?php } else { ?>
		<p class="no-comments"><?php echo JText::_('No comments found.'); ?></p>
	<?php } ?>
	</div><!-- / .subject -->
	<div class="clear"></div>
</div><!-- / .below section -->
<?php } // ACL can read comments ?>

<?php if ($this->acl->check('create', 'comments') || $this->acl->check('update', 'tickets')) { ?>
<div class="below section">
	<h3>
		<?php echo JText::_('COMMENT_FORM'); ?>
	</h3>
	
	<div class="aside">
		<p>Please remember to describe problems in detail, including any steps you may have taken before encountering an error.</p>
	</div><!-- / .aside -->
	
	<div class="subject">
		<form action="<?php echo JRoute::_('index.php?option=' . $this->option . '&controller=' . $this->controller . '&task=update'); ?>" method="post" id="commentform" enctype="multipart/form-data">
			<p class="comment-member-photo">
				<span class="comment-anchor"><a name="commentform"></a></span>
			<?php
				if (!$juser->get('guest')) {
					$jxuser = new \Hubzero\User\Profile();
					$jxuser->load($juser->get('id'));
					$thumb = \Hubzero\User\Profile\Helper::getMemberPhoto($jxuser, 0);
				} else {
					$config = JComponentHelper::getParams('com_members');
					$thumb = DS . ltrim($config->get('defaultpic'), DS);
					$thumb = \Hubzero\User\Profile\Helper::thumbit($thumb);
				}
			?>
				<img src="<?php echo $thumb; ?>" alt="" />
			</p>
			<fieldset>
				<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
				<input type="hidden" name="ticket[id]" id="ticketid" value="<?php echo $this->row->id; ?>" />
				<input type="hidden" name="username" value="<?php echo $juser->get('username'); ?>" />
				
				<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
				<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
				<input type="hidden" name="task" value="update" />
				
				<input type="hidden" name="search" value="<?php echo $this->escape($this->filters['search']); ?>" />
				<input type="hidden" name="show" value="<?php echo $this->escape($this->filters['show']); ?>" />
				<input type="hidden" name="limit" value="<?php echo $this->escape($this->filters['limit']); ?>" />
				<input type="hidden" name="limistart" value="<?php echo $this->escape($this->filters['start']); ?>" />
<?php if (!$this->acl->check('create', 'private_comments')) { ?>
				<input type="hidden" name="access" value="0" />
<?php } ?>

	<?php if ($this->acl->check('update', 'tickets')) { ?>
				<fieldset>
			<?php if ($this->acl->check('update', 'tickets') > 0) { ?>
					<legend><span><?php echo JText::_('Ticket Details'); ?></span></legend>
					<label>
						<?php echo JText::_('COMMENT_TAGS'); ?>:<br />
						<?php 
					$tf = $dispatcher->trigger('onGetMultiEntry', array(array('tags', 'tags', 'actags', '',$this->lists['tags'])));

					if (count($tf) > 0) {
						echo $tf[0];
					} else { ?>
						<input type="text" name="tags" id="tags" value="<?php echo $this->lists['tags']; ?>" size="35" />
					<?php } ?>
					</label>

					<div class="grouping">
						<label>
							<?php echo JText::_('COMMENT_GROUP'); ?>:
							<?php 
						$gc = $dispatcher->trigger('onGetSingleEntryWithSelect', array(array('groups', 'ticket[group]', 'acgroup', '',$this->row->group, '', 'ticketowner')));
						if (count($gc) > 0) {
							echo $gc[0];
						} else { ?>
							<input type="text" name="ticket[group]" value="<?php echo $this->row->group; ?>" id="acgroup" value="" autocomplete="off" />
						<?php } ?>
						</label>

						<label>
							<?php echo JText::_('COMMENT_OWNER'); ?>:
							<?php echo $this->lists['owner']; ?>
						</label>
					</div>
					<div class="clear"></div>

					<div class="grouping">
						<label>
							<?php echo JText::_('COMMENT_SEVERITY'); ?>:
							<?php echo SupportHtml::selectArray('ticket[severity]', $this->lists['severities'], $this->row->severity); ?>
						</label>
			<?php } else { ?>
						<input type="hidden" name="tags" value="<?php echo $this->escape($this->lists['tags']); ?>" />
			<?php } // ACL can update ticket (admin) ?>
						<label>
							<?php echo JText::_('COMMENT_STATUS'); ?>:
							<select name="ticket[resolved]" id="status">
								<option value=""<?php if ($this->row->open && $this->row->status != 2) { echo ' selected="selected"'; } ?>><?php echo JText::_('COMMENT_OPT_OPEN'); ?></option>
								<option value="1"<?php if ($this->row->open && $this->row->status == 2) { echo ' selected="selected"'; } ?>><?php echo JText::_('COMMENT_OPT_WAITING'); ?></option>
								<optgroup label="Closed">
									<option value="noresolution"<?php if (!$this->row->open && $this->row->resolved == 'noresolution') { echo ' selected="selected"'; } ?>><?php echo JText::_('COMMENT_OPT_CLOSED'); ?></option>
						<?php 
						if (isset($this->lists['resolutions']) && $this->lists['resolutions']!='') {
							foreach ($this->lists['resolutions'] as $anode)
							{
								$selected = ($anode->alias == $this->row->resolved)
										  ? ' selected="selected"'
										  : '';
								echo '<option value="'.$anode->alias.'"'.$selected.'>'.$this->escape(stripslashes($anode->title)).'</option>';
							}
						}
						?>
								</optgroup>
							</select>
						</label>
			<?php if ($this->acl->check('update', 'tickets') > 0) { ?>
					</div>

					<?php if (isset($this->lists['categories']) && $this->lists['categories'])  { ?>
					<label for="ticket-field-category">
						<?php echo JText::_('Category:'); ?>
						<select name="ticket[category]" id="ticket-field-category">
							<option value=""><?php echo JText::_('(none)'); ?></option>
							<?php
							foreach ($this->lists['categories'] as $category)
							{
								?>
								<option value="<?php echo $this->escape($category->alias); ?>"<?php if ($this->row->get('category') == $category->alias) { echo ' selected="selected"'; } ?>><?php echo $this->escape(stripslashes($category->title)); ?></option>
								<?php
							}
							?>
						</select>
					</label>
					<?php } ?>
			<?php } ?>
					<div class="clear"></div>
				</fieldset>
	<?php } else { ?>
				<input type="hidden" name="tags" value="<?php echo $this->escape($this->lists['tags']); ?>" />
	<?php } // ACL can update tickets ?>

<?php if ($this->acl->check('create', 'comments') || $this->acl->check('create', 'private_comments')) { ?>
				<?php
					JPluginHelper::importPlugin('support');
					$results = $dispatcher->trigger('onTicketComment', array($this->row));
					echo implode("\n", $results);
				?>
				<fieldset>
					<legend><?php echo JText::_('COMMENT_LEGEND_COMMENTS'); ?>:</legend>
<?php if ($this->acl->check('create', 'comments') > 0 || $this->acl->check('create', 'private_comments')) { ?>
					<div class="top grouping">
<?php } ?>
<?php if ($this->acl->check('create', 'comments') > 0) { ?>
						<label>
							<?php
							$hi = array();
							$o  = '<select name="messages" id="messages">' . "\n";
							$o .= "\t" . '<option value="mc">' . JText::_('COMMENT_CUSTOM') . '</option>' . "\n";
							$jconfig = JFactory::getConfig();
							foreach ($this->lists['messages'] as $message)
							{
								$message->message = str_replace('"', '&quot;', $message->message);
								$message->message = str_replace('&quote;', '&quot;', $message->message);
								$message->message = str_replace('#XXX', '#' . $this->row->id, $message->message);
								$message->message = str_replace('{ticket#}', $this->row->id, $message->message);
								$message->message = str_replace('{sitename}', $jconfig->getValue('config.sitename'), $message->message);
								$message->message = str_replace('{siteemail}', $jconfig->getValue('config.mailfrom'), $message->message);

								$o .= "\t".'<option value="m' . $message->id . '">' . $this->escape(stripslashes($message->title)) . '</option>' . "\n";

								$hi[] = '<input type="hidden" name="m' . $message->id . '" id="m'.$message->id.'" value="' . $this->escape(stripslashes($message->message)) . '" />' . "\n";
							}
							$o .= '</select>' . "\n";
							$hi = implode("\n", $hi);
							echo $o . $hi;
							?>
						</label>
<?php } // ACL can create comment (admin) ?>
<?php if ($this->acl->check('create', 'private_comments')) { ?>
						<label>
							<input class="option" type="checkbox" name="access" id="make-private" value="1" />
							<?php echo JText::_('COMMENT_PRIVATE'); ?>
						</label>
<?php } // ACL can create private comments ?>
<?php if ($this->acl->check('create', 'comments') > 0 || $this->acl->check('create', 'private_comments')) { ?>
					</div>
					<div class="clear"></div>
<?php } // ACL can create comments (admin) or private comments ?>
					<textarea name="comment" id="comment" rows="13" cols="35"></textarea>
				</fieldset>

				<fieldset>
					<legend><?php echo JText::_('COMMENT_LEGEND_ATTACHMENTS'); ?></legend>
					<!-- <div class="grouping">
						<label for="upload">
							<?php echo JText::_('COMMENT_FILE'); ?>:
							<input type="file" name="upload" id="upload" />
						</label>

						<label for="field-description">
							<?php echo JText::_('COMMENT_FILE_DESCRIPTION'); ?>:
							<input type="text" name="description" id="field-description" value="" />
						</label>
					</div> -->

					<?php 
					$tmp = ('-' . time());
					$this->js('jquery.fileuploader.js', 'system');
					$jbase = rtrim(JURI::getInstance()->base(true), '/');
					?>
					<div id="ajax-uploader" data-action="<?php echo $jbase; ?>/index.php?option=com_support&amp;no_html=1&amp;controller=media&amp;task=upload&amp;ticket=<?php echo $this->row->id; ?>&amp;comment=<?php echo $tmp; ?>" data-list="<?php echo $jbase; ?>/index.php?option=com_support&amp;no_html=1&amp;controller=media&amp;task=list&amp;ticket=<?php echo $this->row->id; ?>&amp;comment=<?php echo $tmp; ?>">
						<noscript>
							<label for="upload">
								<?php echo JText::_('COMMENT_FILE'); ?>:
								<input type="file" name="upload" id="upload" />
							</label>
						</noscript>
					</div>
					<div class="field-wrap file-list" id="ajax-uploader-list">
					</div>
					<input type="hidden" name="tmp_dir" id="comment-tmp_dir" value="<?php echo $tmp; ?>" />
					<!-- <script src="<?php echo $jbase; ?>/media/system/js/jquery.fileuploader.js"></script> -->
				</fieldset>
<?php } //if ($this->acl->check('create', 'comments') || $this->acl->check('create', 'private_comments')) { ?>
<?php if ($this->acl->check('create', 'comments') > 0) { ?>
				<fieldset>
					<legend><?php echo JText::_('COMMENT_LEGEND_EMAIL'); ?>:</legend>
					<div class="grouping">
						<label for="email_submitter">
							<input class="option" type="checkbox" name="email_submitter" id="email_submitter" value="1" checked="checked" /> 
							<?php echo JText::_('COMMENT_SEND_EMAIL_SUBMITTER'); ?>
						</label>
						<label for="email_owner">
							<input class="option" type="checkbox" name="email_owner" id="email_owner" value="1" checked="checked" /> 
							<?php echo JText::_('COMMENT_SEND_EMAIL_OWNER'); ?>
						</label>
					</div>
					<div class="clear"></div>

					<label>
						<?php echo JText::_('COMMENT_SEND_EMAIL_CC'); ?>: <?php 
						$mc = $dispatcher->trigger('onGetMultiEntry', array(array('members', 'cc', 'acmembers', '', implode(', ', $cc))));
						if (count($mc) > 0) {
							echo '<span class="hint">'.JText::_('COMMENT_SEND_EMAIL_CC_INSTRUCTIONS_AUTOCOMPLETE').'</span>'.$mc[0];
						} else { ?> <span class="hint"><?php echo JText::_('COMMENT_SEND_EMAIL_CC_INSTRUCTIONS'); ?></span>
						<input type="text" name="cc" id="acmembers" value="<?php echo implode(', ', $cc); ?>" size="35" />
						<?php } ?>
					</label>
				</fieldset>
<?php } else { ?>
				<input type="hidden" name="email_submitter" id="email_submitter" value="1" />
				<input type="hidden" name="email_owner" id="email_owner" value="1" />
<?php } // ACL can create comments (admin) ?>
				<p class="submit">
					<input type="submit" value="<?php echo JText::_('SUBMIT_COMMENT'); ?>" />
				</p>
			</fieldset>
		</form>
	</div><!-- / .subject -->
</div><!-- / .section -->
<?php } // ACL can create comments ?>
<div class="clear"></div>
