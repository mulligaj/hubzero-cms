<?php
/**
 * PressForward compatibility functions.
 *
 * Function names and argument lists are kept in sync
 * with PressForward's functions and provide the same
 * general functionality with alterations where needed
 * to work within the Hubzero framework.
 */

if (!defined('PF_SLUG'))
{
	define('PF_SLUG', 'pf');
}

if (!function_exists('pf_feed_item_post_type'))
{
	/**
	 * Get the feed item post type name
	 *
	 * @return  string  The name of the feed item post_type for PressForward.
	 */
	function pf_feed_item_post_type()
	{
		return Components\PressForward\Models\Post::$post_type;
	}
}

if (!function_exists('pf_feed_item_tag_taxonomy'))
{
	/**
	 * Get the feed item tag taxonomy name
	 *
	 * @return  string  The slug for the taxonomy used by feed items.
	 */
	function pf_feed_item_tag_taxonomy()
	{
		return Components\PressForward\Models\Post::$tag_taxonomy;
	}
}

if (!function_exists('pf_sanitize'))
{
	/**
	 * Sanitize a string for use in URLs and filenames
	 *
	 * @link http://stackoverflow.com/questions/2668854/sanitizing-strings-to-make-them-url-and-filename-safe
	 *
	 * @param   string  $string           The string to be sanitized
	 * @param   bool    $force_lowercase  True to force all characters to lowercase
	 * @param   bool    $anal             True to scrub all non-alphanumeric characters
	 * @return  string  $clean            The cleaned string
	 */
	function pf_sanitize($string, $force_lowercase = true, $anal = false)
	{
		$strip = array(
			"~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
			"}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
			"", "", ",", "<", ".", ">", "/", "?"
		);

		if (is_array($string))
		{
			$string = implode(' ', $string);
		}
		$clean = trim(str_replace($strip, '', strip_tags($string)));
		$clean = preg_replace('/\s+/', '-', $clean);
		$clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", '', $clean) : $clean;

		return ($force_lowercase) ?
			(function_exists('mb_strtolower')) ?
				mb_strtolower($clean, 'UTF-8') :
				strtolower($clean) :
			$clean;
	}
}

if (!function_exists('pf_slugger'))
{
	/**
	 * Create a slug from a string
	 *
	 * @uses    pf_sanitize()
	 * @param   string  $string      The string to convert
	 * @param   bool    $case        True to force all characters to lowercase
	 * @param   bool    $string      True to scrub all non-alphanumeric characters
	 * @param   bool    $spaces      False to strip spaces
	 * @return  string  $stringSlug  The sanitized slug
	 */
	function pf_slugger($string, $case = false, $strict = true, $spaces = false)
	{
		if ($spaces == false)
		{
			$string = strip_tags($string);
			$stringArray = explode(' ', $string);
			$stringSlug = '';
			foreach ($stringArray as $stringPart)
			{
				$stringSlug .= ucfirst($stringPart);
			}
			$stringSlug = str_replace('&amp;','&', $stringSlug);
			//$charsToElim = array('?','/','\\');
			$stringSlug = pf_sanitize($stringSlug, $case, $strict);
		}
		else
		{
			//$string = strip_tags($string);
			//$stringArray = explode(' ', $string);
			//$stringSlug = '';
			//foreach ($stringArray as $stringPart)
			//{
			//	$stringSlug .= ucfirst($stringPart);
			//}
			$stringSlug = str_replace('&amp;','&', $string);
			//$charsToElim = array('?','/','\\');
			$stringSlug = pf_sanitize($stringSlug, $case, $strict);
		}

		return $stringSlug;
	}
}

if (!function_exists('pf_noms_excerpt'))
{
	/**
	* Build an excerpt for nominations.
	*
	* @param   string  $text
	* @return  string  $text  Returns the adjusted excerpt.
	*/
	function pf_noms_excerpt($text)
	{
		Event::trigger('pressforward.the_content', array(&$text));

		$text = str_replace('\]\]\>', ']]&gt;', $text);
		$text = preg_replace('@<script[^>]*?>.*?</script>@si', '', $text);

		//$contentObj = pressforward('library.htmlchecker');
		//$text = $contentObj->closetags($text);
		$text = strip_tags($text, '<p>');

		$excerpt_length = 310;
		$words = explode(' ', $text, $excerpt_length + 1);
		if (count($words) > $excerpt_length)
		{
			array_pop($words);
			array_push($words, '...');
			$text = implode(' ', $words);
		}

		return $text;
	}
}

if (!function_exists('pf_feed_excerpt'))
{
	/**
	 * Get a feed excerpt
	 *
	 * @param   string  $text
	 * @return  string  $text  Returns the adjusted excerpt.
	 */
	function pf_feed_excerpt($text)
	{
		Event::trigger('pressforward.the_content', array(&$text));

		$text = str_replace('\]\]\>', ']]&gt;', $text);
		$text = preg_replace('@<script[^>]*?>.*?</script>@si', '', $text);
		$text = strip_tags($text);
		$text = substr($text, 0, 260);
		$excerpt_length = 28;
		$words = explode(' ', $text, $excerpt_length + 1);
		array_pop($words);
		array_push($words, '...');
		$text = implode(' ', $words);

		//$contentObj = pressforward('library.htmlchecker');
		//$item_content = $contentObj->closetags($text);

		return $text;
	}
}

if (!function_exists('pf_shortcut_link'))
{
	/**
	 * Echoes the Nominate This bookmarklet link
	 *
	 * @return  void
	 */
	function pf_shortcut_link()
	{
		echo pf_get_shortcut_link();
	}
}

if (!function_exists('pf_get_shortcut_link'))
{
	/**
	 * Retrieve the Nominate This bookmarklet link.
	 *
	 * Use this in 'a' element 'href' attribute.
	 *
	 * @return  string
	 */
	function pf_get_shortcut_link()
	{
		// In case of breaking changes, version this. #WP20071
		$link = "javascript:
				var d=document,
				w=window,
				e=w.getSelection,
				k=d.getSelection,
				x=d.selection,
				s=(e?e():(k)?k():(x?x.createRange().text:0)),
				f='" . Request::root() . "includes/nomthis/nominate-this.php" . "',
				l=d.location,
				e=encodeURIComponent,
				u=f+'?u='+e(l.href)+'&t='+e(d.title)+'&s='+e(s)+'&v=4';
				a=function(){if(!w.open(u,'t','toolbar=0,resizable=1,scrollbars=1,status=1,width=720,height=620'))l.href=u;};
				if (/Firefox/.test(navigator.userAgent)) setTimeout(a, 0); else a();
				void(0)";

		$link = str_replace(array("\r", "\n", "\t"),  '', $link);

		return apply_filters('shortcut_link', $link);
	}
}

if (!function_exists('create_feed_item_id'))
{
	/**
	 * Create a feed item ID
	 *
	 * @param   string  $url
	 * @param   string  $title
	 * @return  string
	 */
	function create_feed_item_id($url, $title)
	{
		$hash = md5($url . $title);
		return $hash;
	}
}

if (!function_exists('assure_log_string'))
{
	/**
	 * Ensure a log message is a string
	 *
	 * @param   mixed   $message  The message to log
	 * @return  string
	 */
	function assure_log_string($message)
	{
		// Make sure we've got a string to log
		if ($message instanceof Exception)
		{
			$message = $message->getMessage();
		}

		if (is_array($message) || is_object($message))
		{
			$message = print_r($message, true);
		}

		if ($message === true)
		{
			$message = 'True';
		}

		if ($message === false)
		{
			$message = 'False';
		}

		return $message;
	}
}

if (!function_exists('pf_log'))
{
	/**
	 * Send status messages to a custom log
	 *
	 * Importing data via cron (such as in PF's RSS Import module) can be difficult
	 * to debug. This function is used to send status messages to a custom error
	 * log.
	 *
	 * The error log is disabled by default. To enable, turn on site debugging in
	 * the global site configuration.
	 *
	 * @param   string  $message  The message to log
	 * @param   bool    $display
	 * @param   bool    $reset
	 * @param   bool    $return
	 * @return  mixed
	 */
	function pf_log($message = '', $display = false, $reset = false, $return = false)
	{
		if (!App::get('config')->get('debug'))
		{
			return;
		}

		$manager = App::get('log');

		if (!$manager->has('pressforward'))
		{
			$manager->register('pressforward', array(
				'file' => 'pressforward.log',
				'path' => App::get('config')->get('log_path'),
			));
		}

		$message = assure_log_string($message);

		try
		{
			$manager->logger('pressforward')->debug($message);
		}
		catch (Exception $e)
		{
			// Carry on. We'll have to figure out what went wrong another time...
		}

		if ($return)
		{
			return $message;
		}
	}
}

if (!function_exists('pf_iterate_cycle_state'))
{
	/**
	 * Get values for iteration cycles
	 *
	 * @param   string  $option_name
	 * @param   bool    $option_limit
	 * @param   bool    $echo
	 * @return  mixed
	 */
	function pf_iterate_cycle_state($option_name, $option_limit = false, $echo = false)
	{
		$default = array(
			'day'        => 0,
			'week'       => 0,
			'month'      => 0,
			'next_day'   => strtotime('+1 day'),
			'next_week'  => strtotime('+1 week'),
			'next_month' => strtotime('+1 month')
		);
		$retrieval_cycle = Component::params('com_pressforward')->get(PF_SLUG . '_' . $option_name, $default);

		if (!is_array($retrieval_cycle))
		{
			$retrieval_cycle = $default;

			Component::params('com_pressforward')->set(PF_SLUG . '_' . $option_name, $retrieval_cycle);
		}

		if ($echo)
		{
			echo '<br />Day: ' . $retrieval_cycle['day'];
			echo '<br />Week: ' . $retrieval_cycle['week'];
			echo '<br />Month: ' . $retrieval_cycle['month'];
		}
		else if (!$option_limit)
		{
			return $retrieval_cycle;
		}
		else if ($option_limit)
		{
			$states = array('day', 'week', 'month');

			foreach ($states as $state)
			{
				if (strtotime('now') >= $retrieval_cycle['next_' . $state])
				{
					$retrieval_cycle[$state] = 1;
					$retrieval_cycle['next_' . $state] = strtotime('+1 ' . $state);
				}
				else
				{
					$retrieval_cycle[$state] = $retrieval_cycle[$state] + 1;
				}
			}

			update_option(PF_SLUG . '_' . $option_name, $retrieval_cycle);

			return $retrieval_cycle;
		}
		else
		{
			if (strtotime('now') >= $retrieval_cycle['next_' . $option_limit])
			{
				$retrieval_cycle[$option_limit] = 1;
				$retrieval_cycle['next_' . $option_limit] = strtotime('+1 ' . $option_limit);
			}
			else
			{
				$retrieval_cycle[$option_limit] = $retrieval_cycle[$option_limit] + 1;
			}

			update_option(PF_SLUG . '_' . $option_name, $retrieval_cycle);

			return $retrieval_cycle;
		}
	}
}
