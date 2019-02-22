<?php

use Hubzero\Content\Migration\Base;

// No direct access
defined('_HZEXEC_') or die();

/**
 * Migration script for adding component entry for com_search
 **/
class Migration20170831000000ComSearch extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$this->addComponentEntry('search', null, 1, '', false);
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$this->deleteComponentEntry('search');
	}
}