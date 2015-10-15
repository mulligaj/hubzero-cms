<?php

use Hubzero\Content\Migration\Base;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Migration script for ...
 **/
class Migration20151015171421ModIncrementalRegistration extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		try {
			$this->db->setQuery('ALTER TABLE #__profile_completion_awards ADD COLUMN disability int not null default 0');
			$this->db->query();
		}
		catch (\Exception $ex) {
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{

	}
}
