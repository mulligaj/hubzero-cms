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
		//Request::checkToken();
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
		$eview = new \Hubzero\Component\View(array(
			'name'   => 'emails',
			'layout' => 'success'
		));
		$subject  = Config::get('sitename') .' '.Lang::txt('COM_MEMBERS_REGISTER_EMAIL_CONFIRMATION');

		$eview->baseUrl = Request::base();
		$eview->sitename   = Config::get('sitename');
		$eview->option = $this->_option;
		$eview->config = $this->config;
		$eview->agreement   = $agreement;
		$template = $eview->loadTemplate();
		$email = new \Hubzero\Mail\Message();
		$attachment = new \Swift_Attachment($agreement->getDocumentPdf(true), $agreement->contract->title . '.pdf', 'application/pdf');

		$email->attach($attachment);
		$email->setFrom('druidwithboots@gmail.com');
		$email->setTo($agreement->email);
		$email->setSubject($subject);
		$email->setBody($template, 'text/html');
		$transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 587, 'tls')
			->setUsername('druidwithboots@gmail.com')
			->setPassword('poqahvafyxwfjzqf');
		$email->send($transport);

		App::redirect(Route::url('index.php?option=' . $this->_option . '&task=add' . '&alias=' . $agreement->contract->alias));
	}

	public function emailmeTask()
	{
	}

	public function downloadTask()
	{
		$contractId = Request::getVar('alias', 0);
		if (is_numeric($contractId))
		{
			$agreement = Agreement::oneOrFail($contractId);
		}
		else
		{
			$contract = Contract::oneByAlias($contractId);
		}
		$agreement->getDocumentPdf();
	}

	public function emailTask()
	{

	}
}
