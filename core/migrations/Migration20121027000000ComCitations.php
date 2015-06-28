<?php

use Hubzero\Content\Migration\Base;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for adding missing fields to citations
 **/
class Migration20121027000000ComCitations extends Base
{
	public function up()
	{
		$query = "ALTER TABLE `#__citations` MODIFY `type` varchar(30) DEFAULT NULL AFTER `uid`;\n";

		if (!$this->db->tableHasField('#__citations', 'language'))
		{
			$query .= "ALTER TABLE `#__citations` ADD `language` varchar(100) DEFAULT NULL;\n";
		}
		if (!$this->db->tableHasField('#__citations', 'accession_number'))
		{
			$query .= "ALTER TABLE `#__citations` ADD `accession_number` varchar(100) DEFAULT NULL;\n";
		}
		if (!$this->db->tableHasField('#__citations', 'short_title'))
		{
			$query .= "ALTER TABLE `#__citations` ADD `short_title` varchar(250) DEFAULT NULL;\n";
		}
		if (!$this->db->tableHasField('#__citations', 'author_address'))
		{
			$query .= "ALTER TABLE `#__citations` ADD `author_address` text;\n";
		}
		if (!$this->db->tableHasField('#__citations', 'keywords'))
		{
			$query .= "ALTER TABLE `#__citations` ADD `keywords` text;\n";
		}
		if (!$this->db->tableHasField('#__citations', 'abstract'))
		{
			$query .= "ALTER TABLE `#__citations` ADD `abstract` text;\n";
		}
		if (!$this->db->tableHasField('#__citations', 'call_number'))
		{
			$query .= "ALTER TABLE `#__citations` ADD `call_number` varchar(100) DEFAULT NULL;\n";
		}
		if (!$this->db->tableHasField('#__citations', 'label'))
		{
			$query .= "ALTER TABLE `#__citations` ADD `label` varchar(100) DEFAULT NULL;\n";
		}
		if (!$this->db->tableHasField('#__citations', 'research_notes'))
		{
			$query .= "ALTER TABLE `#__citations` ADD `research_notes` text;\n";
		}
		if (!$this->db->tableHasField('#__citations', 'params'))
		{
			$query .= "ALTER TABLE `#__citations` ADD `params` text;\n";
		}
		if (!$this->db->tableHasKey('#__citations', 'ftidx_title_isbn_doi_abstract_author_publisher'))
		{
			$query .= "CREATE FULLTEXT INDEX ftidx_title_isbn_doi_abstract_author_publisher ON `#__citations` (title,isbn,doi,abstract,author,publisher);\n";
		}

		if (!empty($query))
		{
			$this->db->setQuery($query);
			$this->db->query();
		}
	}
}
