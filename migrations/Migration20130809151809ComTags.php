<?php

use Hubzero\Content\Migration\Base;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Migration script for adding missing tags_log table
 **/
class Migration20130809151809ComTags extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$query = "";

		if (!$this->db->tableExists('#__tags_log'))
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__tags_log` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`tag_id` int(11) NOT NULL DEFAULT '0',
						`timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						`user_id` int(11) DEFAULT '0',
						`action` varchar(50) DEFAULT NULL,
						`comments` text,
						`actorid` int(11) DEFAULT '0',
						PRIMARY KEY (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		}

		if (!empty($query))
		{
			$this->db->setQuery($query);
			$this->db->query();
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$query = "";

		if ($this->db->tableExists('#__tags_log'))
		{
			$query = "DROP TABLE IF EXISTS `#__tags_log`;";
		}

		if (!empty($query))
		{
			$this->db->setQuery($query);
			$this->db->query();
		}
	}
}