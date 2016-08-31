<?php

use Hubzero\Content\Migration\Base;
use Components\Feedaggregator\Models\Post;

/**
 * Migration script for converting the timestamps in the created field to
 * standard format
 **/

class Migration20160208183103ComFeedaggregator extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		if ($this->db->tableExists('#__feedaggregator_posts'))
		{
			require_once(PATH_CORE . DS . 'components' . DS . 'com_feedaggregator' . DS . 'models' . DS . 'post.php');

			// Grab rows first
			$rows = Post::all()->rows();

			// Convert the field
			$query = "ALTER TABLE `#__feedaggregator_posts` MODIFY created DATETIME;";
			$this->db->setQuery($query);
			$this->db->query();

			// Convert each timestamp into SQL date format
			foreach ($rows as $row)
			{
				$row->set('created', Date::of(date("F j, Y, g:i a", $row->created))->toSql());
				$row->save();
			}
		}
	}

	/**
	 * Down
	 **/
	public function down()
	{
		if ($this->db->tableExists('#__feedaggregator_posts'))
		{
			require_once(PATH_CORE . DS . 'components' . DS . 'com_feedaggregator' . DS . 'models' . DS . 'post.php');

			// Grab rows first
			$rows = Post::all()->rows();

			// Convert the field
			$query = "ALTER TABLE `#__feedaggregator_posts` MODIFY created INT(11);";
			$this->db->setQuery($query);
			$this->db->query();

			// Convert each timestamp into SQL date format
			foreach ($rows as $row)
			{
				$row->set('created', Date::of($row->created)->toUnix());
				$row->save();
			}
		}
	}
}
