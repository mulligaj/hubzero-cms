<?php
namespace Components\PressForward\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\PressForward\Models\Post;
use Components\PressForward\Models\Postmeta;
use Components\PressForward\Models\Folder;
use Request;
use Notify;
use Route;
use Lang;
use App;

/**
 * PressForward controller for feeds
 */
class Feeds extends AdminController
{
	/**
	 * Execute a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->registerTask('apply', 'save');

		// Call the parent execute() method. Important! Otherwise, the
		// controller will never actually execute anything.
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
			'date' => Request::getState(
				$this->_option . '.' . $this->_controller . '.date',
				'date',
				''
			),
			// Get sorting variables
			'sort' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sort',
				'filter_order',
				'post_title'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sortdir',
				'filter_order_Dir',
				'ASC'
			)
		);

		// Get our model
		// This is the entry point to the database and the 
		// table of seasons we'll be retrieving data from
		$record = Post::feeds();

		if ($search = $filters['search'])
		{
			$record->whereLike('post_title', $search);
		}

		if ($dte = $filters['date'])
		{
			$record->where('post_date_gmt', '>', $dte . '-01 00:00:00');
			$record->where('post_date_gmt', '<', Date::of($dte . '-01 00:00:00')->modify('+1 Month')->toSql());
		}

		$rows = $record
			->order($filters['sort'], $filters['sort_Dir'])
			->paginated()
			->rows();

		// Get a list of all months and years we have feeds for
		$alldates = Post::feeds()
			->select('post_date_gmt')
			->order('post_date_gmt', 'desc')
			->rows();

		$dates = array();
		foreach ($alldates as $adate)
		{
			$dates[Date::of($adate->get('post_date_gmt'))->format('Y-m')] = Date::of($adate->get('post_date_gmt'))->format('F Y');
		}


		// Output the view
		$this->view
			->set('rows', $rows)
			->set('filters', $filters)
			->set('dates', $dates)
			->display();
	}

	/**
	 * Show a form for subscribing to feeds
	 *
	 * @return  void
	 */
	public function addTask()
	{
		Request::setVar('hidemainmenu', 1);

		// Output the view
		$this->view
			->display();
	}

	/**
	 * Show a form for subscribing to feeds
	 *
	 * @return  void
	 */
	public function subscribeTask()
	{
		// Check for request forgeries
		Request::checkToken();

		if (!User::authorise('core.create', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		$feedlist = Request::getVar('pf_feedlist', array());

		/*if (isset($feedlist['opml_single']))
		{
			$aOPML_url = $feedlist['opml_single'];

			include_once dirname(dirname(__DIR__)) . '/library/includes/opml/reader.php';
			include_once dirname(dirname(__DIR__)) . '/library/includes/opml/object.php';

			pf_log('Getting OPML Feed at '. $aOPML_url);

			$OPML_reader = new OPML_reader($aOPML_url);
			$opml_object = $OPML_reader->get_OPML_obj();

			pf_log('OPML object received to turn into feeds.');

			$c = 0;
			$opmlObject = array();
			foreach ($opml_object->feeds as $feed_obj)
			{
				// The Unique ID for this feed.
				//
				// Ok, so why don't we use the ->title property of the feed here?
				// The reason is because a feed could potentially be added by more than
				// one OPML file. BUT the ->title property is set by the owner of the
				// OPML file, if it is even set at all. Which means it could be different
				// across more than one OPML file. But we don't want to add a feed more
				// than once, so we only use the feedUrl as a unique notifier.
				pf_log('Prepping item ' . $feed_obj->title);

				$id = $feed_obj->id;
			}
		}*/

		// Create the post object and set some defaults
		$feed = Post::blank();
		$feed->set('guid', $feedlist['single']);
		$feed->set('post_type', Post::$feed_type);
		$feed->set('post_parent', 0);
		$feed->set('post_status', 'publish');
		$feed->set('comment_status', 'closed');
		$feed->set('ping_status', 'closed');
		$feed->set('post_date', Date::of('now')->toSql());

		// Validate url
		if (!filter_var($feed->get('guid'), FILTER_VALIDATE_URL))
		{
			Notify::error(Lang::txt('COM_FEEDAGGREGATOR_ERROR_INVALID_URL'));
			return $this->cancelTask();
		}

		// Get the feed
		$parser = App::get('feed.parser');
		$parser->set_feed_url($feed->get('guid'));
		$parser->init();
		$parser->set_output_encoding('UTF-8');
		$parser->handle_content_type();

		// If we can't retrieve the feed, there's not much else we can do
		if ($parser->error())
		{
			Notify::error(Lang::txt('PF_ERROR_READING_FEED'));

			pf_log('Can not use Simple Pie to retrieve the feed');
			pf_log($parser->error());

			return $this->cancelTask();
		}

		$feed->set('post_title', html_entity_decode(strip_tags($parser->get_title())));
		$feed->set('post_content', html_entity_decode(strip_tags($parser->get_description())));

		if (!$feed->save())
		{
			Notify::error(Lang::txt('PF_ERROR_UPDATE_FAILED'));
			return $this->cancelTask();
		}

		// Compile the metadata
		$meta = array();
		$meta['feedUrl'] = $feed->get('guid'); //$parser->get_link();
		$meta['user_added'] = User::get('username');
		$meta['pf_meta'] = array(
			'pf_feed_error_count' => 0
		);
		$meta['post_name'] = $feed->get('post_name');
		$meta['post_modified'] = $feed->get('post_modified');
		$meta['post_modified_gmt'] = $feed->get('post_modified_gmt');
		$meta['pf_feed_last_retrieved'] = Date::of('now')->toSql();
		$meta['pf_feed_last_checked'] = Date::of('now')->toSql();
		$meta['comment_status'] = $feed->get('comment_status');
		$meta['ping_status'] = $feed->get('ping_status');
		$meta['feed_type'] = $parser->get_type();

		// Collect the metadata into the proper form
		$data = array();
		foreach ($meta as $key => $val)
		{
			$data[] = array(
				'key'   => $key,
				'value' => $val
			);
		}

		if (!$feed->saveMetadata($data))
		{
			Notify::error($feed->getError());
		}

		// Now let's try importing items from the feed
		return $this->importTask($feed);
	}

	/**
	 * Show a form for subscribing to feeds
	 *
	 * @param   object  $parent
	 * @return  void
	 */
	public function importTask($parent = null)
	{
		if (!User::authorise('core.create', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		if (!$parent)
		{
			$parent = Post::oneOrFail(Request::getInt('id', 0));

			if (!$parent->get('ID'))
			{
				Notify::error('Feed not found');
				return $this->cancelTask();
			}
		}

		$feed = App::get('feed.parser');
		$feed->set_feed_url($parent->get('guid'));
		$feed->init();
		$feed->set_output_encoding('UTF-8');
		$feed->handle_content_type();

		// Can we read the feed?
		if ($feed->error())
		{
			Notify::error(Lang::txt('PF_ERROR_READING_FEED'));

			pf_log('Can not use Simple Pie to retrieve the feed');
			pf_log($feed->error());

			//$meta['pf_meta']['pf_feed_error_count']++;
			return $this->cancelTask();
		}

		// Get existing posts
		// We'll use this to compare against any incoming posts
		// and check for duplicates
		$existing = array();
		$urls = Post::all()
			->select('guid')
			->whereEquals('post_type', Post::$post_type)
			->rows();

		foreach ($urls as $url)
		{
			$existing[] = $url->get('guid');
		}

		// Get feed items
		$feed->set_timeout(60);
		$items = $feed->get_items();

		$c = 0;
		pf_log('Begin processing the feed.');

		foreach ($items as $item)
		{
			pf_log('Feed looping through for the ' . $c . ' time.');

			$check_date = $item->get_date('U');
			$dead_date  = time() - (60*60*24*60); //Get the unixdate for two months ago.

			if ($check_date && $check_date <= $dead_date)
			{
				pf_log('Feed item too old. Skip it.');
				continue;
			}

			// checks to see if we have this item
			if (in_array($item->get_link(), $existing))
			{
				pf_log('The post was a repeat, so we are not adding it.');
				continue;
			}

			if (empty($check_date))
			{
				$r_item_date   = date('r');
				$ymd_item_date = date('Y-m-d');
			}
			else
			{
				$r_item_date   = $item->get_date('r');
				$ymd_item_date = $item->get_date('Y-m-d');
			}

			$meta = array();
			$meta['item_id']            = md5($item->get_link() . $item->get_title());
			$meta['item_wp_date']       = $ymd_item_date;
			$meta['item_date']          = $r_item_date;
			$meta['sortable_item_date'] = strtotime($meta['item_date']);

			pf_log('Now on feed ID ' . $meta['item_id'] . '.');

			if ($item->get_source())
			{
				$sourceObj = $item->get_source();
				// Get the link of what created the RSS entry.
				$source = $sourceObj->get_link(0,'alternate');
				// Check if the feed item creator is an aggregator.
				$agStatus = $this->is_from_aggregator($source);
			}
			else
			{
				// If we can't get source information then don't do anything.
				$agStatus = false;
			}
			// If there is less than 160 characters of content, than it isn't really giving us meaningful information.
			// So we'll want to get the good stuff from the source.
			if (strlen($item->get_content()) < 160)
			{
				$agStatus = true;
			}

			$iFeed = $item->get_feed();

			if (!$agStatus)
			{
				$authors = __('No author.', 'pf');

				$authorArray = $item->get_authors();
				if (!empty($authorArray))
				{
					$nameArray = array();
					foreach ($authorArray as $author)
					{
						$nameArray[] = $author->get_name();
					}
					$authors = implode(', ', $nameArray);
				}
			}
			else
			{
				$authors = 'aggregation';
			}

			$meta['item_auhor'] = $authors;

			$content = $item->get_content();
			$content = (string) strip_tags(html_entity_decode($content), '<p> <strong> <bold> <i> <em> <emphasis> <del> <h1> <h2> <h3> <h4> <h5> <a> <img>');

			$meta['pf_meta'] = array(
				'pf_feed_item_word_count' => str_word_count($content),
				'pf_source_link' => $iFeed->get_link(),
				'source_title' => $iFeed->get_title(),
				'source_repeat' => '',
				'revertible_feed_text' => $content,
				'readable_status' => ''
			);

			$item_categories = $item->get_categories();
			if (!empty($item_categories))
			{
				$itemTerms = array();
				foreach ($item_categories as $item_category)
				{
					$itemTerms[] = $item_category->get_term();
				}
				$item_categories_string = implode(',', $itemTerms);
			}
			else
			{
				$item_categories_string = '';
			}

			$meta['item_tags'] = $item_categories_string;

			pf_log('Setting new object for ' . $item->get_title() . ' of ' . $iFeed->get_title() . '.');

			$post = Post::blank();
			$post->set('post_parent', (int) $parent->get('ID'));
			$post->set('post_status', 'publish');
			$post->set('comment_status', 'closed');
			$post->set('ping_status', 'closed');
			$post->set('post_type', Post::$post_type);
			$post->set('post_title', html_entity_decode(strip_tags((string) $item->get_title())));
			$post->set('guid', (string) $item->get_link());
			$post->set('post_content', $content); //String::truncate($content, 300));

			if (!$post->save())
			{
				Notify::error($post->getError());
				continue;
			}

			// Collect the metadata into the proper form
			$data = array();
			foreach ($meta as $key => $val)
			{
				$data[] = array(
					'key'   => $key,
					'value' => $val
				);
			}

			if (!$post->saveMetadata($data))
			{
				Notify::error($post->getError());
				continue;
			}

			pf_log('Post created with ID of ' . $post->get('ID'));

			$c++;

			if ($c > 300)
			{
				break;
			}
		}

		if ($c)
		{
			Notify::success(Lang::txt('Imported %s items', $c));
		}

		$this->cancelTask();
	}

	/**
	 * Show a form for editing an entry
	 *
	 * @param   object  $row
	 * @return  void
	 */
	public function editTask($row=null)
	{
		if (!User::authorise('core.edit', $this->_option)
		 && !User::authorise('core.create', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		Request::setVar('hidemainmenu', 1);

		if (!is_object($row))
		{
			// Incoming
			$id = Request::getVar('id', array(0));
			if (is_array($id) && !empty($id))
			{
				$id = $id[0];
			}

			// Load the record
			$row = Post::oneOrNew($id);
		}

		if ($row->isNew())
		{
			$row->set('post_author', User::get('id'));
			$row->set('post_date_gmt', Date::toSql());
			$row->set('post_date', Date::toSql());
			$row->set('post_status', 'draft');
			$row->set('visibility', 'public');
		}

		// Get the folders
		$folders = Folder::tree();

		// Output the HTML
		$this->view
			->set('row', $row)
			->set('folders', $folders)
			->setLayout('edit')
			->display();
	}

	/**
	 * Save an entry
	 *
	 * @return  void
	 */
	public function saveTask()
	{
		// Check for request forgeries
		Request::checkToken();

		if (!User::authorise('core.edit', $this->_option)
		 && !User::authorise('core.create', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$fields = Request::getVar('fields', array(), 'post', 'none', 2);

		if ($fields['visibility'] == 'private')
		{
			$fields['post_status'] = 'private';
		}
		unset($fields['visibility']);

		// Initiate extended database class
		$row = Post::oneOrNew($fields['ID'])->set($fields);
		$row->set('post_type', 'pf_feed');

		// Store new content
		if (!$row->save())
		{
			Notify::error($row->getError());
			return $this->editTask($row);
		}

		// Process tags
		$row->tag(trim(Request::getVar('tags', '')));

		$metadata = Request::getVar('meta', array(), 'post', 'none', 2);

		$ids = array();
		foreach ($metadata as $i => $data)
		{
			if (!$data['key'])
			{
				continue;
			}

			$meta = Postmeta::oneOrNew($data['id']);
			$meta->set('post_id', $row->get('ID'));
			$meta->set('meta_key', $data['key']);
			$meta->set('meta_value', $data['value']);

			if (!$meta->save())
			{
				Notify::error($meta->getError());
				return $this->editTask($row);
			}

			$ids[] = $meta->get('meta_id');
		}

		// Remove any meta entries that didn't come from the posted data
		// This means the entries were deleted.
		foreach ($row->meta as $meta)
		{
			if (!in_array($meta->get('meta_id'), $ids))
			{
				if (!$meta->destroy())
				{
					Notify::error($meta->getError());
				}
			}
		}

		// Save categories
		$tax_input = Request::getVar('tax_input', array(), 'post', 'none', 2);

		$added = array();
		if (isset($tax_input['pf_feed_category']))
		{
			foreach ($tax_input['pf_feed_category'] as $i => $tax)
			{
				if (!$tax)
				{
					continue;
				}

				$rel = Folder\Relationship::oneByObjectAndTerm($row->get('ID'), $tax);

				if (!$rel->get('object_id'))
				{
					$rel->set('object_id', $row->get('ID'));
					$rel->set('term_taxonomy_id', $tax);

					if (!$rel->save())
					{
						Notify::error($meta->getError());
						return $this->editTask($row);
					}

					// We need to update the usage count
					$taxonomy = Folder\Taxonomy::oneOrFail($tax);
					$taxonomy->set('count', $taxonomy->relationships()->total());
					$taxonomy->save();
				}

				$added[] = $rel->get('term_taxonomy_id');
			}
		}

		foreach ($row->folders()->rows() as $folder)
		{
			if (!in_array($folder->get('term_taxonomy_id'), $added))
			{
				$tax = $folder->get('term_taxonomy_id');

				if (!$folder->destroy())
				{
					Notify::error($folder->getError());
					return $this->editTask($row);
				}

				// We need to update the usage count
				$taxonomy = Folder\Taxonomy::oneOrFail($tax);
				$taxonomy->set('count', $taxonomy->relationships()->total());
				$taxonomy->save();
			}
		}

		// Notify user
		Notify::success(Lang::txt('PF_FEED_SAVED'));

		// Fall back to the edit form if needed
		if ($this->getTask() == 'apply')
		{
			return $this->editTask($row);
		}

		// Set the redirect
		$this->cancelTask();
	}

	/**
	 * Delete one or more entries
	 *
	 * @return  void
	 */
	public function deleteTask()
	{
		// Check for request forgeries
		Request::checkToken();

		if (!User::authorise('core.delete', $this->_option))
		{
			App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
		}

		// Incoming
		$ids = Request::getVar('id', array());

		if (count($ids) > 0)
		{
			$removed = 0;

			// Loop through all the IDs
			foreach ($ids as $id)
			{
				$entry = Post::oneOrFail(intval($id));

				// Delete the entry
				if (!$entry->destroy())
				{
					Notify::error($entry->getError());
					continue;
				}

				$removed++;
			}
		}

		if ($removed)
		{
			Notify::success(Lang::txt('PF_FEEDS_DELETED'));
		}

		// Set the redirect
		$this->cancelTask();
	}
}
