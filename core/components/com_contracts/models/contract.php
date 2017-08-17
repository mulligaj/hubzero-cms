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

	public function contact()
	{
		return $this->belongsToOne('\Hubzero\User\User', 'contact_id');
	}

	public function pages()
	{
		return $this->oneToMany('Page', 'contract_id')->order('ordering', 'ASC');
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
		if (!$this->seasons()->sync(array()))
		{
			return false;
		}

		return parent::destroy();
	}

}
