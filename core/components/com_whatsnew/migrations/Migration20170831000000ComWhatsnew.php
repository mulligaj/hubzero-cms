<?php

use Hubzero\Content\Migration\Base;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for adding component entry for com_whatsnew
 **/
class Migration20170831000000ComWhatsnew extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$this->addComponentEntry('whatsnew');
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$this->deleteComponentEntry('whatsnew');
	}
}
