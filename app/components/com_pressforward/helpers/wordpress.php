<?php
/**
 * WordPress compatibility
 *
 * Function names and argument lists are kept in sync
 * with PressForward's functions and provide the same
 * general functionality with alterations where needed
 * to work within the Hubzero framework.
 */

if (!function_exists('__'))
{
	/**
	 * Translate a string
	 *
	 * @param   string  $txt
	 * @param   string  $namespace
	 * @return  string
	 */
	function __($txt, $namespace = null)
	{
		if (strstr($txt, ' '))
		{
			return $txt;
		}

		$txt = preg_replace('/[^a-zA-Z0-9_]/', '', $txt);
		$txt = str_replace(' ', '_', $txt);
		$key = strtoupper($namespace . '_' . $txt);

		return Lang::txt($key);
	}
}

if (!function_exists('_e'))
{
	/**
	 * Echo a translation
	 *
	 * @param   string  $txt
	 * @param   string  $namespace
	 * @return  void
	 */
	function _e($txt, $namespace = null)
	{
		echo __($txt, $namespace);
	}
}

if (!function_exists('e'))
{
	/**
	 * Escape a string
	 *
	 * @param   string  $string
	 * @return  string
	 */
	function e($string)
	{
		return htmlentities($string, ENT_COMPAT, 'UTF-8');
	}
}

if (!function_exists('esc_attr'))
{
	/**
	 * Escaping for HTML attributes.
	 *
	 * @param   string  $text
	 * @return  string
	 */
	function esc_attr($text)
	{
		$safe_text = wp_check_invalid_utf8($text);
		$safe_text = _wp_specialchars($text, ENT_QUOTES);

		return $safe_text;
	}
}

if (!function_exists('wp_check_invalid_utf8'))
{
	/**
	 * Checks for invalid UTF8 in a string.
	 *
	 * @param   string  $string  The text which is to be checked.
	 * @param   bool    $strip   Optional. Whether to attempt to strip out invalid UTF8. Default is false.
	 * @return  string  The checked text.
	 */
	function wp_check_invalid_utf8($string, $strip = false)
	{
		$string = (string) $string;

		if (0 === strlen($string))
		{
			return '';
		}

		// Store the site charset as a static to avoid multiple calls to get_option()
		static $is_utf8 = 'UTF-8'; /*null;

		if (!isset($is_utf8))
		{
			$is_utf8 = in_array(get_option('blog_charset'), array('utf8', 'utf-8', 'UTF8', 'UTF-8'));
		}*/

		if (!$is_utf8)
		{
			return $string;
		}

		// Check for support for utf8 in the installed PCRE library once and store the result in a static
		static $utf8_pcre = null;

		if (!isset($utf8_pcre))
		{
			$utf8_pcre = @preg_match('/^./u', 'a');
		}

		// We can't demand utf8 in the PCRE installation, so just return the string in those cases
		if (!$utf8_pcre)
		{
			return $string;
		}

		// preg_match fails when it encounters invalid UTF8 in $string
		if (1 === @preg_match('/^./us', $string))
		{
			return $string;
		}

		// Attempt to strip the bad chars if requested (not recommended)
		if ($strip && function_exists('iconv'))
		{
			return iconv('utf-8', 'utf-8', $string);
		}

		return '';
	}
}

if (!function_exists('_wp_specialchars'))
{
	/**
	 * Converts a number of special characters into their HTML entities.
	 *
	 * Specifically deals with: &, <, >, ", and '.
	 *
	 * $quote_style can be set to ENT_COMPAT to encode " to
	 * &quot;, or ENT_QUOTES to do both. Default is ENT_NOQUOTES where no quotes are encoded.
	 *
	 * @param   string      $string         The text which is to be encoded.
	 * @param   int|string  $quote_style    Optional. Converts double quotes if set to ENT_COMPAT,
	 *                                      both single and double if set to ENT_QUOTES or none if set to ENT_NOQUOTES.
	 *                                      Also compatible with old values; converting single quotes if set to 'single',
	 *                                      double if set to 'double' or both if otherwise set.
	 *                                      Default is ENT_NOQUOTES.
	 * @param   string      $charset        Optional. The character encoding of the string. Default is false.
	 * @param   bool        $double_encode  Optional. Whether to encode existing html entities. Default is false.
	 * @return  string      The encoded text with HTML entities.
	 */
	function _wp_specialchars($string, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false)
	{
		$string = (string) $string;

		if (0 === strlen($string))
		{
			return '';
		}

		// Don't bother if there are no specialchars - saves some processing
		if (!preg_match( '/[&<>"\']/', $string))
		{
			return $string;
		}

		// Account for the previous behaviour of the function when the $quote_style is not an accepted value
		if (empty($quote_style))
		{
			$quote_style = ENT_NOQUOTES;
		}
		elseif (!in_array($quote_style, array(0, 2, 3, 'single', 'double'), true))
		{
			$quote_style = ENT_QUOTES;
		}

		// Store the site charset as a static to avoid multiple calls to wp_load_alloptions()
		if (!$charset)
		{
			static $_charset = null;
			if (!isset($_charset))
			{
				//$alloptions = wp_load_alloptions();
				$_charset = 'UTF-8'; //isset( $alloptions['blog_charset'] ) ? $alloptions['blog_charset'] : '';
			}
			$charset = $_charset;
		}

		if (in_array($charset, array('utf8', 'utf-8', 'UTF8')))
		{
			$charset = 'UTF-8';
		}

		$_quote_style = $quote_style;

		if ($quote_style === 'double')
		{
			$quote_style  = ENT_COMPAT;
			$_quote_style = ENT_COMPAT;
		}
		elseif ($quote_style === 'single')
		{
			$quote_style = ENT_NOQUOTES;
		}

		if (!$double_encode)
		{
			// Guarantee every &entity; is valid, convert &garbage; into &amp;garbage;
			// This is required for PHP < 5.4.0 because ENT_HTML401 flag is unavailable.
			//$string = wp_kses_normalize_entities($string);
		}

		$string = @htmlspecialchars($string, $quote_style, $charset, $double_encode);

		// Back-compat.
		if ('single' === $_quote_style)
		{
			$string = str_replace("'", '&#039;', $string);
		}

		return $string;
	}
}

if (!function_exists('add_submenu_page'))
{
	/**
	 * Add a submenu item
	 *
	 * @param   string  $slug
	 * @param   string  $txt
	 * @param   string  $txt2
	 * @param   string  $access
	 * @param   string  $link
	 * @param   string  $action
	 * @return  void
	 */
	function add_submenu_page($slug, $txt, $txt2, $access, $link = null, $action = null)
	{
		return Submenu::append($txt, $link, $isActive);
	}
}

if (!function_exists('current_user_can'))
{
	/**
	 * Authorize a user action
	 *
	 * @param   string  $action
	 * @return  boolean
	 */
	function current_user_can($action)
	{
		$action = str_replace('_', '.', $action);

		return User::authorise('core.' . $action, 'com_pressforward.component');
	}
}

if (!function_exists('get_option'))
{
	/**
	 * Get a configuration option value
	 *
	 * @param   string  $option
	 * @return  mixed
	 */
	function get_option($option)
	{
		return App::get('config')->get($option);
	}
}

if (!function_exists('is_admin'))
{
	/**
	 * Is the site in admin mode?
	 *
	 * @return  bool
	 */
	function is_admin()
	{
		return App::isAdmin();
	}
}

if (!function_exists('do_action'))
{
	/**
	 * Execute functions hooked on a specific action hook.
	 *
	 * This function invokes all functions attached to action hook `$tag`. It is
	 * possible to create new action hooks by simply calling this function,
	 * specifying the name of the new hook using the `$tag` parameter.
	 *
	 * You can pass extra arguments to the hooks, much like you can with apply_filters().
	 *
	 * @param   string   $tag     The name of the action to be executed.
	 * @param   mixed    $arg,... Optional. Additional arguments which are passed on to the
	 *                        functions hooked to the action. Default empty.
	 * @return  void
	 */
	function do_action($tag, $arg = '')
	{
		$args = array();

		if (is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]))
		{
			$args[] =& $arg[0];
		}
		else
		{
			$args[] = $arg;
		}

		for ($a = 2, $num = func_num_args(); $a < $num; $a++)
		{
			$args[] = func_get_arg($a);
		}

		Event::trigger($tag, $args);
	}
}

if (!function_exists('apply_filters'))
{
	/**
	 * Call the functions added to a filter hook.
	 *
	 * The callback functions attached to filter hook $tag are invoked by calling
	 * this function. This function can be used to create a new filter hook by
	 * simply calling this function with the name of the new hook specified using
	 * the $tag parameter.
	 *
	 * The function allows for additional arguments to be added and passed to hooks.
	 *
	 *     // Our filter callback function
	 *     function example_callback( $string, $arg1, $arg2 ) {
	 *         // (maybe) modify $string
	 *         return $string;
	 *     }
	 *     add_filter( 'example_filter', 'example_callback', 10, 3 );
	 *
	 *     /*
	 *      * Apply the filters by calling the 'example_callback' function we
	 *      * "hooked" to 'example_filter' using the add_filter() function above.
	 *      * - 'example_filter' is the filter hook $tag
	 *      * - 'filter me' is the value being filtered
	 *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
	 *     $value = apply_filters( 'example_filter', 'filter me', $arg1, $arg2 );
	 *
	 * @since   0.71
	 * @param   string  $tag      The name of the filter hook.
	 * @param   mixed   $value    The value on which the filters hooked to `$tag` are applied on.
	 * @param   mixed   $var,...  Additional variables passed to the functions hooked to `$tag`.
	 * @return  mixed   The filtered value after all hooked functions are applied to it.
	 */
	function apply_filters($tag, $value)
	{
		if ($tag)
		{
			$args = func_get_args();
			array_shift($args);

			Event::trigger('filter.' . $tag, $args);
		}

		return $value;
	}
}
