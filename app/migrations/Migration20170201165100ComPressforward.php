<?php

use Hubzero\Content\Migration\Base;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for installing tables for pressforward component
 **/
class Migration20170201165100ComPressforward extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		if (!$this->db->tableExists('#__pf_commentmeta'))
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__pf_commentmeta` (
			  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `comment_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			  `meta_key` varchar(255) DEFAULT NULL,
			  `meta_value` longtext,
			  PRIMARY KEY (`meta_id`),
			  KEY `comment_id` (`comment_id`),
			  KEY `meta_key` (`meta_key`(191))
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

			$this->db->setQuery($query);
			$this->db->query();
		}

		if (!$this->db->tableExists('#__pf_comments'))
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__pf_comments` (
			  `comment_ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `comment_post_ID` bigint(20) unsigned NOT NULL DEFAULT '0',
			  `comment_author` tinytext NOT NULL,
			  `comment_author_email` varchar(100) NOT NULL DEFAULT '',
			  `comment_author_url` varchar(200) NOT NULL DEFAULT '',
			  `comment_author_IP` varchar(100) NOT NULL DEFAULT '',
			  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `comment_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `comment_content` text NOT NULL,
			  `comment_karma` int(11) NOT NULL DEFAULT '0',
			  `comment_approved` varchar(20) NOT NULL DEFAULT '1',
			  `comment_agent` varchar(255) NOT NULL DEFAULT '',
			  `comment_type` varchar(20) NOT NULL DEFAULT '',
			  `comment_parent` bigint(20) unsigned NOT NULL DEFAULT '0',
			  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`comment_ID`),
			  KEY `comment_post_ID` (`comment_post_ID`),
			  KEY `comment_approved_date_gmt` (`comment_approved`,`comment_date_gmt`),
			  KEY `comment_date_gmt` (`comment_date_gmt`),
			  KEY `comment_parent` (`comment_parent`),
			  KEY `comment_author_email` (`comment_author_email`(10))
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

			$this->db->setQuery($query);
			$this->db->query();
		}

		if (!$this->db->tableExists('#__pf_postmeta'))
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__pf_postmeta` (
			  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `post_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			  `meta_key` varchar(255) DEFAULT NULL,
			  `meta_value` longtext,
			  PRIMARY KEY (`meta_id`),
			  KEY `post_id` (`post_id`),
			  KEY `meta_key` (`meta_key`(191))
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

			$this->db->setQuery($query);
			$this->db->query();
		}

		if (!$this->db->tableExists('#__pf_posts'))
		{
			$query = "CREATE TABLE `#__pf_posts` (
			  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `post_author` bigint(20) unsigned NOT NULL DEFAULT '0',
			  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `post_content` longtext NOT NULL,
			  `post_title` text NOT NULL,
			  `post_excerpt` text NOT NULL,
			  `post_status` varchar(20) NOT NULL DEFAULT 'publish',
			  `comment_status` varchar(20) NOT NULL DEFAULT 'open',
			  `ping_status` varchar(20) NOT NULL DEFAULT 'open',
			  `post_password` varchar(20) NOT NULL DEFAULT '',
			  `post_name` varchar(200) NOT NULL DEFAULT '',
			  `to_ping` text NOT NULL,
			  `pinged` text NOT NULL,
			  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `post_content_filtered` longtext NOT NULL,
			  `post_parent` bigint(20) unsigned NOT NULL DEFAULT '0',
			  `guid` varchar(255) NOT NULL DEFAULT '',
			  `menu_order` int(11) NOT NULL DEFAULT '0',
			  `post_type` varchar(20) NOT NULL DEFAULT 'post',
			  `post_mime_type` varchar(100) NOT NULL DEFAULT '',
			  `comment_count` bigint(20) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`ID`),
			  KEY `post_name` (`post_name`(191)),
			  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
			  KEY `post_parent` (`post_parent`),
			  KEY `post_author` (`post_author`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

			$this->db->setQuery($query);
			$this->db->query();
		}

		if (!$this->db->tableExists('#__pf_relationships'))
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__pf_relationships` (
			  `id` bigint(20) NOT NULL AUTO_INCREMENT,
			  `user_id` bigint(20) NOT NULL,
			  `item_id` bigint(20) NOT NULL,
			  `relationship_type` smallint(5) NOT NULL,
			  `value` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  KEY `user_id` (`user_id`),
			  KEY `item_id` (`item_id`),
			  KEY `relationship_type` (`relationship_type`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

			$this->db->setQuery($query);
			$this->db->query();
		}

		if (!$this->db->tableExists('#__pf_term_relationships'))
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__pf_term_relationships` (
			  `object_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			  `term_taxonomy_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			  `term_order` int(11) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`object_id`,`term_taxonomy_id`),
			  KEY `term_taxonomy_id` (`term_taxonomy_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

			$this->db->setQuery($query);
			$this->db->query();
		}

		if (!$this->db->tableExists('#__pf_term_taxonomy'))
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__pf_term_taxonomy` (
			  `term_taxonomy_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			  `taxonomy` varchar(32) NOT NULL DEFAULT '',
			  `description` longtext NOT NULL,
			  `parent` bigint(20) unsigned NOT NULL DEFAULT '0',
			  `count` bigint(20) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`term_taxonomy_id`),
			  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
			  KEY `taxonomy` (`taxonomy`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

			$this->db->setQuery($query);
			$this->db->query();
		}

		if (!$this->db->tableExists('#__pf_terms'))
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__pf_terms` (
			  `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `name` varchar(200) NOT NULL DEFAULT '',
			  `slug` varchar(200) NOT NULL DEFAULT '',
			  `term_group` bigint(10) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`term_id`),
			  KEY `slug` (`slug`(191)),
			  KEY `name` (`name`(191))
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

			$this->db->setQuery($query);
			$this->db->query();
		}

		$this->addComponentEntry('pressforward');
	}

	/**
	 * Down
	 **/
	public function down()
	{
		if ($this->db->tableExists('#__pf_commentmeta'))
		{
			$query = "DROP TABLE #__pf_commentmeta";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__pf_comments'))
		{
			$query = "DROP TABLE #__pf_comments";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__pf_postmeta'))
		{
			$query = "DROP TABLE #__pf_postmeta";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__pf_posts'))
		{
			$query = "DROP TABLE #__pf_posts";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__pf_relationships'))
		{
			$query = "DROP TABLE #__pf_relationships";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__pf_term_relationships'))
		{
			$query = "DROP TABLE #__pf_term_relationships";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__pf_term_taxonomy'))
		{
			$query = "DROP TABLE #__pf_term_taxonomy";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__pf_terms'))
		{
			$query = "DROP TABLE #__pf_terms";
			$this->db->setQuery($query);
			$this->db->query();
		}

		$this->deleteComponentEntry('pressforward');
	}
}
