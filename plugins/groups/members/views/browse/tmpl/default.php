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

$filters = array(
	'members'  => JText::_('PLG_GROUPS_MEMBERS'),
	'managers' => JText::_('PLG_GROUPS_MEMBERS_MANAGERS'),
	'pending'  => JText::_('PLG_GROUPS_MEMBERS_PENDING'),
	'invitees' => JText::_('PLG_GROUPS_MEMBERS_INVITEES')
);

if ($this->filter == '') 
{
	$this->filter = 'members';
}

$role_id   = '';
$role_name = '';

if ($this->role_filter) 
{
	foreach ($this->member_roles as $role) 
	{
		if ($role['id'] == $this->role_filter) 
		{
			$role_id   = $role['id'];
			$role_name = $role['name'];
			break;
		}
	}
}
$option = 'com_groups';
?>
<?php if ($this->membership_control == 1) { ?>
	<?php if ($this->authorized == 'manager' || $this->authorized == 'admin') { ?>
		<ul id="page_options">
			<li>
				<a class="icon-add add btn" href="<?php echo JRoute::_('index.php?option=' . $option . '&cn=' . $this->group->get('cn') . '&task=invite'); ?>">
					<?php echo JText::_('PLG_GROUPS_MEMBERS_INVITE_MEMBERS'); ?>
				</a>
				<?php if ($this->membership_control == 1 && $this->authorized == 'manager') : ?>
					<a class="icon-add add btn" href="<?php echo JRoute::_('index.php?option='.$option.'&cn='.$this->group->cn.'&active=members&action=addrole'); ?>">
						<?php echo JText::_('PLG_GROUPS_MEMBERS_ADD_ROLE'); ?>
					</a>
				<?php endif; ?>
			</li>
		</ul>
	<?php } ?>
<?php } ?>

<div class="section">
	<h3 class="section-header">
		<?php echo JText::_('PLG_GROUPS_MEMBERS'); ?>
	</h3>

	<div class="aside">
		<div class="container">
			<h4><?php echo JText::_('PLG_GROUPS_MEMBERS_MEMBER_ROLES'); ?></h4>
			<?php if (count($this->member_roles) > 0) { ?>
				<ul class="roles">
					<?php foreach ($this->member_roles as $role) { ?>
						<?php $cls = ($role['id'] == $this->role_filter) ? 'active' : ''; ?>
						<li>
							<?php if ($this->authorized == 'manager' && $this->membership_control == 1) : ?>
								<a class="remove-role" href="<?php echo JRoute::_('index.php?option='.$option.'&cn='.$this->group->cn.'&active=members&action=removerole&role='.$role['id']); ?>" title="<?php echo JText::_('PLG_GROUPS_MEMBERS_ROLE_REMOVE'); ?>">
									<?php echo JText::_('PLG_GROUPS_MEMBERS_ROLE_REMOVE'); ?>
								</a>
								<a class="edit-role" href="<?php echo JRoute::_('index.php?option='.$option.'&cn='.$this->group->cn.'&active=members&action=editrole&role='.$role['id']); ?>" title="<?php echo JText::_('PLG_GROUPS_MEMBERS_ROLE_EDIT'); ?>">
									<?php echo JText::_('PLG_GROUPS_MEMBERS_ROLE_EDIT'); ?>
								</a>
							<?php endif; ?>
							<a class="role <?php echo $cls; ?>" href="<?php echo JRoute::_('index.php?option='.$option.'&cn='.$this->group->cn.'&active=members&role_filter='.$role['id']); ?>">
								<?php echo $this->escape($role['name']); ?>
							</a>
						</li>
					<?php } ?>
				</ul>
			<?php } else { ?>
				<p class="starter"><?php echo JText::_('PLG_GROUPS_MEMBERS_NO_ROLES_FOUND'); ?></p>
			<?php }?>
		</div><!-- / .container -->
	</div>
	<div class="subject">
		<form action="<?php echo JRoute::_('index.php?option='.$option.'&cn='.$this->group->cn.'&active=members&filter='.$this->filter); ?>" method="post">
			<div class="container">
				<ul class="entries-menu filter-options">
					<?php foreach ($filters as $filter => $name) { ?>
						<?php $active = ($this->filter == $filter) ? ' active': ''; ?>
						<?php 
							if (($filter == 'pending' || $filter == 'invitees') && $this->membership_control == 0) {
								continue;
							}
						?>
						<?php if ($filter != 'pending' && $filter != 'invitees' || ($this->authorized == 'admin' || $this->authorized == 'manager')) { ?>
								<li>
									<a class="<?php echo $filter . $active; ?>" href="<?php echo JRoute::_('index.php?option='.$option.'&cn='.$this->group->cn.'&active=members&filter='.$filter); ?>"><?php echo $name; ?> 
										<?php 
											if ($filter == 'pending') {
												echo '('.count($this->group->get('applicants')).')';
											} elseif ($filter == 'invitees') {
												//get invite emails
												echo '('.(count($this->group->get('invitees')) + count($this->current_inviteemails)).')';
											} else {
												echo '('.count($this->group->get($filter)).')';
											}
										?>
									</a>
								</li>
							
						<?php } ?>
					<?php } ?>
				</ul>
				<div class="entries-search">
					<fieldset>
						<input type="text" name="q" value="<?php echo $this->escape($this->q); ?>" />
						<input type="submit" name="search_members" value="" />
					</fieldset>
				</div>

				<div class="clearfix"></div>

				<table class="groups entries">
					<caption>
						<?php 
							if ($this->role_filter) {
 								echo $role_name;
							} elseif ($this->q) {
								echo JText::_('PLG_GROUPS_MEMBERS_SEARCH') . ': ' . $this->escape($this->q);
							} else {
								echo ucfirst($this->filter);
							}
						?>
						<span>(<?php echo count($this->groupusers); ?>)</span>

						<?php if (($this->authorized == 'manager' || $this->authorized == 'admin') && count($this->groupusers) > 0) { ?>
							<span class="message-all">
								<?php if ($this->messages_acl != 'nobody') { ?>
								<?php
									if ($role_id) {
										$append = '&users[]=role&role_id='.$role_id;
										$title = JText::sprintf('PLG_GROUPS_MEMBERS_MESSAGE_ALL_ROLE', $role_name);
									} else {
										switch($this->filter)
										{
											case 'pending':
												$append = '&users[]=applicants';
												$title = JText::_('PLG_GROUPS_MEMBERS_MESSAGE_ALL_APPLICANTS');
												break;
											case 'invitees':
												$append = '&users[]=invitees';
												$title = JText::_('PLG_GROUPS_MEMBERS_MESSAGE_ALL_INVITEES');
												break;
											case 'managers':
												$append = '&users[]=managers';
												$title = JText::_('PLG_GROUPS_MEMBERS_MESSAGE_ALL_MANAGERS');
												break;
											case 'members':
											default:
												$append = '&users[]=all';
												$title = JText::_('PLG_GROUPS_MEMBERS_MESSAGE_ALL_MEMBERS');
												break;
										}
									}
								?>
								<a class="message tooltips" href="<?php echo JRoute::_('index.php?option='.$option.'&cn='.$this->group->cn.'&active=messages&action=new'.$append); ?>" title="<?php echo JText::_('PLG_GROUPS_MEMBERS_MESSAGE'); ?> :: <?php echo $title; ?>">
									<?php echo JText::_('PLG_GROUPS_MEMBERS_MESSAGE_ALL'); ?>
								</a>
								<?php } ?>
							</span><!-- / .message-all -->
						<?php } ?>
					</caption>
					<tbody>
<?php
						if ($this->groupusers) {
							//$emailthumb = '/components/com_groups/assets/img/emailthumb.png';

							// Some needed libraries
							$juser = JFactory::getUser();
							// Loop through the results
							$html = '';
							if ($this->limit == 0) 
							{
								$this->limit = 500;
							}
							for ($i=0, $n=$this->limit; $i < $n; $i++)
							{
								$cls = '';
								$inviteemail = false;

								if (($i+$this->start) >= count($this->groupusers)) 
								{
									break;
								}
								$guser = $this->groupusers[($i+$this->start)];

								$u = \Hubzero\User\Profile::getInstance($guser);	
								if (preg_match("/^[_\.\%0-9a-zA-Z-]+@([0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $guser)) 
								{
									$inviteemail = true;
									$pic = rtrim(JURI::getInstance()->base(true), '/') . '/components/com_groups/assets/img/emailthumb.png';
								}
								else if(!is_object($u))
								{
									continue;
								}
								else
								{
									$pic = \Hubzero\User\Profile\Helper::getMemberPhoto($u, 0);
								}

								switch ($this->filter)
								{
									case 'invitees':
										$status = JText::_('PLG_GROUPS_MEMBERS_STATUS_INVITEE');
									break;
									case 'pending':
										$status = JText::_('PLG_GROUPS_MEMBERS_STATUS_PENDING');
									break;
									case 'managers':
										$status = JText::_('PLG_GROUPS_MEMBERS_STATUS_MANAGER');
										$cls .= ' manager';
									break;
									case 'members':
									default:
										$status = 'Member';
										if (in_array($guser,$this->managers)) {
											$status = JText::_('PLG_GROUPS_MEMBERS_STATUS_MANAGER');
											$cls .= 'manager';
										}
									break;
								}

								if (is_object($u) && $juser->get('id') == $u->get('uidNumber')) {
									$cls .= ' me';
								}
?>
						<tr<?php echo ($cls) ? ' class="' . $cls . '"' : ''; ?>>
							<td class="photo">
								<img width="50" height="50" src="<?php echo $pic; ?>" alt="" />
							</td>
							<td>
								<?php if ($inviteemail) { ?>
									<span class="name">
										<a href="mailto:<?php echo $guser; ?>">
											<?php echo $guser; ?>
										</a>
									</span>
									<span class="status"><?php echo JText::_('PLG_GROUPS_MEMBERS_INVITE_SENT_TO_EMAIL'); ?></span><br />
								<?php } else { ?>
									<span class="name">
										<a href="<?php echo JRoute::_('index.php?option=com_members&id=' . $u->get('uidNumber')); ?>">
											<?php echo $this->escape(stripslashes($u->get('name'))); ?>
										</a>
									</span> 
									<span class="status"><?php echo $status; ?></span><br />

									<?php if ($u->get('organization')) { ?>
										<span class="organization"><?php echo $this->escape(stripslashes($u->get('organization'))); ?></span><br />
									<?php } ?>
								<?php } ?>
								<?php
								$html = '';
								if ($this->filter == 'members' || $this->filter == 'managers') {
									$html .= '<span class="roles">';
									$all_roles = '';
									$roles = $u->getGroupMemberRoles($u->get('uidNumber'),$this->group->gidNumber);

									if ($roles) {
										$html .= '<strong>' . JText::_('PLG_GROUPS_MEMBERS_MEMBER_ROLES') . ':</strong> ';
										foreach ($roles as $role) {
											$all_roles .= ', <span><a href="'.JRoute::_('index.php?option=com_groups&cn='.$this->group->cn.'&active=members&filter='.$this->filter.'&role_filter='.$role['id']).'">'.$role['name'].'</a>';

											if ($this->authorized == 'manager') {
												if ($this->membership_control == 1) {
													$all_roles .= '<span class="delete-role"><a href="'.JRoute::_('index.php?option=com_groups&cn='.$this->group->cn.'&active=members&action=deleterole&uid='.$u->get('uidNumber').'&role='.$role['id']).'">x</a></span></span>';
												}
											} else {
												$all_roles .= '</span>';
											}
										}

										$html .= '<span class="roles-list" id="roles-list-'.$u->get('uidNumber').'">'.substr($all_roles,2).'</span>';

										if ($this->authorized == 'manager') {
											if ($this->membership_control == 1) {
												$html .= ', <a class="assign-role" href="'.JRoute::_('index.php?option=com_groups&cn='.$this->group->cn.'&active=members&action=assignrole&uid='.$u->get('uidNumber')).'">' . JText::_('PLG_GROUPS_MEMBERS_ASSIGN_ROLE') . '</a>';
											}
										}

									}

									if ($this->membership_control == 1) {
										if (($this->authorized == 'manager' || $this->authorized == 'admin') && !$roles) {
											$html .= '<strong>' . JText::_('PLG_GROUPS_MEMBERS_MEMBER_ROLES') . ':</strong> ';
											$html .= '<span class="roles-list" id="roles-list-'.$u->get('uidNumber').'"></span>';
											$html .= ' <a class="assign-role" href="'.JRoute::_('index.php?option=com_groups&cn='.$this->group->cn.'&active=members&action=assignrole&uid='.$u->get('uidNumber')).'">' . JText::_('PLG_GROUPS_MEMBERS_ASSIGN_ROLE') . '</a>';
										}
									}
									$html .= '</span>';

								}

								if ($this->filter == 'pending') {
									$database = JFactory::getDBO();
									$row = new GroupsReason($database);
									$row->loadReason($u->get('uidNumber'), $this->group->gidNumber);

									if ($row) 
									{
										$html .= '<span class="reason" data-title="' . JText::_('PLG_GROUPS_MEMBERS_REASON_FOR_REQUEST') . '">';
										$html .= '<span class="reason-reason">'.stripslashes($row->reason).'</span>';
										$html .= '<span class="reason-date">'.JHTML::_('date', $row->date, 'F d, Y @ g:ia').'</span>';
										$html .= '</span>';
									}
								} else {
									//$html .= '<span class="activity">Activity: </span>';
								}

								$html .= '</td>'."\n";
								if ($this->authorized == 'manager' || $this->authorized == 'admin') {
									switch ($this->filter)
									{
										case 'invitees':
											if ($this->membership_control == 1) {
												if (!$inviteemail) {
													$html .= "\t\t\t\t".'<td class="remove-member"><a class="cancel tooltips" href="'.JRoute::_('index.php?option='.$option.'&cn='.$this->group->cn.'&active=members&action=cancel&users[]='.$guser.'&filter='.$this->filter).'" title="'.JText::sprintf('PLG_GROUPS_MEMBERS_CANCEL_MEMBER',$this->escape($u->get('name'))).'">'.JText::_('PLG_GROUPS_MEMBERS_CANCEL').'</a></td>'."\n";
												} else {
													$html .= "\t\t\t\t".'<td class="remove-member"><a class="cancel tooltips" href="'.JRoute::_('index.php?option='.$option.'&cn='.$this->group->cn.'&active=members&action=cancel&users[]='.$guser.'&filter='.$this->filter).'" title="'.JText::sprintf('PLG_GROUPS_MEMBERS_CANCEL_MEMBER',$this->escape($guser)).'">'.JText::_('PLG_GROUPS_MEMBERS_CANCEL').'</a></td>'."\n";
												}
											}
											$html .= "\t\t\t\t".'<td class="approve-member"> </td>'."\n";
										break;
										case 'pending':
											if ($this->membership_control == 1) {
												$html .= "\t\t\t\t".'<td class="decline-member"><a class="decline tooltips" href="'.JRoute::_('index.php?option='.$option.'&cn='.$this->group->cn.'&active=members&action=deny&users[]='.$guser.'&filter='.$this->filter).'" title="'.JText::sprintf('PLG_GROUPS_MEMBERS_DECLINE_MEMBER',$this->escape($u->get('name'))).'">'.JText::_('PLG_GROUPS_MEMBERS_DENY').'</a></td>'."\n";
												$html .= "\t\t\t\t".'<td class="approve-member"><a class="approve tooltips" href="'.JRoute::_('index.php?option='.$option.'&cn='.$this->group->cn.'&active=members&action=approve&users[]='.$guser.'&filter='.$this->filter).'" title="'.JText::sprintf('PLG_GROUPS_MEMBERS_APPROVE_MEMBER',$this->escape($u->get('name'))).'">'.JText::_('PLG_GROUPS_MEMBERS_APPROVE').'</a></td>'."\n";
											}
										break;
										case 'managers':
										case 'members':
										default:
											if ($this->membership_control == 1) {
												if (!in_array($guser,$this->managers) || (in_array($guser,$this->managers) && count($this->managers) > 1)) {
													$html .= "\t\t\t\t".'<td class="remove-member"><a class="remove tooltips" href="'.JRoute::_('index.php?option='.$option.'&cn='.$this->group->cn.'&active=members&action=remove&users[]='.$guser.'&filter='.$this->filter).'" title="'.JText::sprintf('PLG_GROUPS_MEMBERS_REMOVE_MEMBER',$this->escape($u->get('name'))).'">'.JText::_('PLG_GROUPS_MEMBERS_REMOVE').'</a></td>'."\n";
												} else {
													$html .= "\t\t\t\t".'<td class="remove-member"> </td>'."\n";
												}

												if (in_array($guser,$this->managers)) {
													//force admins to use backend to demote manager if only 1
													//if ($this->authorized == 'admin' || count($this->managers) > 1) {
													if (count($this->managers) > 1) {
														$html .= "\t\t\t\t".'<td class="demote-member"><a class="demote tooltips" href="'.JRoute::_('index.php?option='.$option.'&cn='.$this->group->cn.'&active=members&action=demote&users[]='.$guser.'&filter='.$this->filter.'&limit='.$this->limit.'&limitstart='.$this->start).'" title="'.JText::sprintf('PLG_GROUPS_MEMBERS_DEMOTE_MEMBER',$this->escape($u->get('name'))).'">'.JText::_('PLG_GROUPS_MEMBERS_DEMOTE').'</a></td>'."\n";
													} else {
														$html .= "\t\t\t\t".'<td class="demote-member"> </td>'."\n";
													}
												} else {
													$html .= "\t\t\t\t".'<td class="promote-member"><a class="promote tooltips" href="'.JRoute::_('index.php?option='.$option.'&cn='.$this->group->cn.'&active=members&action=promote&users[]='.$guser.'&filter='.$this->filter.'&limit='.$this->limit.'&limitstart='.$this->start).'" title="'.JText::sprintf('PLG_GROUPS_MEMBERS_PROMOTE_MEMBER',$this->escape($u->get('name'))).'">'.JText::_('PLG_GROUPS_MEMBERS_PROMOTE').'</a></td>'."\n";
												}
											}
										break;
									}
								} else {
									$html .= "\t\t\t\t".'<td class="remove-member"> </td>'."\n";
									$html .= "\t\t\t\t".'<td class="demote-member"> </td>'."\n";
								}
								if (is_object($u) && $juser->get('id') == $u->get('uidNumber') || $this->filter == 'invitees' || $this->filter == 'pending') {
									$html .= "\t\t\t\t".'<td class="message-member"> </td>'."\n";
								} else {
									if (!$inviteemail && ($this->authorized == 'manager' || $this->authorized == 'admin') && $this->messages_acl != 'nobody') {
										$html .= "\t\t\t\t".'<td class="message-member"><a class="message tooltips" href="'.JRoute::_('index.php?option='.$option.'&cn='.$this->group->cn.'&active=messages&action=new&users[]='.$guser).'" title="Message :: Send a message to '.$this->escape($u->get('name')).'">'.JText::_('PLG_GROUPS_MEMBERS_MESSAGE').'</a></td>'."\n";
									} else {
										$html .= "\t\t\t\t".'<td class="message-member"></td>'."\n";
									}
								}
								echo $html;
?>
						</tr>
<?php
							}
						} else { 
?>
						<tr>
							<td><?php echo JText::_('PLG_GROUPS_MEMBERS_NO_RESULTS'); ?></td>
						</tr>
<?php 
						} 
?>
					</tbody>
				</table>
			<?php 
				$this->pageNav->setAdditionalUrlParam('cn', $this->group->get('cn'));
				$this->pageNav->setAdditionalUrlParam('active', 'members');
				$this->pageNav->setAdditionalUrlParam('filter', $this->filter);
				$this->pageNav->setAdditionalUrlParam('q', $this->q);

				echo $this->pageNav->getListFooter();
			?>
				<div class="clearfix"></div>
			</div><!-- / .container -->
		</div>
		<div class="clear"></div>
	
		
		<input type="hidden" name="cn" value="<?php echo $this->group->cn; ?>" />
		<input type="hidden" name="active" value="members" />
		<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
		<input type="hidden" name="filter" value="<?php echo $this->filter; ?>" />
	</form>
</div><!--/ #group_members -->
