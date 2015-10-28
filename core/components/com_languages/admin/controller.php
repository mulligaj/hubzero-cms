<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_HZEXEC_') or die();

/**
 * Languages Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.5
 */
class LanguagesController extends JControllerLegacy
{
	/**
	 * @var		string	The default view.
	 * @since	1.6
	 */
	protected $default_view = 'installed';

	/**
	 * Method to display a view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/languages.php';

		// Load the submenu.
		LanguagesHelper::addSubmenu(Request::getCmd('view', 'installed'));

		$view	= Request::getCmd('view', 'languages');
		$layout	= Request::getCmd('layout', 'default');
		$client	= Request::getInt('client');
		$id		= Request::getInt('id');

		// Check for edit form.
		if ($view == 'language' && $layout == 'edit' && !$this->checkEditId('com_languages.edit.language', $id)) {

			// Somehow the person just went to the form - we don't allow that.
			$this->setError(Lang::txt('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(Route::url('index.php?option=com_languages&view=languages', false));

			return false;
		}

		parent::display();

		return $this;
	}
}
