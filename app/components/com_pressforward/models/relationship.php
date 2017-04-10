<?php
namespace Components\PressForward\Models;

use Hubzero\Database\Relational;

/**
 * Model class for a relationship
 */
class Relationship extends Relational
{
	/**
	 * The table namespace
	 *
	 * @var  string
	 */
	protected $namespace = 'pf';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public $orderBy = 'user_id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'asc';

	/**
	 * List of the relationship types
	 *
	 * @var  array
	 */
	public static $types = array(
		1 => 'read',
		2 => 'star',
		3 => 'archive',
		4 => 'nominate',
		5 => 'draft'
	);

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'user_id' => 'positive|nonzero',
		'item_id' => 'positive|nonzero',
		'relationship_type' => 'positive|nonzero'
	);

	/**
	 * Get integer value for relationship string type
	 *
	 * @param   string  $value
	 * @return  mixed   Integer upon success, FALSE if value not found
	 */
	public static function stringToInteger($value)
	{
		return array_search((string)$value, self::$types);
	}

	/**
	 * Get string value for relationship integer type
	 *
	 * @param   integer  $value
	 * @return  mixed    String upon success, FALSE if value not found
	 */
	public static function integerToString($value)
	{
		return (isset(self::$types[$value]) ? self::$types[$value] : false);
	}

	/**
	 * Get a record by user_id and item_id
	 *
	 * @param   integer  $user_id
	 * @param   integer  $item_id
	 * @param   integer  $type
	 * @return  object
	 */
	public static function oneByUserAndItem($user_id, $item_id, $type = null)
	{
		$model = self::all()
			->whereEquals('user_id', $user_id)
			->whereEquals('item_id', $item_id);

		if (!is_null($type))
		{
			$model->whereEquals('relationship_type', $type);
		}

		return $model
			->row();
	}
}
