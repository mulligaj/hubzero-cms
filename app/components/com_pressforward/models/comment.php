<?php
namespace Components\PressForward\Models;

use Hubzero\Database\Relational;
use Request;
use Date;
use User;

include_once __DIR__ . '/commentmeta.php';

/**
 * PressForward model for a comment
 */
class Comment extends Relational
{
	/**
	 * The table namespace
	 *
	 * @var string
	 */
	protected $namespace = 'pf';

	/**
	 * The table primary key name
	 *
	 * It defaults to 'id', but can be overwritten by a subclass.
	 *
	 * @var  string
	 **/
	protected $pk = 'comment_ID';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public $orderBy = 'comment_date_gmt';

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
		'comment_content' => 'notempty',
		'comment_post_ID' => 'positive|nonzero'
	);

	/**
	 * Automatic fields to populate every time a row is created
	 *
	 * @var  array
	 */
	public $initiate = array(
		'comment_date_gmt',
		'comment_author_ip',
		'comment_author',
		'comment_author_email',
		'user_id'
	);

	/**
	 * The comment type
	 *
	 * @var  string
	 */
	public static $type = 'pressforward-comment';

	/**
	 * Generates automatic created field value
	 *
	 * @return  string
	 **/
	public function automaticCommentDateGmt()
	{
		return Date::toSql();
	}

	/**
	 * Generates automatic created field value
	 *
	 * @return  string
	 **/
	public function automaticCommentAuthorIp()
	{
		return Request::ip();
	}

	/**
	 * Generates automatic user_id field value
	 *
	 * @return  integer
	 */
	public function automaticUserId()
	{
		return (int)User::get('id');
	}

	/**
	 * Generates automatic comment_author field value
	 *
	 * @return  integer
	 */
	public function automaticCommentAuthor()
	{
		return (string)User::get('username');
	}

	/**
	 * Generates automatic comment_author_email field value
	 *
	 * @return  integer
	 */
	public function automaticCommentAuthorEmail()
	{
		return (string)User::get('email');
	}

	/**
	 * Get metadata for this post
	 *
	 * @return  object
	 */
	public function meta()
	{
		return $this->oneToMany('Commentmeta', 'comment_id');
	}

	/**
	 * Defines a belongs to one relationship between comment and user
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsToOne('Hubzero\User\User', 'user_id', 'id');
	}

	/**
	 * Get either a count of or list of replies
	 *
	 * @param   array  $filters  Filters to apply to query
	 * @return  object
	 */
	public function replies($filters = array())
	{
		if (!isset($filters['comment_post_ID']))
		{
			$filters['comment_post_ID'] = $this->get('comment_post_ID');
		}

		$entries = self::blank()
			->including(['creator', function ($creator){
				$creator->select('*');
			}])
			->whereEquals('comment_parent', (int) $this->get('comment_ID'))
			->whereEquals('comment_type', self::$type);

		if (isset($filters['comment_post_ID']))
		{
			$entries->whereEquals('comment_post_ID', (int) $filters['comment_post_ID']);
		}

		return $entries;
	}

	/**
	 * Get parent comment
	 *
	 * @return  object
	 */
	public function parent()
	{
		return self::oneOrFail($this->get('comment_parent', 0));
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
		foreach ($this->replies()->rows() as $comment)
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

		return parent::destroy();
	}

	/**
	 * Return a formatted timestamp
	 *
	 * @param   string  $as  What format to return
	 * @return  string
	 */
	public function created($as='')
	{
		$as = strtolower($as);
		$dt = $this->get('comment_date_gmt');

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
}
