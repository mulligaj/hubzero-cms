<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_HZEXEC_') or die();

/**
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class JHtmlMessages
{
	/**
	 * @param	int $value	The state value
	 * @param	int $i
	 */
	public static function state($value = 0, $i, $canChange)
	{
		// Array of image, task, title, action.
		$states	= array(
			-2 => array('trash.png',     'messages.unpublish', 'JTRASHED',                   'COM_MESSAGES_MARK_AS_UNREAD'),
			1  => array('tick.png',      'messages.unpublish', 'COM_MESSAGES_OPTION_READ',   'COM_MESSAGES_MARK_AS_UNREAD'),
			0  => array('publish_x.png', 'messages.publish',   'COM_MESSAGES_OPTION_UNREAD', 'COM_MESSAGES_MARK_AS_READ')
		);
		$state = \Hubzero\Utility\Arr::getValue($states, (int) $value, $states[0]);
		$html  = Html::asset('image', 'admin/'.$state[0], Lang::txt($state[2]), NULL, true);
		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" title="' . Lang::txt($state[3]) . '">' . $html . '</a>';
		}

		return $html;
	}
}
