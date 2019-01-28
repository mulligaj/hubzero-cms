<?php

use Hubzero\Content\Migration\Base;

/**
 * Migration script for installing quickicon module
 **/
class Migration20190109000000ModQuickicon extends Base
{
	/**
	 * Up
	 **/
	public function up()
	{
		$this->addModuleEntry('mod_quickicon', 1, '', 1);
	}

	/**
	 * Down
	 **/
	public function down()
	{
		$this->deleteModuleEntry('mod_quickicon');
	}
}