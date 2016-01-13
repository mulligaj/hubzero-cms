<?php

use Hubzero\Content\Migration\Base;

/**
 * Migration script for untagging badges
 **/
class Migration20160113194516ComResources extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$query = "UPDATE `#__tags_object` SET tbl='resources' WHERE tbl='tags' AND label='badge';";
		$this->db->setQuery($query);
		$this->db->query();
	}

	/**
	 * Down
	 **/
	public function down()
	{
	 // nothing to do
	}
}
