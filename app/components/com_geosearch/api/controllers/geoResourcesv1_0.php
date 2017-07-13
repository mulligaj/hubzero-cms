<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Geosearch\Api\Controllers;

use Hubzero\Component\ApiController;
use Request;
use App;

class GeoResourcesv1_0 extends ApiController
{

  public function execute()
  {
    parent::execute();
  }

  /**
   * Get a list of oranizations that have been geolocated
   *
   * @apiMethod GET
   * @apiUri    api/geosearch/organizations
   * @return    void
   */
  public function organizationsTask()
  {
    $geolocatedOrganizationsQuery = "SELECT resources.id, resources.title,
                                      resources.fulltxt,
                                      SUBSTRING(resources.introtext, 1, 100) as description
                                     FROM (SELECT *
                                           FROM #__geosearch_markers
                                           WHERE scope = 'organization') as markers
                                     LEFT JOIN #__resources as resources
                                     ON markers.scope_id = resources.id";

    $id = Request::getVar("id", null);

    if ($id)
    {
      $geolocatedOrganizationsQuery .= " WHERE resources.id = {$id}";
    }

    $db = App::get('db');
    $db->setQuery($geolocatedOrganizationsQuery);
    $geolocatedOrganizations = $db->LoadObjectList();
    $geolocatedOrganizations = $this->addLocationToOrganizations($geolocatedOrganizations);

    $this->send($geolocatedOrganizations);
  }

  protected function addLocationToOrganizations($organizations)
  {
    return array_map(function($organization)
    {
      $organization->location = $this->extractLocation($organization);
      return $organization;
    }, $organizations);
  }

  protected function extractLocation($organization)
  {
    $locationRegex = "/<nb:bio>(.*)<\/nb:bio>/";
    $location = "";
    preg_match($locationRegex, $organization->fulltxt, $matches);

    if ($matches[1])
    {
      $location = $matches[1];
    }

    return $location;
  }

}

