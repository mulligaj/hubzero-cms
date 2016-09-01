<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.	If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package	 hubzero-cms
 * @author		Kevin Wojkovich <kevinw@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license	 http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

use Hubzero\Geocode;

/**
 * Cron plugin for support tickets
 */
class plgCronGeosearch extends \Hubzero\Plugin\Plugin
{
	/**
	 * Return a list of events
	 *
	 * @return  array
	 */
	public function onCronEvents()
	{
		$this->loadLanguage();

		$obj = new stdClass();
		$obj->plugin = $this->_name;
		$obj->events = array(
			array(
				'name'	 => 'getLocationData',
				'label'	=> Lang::txt('PLG_CRON_GEOSEARCH_GET_LOCATION_DATA'),
				'params' => ''
			)
		);

		return $obj;
	}

	/**
	 * populate the geosearch markers table
	 *
	 * @param   object   $job  \Components\Cron\Models\Job
	 * @return  boolean
	 */
	public function getLocationData(\Components\Cron\Models\Job $job)
	{
		//setup database object
		$this->database = App::get('db');

		//get the relevant tables
		require_once(PATH_CORE . DS . 'components' . DS .'com_members' . DS . 'models' . DS . 'member.php');
		require_once(PATH_CORE . DS . 'components' . DS .'com_members' . DS . 'models' . DS . 'profile' . DS . 'field.php');
		require_once(PATH_APP  . DS . 'components' . DS .'com_geosearch' . DS . 'tables' . DS . 'geosearchmarkers.php');
		require_once(PATH_CORE . DS . 'components' . DS .'com_jobs' . DS . 'tables' . DS . 'job.php');
		require_once(PATH_CORE . DS . 'components' . DS .'com_events' . DS . 'tables' . DS . 'event.php');

		// Get list of existing markers
		$query = new Hubzero\Database\Query;
		$existingMarkers = $query->select('scope, scope_id')
			->from('#__geosearch_markers')
			->fetch();

		// user profiles
		$profiles = \Components\Members\Models\Member::all()
			->select('*')
			->whereEquals('block', 0)
			->where('approved', '!=', 0)
			->rows();

		// jobs
		$query = new Hubzero\Database\Query;
		$jobs = $query->select('*')
			->from('#__jobs_openings') 
		  ->whereEquals('status', 1)
			->whereRaw('(DATEDIFF(NOW(), added) < 180)')
			->fetch();

		// events
		$objEvents = new \Components\Events\Tables\Event($this->database);
		$events = $objEvents->getEvents('year', array('year' => date('Y'), 'category' => 0));

		// organizations
		$organizations = array();

		// Habricentral's definitiion of organization is a resource type.
		$query = new Hubzero\Database\Query;
		$resourceTypes = $query->select('id')
		 ->from('#__resource_types')
		 ->whereEquals('alias', 'organizations')
		 ->fetch('column');

		foreach ($resourceTypes as $type)
		{
			$query = new Hubzero\Database\Query;
			$resources = $query->select('*')->from('#__resources')->whereEquals('type',$type)->fetch();
			foreach ($resources as $resource)
			{
				preg_match('/<nb:bio>(.*?)<\/nb:bio>/s', $resource->fulltxt, $matches);
				$title = $resource->title;

				if (isset($matches[0]))
				{
					$organization = new stdClass;
					$organization->title = $title;
					$organization->location = $matches[0];
					$organization->id = $resource->id;
					$organizations[] = $organization;
				}
			}
		}

		$markerMemberIDs = array();
		$markerJobIDs = array();
		$markerEventIDs = array();
		$markerOrganizationIDs = array();
		$markers = array();

		// Ascertain member location
		foreach ($profiles as $profile)
		{
				$fields = $profile->profiles()->rows()->toObject();
				$location = '';
				foreach ($fields as $field)
				{
					// Use the address profile field (preferred) or the organization
					if ($field->profile_key == 'address' && $field->access == 1)
					{
						// Build the address string
						$fieldset = json_decode($field->profile_value);
						foreach ($fieldset as $k => $f)
						{
							$location .= $f . ' ';
						}
					}
					elseif ($field->profile_key == 'organization' && $field->access == 1)
					{
						// Get the organization name
						$location = $field->profile_value;
					}
					else
					{
						// Empty string
						$location = '';
					}
				}

			if (isset($location) && $location != '')
			{
				$obj = array();
				$obj['scope'] = 'member';
				$obj['scope_id'] = $profile->get('id');
				$obj['location'] = $location;
				$obj['title'] = $profile->get('username');
				array_push($markers, $obj);
			}
		}

		foreach ($jobs as $job)
		{
			$obj = array();
			$obj['scope'] = 'job';
			$obj['scope_id'] = $job->code;
			$obj['location'] = $job->companyLocation . ' ' . $job->companyLocationCountry;
			$obj['title'] = $job->title;

			array_push($markers, $obj);
		}

		foreach ($events as $event)
		{
			$obj = array();
			$obj['scope'] = 'event';
			$obj['scope_id'] = $event->id;
			$obj['location'] = $event->adresse_info;
			$obj['title'] = $event->title;

			array_push($markers, $obj);
		}

		foreach ($organizations as $organization)
		{
			$obj = array();
			$obj['scope'] = 'organization';
			$obj['scope_id'] = $organization->id;
			$obj['location'] = $organization->location;
			$obj['title'] = $organization->title;

			array_push($markers, $obj);
		}

		foreach ($markers as &$marker)
		{
			// Only mark non-existant things
			$exists = false;
			foreach ($existingMarkers as $e)
			{
				if ($e->scope_id == $marker['scope_id'] && $e->scope == $marker['scope'])
				{
					$exists = true;
				}
			}

			if ($exists === false)
			{
				try 
				{
					$geocode = new \Hubzero\Geocode\Geocode;
					$marker['geolocate'] = $geocode->locate($marker['location']);
				}
				catch (\Exception $e)
				{
					$marker['geolocate'] = null;
				}

				if ($marker['location'] != '' && $marker['location'] != null && $marker['geolocate'] != null)
				{
					$m->addressLatitude = $marker['geolocate']->getLatitude();
					$m->addressLongitude = $marker['geolocate']->getLongitude();
					$m->title = $marker['title'];
					$m->scope_id = $marker['scope_id'];
					$m->scope = $marker['scope'];
					$m->location = $marker['location'];
					$m->store(true);
				}
			}
		}
		return true;
	}
} //end plgCronGeosearch
