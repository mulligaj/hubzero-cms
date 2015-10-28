<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_HZEXEC_') or die();

/**
 * HTML View class for the Messages component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class MessagesViewMessage extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $state;

	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

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
		if ($this->getLayout() == 'edit')
		{
			Toolbar::title(Lang::txt('COM_MESSAGES_WRITE_PRIVATE_MESSAGE'), 'new-privatemessage.png');
			Toolbar::save('message.save', 'COM_MESSAGES_TOOLBAR_SEND');
			Toolbar::cancel('message.cancel');
			Toolbar::help('JHELP_COMPONENTS_MESSAGING_WRITE');
		}
		else
		{
			Toolbar::title(Lang::txt('COM_MESSAGES_VIEW_PRIVATE_MESSAGE'), 'inbox.png');
			$sender = JUser::getInstance($this->item->user_id_from);
			if ($sender->authorise('core.admin') || $sender->authorise('core.manage', 'com_messages') && $sender->authorise('core.login.admin'))
			{
				Toolbar::custom('message.reply', 'restore.png', 'restore_f2.png', 'COM_MESSAGES_TOOLBAR_REPLY', false);
			}
			Toolbar::cancel('message.cancel');
			Toolbar::help('JHELP_COMPONENTS_MESSAGING_READ');
		}
	}
}
