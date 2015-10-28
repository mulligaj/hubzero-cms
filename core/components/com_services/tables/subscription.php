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
 * @author    Alissa Nedossekina <alisa@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Services\Tables;

use Lang;
use Date;

/**
 * Table class for service subscription
 */
class Subscription extends \JTable
{
	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database
	 * @return  void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__users_points_subscriptions', 'id', $db);
	}

	/**
	 * Validate data
	 *
	 * @return  boolean  True if data is valid
	 */
	public function check()
	{
		if (!$this->uid)
		{
			$this->setError(Lang::txt('Entry must have a user ID.'));
		}

		if (!$this->serviceid)
		{
			$this->setError(Lang::txt('Entry must have a service ID.'));
		}

		if ($this->getError())
		{
			return false;
		}

		return true;
	}

	/**
	 * Load a record and bind to $this
	 *
	 * @param   integer  $id         Entry ID
	 * @param   integer  $oid        User ID
	 * @param   integer  $serviceid  Service ID
	 * @param   array    $status     List of statuses
	 * @return  boolean  True upon success, False if errors
	 */
	public function loadSubscription($id = NULL, $oid=NULL, $serviceid = NULL, $status = array(0, 1, 2))
	{
		if ($id == 0 or  ($oid === NULL && $serviceid === NULL))
		{
			return false;
		}

		$query  = "SELECT * FROM $this->_tbl WHERE ";
		if ($id)
		{
			$query .= "id='$id' ";
		}
		else if ($oid && $serviceid)
		{
			$query .= "uid='$oid' AND serviceid='$serviceid' ";
		}
		$query .= " AND status IN (" . implode(",", $status) . ")";

		$this->_db->setQuery($query);
		if ($result = $this->_db->loadAssoc())
		{
			return $this->bind($result);
		}
		else
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
	}

	/**
	 * Cancel a subscription
	 *
	 * @param   integer  $subid      Subscription ID
	 * @param   integer  $refund     Refund amount
	 * @param   integer  $unitsleft  Units left
	 * @return  boolean  True on success, False on error
	 */
	public function cancelSubscription($subid = NULL, $refund=0, $unitsleft=0)
	{
		if ($subid === NULL )
		{
			return false;
		}

		// status quo if now money back is expected
		$unitsleft = $refund ? $unitsleft : 0;

		$query = "UPDATE $this->_tbl SET status='2', pendingpayment='$refund', pendingunits='$unitsleft' WHERE id='$subid'" ;
		$this->_db->setQuery($query);
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return true;
	}

	/**
	 * Get a count of records
	 *
	 * @param   array    $filters  Filters to apply
	 * @param   boolean  $admin    Is admin?
	 * @return  integer
	 */
	public function getSubscriptionsCount($filters=array(), $admin=false)
	{
		$filters['exlcudeadmin'] = 1;
		$filter = $this->buildQuery( $filters, $admin );

		$sql = "SELECT count(*) FROM $this->_tbl AS u JOIN #__users_points_services as s ON s.id=u.serviceid $filter";

		$this->_db->setQuery($sql);
		return $this->_db->loadResult();
	}

	/**
	 * Get a list of records
	 *
	 * @param   array    $filters  Filters to apply
	 * @param   boolean  $admin    Is admin?
	 * @return  array
	 */
	public function getSubscriptions($filters, $admin=false)
	{
		$filter = $this->buildQuery( $filters, $admin );
		$filters['exlcudeadmin'] = 1;

		$sql  = "SELECT u.*, s.title, s.category, s.unitprice, s.currency, s.unitsize, s.unitmeasure, s.minunits, s.maxunits ";
		$sql .= " FROM $this->_tbl AS u JOIN #__users_points_services as s ON s.id=u.serviceid ";
		$sql .= $this->buildQuery( $filters, $admin );
		$sql .= (isset($filters['limit']) && $filters['limit'] > 0) ? " LIMIT " . $filters['start'] . ", " . $filters['limit'] : "";

		$this->_db->setQuery( $sql );
		return $this->_db->loadObjectList();
	}

	/**
	 * Get a subscription
	 *
	 * @param   integer  $id  User ID
	 * @return  mixed
	 */
	public function getSubscription($id)
	{
		if ($id === NULL)
		{
			return false;
		}

		$sql  = "SELECT u.*, s.id as serviceid, s.title, s.category, s.unitprice, s.pointsprice, s.currency, s.unitsize, s.unitmeasure, s.minunits, s.maxunits, e.companyLocation, e.companyName, e.companyWebsite ";
		$sql .= " FROM $this->_tbl AS u JOIN #__users_points_services as s ON s.id=u.serviceid ";
		$sql .= " JOIN #__jobs_employers as e ON e.uid=u.uid ";
		$sql .= " WHERE u.id='$id' ";

		$this->_db->setQuery($sql);
		$result = $this->_db->loadObjectList();

		$result = $result ? $result[0] : NULL;
		return $result;
	}

	/**
	 * Build a query statement
	 *
	 * @param   array    $filters  Filters to apply
	 * @param   boolean  $admin    Is admin?
	 * @return  string   SQL
	 */
	public function buildQuery($filters=array(), $admin=false)
	{
		$query = "WHERE 1=1 ";
		if (isset($filters['filterby']))
		{
			switch ($filters['filterby'])
			{
				case 'pending':   $query .= "AND (u.status=0 OR u.pendingpayment > 0 OR u.pendingunits > 0) "; break;
				case 'cancelled': $query .= "AND u.status=2 ";  break;
				default:          $query .= ''; break;
			}
		}

		if (isset($filters['exlcudeadmin']))
		{
			$query .= "AND u.uid!=1 ";
		}

		$query .= " ORDER BY ";
		if (isset($filters['sortby']))
		{
			switch ($filters['sortby'])
			{
				case 'date':
				case 'date_added':   $query .= 'u.added DESC';    break;
				case 'date_expires': $query .= 'u.expires DESC';  break;
				case 'date_updated': $query .= 'u.updated DESC';  break;
				case 'category':     $query .= 's.category DESC'; break;
				case 'status':       $query .= 'u.status ASC';    break;
				case 'pending':
				default:  $query .= 'u.pendingunits DESC, u.pendingpayment DESC, u.status ASC, u.updated DESC ';   break;
			}
		}

		return $query;
	}

	/**
	 * Generate a code
	 *
	 * @param   integer  $minlength   Minimum length
	 * @param   integer  $maxlength   Maximum length
	 * @param   integer  $usespecial  Use special characters?
	 * @param   integer  $usenumbers  Use numbers?
	 * @param   integer  $useletters  Use letters?
	 * @return  string   Return description (if any) ...
	 */
	public function generateCode($minlength = 6, $maxlength = 6, $usespecial = 0, $usenumbers = 1, $useletters = 1)
	{
		$key = '';
		$charset = '';

		if ($useletters) $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if ($usenumbers) $charset .= "0123456789";
		if ($usespecial) $charset .= "~@#$%^*()_+-={}|]["; // Note: using all special characters this reads: "~!@#$%^&*()_+`-={}|\\]?[\":;'><,./";
		if ($minlength > $maxlength) $length = mt_rand ($maxlength, $minlength);
		else $length = mt_rand ($minlength, $maxlength);
		for ($i=0; $i<$length; $i++) $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];

		return $key;
	}

	/**
	 * Get remaining
	 *
	 * @param   string   $type          Type
	 * @param   object   $subscription  Subscription object
	 * @param   integer  $maxunits      Maximum units
	 * @param   mixed    $unitsize      Unit size
	 * @return  mixed
	 */
	public function getRemaining($type='unit', $subscription = NULL, $maxunits = 24, $unitsize=1)
	{
		if ($subscription === NULL)
		{
			return false;
		}

		$current_time = time();

		$limits    = array();
		$starttime = $subscription->added;
		$lastunit  = 0;
		$today     = Date::of(time() - (24 * 60 * 60))->toSql();

		for ($i = 0; $i < $maxunits; $i++)
		{
			$starttime = Date::of(strtotime("+".$unitsize."month", strtotime($starttime)))->format('Y-m-d');
			$limits[$i] = $starttime;
		}

		for ($j = 0; $j < count($limits); $j++)
		{
			if (strtotime($current_time) < strtotime($limits[$j]))
			{
				$lastunit = $j + 1;
				if ($type == 'unit')
				{
					$remaining = $subscription->units - $lastunit;
					$refund    = $remaining > 0 ? $remaining : 0;
					return ($remaining);
				}
			}
		}
	}
}

