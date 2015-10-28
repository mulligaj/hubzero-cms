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
 * @author    Christopher Smoak <csmoak@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Developer\Tables\Api;

use Hubzero\Utility\Validate;
use Hubzero\Utility\Date;
use User;
use Lang;

/**
 * Developer application table class
 */
class Application extends \JTable
{
	/**
	 * Constructor
	 * 
	 * @param   object  $db  Database object
	 * @return  void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__developer_applications', 'id', $db);
	}

	/**
	 * Check method, used when saving
	 * 
	 * @return  bool
	 */
	public function check()
	{
		// verify name
		$this->name = trim($this->name);
		if ($this->name == '')
		{
			$this->setError(Lang::txt('COM_DEVELOPER_API_APPLICATION_MISSING_NAME'));
			return false;
		}

		// verify description
		$this->description = trim($this->description);
		if ($this->description == '')
		{
			$this->setError(Lang::txt('COM_DEVELOPER_API_APPLICATION_MISSING_DESCRIPTION'));
			return false;
		}

		// verify redirect URIs
		$uris = array_map('trim', explode(PHP_EOL, $this->redirect_uri));

		// must have one
		if (empty($uris))
		{
			$this->setError(Lang::txt('COM_DEVELOPER_API_APPLICATION_MISSING_REDIRECT_URI'));
			return false;
		}

		// validate each one
		$invalid = array();
		foreach ($uris as $uri)
		{
			if (!Validate::url($uri))
			{
				$invalid[] = $uri;
			}
		}

		// if we have any invalid URIs lets inform the user
		if (!empty($invalid))
		{
			$this->setError(Lang::txt('COM_DEVELOPER_API_APPLICATION_INVALID_REDIRECT_URI', implode('<br />', $invalid)));
			return false;
		}

		// turn back into string for saving
		$this->redirect_uri = implode(' ', $uris);

		// if we dont have a created by add one
		if (!$this->created_by)
		{
			$this->created_by = User::get('id');
		}

		// if this is a new record
		if (!$this->id)
		{
			$this->created = with(new Date('now'))->toSql();

			if (!$this->hub_account)
			{
				// Allow the 3 main grantypes
				// 
				// authorization code = 3 legged oauth
				// password           = users username/password
				// refresh_token      = allow refreshing of access_tokens to require less logins
				$this->grant_types = 'authorization_code password refresh_token';
			}

			// generate unique client id & secret
			list($this->client_id, $this->client_secret) = $this->generateUniqueClientIdAndSecret();
		}

		return true;
	}

	/**
	 * Generate a unique client id/secret for application
	 * 
	 * @return  array  client id/secret
	 */
	public function generateUniqueClientIdAndSecret()
	{
		$id     = md5(uniqid($this->created_by, true));
		$secret = sha1($id);

		return array($id, $secret);
	}

	/**
	 * Get collection of application records
	 * 
	 * @param   array  $filters  Filters for querying
	 * @return  array  Array of applications
	 */
	public function find($filters = array())
	{
		$sql  = "SELECT a.* FROM {$this->_tbl} AS a";
		$sql .= $this->_buildQuery($filters);

		// limit (handle here so it doesnt effect count)
		if (isset($filters['limit']))
		{
			$sql .= " LIMIT " . $filters['limit'];
			if (isset($filters['start']))
			{
				$sql .= " OFFSET " . $filters['start'];
			}
		}

		$this->_db->setQuery($sql);
		return $this->_db->loadObjectList();
	}

	/**
	 * Get count of records based on filters
	 * 
	 * @param   array    $filters  Filters for querying
	 * @return  integer  Record count
	 */
	public function count($filters = array())
	{
		$sql  = "SELECT COUNT(*) FROM {$this->_tbl} AS s";
		$sql .= $this->_buildQuery( $filters );

		$this->_db->setQuery($sql);
		return $this->_db->loadResult();
	}

	/**
	 * Load by Client Id
	 * 
	 * @param   integer  $client_id
	 * @return  object
	 */
	public function loadByClientid($client_id)
	{
		$sql  = "SELECT * FROM {$this->_tbl} AS a";
		$sql .= $this->_buildQuery(array(
			'client_id' => $client_id
		));
		$sql .= " LIMIT 1";
		$this->_db->setQuery($sql);
		return $this->_db->loadObject();
	}

	/**
	 * Generic method to take an array of filters & gnerate sql
	 * 
	 * @param  array  $filters  Filters for querying
	 * @return string           SQL query
	 */
	public function _buildQuery($filters = array())
	{
		// var to hold conditions
		$where = array();
		$sql   = '';

		// state
		if (isset($filters['state']))
		{
			if (!is_array($filters['state']))
			{
				$filters['state'] = array($filters['state']);
			}
			$where[] = "state IN (" . implode(',', $filters['state']) . ")";
		}

		// created by
		if (isset($filters['created_by']))
		{
			$where[] = "created_by=" . $this->_db->quote( $filters['created_by'] );
		}

		// client id
		if (isset($filters['client_id']))
		{
			$where[] = "client_id=" . $this->_db->quote( $filters['client_id'] );
		}

		// hub account
		if (isset($filters['hub_account']))
		{
			$where[] = "hub_account=" . $this->_db->quote( $filters['hub_account'] );
		}

		// uidNumber (has access to)
		if (isset($filters['uidNumber']))
		{
			$sql     = ", #__developer_application_team_members AS atm";
			$where[] = "a.id=atm.application_id";
			$where[] = "atm.uidNumber=" . $this->_db->quote( $filters['uidNumber'] );
		}

		// if we have and conditions
		if (count($where) > 0)
		{
			$sql .= " WHERE " . implode(" AND ", $where);
		}

		// order by param
		if (isset($filters['orderby']))
		{
			$sql .= " ORDER BY " . $filters['orderby'];
		}
		elseif (isset($filters['sort']))
		{
			$sql .= " ORDER BY " . $filters['sort'];

			if (isset($filters['sort_Dir']))
			{
				$sql .= " " . $filters['sort_Dir'];
			}
		}

		return $sql;
	}
}