<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_HZEXEC_') or die();

/**
 * View class for a list of newsfeeds.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @since		1.6
 */
class NewsfeedsViewNewsfeeds extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			App::abort(500, implode("\n", $errors));
			return false;
		}

		parent::display($tpl);
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$canDo = NewsfeedsHelper::getActions($state->get('filter.category_id'));

		Toolbar::title(Lang::txt('COM_NEWSFEEDS_MANAGER_NEWSFEEDS'), 'newsfeeds.png');
		if (count(User::getAuthorisedCategories('com_newsfeeds', 'core.create')) > 0)
		{
			Toolbar::addNew('newsfeed.add');
		}
		if ($canDo->get('core.edit'))
		{
			Toolbar::editList('newsfeed.edit');
		}
		if ($canDo->get('core.edit.state'))
		{
			Toolbar::divider();
			Toolbar::publish('newsfeeds.publish', 'JTOOLBAR_PUBLISH', true);
			Toolbar::unpublish('newsfeeds.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			Toolbar::divider();
			Toolbar::archiveList('newsfeeds.archive');
		}
		if ($canDo->get('core.admin'))
		{
			Toolbar::checkin('newsfeeds.checkin');
		}
		if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			Toolbar::deleteList('', 'newsfeeds.delete', 'JTOOLBAR_EMPTY_TRASH');
			Toolbar::divider();
		}
		elseif ($canDo->get('core.edit.state'))
		{
			Toolbar::trash('newsfeeds.trash');
			Toolbar::divider();
		}
		if ($canDo->get('core.admin'))
		{
			Toolbar::preferences('com_newsfeeds');
			Toolbar::divider();
		}
		Toolbar::help('JHELP_COMPONENTS_NEWSFEEDS_FEEDS');
	}
}
