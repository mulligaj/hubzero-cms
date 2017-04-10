<?php
namespace Components\PressForward\Admin\Controllers;

use Hubzero\Component\AdminController;
/**
 * PressForward controller for logs
 */
class Logs extends AdminController
{
	/**
	 * Execute a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		parent::execute();
	}

	/**
	 * Display logs
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Output the view
		$this->view
			->display();
	}
}
