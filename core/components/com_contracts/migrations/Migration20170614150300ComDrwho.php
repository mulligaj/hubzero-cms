<?php

use Hubzero\Content\Migration\Base;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for installing tables for DrWho component
 **/
class Migration20170614150300ComDrwho extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		if (!$this->db->tableExists('#__drwho_characters'))
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__drwho_characters` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL DEFAULT '',
			  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
			  `doctor` tinyint(2) NOT NULL DEFAULT '0',
			  `friend` tinyint(2) unsigned NOT NULL DEFAULT '0',
			  `enemy` tinyint(2) unsigned NOT NULL DEFAULT '0',
			  `bio` mediumtext NOT NULL,
			  `state` tinyint(2) NOT NULL DEFAULT '0',
			  `species` varchar(100) NOT NULL DEFAULT '',
			  PRIMARY KEY (`id`),
			  KEY `idx_created_by` (`created_by`),
			  KEY `idx_friend` (`friend`),
			  KEY `idx_enemy` (`enemy),
			  KEY `idx_doctor` (`doctor`),
			  KEY `idx_state` (`state`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

			$this->db->setQuery($query);
			$this->db->query();
		}

		if (!$this->db->tableExists('#__drwho_seasons'))
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__drwho_seasons` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `title` varchar(255) NOT NULL DEFAULT '',
			  `alias` varchar(255) NOT NULL,
			  `premiere_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `finale_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `doctor_id` int(11) unsigned NOT NULL DEFAULT '0',
			  `ordering` int(11) unsigned NOT NULL DEFAULT '0',
			  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
			  `state` tinyint(2) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`),
			  KEY `idx_state` (`state`),
			  KEY `idx_doctor_id` (`doctor_id`),
			  KEY `idx_created_by` (`created_by`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

			$this->db->setQuery($query);
			$this->db->query();
		}

		if (!$this->db->tableExists('#__drwho_character_seasons'))
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__drwho_character_seasons` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `character_id` int(11) unsigned NOT NULL DEFAULT '0',
			  `season_id` int(11) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`meta_id`),
			  KEY `idx_character_id` (`character_id`),
			  KEY `idx_season_id` (`season_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

			$this->db->setQuery($query);
			$this->db->query();
		}

		$this->addComponentEntry('drwho');
	}

	/**
	 * Down
	 **/
	public function down()
	{
		if ($this->db->tableExists('#__drwho_character_seasons'))
		{
			$query = "DROP TABLE #__drwho_character_seasons";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__drwho_characters'))
		{
			$query = "DROP TABLE #__drwho_characters";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__drwho_seasons'))
		{
			$query = "DROP TABLE #__drwho_seasons";
			$this->db->setQuery($query);
			$this->db->query();
		}

		$this->deleteComponentEntry('drwho');
	}
}
