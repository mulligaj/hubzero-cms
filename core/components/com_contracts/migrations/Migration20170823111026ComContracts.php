<?php

use Hubzero\Content\Migration\Base;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for adding 'derivatives' field to publication licenses
 **/
class Migration20170823111026ComContracts extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		if (!$this->db->tableExists('#__contracts'))
		{
			$query = "CREATE TABLE `jos_contracts` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`title` varchar(100) NOT NULL,
				`alias` varchar(45) NOT NULL,
				`created` datetime NOT NULL,
				`modified` datetime NOT NULL,
				`created_by` int(10) unsigned NOT NULL,
				`modified_by` int(10) unsigned NOT NULL,
				`accepted_message` text,
				`manual_message` text,
				PRIMARY KEY (`id`)
				);";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if (!$this->db->tableExists('#__contract_agreements'))
		{
			$query = "CREATE TABLE `jos_contract_agreements` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`firstname` varchar(200) NOT NULL,
				`lastname` varchar(200) CHARACTER SET big5 NOT NULL,
				`email` varchar(256) NOT NULL,
				`organization_name` varchar(256) DEFAULT NULL,
				`organization_address` text,
				`authority` int(11) NOT NULL,
				`accepted` int(11) NOT NULL,
				`created` datetime NOT NULL,
				`modified` datetime NOT NULL,
				`contract_id` int(11) NOT NULL,
				PRIMARY KEY (`id`));";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if (!$this->db->tableExists('#__contract_pages'))
		{
			$query = "CREATE TABLE `jos_contract_pages` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`contract_id` int(10) unsigned NOT NULL,
				`ordering` int(10) unsigned DEFAULT NULL,
				`content` text NOT NULL,
				PRIMARY KEY (`id`)
				);";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if (!$this->db->tableExists('#__contract_contacts'))
		{
			$query = "CREATE TABLE `jos_contract_contacts` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`contract_id` int(10) unsigned NOT NULL,
				`user_id` int(10) unsigned NOT NULL,
				PRIMARY KEY (`id`)
				);";
			$this->db->setQuery($query);
			$this->db->query();
		}

		$this->addComponentEntry('contracts');
	}

	/**
	 * Down
	 **/
	public function down()
	{
		if ($this->db->tableExists('#__contracts'))
		{
			$query = "DROP TABLE `#__contracts`;";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__contract_agreements'))
		{
			$query = "DROP TABLE `#__contract_agreements`;";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__contract_pages'))
		{
			$query = "DROP TABLE `#__contract_pages`;";
			$this->db->setQuery($query);
			$this->db->query();
		}

		if ($this->db->tableExists('#__contract_contacts'))
		{
			$query = "DROP TABLE `#__contract_contacts`;";
			$this->db->setQuery($query);
			$this->db->query();
		}

		$this->deleteComponentEntry('contracts');
	}
}
