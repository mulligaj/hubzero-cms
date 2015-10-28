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
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Hubzero\Bank;

use Hubzero\Base\Object;
use Hubzero\Utility\Date;

/**
 * Teller class for controlling bank transactions
 */
class Teller extends Object
{
	/**
	 * Database
	 *
	 * @var  object
	 */
	protected $_db   = NULL;

	/**
	 * User ID
	 *
	 * @var  string
	 */
	public $uid      = NULL;

	/**
	 * Current point balance
	 *
	 * @var  mixed
	 */
	public $balance  = NULL;

	/**
	 * Lifetime point earnings
	 *
	 * @var  mixed
	 */
	public $earnings = NULL;

	/**
	 * Credit point balance
	 *
	 * @var  mixed
	 */
	public $credit   = NULL;

	/**
	 * Constructor
	 * Find the balance from the most recent transaction.
	 * If no balance is found, create an initial transaction.
	 *
	 * @param   object   &$db  Database
	 * @param   integer  $uid  User ID
	 * @return  void
	 */
	public function __construct(&$db, $uid)
	{
		$this->_db = $db;
		$this->uid = $uid;

		$BA = new Account($this->_db);

		if ($BA->load_uid($this->uid))
		{
			$this->balance  = $BA->balance;
			$this->earnings = $BA->earnings;
			$this->credit   = $BA->credit;
		}
		else
		{
			// no points are given initially
			$this->balance  = 0;
			$this->earnings = 0;
			$this->credit   = 0;
			$this->_saveBalance('creation');
		}
	}

	/**
	 * Get the current balance
	 *
	 * @return  integer
	 */
	public function summary()
	{
		return $this->balance;
	}

	/**
	 * Get the current credit balance
	 *
	 * @return  integer
	 */
	public function credit_summary()
	{
		return $this->credit;
	}

	/**
	 * Add points
	 *
	 * @param   integer  $amount  Amount to deposit
	 * @param   string   $desc    Transaction description
	 * @param   string   $cat     Transaction category
	 * @param   integer  $ref     ID of item transaction references
	 * @return  void
	 */
	public function deposit($amount, $desc='Deposit', $cat, $ref)
	{
		$amount = $this->_amountCheck($amount);

		if ($this->getError())
		{
			echo $this->getError();
			return;
		}

		$this->balance  += $amount;
		$this->earnings += $amount;

		if (!$this->_save('deposit', $amount, $desc, $cat, $ref))
		{
			echo $this->getError();
		}
	}

	/**
	 * Withdraw (spend) points
	 *
	 * @param   number   $amount  Amount to withdraw
	 * @param   string   $desc    Transaction description
	 * @param   string   $cat     Transaction category
	 * @param   integer  $ref     ID of item transaction references
	 * @return  void
	 */
	public function withdraw($amount, $desc='Withdraw', $cat, $ref)
	{
		$amount = $this->_amountCheck($amount);

		if ($this->getError())
		{
			echo $this->getError();
			return;
		}

		if ($this->_creditCheck($amount))
		{
			$this->balance -= $amount;

			if (!$this->_save('withdraw', $amount, $desc, $cat, $ref))
			{
				echo $this->getError();
			}
		}
		else
		{
			echo $this->getError();
		}
	}

	/**
	 * Set points aside (credit)
	 *
	 * @param   integer  $amount  Amount to put on hold
	 * @param   string   $desc    Transaction description
	 * @param   string   $cat     Transaction category
	 * @param   integer  $ref     ID of item transaction references
	 * @return  void
	 */
	public function hold($amount, $desc='Hold', $cat, $ref)
	{
		$amount = $this->_amountCheck($amount);

		if ($this->getError())
		{
			echo $this->getError();
			return;
		}

		// Current order processing workflow (which requires manual order fulfillment on the backend) prevents race
		// condition with the check and update below from corrupting user point balance, but if
		// but if workflow is ever changed, a table/row level lock would need to be added to this function
		// and error code added to deal with multiple orders with insufficient balances.
		//
		// See https://hubzero.org/support/ticket/234 for details

		if ($this->_creditCheck($amount))
		{
			$this->credit += $amount;

			if (!$this->_save('hold', $amount, $desc, $cat, $ref))
			{
				echo $this->getError();
			}
		}
		else
		{
			echo $this->getError();
		}
	}

	/**
	 * Make credit adjustment
	 *
	 * @param   integer  $amount  Amount to credit
	 * @return  void
	 */
	public function credit_adjustment($amount)
	{
		$amount = (intval($amount) > 0) ? intval($amount) : 0;
		$this->credit = $amount;
		$this->_saveBalance('update');
	}

	/**
	 * Get a history of transactions
	 *
	 * @param   integer  $limit  Number of records to return
	 * @return  array
	 */
	public function history($limit=20)
	{
		$lmt = "";
		if ($limit > 0)
		{
			$lmt .= " LIMIT " . $limit;
		}
		$this->_db->setQuery("SELECT * FROM `#__users_transactions` WHERE uid=" . $this->uid . " ORDER BY created DESC, id DESC" . $lmt);
		return $this->_db->loadObjectList();
	}

	/**
	 * Check that they have enough in their account to perform the transaction.
	 *
	 * @param   number   $amount  Amount to subtract from balance
	 * @return  boolean  True if they have enough credit
	 */
	public function _creditCheck($amount)
	{
		$b = $this->balance;
		$b -= $amount;
		$c = $this->credit;
		$ccheck = $b - $c;

		if ($b >= 0 && $ccheck >= 0)
		{
			return true;
		}
		else
		{
			$this->setError('Not enough points in user account to process transaction.');
			return false;
		}
	}

	/**
	 * Check if an amount is greater than 0
	 *
	 * @param   integer  $amount  Amount to check
	 * @return  integer
	 */
	public function _amountCheck($amount)
	{
		$amount = intval($amount);
		if ($amount == 0)
		{
			$this->setError('Cannot process transaction with 0 points.');
		}
		return $amount;
	}

	/**
	 * Save a record
	 *
	 * @param   string   $type    Record type (inserting or updating)
	 * @param   integer  $amount  Amount to process
	 * @param   string   $desc    Transaction description
	 * @param   string   $cat     Transaction category
	 * @param   integer  $ref     ID of item transaction references
	 * @return  boolean  True on success
	 */
	public function _save($type, $amount, $desc, $cat, $ref)
	{
		if (!$this->_saveBalance($type))
		{
			return false;
		}
		if (!$this->_saveTransaction($type, $amount, $desc, $cat, $ref))
		{
			return false;
		}

		return true;
	}

	/**
	 * Save the current balance
	 *
	 * @param   string   $type  Record type (inserting or updating)
	 * @return  boolean  True on success
	 */
	public function _saveBalance($type)
	{
		if ($type == 'creation')
		{
			$query = "INSERT INTO `#__users_points` (uid, balance, earnings, credit) VALUES('" . $this->uid . "','" . $this->balance . "','" . $this->earnings . "','" . $this->credit . "')";
		}
		else
		{
			$query = "UPDATE `#__users_points` SET balance='" . $this->balance . "', earnings='" . $this->earnings . "', credit='" . $this->credit . "' WHERE uid=" . $this->uid;
		}
		$this->_db->setQuery($query);
		if ($this->_db->query())
		{
			return true;
		}
		else
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return true;
	}

	/**
	 * Record the transaction
	 *
	 * @param   string   $type    Record type (inserting or updating)
	 * @param   integer  $amount  Amount to process
	 * @param   string   $desc    Transaction description
	 * @param   string   $cat     Transaction category
	 * @param   integer  $ref     ID of item transaction references
	 * @return  boolean  True on success
	 */
	public function _saveTransaction($type, $amount, $desc, $cat, $ref)
	{
		$data = array();
		$data['uid']         = $this->uid;
		$data['type']        = $type;
		$data['amount']      = $amount;
		$data['description'] = $desc;
		$data['category']    = $cat;
		$data['referenceid'] = $ref;
		$data['created']     = with(new Date('now'))->toSql();
		$data['balance']     = $this->balance;

		$BT = new Transaction($this->_db);
		if (!$BT->bind($data))
		{
			$this->setError($BT->getError());
			return false;
		}
		if (!$BT->check())
		{
			$this->setError($BT->getError());
			return false;
		}
		if (!$BT->store())
		{
			$this->setError($BT->getError());
			return false;
		}
		return true;
	}
}

