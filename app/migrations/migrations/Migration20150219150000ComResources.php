<?php

use Hubzero\Content\Migration\Base;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Migration script for adding back the series type resource for HabriCentral
 **/
class Migration20150219150000ComResources extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		//query for result to see if result exists
		$query = "SELECT alias FROM `#__resource_types` WHERE id = 31;";
		$this->db->setQuery($query);
		$alias = $this->db->loadResult();

		if ($this->db->tableExists('#__resource_types'))
		{
			$query = "INSERT INTO `#__resource_types` (`id`,`alias`,`type`,`category`,`description`,`contributable`,`customFields`,`params`)
				VALUES (31,'series','Series',27,'Series are collections of lectures, publications, and other resources presented as a list.  Each series is available as a podcast feed.'
					,0,'credits=Credits=textarea=0\nsponsoredby=Sponsored by=textarea=0\nreferences=References=textarea=0','plg_citations=0\nplg_questions=0\nplg_recommendations=1\nplg_related=1\n
					plg_reviews=1\nplg_usage=0\nplg_versions=0\nplg_favorite=1\nplg_share=1\nplg_wishlist=0\nplg_supportingdocs=1\nplg_about=1\nplg_abouttool=0');";

			$this->db->setQuery($query);
			$this->db->query();
		}
	}

}