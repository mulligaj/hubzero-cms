<?php
// Declare the namespace.
namespace Components\Contracts\Site\Controllers;

use Hubzero\Component\SiteController;
use Components\Contracts\Models\Contract;
use Components\Contracts\Models\Agreement;

class Contracts extends SiteController
{
	/**
	 * Default task.
	 *
	 * @return  void
	 */
	public function addTask($agreement = null)
	{
		if ($agreement instanceof Agreement)
		{
			$contract = $agreement->contract;
		}
		else
		{
			$contractId = Request::getVar('alias', 0);
			if (is_numeric($contractId))
			{
				$contract = Contract::oneOrFail($contractId);
			}
			else
			{
				$contract = Contract::oneByAlias($contractId);
			}
			$agreement = Agreement::blank();
		}

		$this->view->set('contract', $contract)
					->set('agreement', $agreement)
					->setLayout('add')
					->display();
	}

	public function saveTask()
	{
		Request::checkToken();
		$attributes = array(
			'firstname' => Request::getVar('firstname'),
			'lastname' => Request::getVar('lastname'),
			'email' => Request::getVar('email'),
			'accepted' => Request::getVar('accepted', 0),
			'authority' => Request::getVar('authority'),
			'organization_name' => Request::getVar('organization_name'),
			'organization_address' => Request::getVar('organization_address'),
			'contract_id' => Request::getVar('contract_id', 0)
		);

		$agreement = Agreement::blank();
		$agreement->set($attributes);
		if (!$agreement->save())
		{
			foreach ($agreement->getErrors() as $error)
			{
				Notify::error($error);
			}
			$this->addTask($agreement);
			return;
		}	
		Notify::success('Successfully submitted contract.');
		App::redirect(Route::url('index.php?option=' . $this->_option . '&task=add' . '&alias=' . $agreement->contract->alias));
	}
}
