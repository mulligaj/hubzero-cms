<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_HZEXEC_') or die();

jimport('joomla.application.component.controllerform');

/**
 * Languages Override Controller
 *
 * @package			Joomla.Administrator
 * @subpackage	com_languages
 * @since				2.5
 */
class LanguagesControllerOverride extends JControllerForm
{
	/**
	 * Method to edit an existing override
	 *
	 * @param		string	$key		The name of the primary key of the URL variable (not used here).
	 * @param		string	$urlVar	The name of the URL variable if different from the primary key (not used here).
	 *
	 * @return	void
	 *
	 * @since		2.5
	 */
	public function edit($key = null, $urlVar = null)
	{
		// Initialize variables
		$cid     = Request::getVar('cid', array(), 'post', 'array');
		$context = "$this->option.edit.$this->context";

		// Get the constant name
		$recordId = (count($cid) ? $cid[0] : Request::getCmd('id'));

		// Access check
		if (!$this->allowEdit())
		{
			$this->setError(Lang::txt('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(Route::url('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));
			return;
		}

		User::setState($context.'.data', null);

		$this->setRedirect('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId, 'id'));
	}

	/**
	 * Method to save an override
	 *
	 * @param		string	$key		The name of the primary key of the URL variable (not used here).
	 * @param		string	$urlVar	The name of the URL variable if different from the primary key (not used here).
	 *
	 * @return	void
	 *
	 * @since		2.5
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries
		Session::checkToken() or exit(Lang::txt('JINVALID_TOKEN'));

		// Initialize variables
		$model   = $this->getModel();
		$data    = Request::getVar('jform', array(), 'post', 'array');
		$context = "$this->option.edit.$this->context";
		$task    = $this->getTask();

		$recordId = Request::getCmd('id');
		$data['id'] = $recordId;

		// Access check
		if (!$this->allowSave($data, 'id'))
		{
			$this->setError(Lang::txt('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(Route::url('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));

			return;
		}

		// Validate the posted data
		$form = $model->getForm($data, false);
		if (!$form)
		{
			Notify::error($model->getError());
			return;
		}

		// Require helper for filter functions called by JForm
		require_once JPATH_COMPONENT.'/helpers/languages.php';

		// Test whether the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					Notify::warning($errors[$i]->getMessage());
				}
				else
				{
					Notify::warning($errors[$i]);
				}
			}

			// Save the data in the session
			User::setState($context.'.data', $data);

			// Redirect back to the edit screen
			$this->setRedirect(Route::url('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId, 'id'), false));

			return;
		}

		// Attempt to save the data
		if (!$model->save($validData))
		{
			// Save the data in the session
			User::setState($context.'.data', $validData);

			// Redirect back to the edit screen
			$this->setError(Lang::txt('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(Route::url('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId, 'id'), false));

			return;
		}

		// Add message of success
		$this->setMessage(Lang::txt('COM_LANGUAGES_VIEW_OVERRIDE_SAVE_SUCCESS'));

		// Redirect the user and adjust session state based on the chosen task
		switch ($task)
		{
			case 'apply':
				// Set the record data in the session
				$recordId = $model->getState($this->context.'.id');
				User::setState($context.'.data', null);

				// Redirect back to the edit screen
				$this->setRedirect(Route::url('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($validData['key'], 'id'), false));
				break;

			case 'save2new':
				// Clear the record id and data from the session
				User::setState($context.'.data', null);

				// Redirect back to the edit screen
				$this->setRedirect(Route::url('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend(null, 'id'), false));
				break;

			default:
				// Clear the record id and data from the session
				User::setState($context.'.data', null);

				// Redirect to the list screen
				$this->setRedirect(Route::url('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));
				break;
		}
	}

	/**
	 * Method to cancel an edit
	 *
	 * @param		string	$key	The name of the primary key of the URL variable (not used here).
	 *
	 * @return	void
	 *
	 * @since		2.5
	 */
	public function cancel($key = null, $test = null)
	{
		Session::checkToken() or exit(Lang::txt('JINVALID_TOKEN'));

		// Initialize variables
		$context = "$this->option.edit.$this->context";

		User::setState($context.'.data', null);

		$this->setRedirect(Route::url('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));
	}
}
