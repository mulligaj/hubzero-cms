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
		if (!($agreement instanceof Agreement))
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
			$agreement->set('contract_id', $contract->id);
		}

		$this->view->set('agreement', $agreement)
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
			if (Request::getVar('no_html') == 1)
			{
				if ($agreement->documentViewable())
				{
					$this->view->setLayout('add')
						->set('agreement', $agreement);
					$response = array(
						'showDocument' => true,
						'html' => $this->view->loadTemplate('agreement')
					);
				}
				else
				{
					$response = array('showDocument' => false);
				}
				header('Content-type: application/json');
				echo json_encode($response);
				exit();
			}
			Notify::warning($agreement->getError());
			$this->addTask($agreement);
			return;
		}	
		Notify::success('Successfully submitted contract.');
		$this->view
			->setLayout('success')
			->set('row', $agreement)
			->display();
	}
}
