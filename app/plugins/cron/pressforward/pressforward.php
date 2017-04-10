<?php
defined('_HZEXEC_') or die();

/**
 * Cron plugin for PressForward
 */
class plgCronPressforward extends \Hubzero\Plugin\Plugin
{
	/**
	 * Return a list of events
	 *
	 * @return  array
	 */
	public function onCronEvents()
	{
		$this->loadLanguage();

		$obj = new stdClass();
		$obj->plugin = $this->_name;
		$obj->events = array(
			array(
				'name'   => 'checkForFeedUpdates',
				'label'  => Lang::txt('Import new posts from a feed.'),
				'params' => ''
			)
		);

		return $obj;
	}

	/**
	 * Check feeds for new posts and import them
	 *
	 * @param   object   $job
	 * @return  boolean
	 */
	public function checkForFeedUpdates(\Components\Cron\Models\Job $job)
	{
		require_once Component::path('com_pressforward') . '/helpers/wordpress.php');
		require_once Component::path('com_pressforward') . '/helpers/pressforward.php');
		require_once Component::path('com_pressforward') . '/models/post.php';

		// Get the list of feeds
		$parents = Components\PressForward\Models\Post::all()
			->select('guid')
			->whereEquals('post_type', Post::$feed_type)
			->rows();

		if (!$parents->count())
		{
			return true;
		}

		// Get existing posts
		// We'll use this to compare against any incoming posts
		// and heck for duplicates
		$existing = array();
		$urls = Components\PressForward\Models\Post::all()
			->select('guid')
			->whereEquals('post_type', Components\PressForward\Models\Post::$post_type)
			->rows();

		foreach ($urls as $url)
		{
			$existing[] = $url->get('guid');
		}

		foreach ($parents as $parent)
		{
			$feed = App::get('feed.parser');
			$feed->set_feed_url($parent->get('guid'));
			$feed->init();
			$feed->set_output_encoding('UTF-8');
			$feed->handle_content_type();

			// Can we read the feed?
			if ($feed->error())
			{
				pf_log('Can not use Simple Pie to retrieve the feed');
				pf_log($feed->error());

				continue;
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

				$post = Components\PressForward\Models\Post::blank();
				$post->set('post_parent', (int) $parent->get('ID'));
				$post->set('post_status', 'publish');
				$post->set('comment_status', 'closed');
				$post->set('ping_status', 'closed');
				$post->set('post_type', Components\PressForward\Models\Post::$post_type);
				$post->set('post_title', html_entity_decode(strip_tags((string) $item->get_title())));
				$post->set('guid', (string) $item->get_link());
				$post->set('post_content', $content);

				if (!$post->save())
				{
					pf_log('Failed to save post: ' . $post->getError());
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
					pf_log('Failed to save post metadata: ' . $post->getError());
					continue;
				}

				pf_log('Post created with ID of ' . $post->get('ID'));

				$c++;

				if ($c > 300)
				{
					break;
				}
			}
		}

		return true;
	}
}
