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

use Hubzero\User\Group;
use Lang;

/**
 * Table class for wishlist owner group
 */
class OwnerGroup extends \JTable
{
	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database
	 * @return  void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__wishlist_ownergroups', 'id', $db);
	}

	/**
	 * Get the groups ow a wishlist owner
	 *
	 * @param   integer  $listid        List ID
	 * @param   string   $controlgroup  Control group name
	 * @param   object   $wishlist      Wishlist
	 * @param   integer  $native        Get groups assigned to this wishlist?
	 * @param   array    $groups        List of gorups
	 * @return  mixed    False if errors, array on success
	 */
	public function get_owner_groups($listid, $controlgroup='', $wishlist=null, $native=0, $groups = array())
	{
		if ($listid === NULL)
		{
			return false;
		}

		$wishgroups = array();

		$obj = new Wishlist($this->_db);

		// if tool, get tool group
		if (!$wishlist)
		{
			$wishlist = $obj->get_wishlist($listid);
		}
		if (isset($wishlist->resource) && $wishlist->resource->type == 7)
		{
			$toolgroup = $obj->getToolDevGroup ($wishlist->referenceid);
			if ($toolgroup)
			{
				$groups[] = $toolgroup;
			}
		}

		// if primary list, add all site admins
		if ($controlgroup && $wishlist->category == 'general')
		{
			$instance = Group::getInstance($controlgroup);

			if (is_object($instance))
			{
				$gid = $instance->get('gidNumber');
				if ($gid)
				{
					$groups[] = $gid;
				}
			}
		}

		// if private group list, add the group
		if ($wishlist->category == 'group')
		{
			$groups[] = $wishlist->referenceid;
		}

		// get groups assigned to this wishlist
		if (!$native)
		{
			$sql = "SELECT o.groupid FROM `#__wishlist_ownergroups` AS o WHERE o.wishlist=" . $this->_db->quote($listid);

			$this->_db->setQuery($sql);
			$wishgroups = $this->_db->loadObjectList();

			if ($wishgroups)
			{
				foreach ($wishgroups as $wg)
				{
					if (Group::exists($wg->groupid))
					{
						$groups[] = $wg->groupid;
					}
				}
			}
		}

		$groups = array_unique($groups);
		sort($groups);
		return $groups;
	 }

	/**
	 * Remove a user as owner
	 *
	 * @param   integer  $listid      List ID
	 * @param   integer  $groupid     Group ID
	 * @param   object   $admingroup  Admin group
	 * @return  boolean  False if errors, true on success
	 */
	 public function delete_owner_group($listid, $groupid, $admingroup)
	 {
		if ($listid === NULL or $groupid === NULL)
		{
			return false;
		}

		$nativegroups = $this->get_owner_groups($listid, $admingroup, '', 1);

		// cannot delete "native" owners (e.g. tool dev group)
		if (Group::exists($groupid)
		 && !in_array($groupid, $nativegroups, true))
		{
			$query = "DELETE FROM $this->_tbl WHERE wishlist=" . $this->_db->quote($listid) . " AND groupid=" . $this->_db->quote($groupid);
			$this->_db->setQuery($query);
			$this->_db->query();
			return true;
		}
	}

	/**
	 * Add a user as owner to groups
	 *
	 * @param   integer  $listid      Wishlist ID
	 * @param   object   $admingroup  Admin group
	 * @param   array    $newgroups   Groups to add to
	 * @return  boolean  True on success
	 */
	public function save_owner_groups($listid, $admingroup, $newgroups = array())
	{
		if ($listid === NULL)
		{
			return false;
		}

		$groups = $this->get_owner_groups($listid, $admingroup);

		if (count($newgroups) > 0)
		{
			foreach ($newgroups as $ng)
			{
				$instance = Group::getInstance($ng);
				if (is_object($instance))
				{
					$gid = $instance->get('gidNumber');

					if ($gid && !in_array($gid, $groups, true))
					{
						$this->id       = 0;
						$this->groupid  = $gid;
						$this->wishlist = $listid;

						if (!$this->store())
						{
							$this->setError(Lang::txt('Failed to add a user.'));
							return false;
						}
					}
				}
			}
		}
		return true;
	}
}

