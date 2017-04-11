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
		return $this->oneToOne(__NAMESPACE__ . '\\Taxonomy', 'term_taxonomy_id');
	}

	/**
	 * Get an entry by $object_id and $term_taxonomy_id
	 *
	 * @return  object
	 */
	public static function oneByObjectAndTerm($object_id, $term_taxonomy_id)
	{
		return self::all()
			->whereEquals('object_id', $object_id)
			->whereEquals('term_taxonomy_id', $term_taxonomy_id)
			->row();
	}

	/**
	 * Determines if the current model is new by looking for the presence of a primary key attribute
	 *
	 * @return  bool
	 */
	public function isNew()
	{
		return ((!$this->hasAttribute($this->get('object_id')) || !$this->object_id) && (!$this->hasAttribute($this->get('term_taxonomy_id')) || !$this->term_taxonomy_id));
	}

	/**
	 * Saves the current model to the database
	 *
	 * @return  bool
	 */
	public function save()
	{
		// Validate
		if (!$this->validate())
		{
			return false;
		}

		// See if we're creating or updating
		$method = $this->isNew() ? 'create' : 'modify';
		$result = $this->$method($this->getAttributes());

		// If creating, result is our new id, so set that back on the model
		if ($this->isNew())
		{
			Event::trigger($this->getTableName() . '_new', ['model' => $this]);
		}

		Event::trigger('system.onContentSave', array($this->getTableName(), $this));

		return $result;
	}

	/**
	 * Inserts a new row into the database
	 *
	 * @return  bool
	 * @since   2.0.0
	 **/
	private function create()
	{
		return $this->getQuery()->push($this->getTableName(), $this->getAttributes());
	}

	/**
	 * Updates an existing item in the database
	 *
	 * @return  bool
	 */
	private function modify()
	{
		// Add any automatic fields
		$this->parseAutomatics('renew');

		// Add insert statement
		$query = $this->getQuery()
			->update($this->getTableName())
			->set($this->getAttributes())
			->whereEquals('object_id', $this->get('object_id'))
			->whereEquals('term_taxonomy_id', $this->get('term_taxonomy_id'));

		// Return the result of the query
		return $query->execute();
	}

	/**
	 * Deletes the existing/current model
	 *
	 * @return  bool
	 */
	public function destroy()
	{
		$query = $this->getQuery()
			->delete($this->getTableName())
			->whereEquals('object_id', $this->get('object_id'))
			->whereEquals('term_taxonomy_id', $this->get('term_taxonomy_id'));

		// Return result of the query
		return $query->execute();
	}
}
