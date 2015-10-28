<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_HZEXEC_') or die();

/**
 * The HTML Menus Menu Item TYpes View.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
class MenusViewMenutypes extends JViewLegacy
{
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->recordId = Request::getInt('recordId');
		$this->types 	= $this->get('TypeOptions');

		parent::display($tpl);
	}
}
