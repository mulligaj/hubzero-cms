<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_HZEXEC_') or die();

/**
 * Utility class for creating HTML Grids
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @since		1.5
 */
class JHtmlNewsfeed
{
	/**
	 * @param	int $value	The state value
	 * @param	int $i
	 */
	public static function state($value = 0, $i)
	{
		// Array of image, task, title, action
		$states	= array(
			1	=> array('tick.png',		'newsfeeds.unpublish',	'JPUBLISHED',			'COM_NEWSFEEDS_UNPUBLISH_ITEM'),
			0	=> array('publish_x.png',	'newsfeeds.publish',		'JUNPUBLISHED',		'COM_NEWSFEEDS_PUBLISH_ITEM')
		);
		$state	= \Hubzero\Utility\Arr::getValue($states, (int) $value, $states[0]);
		$html	= '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.Lang::txt($state[3]).'">'
				. Html::asset('image', 'admin/'.$state[0], Lang::txt($state[2]), NULL, true).'</a>';

		return $html;
	}

	/**
	 * Display an HTML select list of state filters
	 *
	 * @param	int $selected	The selected value of the list
	 * @return	string			The HTML code for the select tag
	 * @since	1.6
	 */
	public static function filterstate($selected)
	{
		// Build the active state filter options.
		$options	= array();
		$options[]	= Html::select('option', '*', Lang::txt('JOPTION_ANY'));
		$options[]	= Html::select('option', '1', Lang::txt('JPUBLISHED'));
		$options[]	= Html::select('option', '0', Lang::txt('JUNPUBLISHED'));


		return Html::select('genericlist', $options, 'filter_published',
			array(
				'list.attr' => 'class="inputbox" onchange="this.form.submit();"',
				'list.select' => $selected
			)
		);
	}
}
