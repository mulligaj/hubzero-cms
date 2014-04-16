<?php

use Hubzero\Content\Migration\Base;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Migration script for ...
 **/
class Migration20140407100000ComGeosearch extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		// component entry
		$this->addComponentEntry('Geosearch');

		// markers table
		if (!$this->db->tableExists('#__geosearch_markers'))
		{
			$query = 'CREATE TABLE IF NOT EXISTS `#__geosearch_markers` (
  						`id` INT NOT NULL AUTO_INCREMENT,
  						`scope` VARCHAR(100) NOT NULL,
						`scope_id` INT NOT NULL,
  						`addressLatitude` FLOAT NULL,
  						`addressLongitude` FLOAT NULL,
  					PRIMARY KEY (`id`));';
			$this->db->setQuery($query);
			$this->db->query();
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		// component entry
		$this->deleteComponentEntry('Geosearch');

		// markers table
		if ($this->db->tableExists('#__geosearch_markers'))
		{
			$query = 'DROP TABLE IF EXISTS `#__geosearch_markers`;';
			$this->db->setQuery($query);
			$this->db->query();
		}
	}
}