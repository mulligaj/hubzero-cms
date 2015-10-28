<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_HZEXEC_') or die();

/**
 * Profile view class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @since		1.6
 */
class UsersViewProfile extends JViewLegacy
{
	protected $data;
	protected $form;
	protected $params;
	protected $state;

	/**
	 * Method to display the view.
	 *
	 * @param	string	$tpl	The template file to include
	 * @since	1.6
	 */
	public function display($tpl = null)
	{
		// Get the view data.
		$this->data   = $this->get('Data');
		$this->form   = $this->get('Form');
		$this->state  = $this->get('State');
		$this->params = $this->state->get('params');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			App::abort(500, implode('<br />', $errors));
			return false;
		}

		// Check if a user was found.
		if (!$this->data->id)
		{
			App::abort(404, Lang::txt('JERROR_USERS_PROFILE_NOT_FOUND'));
			return false;
		}

		// Check for layout override
		$active = \App::get('menu')->getActive();
		if (isset($active->query['layout']))
		{
			$this->setLayout($active->query['layout']);
		}

		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$this->prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @since	1.6
	 */
	protected function prepareDocument()
	{
		$menus = \App::get('menu');
		$login = User::isGuest() ? true : false;
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', User::get('name')));
		}
		else
		{
			$this->params->def('page_heading', Lang::txt('COM_USERS_PROFILE'));
		}

		$title = $this->params->get('page_title', '');
		if (empty($title))
		{
			$title = Config::get('sitename');
		}
		elseif (Config::get('sitename_pagetitles', 0) == 1)
		{
			$title = Lang::txt('JPAGETITLE', Config::get('sitename'), $title);
		}
		elseif (Config::get('sitename_pagetitles', 0) == 2)
		{
			$title = Lang::txt('JPAGETITLE', $title, Config::get('sitename'));
		}
		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
