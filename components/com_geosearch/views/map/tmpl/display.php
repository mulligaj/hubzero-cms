<?php
/**
 * @package     hubzero-cms
 * @copyright   Copyright 2005-2012 Purdue University. All rights reserved.
 * @license     http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 * @author	    Brandon Beatty
 *
 * Copyright 2005-2012 Purdue University. All rights reserved.
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
defined('_JEXEC') or die('Restricted access');

// add scripts
$doc = JFactory::getDocument();
$doc->addScript("https://maps.googleapis.com/maps/api/js?&sensor=false");
$doc->addScript("/components/com_geosearch/assets/js/oms.min.js");

// build internal script
$js = "";
// set map center
if (isset($this->latlng) && is_array($this->latlng)) 
{
	$js .= "var latlng = new google.maps.LatLng({$this->latlng[0]}, {$this->latlng[1]});";
} 
else 
{
	//echo "var latlng = new google.maps.LatLng(40.4293930, -86.8916510)"; WL
	//echo "var latlng = new google.maps.LatLng(37.0902400, -95.7128910);"; US
	$js .= "var latlng = new google.maps.LatLng(6.4280732,-8.5502323);"; // DR Congo
}

// TODO: change zoom?
// display specific members
$js .= "var uids = [];";
if (isset($this->uids)) 
{
	if ($this->uids == 0) 
	{
		$js .= "uids.push(0);";
	} 
	else 
	{
		foreach ($this->uids as $uid) 
		{
			if (property_exists($uid,'taggerid')) 
			{
				$js .= "uids.push($uid->taggerid);";	
			} 
			elseif (property_exists($uid,'uidNumber')) 
			{
				$js .= "uids.push($uid->uidNumber);";
			} 
			else 
			{
				$js .= "uids.push($uid);";
			}
		}
	}
}

// display specific jobs
$js .= "var jids = [];";
if (isset($this->jids)) 
{
	if ($this->jids == 0) 
	{
		$js .= "jids.push(0);";
	} 
	else 
	{
		foreach ($this->jids as $jid) 
		{
			$js .= "jids.push($jid->scope_id);";
		}
	}
}

// display specific events
$js .= "var eids = [];";
if (isset($this->eids)) 
{
	if ($this->eids == 0) 
	{
		$js .= "eids.push(0);";
	} 
	else 
	{
		foreach ($this->eids as $eid) 
		{
			if (property_exists($eid,'objectid')) 
			{
				$js .= "eids.push($eid->objectid);";
			}
			elseif (property_exists($eid,'scope_id'))  
			{
				$js .= "eids.push($eid->scope_id);";
			} 
			else 
			{
				$js .= "eids.push($eid);";
			}
		}
	}
}

// display specific orgs
$js .= "var oids = [];";
if (isset($this->oids)) 
{
	if ($this->oids == 0) 
	{
		$js .= "oids.push(0);";
	}
	else 
	{
		foreach ($this->oids as $oid) 
		{
			if (property_exists($oid,'objectid')) 
			{
				$js .= "oids.push($oid->objectid);";
			} 
			elseif (property_exists($oid,'scope_id')) 
			{
				$js .= "oids.push($oid->scope_id);";
			} 
			else 
			{
				$js .= "eids.push($oid);";
			}
		}
	}
}

$doc->addScriptDeclaration($js);

// get bio parser
//$p = Hubzero_Wiki_Parser::getInstance();
?>
	
<div id="content-header" class="full">
	<h2><?php echo JText::_('COM_GEOSEARCH_TITLE'); ?></h2>
</div>
<div class="main section">
<form action="<?php echo JRoute::_('index.php?option=' . $this->option); ?>" method="post" id="frm_search">
<?php if ($this->getError()) { ?>
	<p class="error"><?php echo implode("\n", $this->getErrors()); ?></p>
<?php } ?>
	<div class="aside geosearch">
		<div class="container">
			<h3><?php echo JText::_('COM_GEOSEARCH_GEOHEAD'); ?></h3>
            
            <fieldset>
            	<legend><?php echo JText::_('COM_GEOSEARCH_LIM_RES'); ?></legend>
                <div><div class="key"><img src="/components/com_geosearch/assets/img/icn_member2.png" /></div><input type="checkbox" name="resource[]" class="resck" value="members" <?php if (in_array("members",$this->resources)) { echo 'checked="checked"'; }?> /> Members </div>
                <div><div class="key"><img src="/components/com_geosearch/assets/img/icn_job2.png" /></div><input type="checkbox" name="resource[]" class="resck" value="jobs" <?php if (in_array("jobs",$this->resources)) { echo 'checked="checked"'; }?> /> Jobs</div>
                <div><div class="key"><img src="/components/com_geosearch/assets/img/icn_event2.png" /></div><input type="checkbox" name="resource[]" class="resck" value="events" <?php if (in_array("events",$this->resources)) { echo 'checked="checked"'; }?> /> Events</div>
                <div><div class="key"><img src="/components/com_geosearch/assets/img/icn_org2.png" /></div><input type="checkbox" name="resource[]" class="resck" value="orgs" <?php if (in_array("orgs",$this->resources)) { echo 'checked="checked"'; }?> /> Organizations </div>
                <div class="clear-right"></div>
            </fieldset>
            
            <fieldset>
            	<legend><?php echo JText::_('COM_GEOSEARCH_LIM_TAGS'); ?></legend>
                <?php
                	if (isset($this->stags)) 
                	{ 
						$stags = implode(",",$this->stags); 
					} 
					else 
					{ 
						$stags = ""; 
					}

					// load tags plugin
					JPluginHelper::importPlugin( 'hubzero' );  
					$dispatcher = JDispatcher::getInstance();  
					$tf = $dispatcher->trigger( 'onGetMultiEntry', array(array('tags','tags','actags','',$stags)) );
					if (count($tf) > 0) 
					{  
						echo $tf[0];  
					}
					else 
					{  
						echo '<input type="text" name="tags" value="'. $stags .'" />';  
					}  
				?>				
            </fieldset>
            
            <fieldset>
            	<legend><?php echo JText::_('COM_GEOSEARCH_LIM_LOC'); ?></legend>
                Within:<br />
                <input type="text" name="distance" id="idist" value="<?php echo $this->distance; ?>" />
                <select name="dist_units">
                	<option value="mi">Miles</option>
                    <option value="km" <?php if ($this->unit == 'km') echo 'selected="selected"'; ?>>Kilometers</option>
                </select><br />
                of:<br />
                <input type="text" name="location" id="iloc" value="<?php if ($this->location != "") echo $this->location; ?>" <?php if ($this->location == "") echo "placeholder=\"place, address, or zip\""; ?>/>
            </fieldset>
            
            <input type="submit" value="Search" /> <input type="button" value="Clear" id="clears"/> 
            
            <div class="clear"></div>
		</div><!-- / .container -->
	</div><!-- / .aside -->
	<div class="subject">
		<div id="map_canvas"></div>
		<br />
			<div class="container">
				<div class="container-block">
					<h3><?php echo JText::_('COM_GEOSEARCH_LIST'); ?></h3>
							<div class="list">
							<?php 
								if (isset($this->members))  {
									foreach ($this->members as $row) {
										if ($row->surname != "") {
											$name = $row->surname.", ".$row->givenName;
										} else {
											$name = $row->name;
										}
							?>
								<div class="list-item">
                                    <div class="list-content">
                                		<a href="<?php echo JRoute::_('index.php?option=com_members&id=' . $row->uidNumber); ?>" title="<?php echo $name; ?>"><?php echo $name; ?></a><br />
                                         <?php
                                         	$job = $this->ROT->loadType($row->orgtype);
                                         	if ($job) { ?> 
                                            <span class="list-detail"><?php echo " ".$this->ROT->title; ?></span><br />
										 <?php } ?>
                                         
                                           <div class="member-img">
                                                <a href="<?php echo JRoute::_('index.php?option=com_members&id=' . $row->uidNumber); ?>" title="<?php echo $name; ?>">
                                                    <img src="<?php echo \Hubzero\User\Profile\Helper::getMemberPhoto($row->uidNumber, 0); ?>" />
                                                </a>
                                            </div>
                                            <?php 
												$user = \Hubzero\User\Profile::getInstance($row->uidNumber);
												if ($user->get('bio'))
												{
													$bio = $user->getBio('parsed');
													echo \Hubzero\Utility\String::truncate($bio, 220);
												}
                                                $tags = $this->MT->get_tag_cloud(0,0,$row->uidNumber);
                                                echo ($tags) ? "<br /><span class=\"list-detail\">Interests:</span>".$tags : ""; //:  JText::_('COM_HMAP_NO_TAGS');
                                            ?>
									</div>
                                    <div class="list-label"><?php echo JText::_('COM_GEOSEARCH_LABEL_MEMBER'); ?></div>
                                    <div class="clear"></div>
                                 </div>
							<?php 
								} 
							} else { ?>
							<p><?php if (in_array("members",$this->resources)) { echo JText::_('COM_GEOSEARCH_NO_MEMBERS'); } ?></p>
							
                            <?php 
							}
							  if (isset($this->jobs) && count($this->jobs) > 0)  {
								  foreach ($this->jobs as $row) { 
							?>
								<div class="list-item">
                                    <div class="list-content">
                                		<a href="<?php echo JRoute::_('index.php?option=com_jobs&task=job&code=' . $row->code); ?>" title="<?php echo $row->title; ?>"><?php echo $row->title; ?></a><br />
                                        <span class="list-detail"><?php echo $row->companyName; ?></span>
										<div class="job-desc">
                                        	<?php 
                                        		if ($row->description) 
                                        		{
                                        			$jobsModelJob = new JobsModelJob($row->id);
                                        			$desc = $jobsModelJob->content('parsed');
                                        			$desc = strip_tags($desc);
                                        			echo \Hubzero\Utility\String::truncate($desc, 290);
												} 
											?>
                                        </div>
									</div>
                                    <div class="list-label"><?php echo JText::_('COM_GEOSEARCH_LABEL_JOB'); ?></div>
                                    <div class="clear"></div>
                                 </div>
							<?php 
									}
								} else { ?>
                            <p><?php if (in_array("jobs",$this->resources)) { echo JText::_('COM_GEOSEARCH_NO_JOBS'); } ?></p>
                            
                             <?php 
								}
							  if (isset($this->events) && count($this->events) > 0)  
							  {
								  foreach ($this->events as $row) 
								  { 
									  // object or array?
									  if (is_object($row))
									  {
										  $id = $row->id;	
										  $title = $row->title;
										  $publish_up = $row->publish_up;
										  $publish_down = $row->publish_down;
										  $content = $row->content;
									  } 
									  else 
									  {
										  $id = $row[0];	
										  $title = $row[1];
										  $publish_up = $row[2];
										  $publish_down = $row[3];
										  $content = $row[4];
									  }
							?>
								<div class="list-item">
                                    <div class="list-content">
                                		<a href="<?php echo JRoute::_('index.php?option=com_events&task=details&id=' . $id); ?>" title="<?php echo stripslashes($title); ?>"><?php echo stripslashes($title); ?></a><br />
                                        <span class="list-detail">
                                        <?php
											// date and time
											echo JHTML::_('date', $publish_up, 'l, F j, Y g:i a');
											if ($publish_down != "")
											{ 
												echo " to "; 
												echo JHTML::_('date', $publish_down, 'l, F j, Y g:i a');
											}
										?>
                                        </span>
										<div class="job-desc">
                                        	<?php if ($content) { 
                                        		echo \Hubzero\Utility\String::truncate($content, 290);
											} ?>
                                        </div>
										<?php
                                            $tags = $this->ET->get_tag_cloud(0,0,$id);
                                            echo ($tags) ? $tags :  ""; /*JText::_('COM_HMAP_NO_TAGS');*/
                                        ?>
									</div>
                                    <div class="list-label"><?php echo JText::_('COM_GEOSEARCH_LABEL_EVENT'); ?></div>
                                    <div class="clear"></div>
                                 </div>
							<?php }
								} else { ?>
							<p><?php if (in_array("events",$this->resources)) { echo JText::_('COM_GEOSEARCH_NO_EVENTS'); } ?></p>
							<?php }
								if (isset($this->orgs) && count($this->orgs) > 0)  {
									foreach ($this->orgs as $row) { 
									  // object or array?
									  if (is_object($row)) {
										  $id = $row->id;	
										  $title = $row->title;
										  $text = $row->fulltxt;
									  } else {
										  $id = $row[0];	
										  $title = $row[1];
										  $text = $row[2];
									  }
							?>
								<div class="list-item">
                                    <div class="list-content">								
                                		<a href="<?php echo JRoute::_('index.php?option=com_resources&id=' . $id); ?>" title="<?php echo stripslashes($title); ?>"><?php echo stripslashes($title); ?></a><br />
                                        <span class="list-detail">

                                        </span>
                                        <?php
											$text = preg_replace("#<nb:(.*?)>(.*?)</nb:(.*?)>#s", '', $text);
											$bio = trim($text);
										?>
										<div class="job-desc">
                                        	<?php if ($text) { 
												echo \Hubzero\Utility\String::truncate(stripslashes($bio), 290); 
											} ?>
                                        </div>
                                        
										<?php
											$tags = $this->RT->getTags($id);
                                            $taglist = $this->RT->buildTopCloud($tags);
                                            echo ($taglist) ? $taglist :  ""; 
                                        ?>
									</div>
                                    <div class="list-label"><?php echo JText::_('COM_GEOSEARCH_LABEL_ORG'); ?></div>
                                    <div class="clear"></div>
                                 </div>
							<?php }
								} else { ?>
							<p><?php if (in_array("orgs",$this->resources)) { echo JText::_('COM_GEOSEARCH_NO_ORGS'); } ?></p>
							<?php } ?>
                            <?php echo $this->pagenavhtml; ?>
                            <br class="clear" />
                        </div>
				</div><!-- / .container-block -->
			</div><!-- / .container -->
	</div><!-- / .subject -->
	<div class="clear"></div>
    </form>
</div><!-- / .main section -->
