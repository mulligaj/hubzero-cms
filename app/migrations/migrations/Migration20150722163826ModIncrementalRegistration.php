<?php

use Hubzero\Content\Migration\Base;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Migration script for ...
 **/
class Migration20150722163826ModIncrementalRegistration extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$this->db->setQuery('CREATE UNIQUE INDEX incremental_registration_labels_field_uidx ON #__incremental_registration_labels(field)');
		$this->db->query();
		$this->db->setQuery("INSERT IGNORE INTO #__incremental_registration_labels(field, label) VALUES ('name', 'Full Name'), ('orgtype', 'Organization Type'), ('organization', 'Organization'), ('countryresident', 'Country of Residence'), ('countryorigin', 'Country of Origin'), ('gender', 'Gender'), ('url', 'URL'), ('reason', 'Reason for using the hub'), ('race', 'Race'), ('phone', 'Phone Number'), ('picture', 'Profile Picture'), ('disability', 'Disability'), ('mailPreferenceOption', 'E-Mail Updates'), ('location', 'Postal Code')");
		$this->db->query();
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$this->db->setQuery('DROP INDEX incremental_registration_labels_field_uidx ON #__incremental_registration_labels');
		$this->db->query();
	}
}
