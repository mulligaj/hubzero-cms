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

	public function document()
	{
		if (empty($this->document))
		{
			$this->document = $this->_replacePlaceholders();
		}
		return $this->document;
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

	private function _replacePlaceholders()
	{
		$document = preg_replace_callback("/{{([^}{]*)}}/", function($matches){
			$property = trim(strtolower($matches[1]));
			return $this->$property;
		}, $this->contract->template());
		return $document;
	}

	public function automaticModified($data)
	{
		return isset($data['modified']) && $data['modified'] ? $data['modified'] : Date::toSql(); 
	}

	public function transformCreated()
	{
		$dateString = $this->get('created');
		$date = Date::of($dateString)->toLocal('F jS, Y g:h A');
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
