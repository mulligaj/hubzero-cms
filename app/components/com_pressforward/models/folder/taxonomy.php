<?php
namespace Components\PressForward\Models\Folder;

use Hubzero\Database\Relational;

/**
 * Model class for folder taxonomy
 */
class Taxonomy extends Relational
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
	protected $table = '#__pf_term_taxonomy';

	/**
	 * The table primary key name
	 *
	 * It defaults to 'id', but can be overwritten by a subclass.
	 *
	 * @var  string
	 **/
	protected $pk = 'term_taxonomy_id';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public $orderBy = 'term_taxonomy_id';

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
		'taxonomy' => 'notempty'
	);

	/**
	 * The name of the taxonomy type
	 *
	 * @var  string
	 */
	public static $term_type = 'pf_feed_category';

	/**
	 * Generates automatic post_name field value
	 *
	 * @param   array   $data  the data being saved
	 * @return  string
	 */
	public function automaticTaxonomy($data)
	{
		$alias = $data['taxonomy'];
		$alias = strip_tags($alias);
		$alias = trim($alias);
		if (strlen($alias) > 200)
		{
			$alias = substr($alias . ' ', 0, 200);
			$alias = substr($alias, 0, strrpos($alias,' '));
		}
		$alias = str_replace(' ', '_', $alias);

		return preg_replace("/[^a-zA-Z0-9_]/", '', strtolower($alias));
	}

	/**
	 * Returns all rows (unless otherwise limited)
	 *
	 * @param   string|array  $columns  The columns to select
	 * @return  \Hubzero\Database\Relational|static
	 */
	public static function all($columns = null)
	{
		return self::blank()->whereEquals('taxonomy', static::$term_type);
	}

	/**
	 * Get metadata for this post
	 *
	 * @return  object
	 */
	public function folder()
	{
		return $this->oneToOne('Components\PressForward\Models\Folder', 'term_id');
	}

	/**
	 * Get a list of child entries for this entry
	 *
	 * @return  object
	 */
	public function children()
	{
		return $this->oneToMany('Taxonomy', 'parent');
	}

	/**
	 * Get a parent entry
	 *
	 * @return  object
	 */
	public function parent()
	{
		return $this->oneToOne('Taxonomy', 'term_taxonomy_id', 'parent');
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @return  boolean  False if error, True on success
	 */
	public function destroy()
	{
		// Remove comments
		foreach ($this->children()->rows() as $child)
		{
			if (!$child->destroy())
			{
				$this->addError($child->getError());
				return false;
			}
		}

		$folder = $this->folder;

		// Attempt to delete the record
		$result = parent::destroy();

		if ($result)
		{
			if (!$folder->destroy())
			{
				$this->addError($folder->getError());
				return false;
			}
		}

		return $result;
	}
}
