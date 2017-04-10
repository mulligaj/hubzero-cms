<?php
namespace Components\PressForward\Models\Folder;

use Hubzero\Database\Relational;

/**
 * Model class for a folder relationship
 */
class Relationship extends Relational
{
	/**
	 * The table namespace
	 *
	 * @var  string
	 */
	protected $namespace = 'pf_term';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public $orderBy = 'term_order';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'asc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'object_id'        => 'positive|nonzero',
		'term_taxonomy_id' => 'positive|nonzero'
	);

	/**
	 * Get post
	 *
	 * @return  object
	 */
	public function post()
	{
		return $this->oneToOne('Components\PressForward\Models\Post', 'object_id');
	}

	/**
	 * Get taxonomy
	 *
	 * @return  object
	 */
	public function taxonomy()
	{
		return $this->oneToOne('Taxonomy', 'term_taxonomy_id');
	}
}
