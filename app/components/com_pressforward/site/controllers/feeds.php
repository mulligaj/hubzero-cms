<?php
namespace Components\Pressforward\Site\Controllers;

use Components\PressForward\Models\Post;
use Components\PressForward\Models\Folder;
use Hubzero\Component\SiteController;
use Exception;
use Document;
use Request;
use Config;
use Lang;
use OPML_Object;
use OPML_Maker;

/**
 * PressForward controller class for feeds
 */
class Feeds extends SiteController
{
	/**
	 * Display a list of entries
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		$folder = Request::getCmd('opml_folder');

		$feeds = Post::feeds()
			->order('post_date_gmt', 'desc')
			->paginated()
			->rows();

		// Set the mime encoding for the document
		Document::setType('xml');

		if (!is_file(Component::path($this->_option) . '/pressforward/includes/opml/object.php'))
		{
			App::abort(500, Lang::txt('Required OPML library not found.'));
		}

		include_once Component::path($this->_option) . '/pressforward/includes/opml/maker.php';
		include_once Component::path($this->_option) . '/pressforward/includes/opml/object.php';

		$master_opml_obj = new OPML_Object(Request::current());
		$master_opml_obj->set_title('PressForward Subscription List for ' . Config::get('sitename'));

		if ($folder)
		{
			$master_opml_obj->set_title('PressForward Subscription List for the ' . $folder . ' folder on ' . Config::get('sitename'));
		}
		else
		{
			$folders = Folder::all()->rows();

			foreach ($folders as $folder)
			{
				$entry = array();
				$entry['title'] = $folder->get('name', $folder->get('slug'));
				$entry['text']  = $folder->get('description', $entry['title']);

				$folder_obj = $master_opml_obj->make_a_folder_obj($entry);

				$master_opml_obj->set_folder($folder_obj);
			}
		}

		foreach ($feeds as $feed)
		{
			$meta = array();
			foreach ($feed->meta as $m)
			{
				$meta[$m->get('meta_key')] = $m->get('meta_value');
			}

			if (empty($meta['feedUrl']))
			{
				continue;
			}

			if ('http' != substr($meta['feedUrl'], 0, 4))
			{
				$meta['feedUrl'] = 'http://' . $meta['feedUrl'];
			}

			$url_parts = parse_url($meta['feedUrl']);

			if (!isset($meta['feed_type']))
			{
				$meta['feed_type'] = 'rss';
			}

			$entry = array(
				'title'   => $feed->get('post_title'),
				'text'    => $feed->get('post_content'),
				'type'    => ('rss-quick' == $meta['feed_type'] ? 'rss' : $meta['feed_type']),
				'feedUrl' => $meta['feedUrl'],
				'xmlUrl'  => $meta['feedUrl'],
				'htmlUrl' => $url_parts['scheme'] . '://' . $url_parts['host']
			);
			$feed_obj = $master_opml_obj->make_a_feed_obj($entry);

			$parent = null;

			if (empty($parent))
			{
				$parent = false;
			}

			$master_opml_obj->set_feed($feed_obj, $parent);
		}

		header('Content-Type: text/x-opml');
		$opml = new OPML_Maker($master_opml_obj);
		echo $opml->template();
		die();
	}
}
