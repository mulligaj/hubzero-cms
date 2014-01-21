<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/controller.php';

/**
 * Registration controller class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @since		1.6
 */
class UsersControllerUser extends UsersController
{
	/**
	 * Method to log in a user.
	 *
	 * @since	1.6
	 */
	public function login()
	{
		$app = JFactory::getApplication();

		// Populate the data array:
		$data             = array();
		$options          = array();
		$data['return']   = base64_decode(JRequest::getVar('return', '', 'POST', 'BASE64'));
		$data['username'] = JRequest::getVar('username', '', 'method', 'username');
		$data['password'] = JRequest::getString('passwd', '', 'post', JREQUEST_ALLOWRAW);

		$authenticator    = JRequest::getVar('authenticator', '', 'method');

		// If a specific authenticator is specified try to call the login method for that plugin
		if (!empty($authenticator)) {
			JPluginHelper::importPlugin('authentication');

			$plugins = JPluginHelper::getPlugin('authentication');

			foreach ($plugins as $plugin)
			{
				$className = 'plg'.$plugin->type.$plugin->name;

				if ($plugin->name != $authenticator) {
					continue;
				}

				if (class_exists($className)) {
					if (method_exists($className,'login')) {

						$myplugin = new $className($this,(array)$plugin);

						$myplugin->login($credentials, $options);

						if (isset($options['return'])) {
								$data['return'] = $options['return'];
						}
					}

					$options['authenticator'] = $authenticator;

					break;
				}
			}
		}

		// If no authenticator is specified, or the login method for that plugin did not exist then use joomla default
		if (!isset($myplugin)) {
			// Check for request forgeries
			JRequest::checkToken('request') or jexit( 'Invalid Token' );

			if ($return = JRequest::getVar('return', '', 'method', 'base64')) {
				$return = base64_decode($return);
				if (!JURI::isInternal($return)) {
					$return = '';
				}
			}

			if ($freturn = JRequest::getVar('freturn', '', 'method', 'base64')) {
				$freturn = base64_decode($freturn);
				if (!JURI::isInternal($freturn)) {
					$freturn = '';
				}
			}

			// Get the log in options.
			$options = array();
			$options['remember'] = JRequest::getBool('remember', false);
			$options['return'] = $data['return'];
			if (!empty($authenticator)) {
				$options['authenticator'] = $authenticator;
			}

			// Get the log in credentials.
			$credentials = array();
			$credentials['username'] = $data['username'];
			$credentials['password'] = $data['password'];
		}

		// Set the return URL if empty.
		if (empty($data['return'])) {
			$data['return'] = 'index.php?option=com_members&task=myaccount';
		}

		// Set the return URL in the user state to allow modification by plugins
		$app->setUserState('users.login.form.return', $data['return']);

		$result = $app->login($credentials, $options);
		// Perform the log in.
		if (true === $result) {
			// Success
			$app->setUserState('users.login.form.data', array());

			// If no_html is set, return json response
			if(JRequest::getInt('no_html', 0))
			{
				echo json_encode( array("success" => true, "redirect" => JRoute::_($app->getUserState('users.login.form.return'), false)) );
				exit;
			}
			else
			{
				$app->redirect(JRoute::_($app->getUserState('users.login.form.return'), false));
			}
		} else {
			// Login failed !
			$data['remember'] = (int)$options['remember'];
			$app->setUserState('users.login.form.data', $data);

			// Facilitate third party login forms
			if (!$return) {
				$return	= JRoute::_('index.php?option=com_users&view=login');
			}

			if (isset($freturn)) {
				$return = $freturn;
			}

			// If no_html is set, return json response
			if(JRequest::getInt('no_html', 0))
			{
				echo json_encode( array("error" => $result->getMessage(), "freturn" => JRoute::_($return, false)) );
				exit;
			}
			else
			{
				// Redirect to a login form
				$app->redirect(JRoute::_($return, false));
			}
		}
	}

	/**
	 * Method to log out a user.
	 *
	 * @since	1.6
	 */
	public function logout()
	{
		$app = JFactory::getApplication();

		$juser = JFactory::getUser();

		$authenticator = JRequest::getVar('authenticator', '', 'method');

		// If a specific authenticator is specified try to call the logout method for that plugin
		if (!empty($authenticator)) {
			JPluginHelper::importPlugin('authentication');

			$plugins = JPluginHelper::getPlugin('authentication');

			foreach ($plugins as $plugin)
			{
				$className = 'plg'.$plugin->type.$plugin->name;

				if ($plugin->name != $authenticator) {
					continue;
				}

				if (class_exists($className))
				{
					if (method_exists($className,'logout'))
					{
						$myplugin = new $className($this,(array)$plugin);

						$result = $myplugin->logout();
					}

					break;
				}
			}
		}

		// Perform the log in.
		$error = $app->logout();

		// Check if the log out succeeded.
		if (!($error instanceof Exception)) {
			// If the authenticator is empty, but they have an active third party session,
			// redirect to a page indicating this and offering complete signout
			if(isset($juser->auth_link_id) && $juser->auth_link_id && empty($authenticator))
			{
				ximport('Hubzero_Auth_Link');
				ximport('Hubzero_Auth_Domain');
				$auth_domain_name = '';
				$auth_domain      = Hubzero_Auth_Link::find_by_id($juser->auth_link_id);

				if (is_object($auth_domain))
				{
					$auth_domain_id   = $auth_domain->auth_domain_id;
					$auth_domain_name = Hubzero_Auth_Domain::find_by_id($auth_domain_id)->authenticator;
				}

				// Redirect to user third party signout view
				// Only do this for PUCAS for the time being (it's the one that doesn't lose session info after hub logout)
				if($auth_domain_name == 'pucas')
				{
					// Get plugin params
					$plugin      = JPluginHelper::getPlugin('authentication', $auth_domain_name);
					$paramsClass = 'JParameter';
					if (version_compare(JVERSION, '1.6', 'ge'))
					{
						$paramsClass = 'JRegistry';
					}

					$pparams = new $paramsClass($plugin->params);
					$auto_logoff = $pparams->get('auto_logoff', false);

					if ($auto_logoff)
					{
						$app->redirect(JRoute::_('index.php?option=com_users&task=user.logout&authenticator=' . $auth_domain_name, false));
						return;
					}
					else
					{
						$app->redirect(JRoute::_('index.php?option=com_users&view=endsinglesignon&authenticator=' . $auth_domain_name, false));
						return;
					}
				}
			}

			// Get the return url from the request and validate that it is internal.
			$return = JRequest::getVar('return', '', 'method', 'base64');
			$return = base64_decode($return);
			if (!JURI::isInternal($return)) {
				$return = '';
			}

			// Redirect the user.
			$app->redirect(JRoute::_($return, false));
		} else {
			$app->redirect(JRoute::_('index.php?option=com_users&view=login', false));
		}
	}

	/**
	 * Method to register a user.
	 *
	 * @since	1.6
	 */
	public function register()
	{
		JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		// Get the form data.
		$data	= JRequest::getVar('user', array(), 'post', 'array');

		// Get the model and validate the data.
		$model	= $this->getModel('Registration', 'UsersModel');
		$return	= $model->validate($data);

		// Check for errors.
		if ($return === false) {
			// Get the validation messages.
			$app	=  JFactory::getApplication();
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');
				} else {
					$app->enqueueMessage($errors[$i], 'notice');
				}
			}

			// Save the data in the session.
			$app->setUserState('users.registration.form.data', $data);

			// Redirect back to the registration form.
			$this->setRedirect('index.php?option=com_users&view=registration');
			return false;
		}

		// Finish the registration.
		$return	= $model->register($data);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('users.registration.form.data', $data);

			// Redirect back to the registration form.
			$message = JText::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_users&view=registration', $message, 'error');
			return false;
		}

		// Flush the data from the session.
		$app->setUserState('users.registration.form.data', null);

		exit;
	}

	/**
	 * Method to login a user.
	 *
	 * @since	1.6
	 */
	public function remind()
	{
		// Check the request token.
		JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		$app	= JFactory::getApplication();
		$model	= $this->getModel('User', 'UsersModel');
		$data	= JRequest::getVar('jform', array(), 'post', 'array');

		// Submit the username remind request.
		$return	= $model->processRemindRequest($data);

		// Check for a hard error.
		if ($return instanceof Exception) {
			// Get the error message to display.
			if ($app->getCfg('error_reporting')) {
				$message = $return->getMessage();
			} else {
				$message = JText::_('COM_USERS_REMIND_REQUEST_ERROR');
			}

			// Get the route to the next page.
			$itemid = UsersHelperRoute::getRemindRoute();
			$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
			$route	= 'index.php?option=com_users&view=remind'.$itemid;

			// Go back to the complete form.
			$this->setRedirect(JRoute::_($route, false), $message, 'error');
			return false;
		} elseif ($return === false) {
			// Complete failed.
			// Get the route to the next page.
			$itemid = UsersHelperRoute::getRemindRoute();
			$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
			$route	= 'index.php?option=com_users&view=remind'.$itemid;

			// Go back to the complete form.
			$message = JText::sprintf('COM_USERS_REMIND_REQUEST_FAILED', $model->getError());
			$this->setRedirect(JRoute::_($route, false), $message, 'notice');
			return false;
		} else {
			// Complete succeeded.
			// Get the route to the next page.
			$itemid = UsersHelperRoute::getLoginRoute();
			$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
			$route	= 'index.php?option=com_users&view=login'.$itemid;

			// Proceed to the login form.
			$message = JText::_('COM_USERS_REMIND_REQUEST_SUCCESS');
			$this->setRedirect(JRoute::_($route, false), $message);
			return true;
		}
	}

	/**
	 * Method to login a user.
	 *
	 * @since	1.6
	 */
	public function resend()
	{
		// Check for request forgeries
		JSession::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));
	}

	public function link()
	{
		$user = JFactory::getUser();
		$app  = JFactory::getApplication();

		// First, they should already be logged in, so check for that
		if($user->get('guest'))
		{
			JError::raiseError( 403, JText::_( 'You must be logged in to perform this function' ));
			return;
		}

		$authenticator = JRequest::getVar('authenticator', '', 'method');

		// If a specific authenticator is specified try to call the link method for that plugin
		if (!empty($authenticator)) {
			JPluginHelper::importPlugin('authentication');

			$plugin = JPluginHelper::getPlugin('authentication', $authenticator);

			$className = 'plg'.$plugin->type.$plugin->name;

			if (class_exists($className)) {
				if (method_exists($className,'link')) {

					$myplugin = new $className($this,(array)$plugin);

					$myplugin->link();
				} else {
					// No Link method is availble
					$app->redirect(JRoute::_('index.php?option=com_members&id=' . $user->get('id') . '&active=account'),
						'Linked accounts are not currently available for this provider.',
						'error');
				}
			}
		} else {
			// No authenticator provided...
			JError::raiseError( 400, JText::_( 'Missing authenticator' ));
			return;
		}

		// Success!  Redict with message
		$app->redirect(JRoute::_('index.php?option=com_members&id=' . $user->get('id') . '&active=account'),
			'Your account has been successfully linked!');
	}

	public function attach(){}
}
