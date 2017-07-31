<?php
// Declare the namespace.
namespace Components\Contracts\Site\Controllers;

use Hubzero\Component\SiteController;
use Components\Contracts\Models\Contract;

class Contracts extends SiteController
{
	/**
	 * Default task.
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		$contractId = Request::getVar('id', 0);
		if (is_numeric($contractId))
		{
			$contract = Contract::oneOrFail($contractId);
		}
		else
		{
			$contract = Contract::oneByAlias($contractId);
		}
		$this->view->set('contract', $contract)->display();
	}

	public function saveTask()
	{
		print_r(Request::request());
		exit();
	}
}
