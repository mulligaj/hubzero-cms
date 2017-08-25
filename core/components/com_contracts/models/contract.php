<?php
namespace Components\Contracts\Models;

require __DIR__ . '/page.php';
require __DIR__ . '/agreement.php';
use Hubzero\Database\Relational;
use Hubzero\Utility\String;
use Session;
use Date;

/**
 * Drwho model class for a character
 */
class Contract extends Relational
{

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public $orderBy = 'title';

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'title' => 'notempty',
		'alias' => 'notempty',
	);

	public $initiate = array(
		'created',
		'created_by'
	);

	public $always = array(
		'modified',
		'modified_by'
	);


	public function automaticModified($data)
	{
		return Date::of()->toSql();
	}

	public function automaticModifiedBy($data)
	{
		return User::getInstance()->get('id');
	}

	public function pages()
	{
		return $this->oneToMany('Page', 'contract_id')->order('ordering', 'ASC');
	}

	public function contacts()
	{
		return $this->manyToMany('Hubzero\User\User', '#__contract_contacts', 'contract_id', 'user_id');
	}

	public function contactsAutoComplete()
	{
		$contacts = array();
		foreach ($this->contacts as $contact)
		{
			$contacts[] = $contact->name . ' (' . $contact->id . ')';
		}
		return implode(',', $contacts);
	}

	public function agreements()
	{
		return $this->oneToMany('Agreement', 'contract_id')->order('created', 'DESC');
	}

	public function template()
	{
		$template = '';
		foreach ($this->pages as $page)
		{
			$template .= $page->content;	
		}
		return $template;
	}


	/**
	 * Defines a belongs to one relationship
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsToOne('Hubzero\User\User', 'created_by');
	}

	/**
	 * Deletes the existing/current model
	 *
	 * @return  bool
	 */
	public function destroy()
	{
		if (!$this->contacts()->sync(array()))
		{
			return false;
		}

		if (!$this->pages->destroyAll())
		{
			return false;
		}

		if (!$this->agreements->destroyAll())
		{
			return false;
		}

		return parent::destroy();
	}
}
