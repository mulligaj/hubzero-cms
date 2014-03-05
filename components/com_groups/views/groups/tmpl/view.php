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
defined('_JEXEC') or die( 'Restricted access' );

//get objects
$config 	= JFactory::getConfig();
$database 	= JFactory::getDBO();

//is membership control managed on group?
$membership_control = $this->gparams->get('membership_control', 1);

//get no_html request var
$no_html = JRequest::getInt( 'no_html', 0 );
?>

<?php if (!$no_html) : ?>
	
	<?php 
		foreach ($this->beforeGroupContent as $content)
		{
			echo $content;
		}
	?>
	
	<div class="innerwrap">
		<div id="page_container">
			<div id="page_sidebar">
				<?php
					//default logo
					$default_logo = DS.'components'.DS.$this->option.DS.'assets'.DS.'img'.DS.'group_default_logo.png';

					//logo link - links to group overview page
					$link = JRoute::_('index.php?option='.$this->option.'&cn='.$this->group->get('cn'));

					//path to group uploaded logo
					$path = '/site/groups/'.$this->group->get('gidNumber').DS.$this->group->get('logo');

					//if logo exists and file is uploaded use that logo instead of default
					$src = ($this->group->get('logo') != '' && is_file(JPATH_ROOT.$path)) ? $path : $default_logo;
					
					//check to make sure were a member to show logo for hidden group
					$members_and_invitees = array_merge($this->group->get('members'), $this->group->get('invitees'));
					if( $this->group->get('discoverability') == 1 && !in_array($this->juser->get("id"), $members_and_invitees) )
					{
						$src = $default_logo;
					}
				?>
				<div id="page_identity">
					<a href="<?php echo $link; ?>" title="<?php echo $this->group->get('description'); ?> Home">
						<img src="<?php echo $src; ?>" alt="<?php echo $this->group->get('description'); ?> Logo" />
					</a>
				</div><!-- /#page_identity -->
				
				<ul id="group_options">
					<?php if(in_array($this->juser->get("id"), $this->group->get("invitees"))) : ?>
						<?php if($membership_control == 1) : ?>
							<li>
								<a class="group-invited" href="<?php echo JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&task=accept'); ?>">
									Accept Group Invitation
								</a>
							</li>
						<?php endif; ?>
					<?php elseif($this->group->get('join_policy') == 3 && !in_array($this->juser->get("id"), $this->group->get("members"))) : ?>
						<li>
							<span class="group-closed">Group Closed</span>
						</li>
					<?php elseif($this->group->get('join_policy') == 2 && !in_array($this->juser->get("id"), $this->group->get("members"))) : ?>
						<li>
							<span class="group-inviteonly">Group is Invite Only</span>
						</li>
					<?php elseif($this->group->get('join_policy') == 0 && !in_array($this->juser->get("id"), $this->group->get("members"))) : ?>
						<?php if($membership_control == 1) : ?> 
							<li>
								<a class="group-join" href="<?php echo JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&task=join'); ?>">Join Group</a>
							</li>
						<?php endif; ?> 
					<?php elseif($this->group->get('join_policy') == 1 && !in_array($this->juser->get("id"), $this->group->get("members"))) : ?>
						<?php if($membership_control == 1) : ?>
							<?php if(in_array($this->juser->get("id"), $this->group->get("applicants"))) : ?>
								<li><span class="group-pending">Request Waiting Approval</span></li>
							<?php else : ?>
								<li>
									<a class="group-request" href="<?php echo JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&task=join'); ?>">Request Group Membership</a>
								</li>
							<?php endif; ?>
						<?php endif; ?>
					<?php else : ?>
						<?php $isManager = (in_array($this->juser->get("id"), $this->group->get("managers"))) ? true : false; ?>
						<?php $canCancel = (($isManager && count($this->group->get("managers")) > 1) || (!$isManager && in_array($this->juser->get("id"), $this->group->get("members")))) ? true : false; ?>
						<li class="no-float">
							<a href="javascript:void(0);" class="dropdown group-<?php echo ($isManager) ? "manager" : "member" ?>">
								Group <?php echo ($isManager) ? "Manager" : "Member" ?>
								<span class="caret"></span>
							</a>
							<ul class="dropdown-menu pull-right">
								<?php if($isManager) : ?>
									<?php if($membership_control == 1) : ?> 
										<li>
											<a class="group-invite" href="<?php echo JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&task=invite'); ?>">
												Invite Members
											</a>
										</li>
									<?php endif; ?>
									<li>
										<a class="group-edit" href="<?php echo JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&task=edit'); ?>">
											Edit Group Settings
										</a>
									</li>
									<li>
										<a class="group-customize" href="<?php echo JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&task=customize'); ?>">
											Customize Group
										</a>
									</li>
									<li>
										<a class="group-pages" href="<?php echo JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&task=pages'); ?>">
											Manage Group Pages
										</a>
									</li>
									<?php if($membership_control == 1) : ?> 
										<li class="divider"></li>
									<?php endif; ?>
								<?php endif; ?>
								<?php if($canCancel) : ?>
									<?php if($membership_control == 1) : ?> 
										<li>
											<a class="group-cancel" href="<?php echo JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&task=cancel'); ?>">
												Cancel Group Membership
											</a>
										</li>
										<?php if($isManager): ?>
											<li class="divider"></li>
										<?php endif; ?>
									<?php endif; ?>
								<?php endif; ?>
								<?php if($isManager) : ?>
									<?php if($membership_control == 1) : ?> 
										<li>
											<a class="group-delete" href="<?php echo JRoute::_('index.php?option=com_groups&cn='.$this->group->get('cn').'&task=delete'); ?>">
												Delete Group
											</a>
										</li>
									<?php endif; ?>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
				</ul><!-- /#page_options -->
				
				<ul id="page_menu">
					<?php
						echo Hubzero_Group_Helper::displayGroupMenu($this->group, $this->sections, $this->hub_group_plugins, $this->group_plugin_access, $this->pages, $this->tab);
					?>
				</ul><!-- /#page_menu -->
				
				<div id="page_info">
					<?php 
						// Determine the join policy
						switch ($this->group->get('join_policy'))
						{
							case 3: $policy = JText::_('Closed');      break;
							case 2: $policy = JText::_('Invite Only'); break;
							case 1: $policy = JText::_('Restricted');  break;
							case 0:
							default: $policy = JText::_('Open'); break;
						}

						// Determine the discoverability
						switch ($this->group->get('discoverability'))
						{
							case 1: $discoverability = JText::_('Hidden'); break;
							case 0:
							default: $discoverability = JText::_('Visible'); break;
						}
						
						// use created date
						$created = JHTML::_('date', $this->group->get('created'), JText::_('DATE_FORMAT_HZ1'));
					?>
					<div class="group-info">
						<ul>
							<li class="info-discoverability">
								<span class="label">Discoverability</span>
								<span class="value"><?php echo $discoverability; ?></span>
							</li>
							<li class="info-join-policy">
								<span class="label">Join Policy</span>
								<span class="value"><?php echo $policy; ?></span>
							</li>
							<li class="info-created">
								<span class="label">Created</span>
								<span class="value"><?php echo $created; ?></span>
							</li>
						</ul>
					</div>
				</div>
			</div><!-- /#page_sidebar --> 
			
			<div id="page_main">
				<div id="page_header">
					<h2><a href="/groups/<?php echo $this->group->get("cn"); ?>"><?php echo $this->group->get('description'); ?></a></h2>
					<span class="divider">►</span>
					<h3>
						<?php
							foreach($this->hub_group_plugins as $cat)
							{
								if($this->tab == $cat['name'])
								{
									echo $cat['title'];
								}
							}
						?>
					</h3>
					
					<?php
						if($this->tab == 'overview') : 
							$gt = new GroupsTags( $database );
							echo $gt->get_tag_cloud(0,0,$this->group->get('gidNumber'));
						endif;
					?>
				</div><!-- /#page_header -->
				<div id="page_notifications">
					<?php
						foreach($this->notifications as $notification) {
							echo "<p class=\"{$notification['type']}\">{$notification['message']}</p>";
						}
					?>
				</div><!-- /#page_notifications -->
				
				<div id="page_content" class="group_<?php echo $this->tab; ?>">
					<?php endif; ?>
					
					<?php 
						echo Hubzero_Group_Helper::displayGroupContent($this->sections, $this->hub_group_plugins, $this->tab); 
					?>
					
					<?php if (!$no_html) : ?>
				</div><!-- /#page_content -->
			</div><!-- /#page_main -->
			<br class="clear" />
		</div><!-- /#page_container -->
	</div><!-- /.innerwrap -->
<?php endif; ?>

