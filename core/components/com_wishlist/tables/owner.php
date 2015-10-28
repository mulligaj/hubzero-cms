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

namespace Components\Wishlist\Tables;

use Component;
use Request;
use Config;
use Route;
use Event;
use Lang;
use User;

/**
 * Table class for wishlist owner
 */
class Owner extends \JTable
{
	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database
	 * @return  void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__wishlist_owners', 'id', $db);
	}

	/**
	 * Delete a record
	 *
	 * @param   integer  $listid      List ID
	 * @param   integer  $uid         User ID
	 * @param   object   $admingroup  Admin group
	 * @return  boolean  False if errors, True on success
	 */
	public function delete_owner($listid, $uid, $admingroup)
	{
		if ($listid === NULL or $uid === NULL)
		{
			return false;
		}

		$nativeowners = $this->get_owners($listid, $admingroup, 1);

		$quser = User::getInstance($uid);

		// cannot delete "native" owner (e.g. resource contributor)
		if (is_object($quser) && !in_array($quser->get('id'), $nativeowners, true))
		{
			$query = "DELETE FROM $this->_tbl WHERE wishlist=" . $this->_db->quote($listid) . " AND userid=" . $this->_db->quote($uid);
			$this->_db->setQuery($query);
			$this->_db->query();
		}
	}

	/**
	 * Save a list of users as owners of a wishlist
	 *
	 * @param   integer  $listid      List ID
	 * @param   object   $admingroup  Admin group
	 * @param   array    $newowners   Users to add
	 * @param   integer  $type        Type
	 * @return  boolean  False if errors, True on success
	 */
	public function save_owners($listid, $admingroup, $newowners = array(), $type = 0)
	{
		if ($listid === NULL)
		{
			return false;
		}

		$owners = $this->get_owners($listid, $admingroup);

		if (count($newowners) > 0)
		{
			foreach ($newowners as $no)
			{
				$quser = User::getInstance($no);
				if (is_object($quser)
				 && !in_array($quser->get('id'), $owners['individuals'], true)
				 && !in_array($quser->get('id'), $owners['advisory'], true))
				{
					$this->id = 0;
					$this->userid = $quser->get('id');
					$this->wishlist = $listid;
					$this->type = $type;

					if (!$this->store())
					{
						$this->setError(Lang::txt('Failed to add a user.'));
						return false;
					}

					// send email to added user
					$admin_email = Config::get('mailfrom');

					$kind = $type==2 ? Lang::txt('member of Advisory Committee') : Lang::txt('list administrator');
					$subject = Lang::txt('Wish List') . ', ' . Lang::txt('You have been added as a') . ' ' . $kind . ' ' . Lang::txt('FOR') . ' ' . Lang::txt('Wish List') . ' #' . $listid;

					$from = array(
						'name'  => Config::get('sitename') . ' ' . Lang::txt('Wish List'),
						'email' => Config::get('mailfrom')
					);

					$message  = $subject . '. ';
					$message .= "\r\n\r\n";
					$message .= '----------------------------' . "\r\n";
					$url = Request::base() . Route::url('index.php?option=com_wishlist&id=' . $listid);
					$message .= Lang::txt('Please go to %s to view the wish list and rank new wishes.', $url);

					if (!Event::trigger('xmessage.onSendMessage', array('wishlist_new_owner', $subject, $message, $from, array($quser->get('id')), 'com_wishlist')))
					{
						$this->setError(Lang::txt('Failed to message new wish list owner.'));
					}
				}
			}
		}
		return true;
	}

	/**
	 * Get a list of owners
	 *
	 * @param   integer  $listid      List ID
	 * @param   object   $admingroup  Admin Group
	 * @param   object   $wishlist    Wish list
	 * @param   integer  $native      Get groups assigned to this wishlist?
	 * @param   integer  $wishid      Wish ID
	 * @param   array    $owners      Owners
	 * @return  mixed    False if errors, array on success
	 */
	public function get_owners($listid, $admingroup, $wishlist='', $native=0, $wishid=0, $owners = array())
	{
		if ($listid === NULL)
		{
			return false;
		}

		$obj  = new Wishlist($this->_db);
		$objG = new OwnerGroup($this->_db);
		if (!$wishlist)
		{
			$wishlist = $obj->get_wishlist($listid);
		}

		// If private user list, add the user
		if ($wishlist->category == 'user')
		{
			$owners[] = $wishlist->referenceid;
		}

		// If resource, get contributors
		if ($wishlist->category == 'resource' &&  $wishlist->resource->type != 7)
		{
			$cons = $obj->getCons($wishlist->referenceid);
			if ($cons)
			{
				foreach ($cons as $con)
				{
					$owners[] = $con->id;
				}
			}
		}

		// Get groups
		$groups = $objG->get_owner_groups($listid, (is_object($admingroup) ? $admingroup->get('group') : $admingroup), $wishlist, $native);
		if ($groups)
		{
			foreach ($groups as $g)
			{
				// Load the group
				$group = \Hubzero\User\Group::getInstance($g);
				if ($group && $group->get('gidNumber'))
				{
					$members  = $group->get('members');
					$managers = $group->get('managers');
					$members  = array_merge($members, $managers);
					if ($members)
					{
						foreach ($members as $member)
						{
							$owners[] = $member;
						}
					}
				}
			}
		}

		// Get individuals
		if (!$native)
		{
			$sql = "SELECT o.userid FROM `#__wishlist_owners` AS o WHERE o.wishlist=" . $this->_db->quote($listid) . " AND o.type!=2";

			$this->_db->setQuery($sql);
			if ($results =  $this->_db->loadObjectList())
			{
				foreach ($results as $result)
				{
					$owners[] = $result->userid;
				}
			}
		}

		$owners = array_unique($owners);
		sort($owners);

		// Are we also including advisory committee?
		$wconfig = Component::params('com_wishlist');

		$advisory = array();

		if ($wconfig->get('allow_advisory'))
		{
			$sql = "SELECT DISTINCT o.userid FROM `#__wishlist_owners` AS o WHERE o.wishlist=" . $this->_db->quote($listid) . " AND o.type=2";

			$this->_db->setQuery($sql);
			if ($results = $this->_db->loadObjectList())
			{
				foreach ($results as $result)
				{
					$advisory[] = $result->userid;
				}
			}
		}

		// Find out those who voted - for distribution of points
		if ($wishid)
		{
			$activeowners = array();

			$query  = "SELECT v.userid FROM `#__wishlist_vote` AS v LEFT JOIN `#__wishlist_item` AS i ON v.wishid = i.id ";
			$query .= "WHERE i.wishlist = " . $this->_db->quote($listid) . " AND v.wishid=" . $this->_db->quote($wishid) . " AND (v.userid IN ('" . implode("','", $owners) . "')) ";

			$this->_db->setQuery($query);
			if ($result = $this->_db->loadObjectList())
			{
				foreach ($result as $r)
				{
					$activeowners[] = $r->userid;
				}

				$owners = $activeowners;
			}
		}

		$collect = array();
		$collect['individuals'] = $owners;
		$collect['groups']      = $groups;
		$collect['advisory']    = $advisory;

		return $collect;
	}
}

