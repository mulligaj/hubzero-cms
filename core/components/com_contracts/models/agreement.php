<?php
namespace Components\Contracts\Models;

use Hubzero\Database\Relational;
use Hubzero\Utility\String;
use Session;
use Date;

/**
 * Drwho model class for a character
 */
class Agreement extends Relational
{
	/**
	 *
	 * Namespace of table.
	 *
	 * @var string
	 */
	public $namespace = 'contract';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public $orderBy = 'lastname';

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'firstname' => 'notempty',
		'lastname' => 'notempty',
		'email' => 'notempty',
		'authority' => 'notempty',
		'organization_name' => 'notempty',
		'organization_address' => 'notempty'
	);

	public $initiate = array(
		'created'
	);

	public $always = array(
		'modified'
	);

	public $document = '';
	public $documentAttributes = array('firstname', 'lastname', 'email', 'organization_name', 'organization_address');

	public function setup()
	{
		$this->addRule('accepted', function($data){
			if ($data['authority'] == 1 && empty($data['accepted']))
			{
				return "Please read through the entire contract and pick an agreement option the last page.";
			}
			return false;
		});
		$this->addRule('authority', function($data){
			if (!is_numeric($data['authority']))
			{
				return "Please select whether or not you have authority to approve contracts.";
			}
			return false;
		});

		Event::addListener(function($event){
			$this->sendEmail();
		}, '#__contract_agreements_new');
	}

	/**
	 * Defines a belongs to one relationship
	 *
	 * @return  object
	 */
	public function contract()
	{
		return $this->belongsToOne('Contract', 'contract_id');
	}

	/**
	 * Returns the completed document based on the template created on the Contract.
	 *
	 * @return string
	 */
	public function document()
	{
		if (empty($this->document))
		{
			$this->document = $this->_replacePlaceholders($this->contract->template());
		}
		return $this->document;
	}

	/**
	 * Returns the completed accepted message based on the template created on the Contract.
	 *
	 * @return string
	 */
	public function transformAcceptedMessage()
	{
		$message = $this->_replacePlaceholders($this->contract->accepted_message);
		return $message;
	}

	/**
	 * Returns the completed manual process message based on the template created on the Contract.
	 *
	 * @return string
	 */
	public function transformManualMessage()
	{
		$message = $this->_replacePlaceholders($this->contract->manual_message);
		return $message;
	}

	/**
	 * Returns appropriate message based on accepted status
	 * @return string
	 */
	public function message()
	{
		$message = $this->get('accepted') == 1 ? $this->accepted_message : $this->manual_message;
		return $message;
	}

	public function hasDocumentAttributes()
	{
		foreach ($this->documentAttributes as $attribute)
		{
			if (!$this->get($attribute))
			{
				return false;
			}	
		}
		return true;
	}

	public function documentViewable()
	{
		if ($this->get('authority') == 1 && $this->hasDocumentAttributes())
		{
			return true;
		}
		return false;
	}

	public function getDocumentPdf($asString = false)
	{
		$pageHtml = $this->document();
		
		if (empty($pageHtml))
		{
			return false;
		}

		$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetTitle($this->title);

		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);

		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		$pdf->SetImageScale(PDF_IMAGE_SCALE_RATIO);

		$pdf->setFontSubsetting(true);

		$pdf->SetFont('dejavusans', '', 10, '', true);
		$pdf->AddPage();

		$pdf->writeHTML($pageHtml, true, false, true, false, '');
		if ($asString)
		{
			return $pdf->Output($this->contract->title . '.pdf', 'S');
		}
		else
		{
			header('Content-type: application/pdf');
			$pdf->Output($this->contract->title . '.pdf', 'I');
			exit();
		}
	}

	public function sendEmail()
	{
		$eview = new \Hubzero\Component\View(array(
			'name'   => 'emails',
			'layout' => 'success'
		));
		$subject  = Config::get('sitename') . ' ' . $this->contract->title . ' ' . Lang::txt('COM_CONTRACTS_EMAIL_SUBJECT');
		$from = Config::get('mailfrom');

		$eview->baseUrl = Request::base();
		$eview->sitename   = Config::get('sitename');
		$eview->option = $this->_option;
		$eview->config = $this->config;
		$eview->agreement   = $this;
		$template = $eview->loadTemplate();
		$email = new \Hubzero\Mail\Message();
		$attachment = new \Swift_Attachment($this->getDocumentPdf(true), $this->contract->title . '.pdf', 'application/pdf');

		$email->attach($attachment);
		$email->setFrom($from);
		$email->setTo($this->email);
		$email->setCc($this->contract->contacts->fieldsByKey('email'));
		$email->setSubject($subject);
		$email->setBody($template, 'text/html');
		//$transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 587, 'tls')
		//		->setUsername('')
		//	->setPassword('');
		$email->send();
	}	

	/**
	 * Replaces all values encapsulated in {{ }} with the appropriate agreement property value.
	 * @param   string   $template  the template provided that needs any placeholders provided replaced.
	 * @return string
	 */
	private function _replacePlaceholders($template)
	{
		$document = preg_replace_callback("/{{([^}{]*)}}/", function($matches){
			$property = trim(strtolower($matches[1]));
			return $this->$property;
		}, $template);
		return $document;
	}

	public function automaticModified($data)
	{
		return isset($data['modified']) && $data['modified'] ? $data['modified'] : Date::toSql(); 
	}

	public function transformCreated()
	{
		$dateString = $this->get('created');
		$date = Date::of($dateString)->toLocal('F jS, Y g:i A T');
		return $date;
	}

	public function transformAccepted()
	{
		$status = array(
			'-1' => 'Changes Required',
			'0' => 'No',
			'1' => 'Yes'
		);
		$statusId = (string) $this->get('accepted', 0);
		return $status[$statusId];
	}

	public function transformAuthority()
	{
		$authority = $this->get('authority', 0) == 1 ? 'Yes' : 'No';
		return $authority;
	}
}
