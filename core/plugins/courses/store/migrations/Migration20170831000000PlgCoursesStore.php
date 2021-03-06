<?php

use Hubzero\Content\Migration\Base;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for adding Courses - Store plugin
 **/
class Migration20170831000000PlgCoursesStore extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$this->addPluginEntry('courses', 'store', 0);
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$this->deletePluginEntry('courses', 'store');
	}
}
