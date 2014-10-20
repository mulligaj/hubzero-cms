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
defined('_JEXEC') or die('Restricted access');

/**
 * Display HABRI Members on Google Map
 */
class GeosearchControllerMap extends \Hubzero\Component\SiteController 
{	
	/**
	 * display 
	 */
	public function displayTask() 
	{
		$filters          = array();
		$filters['limit'] = 1000; //JRequest::getInt('limit', 1000, 'request');
		$filters['start'] = 0; //JRequest::getInt('limitstart', 0, 'request');
		$resources        = JRequest::getVar('resource', '', 'request');
		$tags             = trim(JRequest::getString('tags', '', 'request'));
		$distance         = JRequest::getInt('distance', '', 'request');
		$location         = JRequest::getVar('location', '', 'request');
		$unit             = JRequest::getVar('dist_units', '', 'request');

		// get resources, set to all if none selected
		if (empty($resources))
		{ 
			$resources = array('members','jobs','events','orgs'); 
		}

		// page count
		$total = 0;
		
		// Tag search
		if ($tags != '')
		{
			$tags = explode(",",$tags);
			$HT = new GeosearchTags($this->database);
			$uids = $HT->searchTagsMems($tags, $filters); 
			$eids = $HT->searchTagsEvents($tags, $filters);
			$oids = $HT->searchTagsOrgs($tags, $filters);
			$this->view->uids = $uids;
			$this->view->eids = $eids;
			$this->view->jids = 0;
			$this->view->oids = $oids;
			// keep tags
			$this->view->stags = $tags;
		}
		else
		{
			//$this->view->uids = array();
		}

		// Location search
		if ($distance  != "") 
		{
			if ($latlng = $this->doGeocode($location)) 
			{
				$GM = new GeosearchMarkers($this->database);
				$uids = $GM->getAddressLimit($distance,$latlng,'member',$unit, $filters);
				$eids = $GM->getAddressLimit($distance,$latlng,'event',$unit, $filters);
				$jids = $GM->getAddressLimit($distance,$latlng,'job',$unit, $filters);
				$oids = $GM->getAddressLimit($distance,$latlng,'org',$unit, $filters);
				
				// set member IDs
				if (isset($this->view->uids)) 
				{
					// combine tags and loc results
					$muids = array_merge($this->view->uids, $uids);
					$nuids = array();
					// put objects into array
					foreach ($muids as $nuid) 
					{
						if (property_exists($nuid,'taggerid')) 
						{
							$nuids[] = $nuid->taggerid;
						}
						else 
						{
							$nuids[] = $nuid->uidNumber;
						}
					}
					// pull out matches 
					$matches = array_unique(array_diff_assoc($nuids,array_unique($nuids)));
					$this->view->uids = $matches;
				}
				else 
				{
					$this->view->uids = $uids;
				}

				// set event IDs
				if (isset($this->view->eids))
				{
					$meids = array_merge($this->view->eids, $eids);
					$neids = array();
					foreach ($meids as $neid) 
					{
						if (property_exists($neid,'objectid')) 
						{
							$neids[] = $neid->objectid;
						} 
						else 
						{
							$neids[] = $neid->scope_id;
						}
					}
					$matches = array_unique(array_diff_assoc($neids,array_unique($neids)));
					$this->view->eids = $matches;
				} 
				else 
				{
					$this->view->eids = $eids;
				}

				// set job IDs
				$this->view->jids = $jids;

				// set org IDs
				if (isset($this->view->oids)) 
				{
					$moids = array_merge($this->view->oids, $oids);
					$noids = array();
					foreach ($moids as $noid) 
					{
						if (property_exists($noid,'objectid')) 
						{
							$noids[] = $noid->objectid;
						} 
						else 
						{
							$noids[] = $noid->scope_id;
						}
					}
					$matches = array_unique(array_diff_assoc($noids,array_unique($noids)));
					$this->view->oids = $matches;
				} 
				else 
				{
					$this->view->oids = $oids;
				}

				// set center lat/lng
				$this->view->latlng = $latlng;
			}
		}

		// keep search inputs
		$this->view->distance = $distance;
		$this->view->location = $location;
		$this->view->unit = $unit;

		// get xprofiles
		if (in_array("members",$resources)) 
		{
			$MP = new MembersProfile($this->database);
			$filters['sortby'] = 'fullname ASC';
			$filters['show'] = '';
			if (isset($this->view->uids)) 
			{
				$i = 0;
				$u = array();
				foreach ($this->view->uids as $uid) 
				{
					if (property_exists($uid,'taggerid') && $uid->taggerid == 0) 
					{
						$i++;
					} 
					else 
					{
						if (property_exists($uid,'taggerid')) 
						{
							$u[] = $uid->taggerid;
						} 
						elseif (property_exists($uid,'uidNumber')) 
						{
							$u[] = $uid->uidNumber;
						} 
						else 
						{
							$u[] = $uid;
						}
						$i++;
					}
				}

				// no user id can equal -999
				// ensures no results
				if (empty($u))
				{
					$u[] = '-999';
				}
				
				$search = "public = 1 AND (uidNumber IN (" . implode(',', $u) . "))";
				$all = 0;
			} 
			else 
			{
				$search = "surname != '' AND public = 1";
				$all = 1;
			}

			$this->view->members = $MP->selectWhere('*',"$search AND email NOT LIKE 'DISABLED%' ORDER BY surname LIMIT {$filters['start']}, {$filters['limit']}");
			
			if ($all) 
			{
				// add all members to total
				$total += $MP->getCount($filters);
			} 
			else 
			{
				$total = count($this->view->members);
			}

			/**
			 * Filter events by search term
			 */
			$search = JRequest::getVar('search', '');
			if ($search != '')
			{
				// reset map markers
				$this->view->uids = array();
				foreach ($this->view->members as $k => $member)
				{
					if (stristr($member->name, $search) === false)
					{
						unset($this->view->members[$k]);
					}
					else
					{
						// add a map marker for each org that matches search
						$u                  = new stdClass;
						$u->scope           = 'member';
						$u->uidNumber       = $member->uidNumber;
						$this->view->uids[] = $u;
					}
				}
			}
			$this->view->members = array_values(array_filter($this->view->members));

			// clear ids var if no results
			if (count($this->view->members) == 0) 
			{
				$this->view->uids = array();
			}

			// get MemberTags Object
			$this->view->MT = new MembersTags($this->database);

			// get RegisterOrganizationType Object
			$this->view->ROT = new MembersTableOrganizationType($this->database);
		}
		
		// get jobs
		if (in_array("jobs",$resources) && $tags == "") 
		{
			$J = new Job($this->database);
			$filters['search'] = '';
			$filters['sortby'] = '';
			$filters['active'] = 1;
			
			if (isset($jids) && $jids != "") 
			{
				$jobs = array();
				foreach($jids as $jid) 
				{
					$jobs[] = $J->get_opening($jid->scope_id);
				}
				$this->view->jobs = $jobs;
			} 
			else 
			{
				$this->view->jobs = $J->get_openings($filters, 0, 0);
			}

			/**
			 * Filter events by search term
			 */
			$search = JRequest::getVar('search', '');
			if ($search != '')
			{
				// reset map markers
				$this->view->jids = array();
				foreach ($this->view->jobs as $k => $job)
				{
					if (stristr($job->title, $search) === false
						&& stristr($job->description, $search) === false)
					{
						unset($this->view->jobs[$k]);
					}
					else
					{
						// add a map marker for each org that matches search
						$j                  = new stdClass;
						$j->scope           = 'job';
						$j->scope_id        = $job->id;
						$this->view->jids[] = $j;
					}
				}
			}
			$this->view->jobs = array_values(array_filter($this->view->jobs));

			// clear ids var if no results
			if (count($this->view->jobs) == 0) 
			{
				$this->view->jids = 0;
			}

			$total += count($this->view->jobs);
		}
		
		// get events
		if (in_array("events",$resources)) 
		{
			$EE = new EventsEvent($this->database);
			$filters['year'] = date("Y");
			$filters['category'] = '';
			if (isset($this->view->eids) && $this->view->eids != "") 
			{
				$events = array();
				foreach ($this->view->eids as $eid) 
				{
					if (property_exists($eid,'objectid') && $eid->objectid != 0) 
					{
						$EE->load($eid->objectid);
					} 
					elseif (property_exists($eid,'scope_id')) 
					{
						$EE->load($eid->scope_id);
					}
					else 
					{
						$EE->load($eid);
					}

					$e                    = new StdClass;
					$e->id                = $EE->id;
					$e->title             = $EE->title;
					$e->publish_up        = $EE->publish_up;
					$e->publish_down      = $EE->publish_down;
					$e->content           = $EE->content;
					$this->view->events[] = $e;
				}
			} 
			else 
			{
				$this->view->events = $EE->getEvents($period='year', $filters);
			}

			/**
			 * Filter events by search term
			 */
			$search = JRequest::getVar('search', '');
			if ($search != '')
			{
				// reset map markers
				$this->view->eids = array();
				foreach ($this->view->events as $k => $event)
				{
					if (stristr($event->title, $search) === false
						&& stristr($event->content, $search) === false)
					{
						unset($this->view->events[$k]);
					}
					else
					{
						// add a map marker for each event that matches search
						$e                  = new stdClass;
						$e->scope           = 'event';
						$e->scope_id        = $event->id;
						$this->view->eids[] = $e;
					}
				}
			}
			
			$now = JFactory::getDate('now');
			foreach ($this->view->events as $k => $event)
			{
				$down = JFactory::getDate($event->publish_down);
				if ($now > $down)
				{
					unset($this->view->events[$k]);
				}
			}

			$this->view->events = array_values(array_filter($this->view->events));
			
			// clear ids var if no results
			if (count($this->view->events) == 0) 
			{
				$this->view->eids = 0;
			}

			$total += count($this->view->events);

			// get EventTags Object
			$this->view->ET = new EventsTags($this->database);
		}
		
		// get organizations
		if (in_array("orgs",$resources)) 
		{
			$RR = new ResourcesResource($this->database);
			$filters['type'] = 90;
			$filters['sortby'] = 'title';
			if (isset($this->view->oids) && $this->view->oids != "") 
			{
				$orgs = array();
				foreach ($this->view->oids as $oid)
				{
					if (property_exists($oid,'objectid') && $oid->objectid != 0) 
					{
						$RR->load($oid->objectid);
					} 
					elseif (property_exists($oid,'scope_id')) 
					{
						$RR->load($oid->scope_id);
					} 
					else 
					{
						$RR->load($oid);
					}

					$o                  = new StdClass;
					$o->id              = $RR->id;
					$o->title           = $RR->title;
					$o->fulltxt         = $RR->fulltxt;
					$this->view->orgs[] = $o;
				}
			}
			else 
			{
				$this->view->orgs = $RR->getRecords($filters);
			}

			/**
			 * Filter events by search term
			 */
			$search = JRequest::getVar('search', '');
			if ($search != '')
			{
				// reset map markers
				$this->view->oids = array();
				foreach ($this->view->orgs as $k => $org)
				{
					if (stristr($org->title, $search) === false
						&& stristr($org->introtext, $search) === false)
					{
						unset($this->view->orgs[$k]);
					}
					else
					{
						// add a map marker for each org that matches search
						$o                  = new stdClass;
						$o->scope           = 'org';
						$o->scope_id        = $org->id;
						$this->view->oids[] = $o;
					}
				}
			}
			$this->view->orgs = array_values(array_filter($this->view->orgs));

			// clear ids var if no results
			if (count($this->view->orgs) == 0) 
			{
				$this->view->oids = 0;
			}

			$total += count($RR->getCount($filters));

			// get ResourcesTags Object
			$this->view->RT = new ResourcesTags($this->database);
		}
		
		// keep checkboxes
		$this->view->resources = $resources;
		
		// Push some styles and scripts to the template
		$this->_getScripts('assets/js/geosearch.jquery');
		$this->_getStyles();
		$this->_getStyles('com_kb');
		
		// Initiate paging
		jimport('joomla.html.pagination');
		$pageNav = new JPagination( $total, $filters['start'], $filters['limit'] );
		if ($distance != 0) 
		{ 
			$pageNav->setAdditionalUrlParam("distance",$distance);
		}
		if ($tags != '') 
		{
			$pageNav->setAdditionalUrlParam("tags",trim(JRequest::getString('tags', '', 'request'))); 
		}

		$pageNav->setAdditionalUrlParam("location",$location);
		$pagenavhtml = $pageNav->getListFooter();
		$this->view->pagenavhtml = $pagenavhtml;

		// if we have no users & we have attempted a search
		// we do NOT want to set this for default listing (no search)
		// use -999 which will return no users in js
		if (empty($this->view->members))
		{
			$this->view->uids[] = -999; 
		}
		
		$this->_pathway();
		$this->_title();

		// Output HTML
		if ($this->getError()) 
		{
			$this->view->setError( $this->getError() );
		}

		$this->view->display();
	}

	/**
	 * Set breadcrumbs
	 * @return
	 */
	private function _pathway()
	{
		$pathway = JFactory::getApplication()->getPathway();
		
		//add 'groups' item to pathway
		if (count($pathway->getPathWay()) <= 0)
		{
			$pathway->addItem(
				JText::_(strtoupper($this->_option)),
				'index.php?option=' . $this->_option
			);
		}
	}
	
	/**
	 * Set Title
	 * @return
	 */
	private function _title()
	{
		//set title of browser window
		$document = JFactory::getDocument();
		$document->setTitle(JText::_(strtoupper($this->_option)));
	}

	/**
	 * get marker coordinates 
	 */
	public function getmarkersTask() 
	{
		$checked = JRequest::getVar('checked', array(), 'request');
		$tags = trim(JRequest::getString('tags', '', 'request'));
		
		// get markers object
		$GM = new GeosearchMarkers($this->database);

		// start XML
		$dom = new DOMDocument("1.0");
		$node = $dom->createElement("markers");
		$parnode = $dom->appendChild($node);

		// iterate through the members, adding XML nodes for each
		if (in_array("members",$checked))
		{
			$uids = JRequest::getVar('uids', '', 'request');
			$addys = $GM->getAddresses($uids);
			
			foreach ($addys as $row) 
			{
				$location = "";
				// geocode addy if no coords
				if ($row->addressLatitude == 0 || $row->addressLongitude == 0) 
				{
					if ($row->address1 != "") 
					{
						$location .= $row->address1.", "; 
					}

					if ($row->address2 != "")
					{
						$location .= $row->address2.", "; 
					}

					if ($row->addressCity != "") 
					{ 
						$location .= $row->addressCity.", "; 
					}

					if ($row->addressRegion != "") 
					{ 
						$location .= $row->addressRegion.", "; 
					}
					
					if ($row->addressPostal != "") 
					{ 
						$location .= $row->addressPostal.", "; 
					}
					
					if ($row->addressCountry != "")
					{
						$location .= $row->addressCountry;
					}
					
					if ($latlng = $this->doGeocode($location)) 
					{
						$row->addressLatitude = $latlng[0];
						$row->addressLongitude = $latlng[1];
						// update coords in db
						$GM->update($row->uidNumber,$latlng[0],$latlng[1],'members');
					}
				}
				// add to XML document node 
				$node = $dom->createElement("marker");  
				$newnode = $parnode->appendChild($node);
				$newnode->setAttribute("uid", $row->uidNumber);
				$newnode->setAttribute("lat", $row->addressLatitude); 
				$newnode->setAttribute("lng", $row->addressLongitude);
				$newnode->setAttribute("type", "member");
			}
		}
		
		// iterate through events, adding XML nodes for each
		if (in_array("events",$checked))
		{
			$eids = JRequest::getVar('eids', '', 'request');
			$events = $GM->getEvents(date("Y"),$eids);
			foreach ($events as $row) 
			{
				// skip empty locations 
				if ($row->adresse_info != "") 
				{
					if ($row->addressLatitude == 0 || $row->addressLongitude == 0)
					{
						if ($latlng = $this->doGeocode($row->adresse_info)) 
						{
							$row->addressLatitude = $latlng[0];
							$row->addressLongitude = $latlng[1];
							// update coords in db
							$GM->update($row->id,$latlng[0],$latlng[1],'event');
						} 
						else 
						{
							continue;
						}
					}

					// add to XML document node 
					$node = $dom->createElement("marker");  
					$newnode = $parnode->appendChild($node);
					$newnode->setAttribute("uid", $row->id);
					$newnode->setAttribute("lat", $row->addressLatitude); 
					$newnode->setAttribute("lng", $row->addressLongitude);
					$newnode->setAttribute("type", "event");
				}
			}
		}

		// iterate through jobs, adding XML nodes for each
		if (in_array("jobs",$checked))
		{
			// jobs have no tags
			if ($tags == '') 
			{
				$jids = JRequest::getVar('jids', '', 'request');
				$jobs = $GM->getJobs($jids);
				foreach ($jobs as $row) 
				{
					// skip empty locations 
					if ($row->companyLocation != "") 
					{
						// geocode city if no coords
						if ($row->addressLatitude == 0 || $row->addressLongitude == 0) 
						{
							if ($latlng = $this->doGeocode($row->companyLocation)) 
							{
								$row->addressLatitude = $latlng[0];
								$row->addressLongitude = $latlng[1];
								// update coords in db
								$GM->update($row->id,$latlng[0],$latlng[1],'job');
							} 
							else 
							{
								continue;
							}
						}
						// add to XML document node 
						$node = $dom->createElement("marker");  
						$newnode = $parnode->appendChild($node);
						$newnode->setAttribute("uid", $row->id);
						$newnode->setAttribute("lat", $row->addressLatitude); 
						$newnode->setAttribute("lng", $row->addressLongitude);
						$newnode->setAttribute("type", "job");
					}
				}
			}
		}

		// iterate through organizations, adding XML nodes for each
		if (in_array("orgs",$checked))
		{
			$oids = JRequest::getVar('oids', '', 'request');
			$orgs = $GM->getOrgs($oids);
			foreach ($orgs as $row) 
			{
				// get location data
				$data = $this->getResourceData($row->fulltxt);
				$location = "<location><value>{$data['bio']}</value></location>";
				$locxml = simplexml_load_string($location);

				$value = (string) $locxml->value;

				// skip empty locations 
				if ($value != "") 
				{
					// check for marker table entry
					if ($check = $GM->checkOrgMarker($row->id)) {
						$locxml->lat = $check[3];
						$locxml->lng = $check[4];
					} 
					else 
					{
						// geocode city if no coords
						if ($locxml->lat == 0 || $locxml->lng == 0) 
						{
							if ($latlng = $this->doGeocode($value)) 
							{
								$locxml->lat = $latlng[0];
								$locxml->lng = $latlng[1];
							} 
							else 
							{
								continue;
							}
						}

						// add marker table entry 
						$GM->update($row->id,$locxml->lat,$locxml->lng,'org');	
					}
					// add to XML document node 
					$node = $dom->createElement("marker");  
					$newnode = $parnode->appendChild($node);
					$newnode->setAttribute("uid", $row->id);
					$newnode->setAttribute("lat", $locxml->lat); 
					$newnode->setAttribute("lng", $locxml->lng);
					$newnode->setAttribute("type", "org");
				}
			}
		}

		// send XML doc
		echo $dom->saveXML();		 
		exit;
	}
	
	/**
	 * get marker infowindow contents
	 */
	public function getaddyxmlTask() 
	{
		// get id and type
		$id = JRequest::getInt('uid', 0, 'request');
		$type = JRequest::getVar('type', 0, 'request');

		// start XML
		$dom = new DOMDocument("1.0");
		$node = $dom->createElement("profiles");
		$parnode = $dom->appendChild($node); 

		// add to XML document node 
		$node = $dom->createElement("profile");  
		$newnode = $parnode->appendChild($node);

		switch ($type) 
		{
			case "member":
				// check for logged in user
				$juser = JFactory::getUser();
				if ($juser->get('id')) 
				{
					$newnode->setAttribute("jid", $juser->get('id'));
				}

				// get profile object
				$user = \Hubzero\User\Profile::getInstance($id);

				// add attributes
				$newnode->setAttribute("org", $user->get('organization'));
				$newnode->setAttribute("url", $user->get('url'));

				if ($user->get('surname')) 
				{
					$name = $user->get('surname').", ".$user->get('givenName');
				} 
				else 
				{
					$name = $user->get('name');
				}

				$newnode->setAttribute("name", $name);

				// get photo
				$newnode->setAttribute("photo", \Hubzero\User\Profile\Helper::getMemberPhoto($id, 0));
				
				// get bio
				if ($user->get('bio'))
				{
					$bio = $user->getBio('parsed');
					$bio = \Hubzero\Utility\String::truncate($bio, 200);
					$newnode->setAttribute("bio", $bio); 
				}

				// link
				$profileLink = JRoute::_('index.php?option=com_members&id=' . $user->get('uidNumber'));
				$newnode->setAttribute("profilelink", $profileLink); 

				$messageLink = JRoute::_('index.php?option=com_members&id=' . $juser->get('id') . '&active=messages&task=new&to[]=' . $user->get('uidNumber'));
				if ($juser->get('guest'))
				{
					$messageLink = '/login?return' . base64_encode($messageLink);
				}
				$newnode->setAttribute("messagelink", $messageLink); 
				break;
			case "event":
				$event = new EventsEvent($this->database);
				$event->load($id);
				$newnode->setAttribute("url", $event->extra_info);
				$newnode->setAttribute("name", $event->title);
				if ($event->content) 
				{
					$desc = \Hubzero\Utility\String::truncate(stripslashes($event->content), 200);
					$newnode->setAttribute("bio", $desc);
				}

				// format dates
				$start = JHTML::_('date', $event->publish_up, 'l, F j, Y g:i a');
				$end   = JHTML::_('date', $event->publish_down, 'l, F j, Y g:i a');
				$newnode->setAttribute("start", $start);
				$newnode->setAttribute("end", $end);
				$newnode->setAttribute("tz", $event->time_zone);

				$link = JRoute::_('index.php?option=com_events&task=details&id=' . $event->id);
				$newnode->setAttribute("link", $link); 
				break;
			case "job":
				$J = new Job($this->database);
				$job = $J->get_opening($id);
				$newnode->setAttribute("url", '');
				$newnode->setAttribute("code", $job->code);
				$newnode->setAttribute("name", $job->title);
				$newnode->setAttribute("org", $job->companyName);
				if ($job->description) 
				{
					$jobsModelJob = new JobsModelJob($job->id);
                    $desc = $jobsModelJob->content('parsed');
                    $desc = \Hubzero\Utility\String::truncate($desc, 290);
					$newnode->setAttribute("bio", $desc);
				}
				$link = JRoute::_('index.php?option=com_jobs&task=job&id=' . $job->code);
				$newnode->setAttribute("link", $link); 
				$newnode->setAttribute("jobtype", $job->typename);
				break;
			case "org":
				$RR = new ResourcesResource($this->database);
				$RR->load($id);

				// get url, location data
				$data = $this->getResourceData($RR->fulltxt);

				// get location xml
				$location = "<location>{$data['citations']}</location>";
				$locxml = simplexml_load_string($location);
				$newnode->setAttribute("url", $data['sponsoredby']);
				$newnode->setAttribute("name", $RR->title);
				$newnode->setAttribute("org", $locxml->value);

				// description
				$bio = preg_replace("#<nb:(.*?)>(.*?)</nb:(.*?)>#s", '', $RR->fulltxt);
				$bio = trim($bio);
				$bio = \Hubzero\Utility\String::truncate(stripslashes($bio), 200);
				$newnode->setAttribute("bio", $bio);

				$link = JRoute::_('index.php?option=com_resources&id=' . $RR->id);
				$newnode->setAttribute("link", $link); 
				break;
		}
		echo $dom->saveXML();
		exit;
	}
	
	/**
	 * geocode location
	 * string	$location
	 * return 	array lat/lng coordinates
	 */
	public function doGeocode($location = "") 
	{
		if ($location != "") 
		{
			// geocode address
			$base_url = "http://maps.googleapis.com/maps/api/geocode/xml?address=";
			$url_addy = urlencode($location);
			$request_url = $base_url . $url_addy . "&sensor=false";
			$xml = simplexml_load_file($request_url);
			$status = $xml->status;
			if ($status == "OK")
			{
				// successful geocode
				$lat = $xml->result->geometry->location->lat;
				$lng = $xml->result->geometry->location->lng;
				$latlng = array($lat,$lng);
			  	return $latlng;
			} 
			else 
			{
				// failure to geocode
				/*
				echo "Location " . $location . " failed to geocode. ";
				echo "Received status " . $status . "\n";
				*/
				return false;
			}
		}
  	}
	
	/**
	 * pull data from resource description 
	 * string	$fulltxt
	 * return 	array 
	 */
	public function getResourceData($fulltxt = "") 
	{
		$data = array();
		preg_match_all("#<nb:(.*?)>(.*?)</nb:(.*?)>#s", $fulltxt, $matches, PREG_SET_ORDER);
		if (count($matches) > 0) 
		{
			foreach ($matches as $match)
			{
				$data[$match[1]] = $match[2];
			}
		}
		return $data;
	}
	
}
