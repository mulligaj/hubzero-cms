<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_HZEXEC_') or die();

/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 */
abstract class JHtmlContentAdministrator
{
	/**
	 * @param	int $value	The state value
	 * @param	int $i
	 */
	static function featured($value = 0, $i, $canChange = true)
	{
		// Array of image, task, title, action
		$states	= array(
			0	=> array('disabled.png',	'articles.featured',	'COM_CONTENT_UNFEATURED',	'COM_CONTENT_TOGGLE_TO_FEATURE'),
			1	=> array('featured.png',		'articles.unfeatured',	'COM_CONTENT_FEATURED',		'COM_CONTENT_TOGGLE_TO_UNFEATURE'),
		);
		$state	= \Hubzero\Utility\Arr::getValue($states, (int) $value, $states[1]);
		$html	= Html::asset('image', 'admin/'.$state[0], Lang::txt($state[2]), NULL, true);
		if ($canChange) {
			$html	= '<a href="#" class="state ' . ($value ? 'yes' : 'no') . '" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.Lang::txt($state[3]).'">'. $html.'</a>';
		}

		return $html;
	}
}
