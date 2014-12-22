<?php

use Hubzero\Content\Migration\Base;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Migration script for cleaning up encoded chars in resource titles & abstracts
 **/
class Migration20141222194817ComResources extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		// get all resources
		$this->db->setQuery("SELECT id, title, fulltxt FROM #__resources");
		$resources = $this->db->loadObjectList();

		// loop through each resource & update titles/abstracts
		foreach ($resources as $resource)
		{
			// hold on to original title and double decode
			$originalTitle = $resource->title;
			$fixedTitle    = html_entity_decode(html_entity_decode($resource->title));

			// hold on to original fulltxt and double decode
			$originalFulltxt = trim(preg_replace("#<nb:(.*?)>(.*?)</nb:(.*?)>#s", '', $resource->fulltxt));
			$fixedFulltxt    = html_entity_decode(html_entity_decode($originalFulltxt));
			
			// only update if items are different
			if ($originalTitle != $fixedTitle || $originalFulltxt != $fixedFulltxt)
			{
				// swap out the original abstract with the fixed
				$fixedFulltxt = str_replace($originalFulltxt, $fixedFulltxt, $resource->fulltxt);

				// update record
				$sql = "UPDATE `#__resources` SET `title`=" . $this->db->quote($fixedTitle) . ", `fulltxt`=" . $this->db->quote($fixedFulltxt) . " WHERE `id`=" . $this->db->quote($resource->id);
				$this->db->setQuery($sql);
				$this->db->query();
			}
		}
	}
}