<?php

use Hubzero\Content\Migration\Base;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for adding default member organization types
 **/
class Migration20141110143234ComMembers extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		if ($this->db->tableExists('#__xorganization_types'))
		{
			$query = "SELECT COUNT(*) FROM `#__xorganization_types`";
			$this->db->setQuery($query);
			if (!$this->db->loadResult())
			{
				$types = array(
					'universityundergraduate' => 'University / College Undergraduate',
					'universitygraduate'      => 'University / College Graduate Student',
					'universityfaculty'       => 'University / College Faculty',
					'universitystaff'         => 'University / College Staff',
					'precollegestudent'       => 'K-12 (Pre-College) Student',
					'precollegefacultystaff'  => 'K-12 (Pre-College) Faculty/Staff',
					'nationallab'             => 'National Laboratory',
					'industry'                => 'Industry / Private Company',
					'government'              => 'Government Agency',
					'military'                => 'Military',
					'unemployed'              => 'Retired / Unemployed'
				);

				include_once(PATH_CORE . DS . 'components' . DS . 'com_members' . DS . 'tables' . DS . 'organizationtype.php');

				foreach ($types as $alias => $title)
				{
					$row = new \Components\Members\Tables\OrganizationType($this->db);
					$row->type  = $alias;
					$row->title = $title;
					$row->store();
				}
			}
		}
	}
}