<?php
namespace Components\Pressforward\Site;

use Hubzero\Component\Router\Base;

/**
 * Routing class for the component
 */
class Router extends Base
{
	/**
	 * Build the route for the component.
	 *
	 * @param   array  &$query  An array of URL arguments
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 */
	public function build(&$query)
	{
		$segments = array();

		if (!empty($query['year']))
		{
			$segments[] = $query['year'];
			unset($query['year']);
		}
		if (!empty($query['month']))
		{
			$segments[] = $query['month'];
			unset($query['month']);
		}
		if (!empty($query['day']))
		{
			$segments[] = $query['day'];
			unset($query['day']);
		}
		if (!empty($query['alias']))
		{
			$segments[] = $query['alias'];
			unset($query['alias']);
		}
		if (!empty($query['task']))
		{
			$segments[] = $query['task'];
			unset($query['task']);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 * @return  array  The URL attributes to be used by the application.
	 */
	public function parse(&$segments)
	{
		$vars = array();

		if (empty($segments))
		{
			return $vars;
		}

		if (isset($segments[0]))
		{
			if (is_numeric($segments[0]))
			{
				$vars['year'] = $segments[0];
				$vars['task'] = 'browse';
			}
			else
			{
				$vars['task'] = $segments[0];
			}
		}
		if (isset($segments[1]))
		{
			$vars['month'] = $segments[1];
		}
		if (isset($segments[2]))
		{
			$vars['day'] = $segments[2];
		}
		if (isset($segments[3]))
		{
			$vars['alias'] = $segments[3];
			$vars['task'] = 'entry';
		}
		if (isset($segments[4]))
		{
			$vars['task'] = $segments[4];
		}

		return $vars;
	}
}
