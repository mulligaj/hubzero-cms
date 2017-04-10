<?php
namespace Components\PressForward\Models;

use Hubzero\Database\Relational;

/**
 * Model class for a comment's meta data
 */
class Commentmeta extends Relational
{
	/**
	 * The table namespace
	 *
	 * @var  string
	 */
	protected $namespace = 'pf';

	/**
	 * The table to which the class pertains
	 *
	 * This will default to #__{namespace}_{modelName} unless otherwise
	 * overwritten by a given subclass. Definition of this property likely
	 * indicates some derivation from standard naming conventions.
	 *
	 * @var  string
	 **/
	protected $table = '#__pf_commentmeta';

	/**
	 * The table primary key name
	 *
	 * It defaults to 'id', but can be overwritten by a subclass.
	 *
	 * @var  string
	 **/
	protected $pk = 'meta_id';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public $orderBy = 'meta_key';

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
		'meta_key'   => 'notempty',
		'comment_id' => 'positive|nonzero'
	);

	/**
	 * Get parent comment
	 *
	 * @return  object
	 */
	public function comment()
	{
		return $this->belongsToOne('Comment', 'comment_id');
	}
}
