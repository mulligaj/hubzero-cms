<?php
namespace Components\Pressforward\Site\Controllers;

use Components\PressForward\Models\Post;
use Components\PressForward\Models\Folder;
use Components\PressForward\Models\Relationship;
use Hubzero\Component\SiteController;
use Hubzero\Utility\String;
use Hubzero\Utility\Sanitize;
use Exception;
use Document;
use Request;
use Pathway;
use Lang;
use Route;
use User;
use Date;

/**
 * PressForward controller class for posts
 */
class Posts extends SiteController
{
	/**
	 * Determines task being called and attempts to execute it
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->_authorize();
		$this->_authorize('post');
		$this->_authorize('comment');

		$this->registerTask('comments.rss', 'comments');
		$this->registerTask('commentsrss', 'comments');

		$this->registerTask('feed.rss', 'feed');
		$this->registerTask('feedrss', 'feed');

		$this->registerTask('archive', 'display');
		$this->registerTask('new', 'edit');

		parent::execute();
	}

	/**
	 * Display a list of entries
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Filters for returning results
		$filters = array(
			'year'       => Request::getInt('year', 0),
			'month'      => Request::getInt('month', 0),
			'day'        => Request::getInt('day', 0),
			'search'     => Request::getVar('search', ''),
			'state'      => 1,
			'access'     => User::getAuthorisedViewLevels()
		);

		if ($filters['year'] > date("Y"))
		{
			$filters['year'] = 0;
		}
		if ($filters['month'] > 12)
		{
			$filters['month'] = 0;
		}
		if ($filters['day'] > 31)
		{
			$filters['day'] = 0;
		}

		$record = Post::all()
			->whereEquals('post_type', 'post')
			->whereEquals('post_status', 'publish');

		if ($filters['year'])
		{
			$dtb = $filters['year'] . '-01-01 00:00:00';
			$dta = ($filters['year']+1) . '-01-01 00:00:00';
			if ($filters['month'])
			{
				$dtb = $filters['year'] . '-' . $filters['month'] . '-01 00:00:00';
				$dta = Date::of($dtb)->modify('+1 month')->toSql();
			}
			if ($filters['day'])
			{
				$dtb = $filters['year'] . '-' . $filters['month'] . '-' . $filters['day'] . ' 00:00:00';
				$dta = Date::of($dtb)->modify('+1 day')->toSql();
			}

			$record->where('post_date_gmt', '>=', $dtb)
				->where('post_date_gmt', '<', $dta);
		}

		$rows = $record
			->order('post_date_gmt', 'desc')
			->paginated()
			->rows();

		// Output HTML
		$this->view
			->set('rows', $rows)
			->set('config', $this->config)
			->set('filters', $filters)
			->display();
	}

	/**
	 * Display an entry
	 *
	 * @return  void
	 */
	public function entryTask()
	{
		$year  = Request::getCmd('year', '');
		$month = Request::getCmd('month', '');
		$day   = Request::getCmd('day', '');
		$alias = Request::getVar('alias', '');

		if (!$alias)
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option)
			);
		}

		// Load entry
		$row = Post::oneByAliasAndDate(
			$alias,
			$year . '-' . $month . '-' . $day
		);

		if (!$row->get('ID'))
		{
			App::abort(404, Lang::txt('PF_NOT_FOUND'));
		}

		if ($row->get('post_type') != 'post' || $row->get('post_status') != 'publish')
		{
			App::abort(404, Lang::txt('PF_NOT_FOUND'));
		}

		// Output HTML
		$this->view
			->set('config', $this->config)
			->set('row', $row)
			->setLayout('entry')
			->display();
	}

	/**
	 * Generate an RSS feed of entries
	 *
	 * @return  void
	 */
	public function feedTask()
	{
		// Set the mime encoding for the document
		Document::setType('feed');

		// Start a new feed object
		$doc = Document::instance();
		$doc->link = Route::url('index.php?option=' . $this->_option);

		// Incoming
		$filters = array(
			'year'       => Request::getInt('year', 0),
			'month'      => Request::getInt('month', 0),
			'day'        => Request::getInt('day', 0),
			'search'     => Request::getVar('search', ''),
			'access'     => User::getAuthorisedViewLevels()
		);

		if (!User::isGuest())
		{
			if ($this->config->get('access-manage-component'))
			{
				//$filters['state'] = null;
				$filters['authorized'] = true;
				array_push($filters['access'], 5);
			}
		}

		if ($filters['year'] > date("Y"))
		{
			$filters['year'] = 0;
		}
		if ($filters['month'] > 12)
		{
			$filters['month'] = 0;
		}
		if ($filters['day'] > 31)
		{
			$filters['day'] = 0;
		}

		$record = Post::all()
			->whereEquals('post_type', 'post')
			->whereEquals('post_status', 'publish');

		if ($filters['year'])
		{
			$dtb = $filters['year'] . '-01-01 00:00:00';
			$dta = ($filters['year']+1) . '-01-01 00:00:00';
			if ($filters['month'])
			{
				$dtb = $filters['year'] . '-' . $filters['month'] . '-01 00:00:00';
				$dta = Date::of($dtb)->modify('+1 month')->toSql();
			}
			if ($filters['day'])
			{
				$dtb = $filters['year'] . '-' . $filters['month'] . '-' . $filters['day'] . ' 00:00:00';
				$dta = Date::of($dtb)->modify('+1 day')->toSql();
			}

			$record->where('post_date_gmt', '>=', $dtb)
				->where('post_date_gmt', '<', $dta);
		}

		$rows = $record
			->order('post_date_gmt', 'desc')
			->paginated()
			->rows();

		// Build some basic RSS document information
		$doc->title  = Config::get('sitename') . ' - ' . Lang::txt(strtoupper($this->_option));
		$doc->title .= ($filters['year']) ? ': ' . $filters['year'] : '';
		$doc->title .= ($filters['month']) ? ': ' . sprintf("%02d", $filters['month']) : '';
		$doc->title .= ($filters['day']) ? ': ' . sprintf("%02d", $filters['day']) : '';

		$doc->description = Lang::txt('PF_RSS_DESCRIPTION', Config::get('sitename'));
		$doc->copyright   = Lang::txt('PF_RSS_COPYRIGHT', date("Y"), Config::get('sitename'));
		$doc->category    = Lang::txt('PF_RSS_CATEGORY');

		// Start outputing results if any found
		if ($rows->count() > 0)
		{
			foreach ($rows as $row)
			{
				$item = new \Hubzero\Document\Type\Feed\Item();

				// Strip html from feed item description text
				$item->description = $row->get('post_content');
				$item->description = html_entity_decode(Sanitize::stripAll($item->description));
				if ($this->config->get('feed_entries') == 'partial')
				{
					$item->description = String::truncate($item->description, 300);
				}
				$item->description = '<![CDATA[' . $item->description . ']]>';

				// Load individual item creator class
				$item->title       = html_entity_decode(strip_tags($row->get('post_title')));
				$item->link        = Route::url($row->link());
				$item->date        = date('r', strtotime($row->get('post_date_gmt')));
				$item->category    = '';
				foreach ($row->meta as $meta)
				{
					if ($meta->get('meta_key') == 'item_author')
					{
						$item->author = $meta->get('meta_value');
					}
				}

				// Loads item info into rss array
				$doc->addItem($item);
			}
		}
	}

	/**
	 * Save a comment
	 *
	 * @return  void
	 */
	public function savecommentTask()
	{
		// Ensure the user is logged in
		if (User::isGuest())
		{
			$rtrn = Request::getVar('REQUEST_URI', Route::url('index.php?option=' . $this->_option), 'server');
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . base64_encode($rtrn)),
				Lang::txt('PF_LOGIN_NOTICE'),
				'warning'
			);
		}

		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$data = Request::getVar('comment', array(), 'post', 'none', 2);

		// Instantiate a new comment object and pass it the data
		$comment = Comment::oneOrNew($data['comment_ID'])->set($data);

		// Store new content
		if (!$comment->save())
		{
			$this->setError($comment->getError());
			return $this->entryTask();
		}

		return $this->entryTask();
	}

	/**
	 * Delete a comment
	 *
	 * @return  void
	 */
	public function deletecommentTask()
	{
		// Ensure the user is logged in
		if (User::isGuest())
		{
			$this->setError(Lang::txt('PF_LOGIN_NOTICE'));
			return $this->entryTask();
		}

		// Incoming
		$id    = Request::getInt('comment', 0);
		$year  = Request::getVar('year', '');
		$month = Request::getVar('month', '');
		$day   = Request::getVar('day', '');
		$alias = Request::getVar('alias', '');

		if (!$id)
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&year=' . $year . '&month=' . $month . '&day=' . $day . '&alias=' . $alias, false)
			);
		}

		// Initiate a blog comment object
		$comment = Comment::oneOrFail($id);

		if (User::get('id') != $comment->get('user_id') && !$this->config->get('access-delete-comment'))
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&year=' . $year . '&month=' . $month . '&day=' . $day . '&alias=' . $alias, false)
			);
		}

		// Mark all comments as deleted
		$comment->set('comment_approved', 0);
		$comment->save();

		// Return the topics list
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&year=' . $year . '&month=' . $month . '&day=' . $day . '&alias=' . $alias),
			($this->getError() ? $this->getError() : null),
			($this->getError() ? 'error' : null)
		);
	}

	/**
	 * Display an RSS feed of comments
	 *
	 * @return  string  RSS
	 */
	public function commentsTask()
	{
		if (!$this->config->get('feeds_enabled'))
		{
			throw new Exception(Lang::txt('Feed not found.'), 404);
		}

		// Set the mime encoding for the document
		Document::setType('feed');

		// Start a new feed object
		$doc = Document::instance();
		$doc->link = Route::url('index.php?option=' . $this->_option);

		// Incoming
		$alias = Request::getVar('alias', '');
		if (!$alias)
		{
			throw new Exception(Lang::txt('Feed not found.'), 404);
		}

		$this->entry = Post::oneByAliasAndDate($alias, $year . '-' . $month . '-' . $day);

		$year  = Request::getInt('year', date("Y"));
		$month = Request::getInt('month', 0);

		// Build some basic RSS document information
		$doc->title  = Config::get('sitename') . ' - ' . Lang::txt(strtoupper($this->_option));
		$doc->title .= ($year) ? ': ' . $year : '';
		$doc->title .= ($month) ? ': ' . sprintf("%02d", $month) : '';
		$doc->title .= stripslashes($this->entry->get('post_title', ''));
		$doc->title .= ': ' . Lang::txt('Comments');

		$doc->description = Lang::txt('PF_COMMENTS_RSS_DESCRIPTION', Config::get('sitename'), stripslashes($this->entry->get('post_title')));
		$doc->copyright   = Lang::txt('PF_RSS_COPYRIGHT', date("Y"), Config::get('sitename'));

		$rows = $this->entry->comments()
			->whereEquals('comment_approved', 1)
			->ordered()
			->rows();

		// Start outputing results if any found
		if ($rows->count() <= 0)
		{
			return;
		}

		foreach ($rows as $row)
		{
			$this->_comment($doc, $row);
		}
	}

	/**
	 * Recursive method to add comments to a flat RSS feed
	 *
	 * @param   object $doc JDocumentFeed
	 * @param   object $row BlogModelComment
	 * @return	void
	 */
	private function _comment(&$doc, $row)
	{
		// Load individual item creator class
		$item = new \Hubzero\Document\Type\Feed\Item();
		$item->title = Lang::txt('Comment #%s', $row->get('comment_ID')) . ' @ ' . $row->created('time') . ' on ' . $row->created('date');
		$item->link  = Route::url($this->entry->link()  . '#c' . $row->get('comment_ID'));

		$item->description = html_entity_decode(Sanitize::stripAll($row->get('comment_content')));
		$item->description = '<![CDATA[' . $item->description . ']]>';

		if ($row->get('anonymous'))
		{
			//$item->author = Lang::txt('PF_ANONYMOUS');
		}
		else
		{
			$item->author = $row->creator->get('email') . ' (' . $row->creator->get('name') . ')';
		}
		$item->date     = $row->created();
		$item->category = '';

		$doc->addItem($item);

		$replies = $row->replies()
			->whereEquals('comment_approved', 1)
			->rows();

		if ($replies->count() > 0)
		{
			foreach ($replies as $reply)
			{
				$this->_comment($doc, $reply);
			}
		}
	}

	/**
	 * Method to check admin access permission
	 *
	 * @return  boolean  True on success
	 */
	protected function _authorize($assetType='component', $assetId=null)
	{
		$this->config->set('access-view-' . $assetType, true);

		if (!User::isGuest())
		{
			$asset  = $this->_option;
			if ($assetId)
			{
				$asset .= ($assetType != 'component') ? '.' . $assetType : '';
				$asset .= ($assetId) ? '.' . $assetId : '';
			}

			$at = '';
			if ($assetType != 'component')
			{
				$at .= '.' . $assetType;
			}

			// Admin
			$this->config->set('access-admin-' . $assetType, User::authorise('core.admin', $asset));
			$this->config->set('access-manage-' . $assetType, User::authorise('core.manage', $asset));
			// Permissions
			$this->config->set('access-create-' . $assetType, User::authorise('core.create' . $at, $asset));
			$this->config->set('access-delete-' . $assetType, User::authorise('core.delete' . $at, $asset));
			$this->config->set('access-edit-' . $assetType, User::authorise('core.edit' . $at, $asset));
			$this->config->set('access-edit-state-' . $assetType, User::authorise('core.edit.state' . $at, $asset));
			$this->config->set('access-edit-own-' . $assetType, User::authorise('core.edit.own' . $at, $asset));
		}
	}
}
