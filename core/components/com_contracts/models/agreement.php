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
		'authority' => 'notempty'
	);

	public $initiate = array(
		'created'
	);

	public $always = array(
		'modified'
	);

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

	public function automaticModified($data)
	{
		return isset($data['modified']) && $data['modified'] ? $data['modified'] : Date::toSql(); 
	}
}
