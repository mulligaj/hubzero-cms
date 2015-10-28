<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_HZEXEC_') or die();

/**
 * View class for a list of messages.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class MessagesViewMessages extends JViewLegacy
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
			throw new Exception(implode("\n", $errors), 500);
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
		$canDo = MessagesHelper::getActions();

		Toolbar::title(Lang::txt('COM_MESSAGES_MANAGER_MESSAGES'), 'inbox.png');

		if ($canDo->get('core.create'))
		{
			Toolbar::addNew('message.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			Toolbar::divider();
			Toolbar::publish('messages.publish', 'COM_MESSAGES_TOOLBAR_MARK_AS_READ');
			Toolbar::unpublish('messages.unpublish', 'COM_MESSAGES_TOOLBAR_MARK_AS_UNREAD');
		}

		if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			Toolbar::divider();
			Toolbar::deleteList('', 'messages.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			Toolbar::divider();
			Toolbar::trash('messages.trash');
		}

		//Toolbar::addNew('module.add');
		Toolbar::divider();
		Toolbar::appendButton('Popup', 'options', 'COM_MESSAGES_TOOLBAR_MY_SETTINGS', 'index.php?option=com_messages&amp;view=config&amp;tmpl=component', 850, 400);

		if ($canDo->get('core.admin'))
		{
			Toolbar::preferences('com_messages');
		}

		Toolbar::divider();
		Toolbar::help('JHELP_COMPONENTS_MESSAGING_INBOX');
	}
}
