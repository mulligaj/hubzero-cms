<?php

use Hubzero\Content\Migration\Base;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Migration script for ...
 **/
class Migration20151028184910ModIncrementalRegistration extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$this->db->setQuery('SELECT 1 FROM #__extensions WHERE element = \'mod_incremental_registration\'');
		$this->db->query();
		if (!$this->db->loadResult()) {
			$this->db->setQuery('INSERT INTO #__extensions(name, type, element, client_id, enabled, access, protected) VALUES (\'mod_incremental_registration\', \'module\', \'mod_incremental_registration\', 0, 1, 1, 1)');
			$this->db->query();
		}
		$this->db->setQuery('SELECT 1 FROM #__modules WHERE module = \'mod_incremental_registration\'');
		if (!$this->db->loadResult()) {
			$this->db->setQuery('INSERT INTO #__modules(title, ordering, position, published, module, access, showtitle, client_id) VALUES (\'Incremental Registration\', 1, \'notices\', 0, \'mod_incremental_registration\', 1, 0, 0)');
			$this->db->query();
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{

	}
}
