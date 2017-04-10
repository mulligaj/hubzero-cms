<?php
namespace Components\PressForward\Models;

use Hubzero\Database\Relational;
use Components\PressForward\Models\Post;
use Components\PressForward\Models\Folder\Taxonomy;

include_once __DIR__ . '/folder/taxonomy.php';
include_once __DIR__ . '/folder/relationship.php';

/**
 * Model class for a folder
 */
class Folder extends Relational
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
	protected $table = '#__pf_terms';

	/**
	 * The table primary key name
	 *
	 * It defaults to 'id', but can be overwritten by a subclass.
	 *
	 * @var  string
	 **/
	protected $pk = 'term_id';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public $orderBy = 'term_id';

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
		'name' => 'notempty'
	);

	/**
	 * Automatically fillable fields
	 *
	 * @var  array
	 */
	public $always = array(
		'slug'
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
	public function automaticSlug($data)
	{
		$alias = (isset($data['slug']) && $data['slug'] ? $data['slug'] : $data['name']);
		$alias = strip_tags($alias);
		$alias = trim($alias);
		if (strlen($alias) > 200)
		{
			$alias = substr($alias . ' ', 0, 200);
			$alias = substr($alias, 0, strrpos($alias,' '));
		}
		$alias = str_replace(' ', '-', $alias);

		return preg_replace("/[^a-zA-Z0-9\-]/", '', strtolower($alias));
	}

	/**
	 * Get metadata for this post
	 *
	 * @return  object
	 */
	public function meta()
	{
		return $this->oneToMany('Termmeta', 'term_id');
	}

	/**
	 * Get a list of child entries for this entry
	 *
	 * @return  object
	 */
	public function children()
	{
		return $this->oneToMany('Folder', 'parent');
	}

	/**
	 * Get a parent entry
	 *
	 * @return  object
	 */
	public function parent()
	{
		return $this->oneToOne('Folder', 'id', 'parent');
	}

	/**
	 * Get all folder taxonomies
	 *
	 * @return  object
	 */
	public static function taxonomy()
	{
		$entries = self::all();

		$a = $entries->getTableName();
		$b = Taxonomy::blank()->getTableName();

		$entries->join($b, $b . '.term_id', $a . '.term_id')
			->whereEquals($b . '.taxonomy', Taxonomy::$term_type);

		return $entries;
	}

	/**
	 * Get folder tree with a list of Feeds
	 *
	 * @return  object
	 */
	public static function treeWithFeeds()
	{
		$rows = Post::feeds()
			->ordered()
			->rows();

		$results = self::tree();

		if ($rows->count() > 0)
		{
			foreach ($rows as $row)
			{
				$entry = Taxonomy::blank();
				$entry->set('term_id', $row->get('ID'));
				$entry->set('name', $row->get('post_title'));
				$entry->set('description', $row->get('post_content'));
				$entry->set('slug', $row->get('post_name'));
				$entry->set('count', $row->children()->total());

				$results[] = $entry;
			}
		}

		return $results;
	}

	/**
	 * Get folder tree
	 *
	 * @return  object
	 */
	public static function tree()
	{
		$rows = Taxonomy::all()
			->including(['folder', function ($folder){
				$folder
					->select('*');
			}])
			->ordered()
			->rows();

		$results = array();

		if ($rows->count() > 0)
		{
			$children = array(
				0 => array()
			);

			foreach ($rows as $row)
			{
				$row->set('name', $row->folder->get('name'));
				$row->set('slug', $row->folder->get('slug'));

				$pt   = $row->get('parent');
				$list = @$children[$pt] ? $children[$pt] : array();

				array_push($list, $row);

				$children[$pt] = $list;
			}

			$results = self::treeRecurse($children[0], $children);
		}

		return $results;
	}

	/**
	 * Recursive function to build tree
	 *
	 * @param   array    $children  Container for parent/children mapping
	 * @param   array    $list      List of records
	 * @param   integer  $maxlevel  Maximum levels to descend
	 * @param   integer  $level     Indention level
	 * @return  void
	 */
	protected static function treeRecurse($children, $list, $maxlevel=9999, $level=0)
	{
		if ($level <= $maxlevel)
		{
			foreach ($children as $v => $child)
			{
				$folders = array();

				if (isset($list[$child->get('term_id')]))
				{
					$folders = self::treeRecurse($list[$child->get('term_id')], $list, $maxlevel, $level+1);
				}

				$children[$v]->set('children', $folders);
			}
		}
		return $children;
	}

	/**
	 * Get folder list
	 *
	 * @return  object
	 */
	public static function listing()
	{
		$rows = Taxonomy::all()
			->including(['folder', function ($folder){
				$folder
					->select('*');
			}])
			->ordered()
			->rows();

		$results = array();

		if ($rows->count() > 0)
		{
			$levellimit = 500;
			$list       = array();
			$children   = array();

			foreach ($rows as $row)
			{
				$row->set('name', $row->folder->get('name'));
				$row->set('slug', $row->folder->get('slug'));

				$pt   = $row->get('parent');
				$list = @$children[$pt] ? $children[$pt] : array();

				array_push($list, $row);

				$children[$pt] = $list;
			}

			$results = self::listRecurse(0, '', array(), $children, max(0, $levellimit-1));
		}

		return $results;
	}

	/**
	 * Recursive function to build tree
	 *
	 * @param   integer  $id        Parent ID
	 * @param   string   $indent    Indent text
	 * @param   array    $list      List of records
	 * @param   array    $children  Container for parent/children mapping
	 * @param   integer  $maxlevel  Maximum levels to descend
	 * @param   integer  $level     Indention level
	 * @param   integer  $type      Indention type
	 * @return  void
	 */
	protected static function listRecurse($id, $indent, $list, $children, $maxlevel=9999, $level=0, $type=0)
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $v)
			{
				$id = $v->get('term_taxonomy_id');

				if ($type)
				{
					$pre    = '<span class="gi treenode">|—</span>&nbsp;'; //&#x2517
					$spacer = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}
				else
				{
					$pre    = '- ';
					$spacer = '&nbsp;&nbsp;';
				}

				if ($v->get('parent') == 0)
				{
					$txt = '';
				}
				else
				{
					$txt = $pre;
				}
				$pt = $v->get('parent');

				$list[$id] = $v;
				$list[$id]->set('treename', "$indent$txt");
				$list[$id]->set('children', count(@$children[$id]));
				$list = self::listRecurse($id, $indent . $spacer, $list, $children, $maxlevel, $level+1);
			}
		}
		return $list;
	}
}
