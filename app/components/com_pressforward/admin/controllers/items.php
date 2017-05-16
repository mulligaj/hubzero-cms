<?php
namespace Components\PressForward\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\PressForward\Models\Post;
use Components\PressForward\Models\Postmeta;
use Components\PressForward\Models\Folder;
use Components\PressForward\Models\Relationship;
use Request;
use Notify;
use Route;
use User;
use Lang;
use Date;
use App;

/**
 * PressForward controller for items
 */
class Items extends AdminController
{
	/**
	 * Execute a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Adding/Editing/Saving
		$this->registerTask('add', 'edit');
		$this->registerTask('apply', 'save');

		// State changes
		$this->registerTask('read', 'state');
		$this->registerTask('star', 'state');
		$this->registerTask('archive', 'state');
		$this->registerTask('nominate', 'state');

		$this->registerTask('unread', 'unstate');
		$this->registerTask('unstar', 'unstate');
		$this->registerTask('unarchive', 'unstate');
		$this->registerTask('unnominate', 'unstate');

		parent::execute();
	}

	/**
	 * Display a list of entries
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Get some incoming filters to apply to the entries list
		$filters = array(
			'search' => urldecode(Request::getState(
				$this->_option . '.' . $this->_controller . '.search',
				'search',
				''
			)),
			'status' => urldecode(Request::getState(
				$this->_option . '.' . $this->_controller . '.status',
				'status',
				''
			)),
			'display' => urldecode(Request::getState(
				$this->_option . '.' . $this->_controller . '.display',
				'display',
				'gogrid'
			)),
			'folder' => Request::getState(
				$this->_option . '.' . $this->_controller . '.folder',
				'folder',
				0,
				'int'
			),
			// Get sorting variables
			//
			// "sort" is the name of the table column to sort by
			// "sort_Dir" is the direction to sort by [ASC, DESC]
			'sort' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sort',
				'filter_order',
				'post_date_gmt'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sortdir',
				'filter_order_Dir',
				'DESC'
			)
		);

		// Get our model
		$p = Post::blank()->getTableName();
		$r = Relationship::blank()->getTableName();

		$record = Post::all()
			->select($p . '.*');

		if ($filters['status'] == 'drafted')
		{
			$record->whereEquals('post_type', Post::$post_nomination);
		}
		else
		{
			$record->whereEquals('post_type', Post::$post_type);
		}
		/*$record = Post::items()
			->select($p . '.*');*/

		if (!$filters['status'])
		{
			$record
				->join($r, $r . '.item_id', $p . '.ID', 'left')
				->whereRaw($r . '.relationship_type IS NULL', array(), 1)
				->orWhere($r . '.relationship_type', '!=', Relationship::stringToInteger('archive'), 1)
				->resetDepth();
		}
		else if ($filters['status'])
		{
			switch ($filters['status'])
			{
				case 'archived':
					$record
						->join($r, $r . '.item_id', $p . '.ID', 'inner')
						->whereEquals($r . '.relationship_type', Relationship::stringToInteger('archive'))
						->whereEquals($r . '.user_id', User::get('id'));
					break;
				case 'starred':
					$record
						->join($r, $r . '.item_id', $p . '.ID', 'inner')
						->whereEquals($r . '.relationship_type', Relationship::stringToInteger('star'))
						->whereEquals($r . '.user_id', User::get('id'));
					break;
				case 'nominated':
					$record
						->join($r, $r . '.item_id', $p . '.ID', 'inner')
						->whereEquals($r . '.relationship_type', Relationship::stringToInteger('nominate'))
						->whereEquals($r . '.user_id', User::get('id'));
					break;
				case 'unread':
					$record
						->joinRaw($r, $r . '.item_id=' . $p . '.ID AND ' . $r . '.user_id=' . User::get('id'), 'left')
						->whereRaw($r . '.relationship_type IS NULL');
					break;
				case 'drafted':
					$record
						->join($r, $r . '.item_id', $p . '.ID', 'inner')
						->whereEquals($r . '.relationship_type', Relationship::stringToInteger('draft'))
						->whereEquals($r . '.user_id', User::get('id'));
					break;
			}
		}

		if ($filters['folder'])
		{
			$tr = \Components\PressForward\Models\Folder\Relationship::blank()->getTableName();
			$t  = \Components\PressForward\Models\Folder\Taxonomy::blank()->getTableName();

			$record
				->joinRaw($tr, $tr . '.object_id IN (' . $p . '.ID, ' . $p . '.post_parent)', 'inner')
				->join($t, $t . '.term_taxonomy_id', $tr . '.term_taxonomy_id', 'inner')
				->whereEquals($t . '.term_id', $filters['folder']);
		}

		if ($search = $filters['search'])
		{
			$record->whereLike($p . '.post_title', $search);
		}

		if ($filters['sort'] == 'dateofitem' || $filters['sort'] == 'reset')
		{
			$filters['sort'] = 'post_date_gmt';
		}
		if ($filters['sort'] == 'dateretrieved')
		{
			$filters['sort'] = 'post_date';
		}

		$rows = $record
			->order($p . '.' . $filters['sort'], $filters['sort_Dir'])
			->paginated('limitstart', 'limit')
			->rows();

		// Get the folders
		$folders = Folder::treeWithFeeds();

		// Output the view
		$this->view
			->set('rows', $rows)
			->set('filters', $filters)
			->set('folders', $folders)
			->set('config', $this->config)
			->display();
	}

	/**
	 * Display a list of nominated entries
	 *
	 * @return  void
	 */
	public function nominatedTask()
	{
		// Get some incoming filters to apply to the entries list
		$filters = array(
			'search' => urldecode(Request::getState(
				$this->_option . '.' . $this->_controller . '.' . $this->_task . '.search',
				'search',
				''
			)),
			'status' => urldecode(Request::getState(
				$this->_option . '.' . $this->_controller . '.' . $this->_task . '.status',
				'status',
				''
			)),
			'folder' => Request::getState(
				$this->_option . '.' . $this->_controller . '.' . $this->_task . '.folder',
				'folder',
				0,
				'int'
			),
			// Get sorting variables
			//
			// "sort" is the name of the table column to sort by
			// "sort_Dir" is the direction to sort by [ASC, DESC]
			'sort' => Request::getState(
				$this->_option . '.' . $this->_controller . '.' . $this->_task . '.sort',
				'filter_order',
				'post_date_gmt'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.' . $this->_controller . '.' . $this->_task . '.sortdir',
				'filter_order_Dir',
				'DESC'
			)
		);

		$p = Post::blank()->getTableName();
		$r = Relationship::blank()->getTableName();

		// Get our model
		$record = Post::nominations()
			->select($p . '.*');

		/*if ($filters['status'])
		{
			$record->whereEquals('post_status', $filters['status']);
		}*/
		if ($filters['status'])
		{
			switch ($filters['status'])
			{
				case 'archived':
					$record
						->join($r, $r . '.item_id', $p . '.ID', 'inner')
						->whereEquals($r . '.relationship_type', Relationship::stringToInteger('archive'))
						->whereEquals($r . '.user_id', User::get('id'));
					break;
				case 'starred':
					$record
						->join($r, $r . '.item_id', $p . '.ID', 'inner')
						->whereEquals($r . '.relationship_type', Relationship::stringToInteger('star'))
						->whereEquals($r . '.user_id', User::get('id'));
					break;
				case 'nominated':
					$record
						->join($r, $r . '.item_id', $p . '.ID', 'inner')
						->whereEquals($r . '.relationship_type', Relationship::stringToInteger('nominate'))
						->whereEquals($r . '.user_id', User::get('id'));
					break;
				case 'unread':
					$record
						->joinRaw($r, $r . '.item_id=' . $p . '.ID AND ' . $r . '.user_id=' . User::get('id'), 'left')
						->whereRaw($r . '.relationship_type IS NULL');
					break;
				case 'drafted':
					$record
						->join($r, $r . '.item_id', $p . '.ID', 'inner')
						->whereEquals($r . '.relationship_type', Relationship::stringToInteger('draft'))
						->whereEquals($r . '.user_id', User::get('id'));
					break;
			}
		}

		if ($search = $filters['search'])
		{
			$record->whereLike($p . '.post_title', $search);
		}

		if ($filters['folder'])
		{
			$tr = \Components\PressForward\Models\Folder\Relationship::blank()->getTableName();
			$t  = \Components\PressForward\Models\Folder\Taxonomy::blank()->getTableName();

			$record
				->joinRaw($tr, $tr . '.object_id IN (' . $p . '.ID, ' . $p . '.post_parent)', 'inner')
				->join($t, $t . '.term_taxonomy_id', $tr . '.term_taxonomy_id', 'inner')
				->whereEquals($t . '.term_id', $filters['folder']);
		}

		$rows = $record
			->order($p . '.' . $filters['sort'], $filters['sort_Dir'])
			->paginated('limitstart', 'limit')
			->rows();

		// Get the folders
		$folders = Folder::treeWithFeeds();

		// Output the view
		$this->view
			->set('rows', $rows)
			->set('filters', $filters)
			->set('folders', $folders)
			->set('config', $this->config)
			->display();
	}

	/**
	 * Set a state value
	 *
	 * @return  void
	 */
	public function stateTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		if (!User::authorise('core.edit.state', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$ids = Request::getVar('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		// Loop through all the IDs
		foreach ($ids as $id)
		{
			$entry = Post::oneOrFail(intval($id));

			if ($entry->isNew())
			{
				Notify::error('Post not found');
				continue;
			}

			$relationship = Relationship::oneByUserAndItem(User::get('id'), $entry->get('ID'), Relationship::stringToInteger($this->getTask()))
				->set([
					'user_id' => User::get('id'),
					'item_id' => $entry->get('ID'),
					'relationship_type' => Relationship::stringToInteger($this->getTask()),
					'value' => 1
				]);

			if (!$relationship->save())
			{
				Notify::error($relationship->getError());
				continue;
			}

			// If a nomination...
			if ($this->getTask() == 'nominate')
			{
				// Check for a nominated post
				// This will be a clone of the original. If it doesn't exist, we'll make it.
				$nomination = Post::all()
					->whereEquals('post_name', $entry->get('post_name'))
					->whereEquals('post_type', Post::$post_nomination)
					->where('ID', '!=', $entry->get('ID'))
					->row();

				if (!$nomination->get('iD'))
				{
					$metadata = $entry->meta;

					$entry->set('ID', 0);
					$entry->set('post_status', 'draft');
					$entry->set('post_type', Post::$post_nomination);

					if (!$entry->save())
					{
						Notify::error($entry->getError());
						continue;
					}

					foreach ($metadata as $meta)
					{
						$meta->set('meta_id', 0);
						$meta->set('post_id', $entry->get('ID'));
						if (!$meta->save())
						{
							Notify::error($meta->getError());
							continue;
						}
					}

					/*$metas = array(
						// Post
						'pf_meta',
						'item_id',
						'item_wp_date',
						'item_feat_img',
						'item_link',
						'item_author',
						'item_date',
						'sortable_item_date',
						// Nominated
						'nom_id',
						'date_nominated',
						'item_tags',
						'source_repeat',
						'nomination_count',
						'submitted_by',
						'nominator_array',
						'readable_status',
						'revertible_feed_text',
						'source_title',
						'_thumbnail_id',
						'archived_by_user_status',
						'pf_feed_item_word_count',
						'pf_archive',
						'pf_feed_error_count',
						'pf_forward_to_origin',
						'pf_source_link',
						'pf_nomination_post_id',
						'pf_item_post_id',
						'pf_no_feed_alert'
					);*/

					$meta = Postmeta::blank();
					$meta->set('post_id', $entry->get('ID'));
					$meta->set('meta_key', 'pf_item_post_id');
					$meta->set('meta_value', $id);
					if (!$meta->save())
					{
						Notify::error($meta->getError());
					}

					$meta = Postmeta::blank();
					$meta->set('post_id', $entry->get('ID'));
					$meta->set('meta_key', 'date_nominated');
					$meta->set('meta_value', Date::of('now')->format('D, d M Y H:i:s') . ' +0000');
					if (!$meta->save())
					{
						Notify::error($meta->getError());
					}
				}
			}

			$success++;
		}

		if ($success)
		{
			Notify::success(Lang::txt('PF_SUCCESS_ITEM_' . strtoupper($this->getTask())));
		}

		if (!Request::getInt('no_html', 0))
		{
			// Set the redirect
			$this->cancelTask();
		}
	}

	/**
	 * Un-set a state value
	 *
	 * @return  void
	 */
	public function unstateTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		if (!User::authorise('core.edit.state', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$ids = Request::getVar('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		// Loop through all the IDs
		foreach ($ids as $id)
		{
			$entry = Post::oneOrFail(intval($id));

			if ($entry->isNew())
			{
				Notify::error('Post not found');
				continue;
			}

			$relationship = Relationship::oneByUserAndItem(User::get('id'), $entry->get('ID'), Relationship::stringToInteger(substr($this->getTask(), 2)));

			if (!$relationship->get('id'))
			{
				continue;
			}

			if (!$relationship->destroy())
			{
				Notify::error($relationship->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			Notify::success(Lang::txt('PF_SUCCESS_ITEM_' . strtoupper($this->getTask())));
		}

		if (!Request::getInt('no_html', 0))
		{
			// Set the redirect
			$this->cancelTask();
		}
	}

	/**
	 * Mark one or more entries as draft
	 *
	 * @return  void
	 */
	public function draftTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		if (!User::authorise('core.edit.state', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$ids = Request::getVar('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		// Loop through all the IDs
		foreach ($ids as $id)
		{
			$entry = Post::oneOrFail(intval($id));

			if ($entry->isNew())
			{
				Notify::error('Post not found');
				continue;
			}

			$relationship = Relationship::oneByUserAndItem(User::get('id'), $entry->get('ID'), Relationship::stringToInteger($this->getTask()))
				->set([
					'user_id' => User::get('id'),
					'item_id' => $entry->get('ID'),
					'relationship_type' => Relationship::stringToInteger($this->getTask()),
					'value' => 1
				]);

			if (!$relationship->save())
			{
				Notify::error($relationship->getError());
				continue;
			}

			// Check for a draft post
			// This will be a clone of the nomination. If it doesn't exist, we'll make it.
			$post = Post::all()
				->whereEquals('post_name', $entry->get('post_name'))
				->whereEquals('post_type', Post::$post_draft)
				->whereEquals('post_status', 'draft')
				->where('ID', '!=', $entry->get('ID'))
				->row();

			if (!$post->get('iD'))
			{
				$metadata = $entry->meta;

				$entry->set('ID', 0);
				$entry->set('post_status', 'draft');
				$entry->set('post_type', Post::$post_draft);

				if (!$entry->save())
				{
					Notify::error($entry->getError());
					continue;
				}

				foreach ($metadata as $meta)
				{
					$meta->set('meta_id', 0);
					$meta->set('post_id', $entry->get('ID'));
					if (!$meta->save())
					{
						Notify::error($meta->getError());
						continue;
					}
				}
			}

			$success++;
		}

		if ($success)
		{
			Notify::success(Lang::txt('PF_SUCCESS_ITEM_' . strtoupper($this->getTask())));
		}

		if (!Request::getInt('no_html', 0))
		{
			// Set the redirect
			$this->cancelTask();
		}
	}

	/**
	 * Mark one or more entries as not draft
	 *
	 * @return  void
	 */
	public function undraftTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		if (!User::authorise('core.edit.state', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$ids = Request::getVar('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		// Loop through all the IDs
		foreach ($ids as $id)
		{
			$entry = Post::oneOrFail(intval($id));

			if ($entry->isNew())
			{
				Notify::error('Post not found');
				continue;
			}

			// Remove the relationship to this user
			$relationship = Relationship::oneByUserAndItem(User::get('id'), $entry->get('ID'), Relationship::stringToInteger('draft'));

			if ($relationship->get('id'))
			{
				if (!$relationship->destroy())
				{
					Notify::error($relationship->getError());
					continue;
				}
			}

			$drafts = $entry->relationships()
				->whereEquals('relationship_type', Relationship::stringToInteger('draft'))
				->total();

			// Curent user was the only one to mark as draft
			// So go ahead and remove the draft entry too
			if (!$drafts)
			{
				// Check for a draft post
				// This will be a clone of the nomination. If it exist, we'll make it.
				$post = Post::all()
					->whereEquals('post_name', $entry->get('post_name'))
					->whereEquals('post_type', 'post')
					->whereEquals('post_status', 'draft')
					->where('ID', '!=', $entry->get('ID'))
					->row();

				if ($post->get('iD'))
				{
					if (!$post->destroy())
					{
						Notify::error($post->getError());
						continue;
					}
				}
			}

			$success++;
		}

		if ($success)
		{
			Notify::success(Lang::txt('PF_SUCCESS_ITEM_' . strtoupper($this->getTask())));
		}

		if (!Request::getInt('no_html', 0))
		{
			// Set the redirect
			$this->cancelTask();
		}
	}

	/**
	 * Delete one or more entries
	 *
	 * @return  void
	 */
	public function deleteTask()
	{
		// Check for request forgeries
		Request::checkToken(['get', 'post']);

		if (!User::authorise('core.delete', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$ids = Request::getVar('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$removed = 0;

		// Loop through all the IDs
		foreach ($ids as $id)
		{
			$entry = Post::oneOrFail(intval($id));
			//$entry->set('post_status', 'removed_pf_feed_item');

			if (!$entry->destroy())
			{
				Notify::error($entry->getError());
				continue;
			}

			$removed++;
		}

		if ($removed)
		{
			Notify::success(Lang::txt('PF_ITEM_DELETED'));
		}

		if (!Request::getInt('no_html', 0))
		{
			// Set the redirect
			$this->cancelTask();
		}
	}
}
