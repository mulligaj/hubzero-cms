<?php

use Hubzero\Content\Migration\Base;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for adding Editors Extd - Image plugin
 **/
class Migration20170831000000PlgEditorsextdImage extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$this->addPluginEntry('editors-xtd', 'image');
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$this->deletePluginEntry('editors-xtd', 'image');
	}
}
