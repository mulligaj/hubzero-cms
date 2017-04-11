<?php
namespace Components\PressForward\Models;

use Hubzero\Database\Relational;
use Date;
use User;
use Lang;

include_once __DIR__ . '/postmeta.php';
include_once __DIR__ . '/folder.php';
include_once __DIR__ . '/comment.php';
include_once __DIR__ . '/relationship.php';
include_once __DIR__ . '/tags.php';

/**
 * Model class for a post
 */
class Post extends Relational
{
	/**
	 * The table namespace
	 *
	 * @var  string
	 */
	protected $namespace = 'pf';

	/**
	 * The table primary key name
	 *
	 * It defaults to 'id', but can be overwritten by a subclass.
	 *
	 * @var  string
	 **/
	protected $pk = 'ID';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public $orderBy = 'post_date_gmt';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'desc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'post_title' => 'notempty'
	);

	/**
	 * Automatically fillable fields
	 *
	 * @var  array
	 */
	public $always = array(
		'post_name'
	);

	/**
	 * Automatic fields to populate every time a row is created
	 *
	 * @var  array
	 */
	public $initiate = array(
		'post_author',
		'post_date_gmt'
	);

	/**
	 * The name of the feed post_type
	 *
	 * @var  string
	 */
	public static $feed_type = 'pf_feed';

	/**
	 * The name of the feed item post_type
	 *
	 * @var  string
	 */
	public static $post_type = 'pf_feed_item';

	/**
	 * The name of the feed item nomination
	 *
	 * @var  string
	 */
	public static $post_nomination = 'nomination';

	/**
	 * The slug for the taxonomy used by feed items
	 *
	 * @var  string
	 */
	public static $tag_taxonomy = 'pf_feed_item_tag';

	/**
	 * Generates automatic post_name field value
	 *
	 * @param   array   $data  the data being saved
	 * @return  string
	 */
	public function automaticPostName($data)
	{
		$alias = (isset($data['post_name']) && $data['post_name'] ? $data['post_name'] : $data['post_title']);
		$alias = strip_tags($alias);
		$alias = trim($alias);
		if (strlen($alias) > 200)
		{
			$alias = substr($alias . ' ', 0, 200);
			$alias = substr($alias, 0, strrpos($alias,' '));
		}
		$alias = str_replace(' ', '-', $alias);
		$alias = preg_replace('/\-+/', '-', $alias);

		return preg_replace("/[^a-zA-Z0-9\-]/", '', strtolower($alias));
	}

	/**
	 * Generates automatic post_author field value
	 *
	 * @return  integer
	 */
	public function automaticPostAuthor()
	{
		return User::get('id');
	}

	/**
	 * Generates automatic post_author field value
	 *
	 * @return  integer
	 */
	public function automaticPostDateGmt()
	{
		return Date::of('now')->toSql();
	}

	/**
	 * Get the creator of this entry
	 *
	 * @return  object
	 */
	public function author()
	{
		return $this->belongsToOne('Hubzero\User\User', 'post_author');
	}

	/**
	 * Get metadata for this post
	 *
	 * @return  object
	 */
	public function meta()
	{
		return $this->oneToMany(__NAMESPACE__ . '\\Postmeta', 'post_id');
	}

	/**
	 * Get a list of child entries for this entry
	 *
	 * @return  object
	 */
	public function children()
	{
		return $this->oneToMany(__NAMESPACE__ . '\\Post', 'post_parent');
	}

	/**
	 * Get a parent entry
	 *
	 * @return  object
	 */
	public function parent()
	{
		return $this->oneToOne(__NAMESPACE__ . '\\Post', 'id', 'post_parent');
	}

	/**
	 * Get comments
	 *
	 * @return  object
	 */
	public function comments()
	{
		return $this->oneToMany(__NAMESPACE__ . '\\Comment', 'comment_post_ID');
	}

	/**
	 * Get relationships
	 *
	 * @return  object
	 */
	public function relationships()
	{
		return $this->oneToMany(__NAMESPACE__ . '\\Relationship', 'item_id');
	}

	/**
	 * Get folders
	 *
	 * @return  object
	 */
	public function folders()
	{
		//return $this->oneToMany(__NAMESPACE__ . '\\Folder\\Relationship', 'object_id');
		$record = Folder::all();

		$a = $record->getTableName();
		$b = Folder\Taxonomy::blank()->getTableName();
		$r = Folder\Relationship::blank()->getTableName();

		return $record
			->select($a . '.*,' . $b . '.*')
			->join($b, $b . '.term_id', $a . '.term_id', 'inner')
			->join($r, $r . '.term_taxonomy_id', $b . '.term_taxonomy_id', 'inner')
			->whereEquals($b . '.taxonomy', Folder\Taxonomy::$term_type)
			->whereEquals($r . '.object_id', $this->get('ID'));
	}

	/**
	 * Was this post read?
	 *
	 * @param   integer  $user_id
	 * @return  boolean
	 */
	public function isRead($user_id = 0)
	{
		$user_id = $user_id ?: User::get('id');

		$count = $this->relationships()
			->whereEquals('user_id', $user_id)
			->whereEquals('relationship_type', Relationship::stringToInteger('read'))
			->total();

		return ($count ? true : false);
	}

	/**
	 * Was this post starred?
	 *
	 * @param   integer  $user_id
	 * @return  boolean
	 */
	public function isStarred($user_id = 0)
	{
		$user_id = $user_id ?: User::get('id');

		$count = $this->relationships()
			->whereEquals('user_id', User::get('id'))
			->whereEquals('relationship_type', Relationship::stringToInteger('star'))
			->total();

		return ($count ? true : false);
	}

	/**
	 * Was this post nominated?
	 *
	 * @param   integer  $user_id
	 * @return  boolean
	 */
	public function isNominated($user_id = 0)
	{
		$user_id = $user_id ?: User::get('id');

		$count = $this->relationships()
			->whereEquals('user_id', $user_id)
			->whereEquals('relationship_type', Relationship::stringToInteger('nominate'))
			->total();

		return ($count ? true : false);
	}

	/**
	 * Was this post archived?
	 *
	 * @param   integer  $user_id
	 * @return  boolean
	 */
	public function isArchived($user_id = 0)
	{
		$user_id = $user_id ?: User::get('id');

		$count = $this->relationships()
			->whereEquals('user_id', $user_id)
			->whereEquals('relationship_type', Relationship::stringToInteger('archive'))
			->total();

		return ($count ? true : false);
	}

	/**
	 * Get a list of folders
	 *
	 * @return  object
	 */
	/*public function taxonomy()
	{
		Taxonomy::all()
			->join($r, $r . '.')
			->whereEquals('object_id', $this->get('ID'))
			->order('term_order', 'asc');

		return $this->oneShiftsToMany('Relationship', 'post_parent');
	}*/

	/**
	 * Delete the record and all associated data
	 *
	 * @param   array    $metadata
	 * @return  boolean  False if error, True on success
	 */
	public function saveMetadata($metadata)
	{
		foreach ($metadata as $i => $data)
		{
			$meta = Postmeta::oneOrNew($i);
			$meta->set('post_id', $this->get('ID'));
			$meta->set('meta_key', $data['key']);

			if (is_object($data['value']) || is_array($data['value']))
			{
				$data['value'] = serialize($data['value']);
			}

			if (!$data['value'])
			{
				continue;
			}

			$meta->set('meta_value', $data['value']);

			if (!$meta->save())
			{
				$this->addError($meta->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @return  boolean  False if error, True on success
	 */
	public function destroy()
	{
		// Can't delete what doesn't exist
		if ($this->isNew())
		{
			return true;
		}

		// Remove comments
		foreach ($this->comments()->rows() as $comment)
		{
			if (!$comment->destroy())
			{
				$this->addError($comment->getError());
				return false;
			}
		}

		// Remove meta
		foreach ($this->meta()->rows() as $meta)
		{
			if (!$meta->destroy())
			{
				$this->addError($meta->getError());
				return false;
			}
		}

		// Remove relationships
		foreach ($this->relationships()->rows() as $relationship)
		{
			if (!$relationship->destroy())
			{
				$this->addError($relationship->getError());
				return false;
			}
		}

		return parent::destroy();
	}

	/**
	 * Get all feeds
	 *
	 * @return  object
	 */
	public static function feeds()
	{
		return self::all()
			->whereEquals('post_type', static::$feed_type);
	}

	/**
	 * Get all feed items
	 *
	 * @return  object
	 */
	public static function items()
	{
		return self::all()
			->whereEquals('post_type', static::$post_type);
	}

	/**
	 * Get all nominations
	 *
	 * @return  object
	 */
	public static function nominations()
	{
		return self::all()
			->whereEquals('post_type', static::$post_nomination);
	}

	/**
	 * Get all nominations
	 *
	 * @return  object
	 */
	public static function oneByAliasAndDate($post_name, $post_date_gmt)
	{
		$dt = Date::of($post_date_gmt);

		return self::all()
			->whereEquals('post_name', $post_name)
			->whereEquals('post_type', 'post')
			->whereEquals('post_status', 'publish')
			->where('post_date_gmt', '>', $dt->format('Y-m-d') . ' 00:00:00')
			->where('post_date_gmt', '<', $dt->modify('+1 Day')->format('Y-m-d') . ' 00:00:00')
			->row();
	}

	/**
	 * Return a formatted timestamp
	 *
	 * @param   string  $as  What format to return
	 * @return  string
	 */
	public function published($as='')
	{
		$as = strtolower($as);
		$dt = $this->get('post_date_gmt');

		if ($as == 'date')
		{
			$dt = Date::of($dt)->toLocal(Lang::txt('DATE_FORMAT_HZ1'));
		}
		else if ($as == 'time')
		{
			$dt = Date::of($dt)->toLocal(Lang::txt('TIME_FORMAT_HZ1'));
		}
		else if ($as)
		{
			$dt = Date::of($dt)->toLocal($as);
		}

		return $dt;
	}

	/**
	 * Generate and return various links to the entry
	 * Link will vary depending upon action desired, such as edit, delete, etc.
	 *
	 * @param   string  $type  The type of link to return
	 * @return  string
	 */
	public function link($type='')
	{
		$base = 'index.php?option=com_pressforward';

		$link  = $base;
		$link .= '&year=' . Date::of($this->get('post_date'))->format('Y');
		$link .= '&month=' . Date::of($this->get('post_date'))->format('m');
		$link .= '&day=' . Date::of($this->get('post_date'))->format('d');
		$link .= '&alias=' . $this->get('post_name');

		// If it doesn't exist or isn't published
		switch (strtolower($type))
		{
			case 'base':
				return $base;
			break;

			case 'edit':
				$link .= '&task=edit';
			break;

			case 'delete':
				$link .= '&task=delete';
			break;

			case 'comments':
				$link .= '#comments';
			break;

			case 'permalink':
			default:

			break;
		}

		return $link;
	}

		/**
	 * Get tags on an entry
	 *
	 * @param   string   $what   Data format to return (string, array, cloud)
	 * @param   integer  $admin  Get admin tags? 0=no, 1=yes
	 * @return  mixed
	 */
	public function tags($what='cloud', $admin=0)
	{
		if (!$this->get('ID'))
		{
			switch (strtolower($what))
			{
				case 'array':
					return array();
				break;

				case 'string':
				case 'cloud':
				case 'html':
				default:
					return '';
				break;
			}
		}

		$cloud = new Tags($this->get('ID'));

		return $cloud->render($what, array('admin' => $admin));
	}

	/**
	 * Tag the entry
	 *
	 * @param   string   $tags     Tags to apply
	 * @param   integer  $user_id  ID of tagger
	 * @param   integer  $admin    Tag as admin? 0=no, 1=yes
	 * @return  boolean
	 */
	public function tag($tags=null, $user_id=0, $admin=0)
	{
		$cloud = new Tags($this->get('ID'));

		return $cloud->setTags($tags, $user_id, $admin);
	}
}
