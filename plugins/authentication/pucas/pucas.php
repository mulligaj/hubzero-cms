<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Nicholas J. Kisseberth <nkissebe@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

require_once(JPATH_SITE . DS . 'libraries' . DS . 'CAS-1.3.3' . DS . 'CAS.php');

/**
 * Authentication Plugin class for PUCAS
 */
class plgAuthenticationPUCAS extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 */
	public function plgAuthenticationPucas(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Actions to perform when logging out a user session
	 *
	 * @return     void
	 */
	public function logout()
	{
		$app = JFactory::getApplication();

		if (JFactory::getApplication()->getCfg('debug'))
		{
			$debug_location = $this->params->get('debug_location', '/var/log/apache2/php/phpCAS.log');
			phpCAS::setDebug($debug_location);
		}

		if (!phpCAS::isInitialized())
		{
			phpCAS::client(CAS_VERSION_2_0, 'www.purdue.edu', 443, '/apps/account/cas', false);
		}

		$service = rtrim(JURI::base(),'/');

		if (empty($service))
		{
			$service = $_SERVER['HTTP_HOST'];
		}

		$return = '';

		if ($return = JRequest::getVar('return', '', 'method', 'base64'))
		{
			$return = base64_decode($return);

			if (!JURI::isInternal($return))
			{
				$return = '';
			}

			$return = '/' . ltrim($return, '/');
		}

		phpCAS::logout(array('service'=>$service . $return, 'url'=>$service . $return));
	}

	/**
	 * Check login status of current user with regards to Purdue CAS
	 *
	 * @access	public
	 * @return	Array $status
	 */
	public function status()
	{
		$app = JFactory::getApplication();

		$status = array();

		if (JFactory::getApplication()->getCfg('debug'))
		{
			$debug_location = $this->params->get('debug_location', '/var/log/apache2/php/phpCAS.log');
			phpCAS::setDebug($debug_location);
		}

		if (!phpCAS::isInitialized())
		{
			phpCAS::client(CAS_VERSION_2_0, 'www.purdue.edu', 443, '/apps/account/cas', false);
		}

		phpCAS::setNoCasServerValidation();

		if (phpCAS::checkAuthentication())
		{
			$status['username'] = phpCAS::getUser();
		}
		return $status;
	}

	/**
	 * Actions to perform when logging in a user session
	 *
	 * @param      unknown &$credentials Parameter description (if any) ...
	 * @param      array &$options Parameter description (if any) ...
	 * @return     void
	 */
	public function login(&$credentials, &$options)
	{
		if ($return = JRequest::getVar('return', '', 'method', 'base64'))
		{
			$return = base64_decode($return);
			if (!JURI::isInternal($return))
			{
				$return = '';
			}
		}

		$options['return'] = $return;
	}

	/**
	 * Method to setup Purdue CAS params and redirect to pucas auth URL
	 *
	 * @access	public
	 * @param   object	$view	view object
	 * @param 	object	$tpl	template object
	 * @return	void
	 */
	public function display($view, $tpl)
	{
		$app = JFactory::getApplication();

		if (JFactory::getApplication()->getCfg('debug'))
		{
			$debug_location = $this->params->get('debug_location', '/var/log/apache2/php/phpCAS.log');
			phpCAS::setDebug($debug_location);
		}

		if (!phpCAS::isInitialized())
		{
			phpCAS::client(CAS_VERSION_2_0, 'www.purdue.edu', 443, '/apps/account/cas', false);
		}

		$service = rtrim(JURI::base(),'/');

		if (empty($service))
		{
			$service = $_SERVER['HTTP_HOST'];
		}

		$return = '';

		if ($view->return)
		{
			$return = '&return=' . $view->return;
		}

		// If someone is logged in already, then we're linking an account, otherwise, we're just loggin in fresh
		$juser = JFactory::getUser();
		if (version_compare(JVERSION, '2.5', 'ge'))
		{
			$com_user = 'com_users';
			$task     = ($juser->get('guest')) ? 'user.login' : 'user.link';
		}
		else
		{
			$com_user = 'com_user';
			$task     = ($juser->get('guest')) ? 'login' : 'link';
		}

		phpCAS::setFixedServiceURL($service . '/index.php?option=' . $com_user . '&task=' . $task . '&authenticator=pucas' . $return);
		phpCAS::setNoCasServerValidation();
		phpCAS::forceAuthentication();

		$app->redirect($service . '/index.php?option=' . $com_user . '&task=' . $task . '&authenticator=pucas' . $return);
	}

	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @access	public
	 * @param   array 	$credentials Array holding the user credentials
	 * @param 	array   $options     Array of extra options
	 * @param	object	$response	 Authentication response object
	 * @return	boolean
	 */
	public function onAuthenticate( $credentials, $options, &$response )
	{
		return $this->onUserAuthenticate($credentials, $options, $response);
	}

	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @access	public
	 * @param   array 	$credentials Array holding the user credentials
	 * @param 	array   $options     Array of extra options
	 * @param	object	$response	 Authentication response object
	 * @return	boolean
	 * @since 1.5
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		if (JFactory::getApplication()->getCfg('debug'))
		{
			$debug_location = $this->params->get('debug_location', '/var/log/apache2/php/phpCAS.log');
			phpCAS::setDebug($debug_location);
		}

		if (!phpCAS::isInitialized())
		{
			phpCAS::client(CAS_VERSION_2_0, 'www.purdue.edu', 443, '/apps/account/cas', false);
		}

		phpCAS::setNoCasServerValidation();

		if (phpCAS::isAuthenticated())
		{
			$username = phpCAS::getUser();

			$method = (\JComponentHelper::getParams('com_users')->get('allowUserRegistration', false)) ? 'find_or_create' : 'find';
			$hzal = \Hubzero\Auth\Link::$method('authentication', 'pucas', null, $username);

			if ($hzal === false)
			{
				$response->status = JAUTHENTICATE_STATUS_FAILURE;
				$response->error_message = 'Unknown user and new user registration is not permitted.';
				return;
			}

			$hzal->email = $username . '@purdue.edu';

			$response->auth_link = $hzal;
			$response->type = 'pucas';
			$response->status = JAUTHENTICATE_STATUS_SUCCESS;

			$email = phpCAS::getAttribute('email');
			$name  = phpCAS::getAttribute('fullname');

			if (!empty($email))
			{
				$hzal->email = $email;
			}

			if (!empty($name))
			{
				$response->fullname = ucwords(strtolower($name));
			}

			if (!empty($hzal->user_id))
			{
				$user = JUser::getInstance($hzal->user_id); // Bring this in line with the rest of the system

				$response->username = $user->username;
				$response->email    = $user->email;
				$response->fullname = $user->name;
			}
			else
			{
				$response->username = '-' . $hzal->id; // The Open Group Base Specifications Issue 6, Section 3.426
				$response->email    = $response->username . '@invalid'; // RFC2606, section 2

				// Also set a suggested username for their hub account
				JFactory::getSession()->set('auth_link.tmp_username', $username);
			}

			$hzal->update();

			// If we have a real user, drop the authenticator cookie
			if (isset($user) && is_object($user))
			{
				// Set cookie with login preference info
				$prefs                  = array();
				$prefs['user_id']       = $user->get('id');
				$prefs['user_img']      = \Hubzero\User\Profile::getInstance($user->get('id'))->getPicture(0, false);
				$prefs['authenticator'] = 'pucas';

				$namespace = 'authenticator';
				$lifetime  = time() + 365*24*60*60;

				\Hubzero\Utility\Cookie::bake($namespace, $lifetime, $prefs);
			}
		}
		else
		{
			$response->status = JAUTHENTICATE_STATUS_FAILURE;
			$response->error_message = 'Username and password do not match or you do not have an account yet.';
		}
	}

	/**
	 * Similar to onAuthenticate, except we already have a logged in user, we're just linking accounts
	 *
	 * @access	public
	 * @param   array - $options
	 * @return	void
	 */
	public function link($options=array())
	{
		$app = JFactory::getApplication();

		// Get the user
		$juser = JFactory::getUser();

		if (JFactory::getApplication()->getCfg('debug'))
		{
			$debug_location = $this->params->get('debug_location', '/var/log/apache2/php/phpCAS.log');
			phpCAS::setDebug($debug_location);
		}

		if (!phpCAS::isInitialized())
		{
			phpCAS::client(CAS_VERSION_2_0, 'www.purdue.edu', 443, '/apps/account/cas', false);
		}

		phpCAS::setNoCasServerValidation();

		if (phpCAS::isAuthenticated())
		{
			// Get unique username
			$username = phpCAS::getUser();

			$hzad = \Hubzero\Auth\Domain::getInstance('authentication', 'pucas', '');

			// Create the link
			if (\Hubzero\Auth\Link::getInstance($hzad->id, $username))
			{
				// This purdue cas account is already linked to another hub account
				$app->redirect(JRoute::_('index.php?option=com_members&id=' . $juser->get('id') . '&active=account'),
					'This Purdue Career Account appears to already be linked to a hub account',
					'error');
			}
			else
			{
				$hzal = \Hubzero\Auth\Link::find_or_create('authentication', 'pucas', null, $username);
				$hzal->user_id = $juser->get('id');
				$hzal->email   = phpCAS::getAttribute('email');
				$hzal->update();
			}
		}
		else
		{
			// User somehow got redirect back without being authenticated (not sure how this would happen?)
			$app->redirect(JRoute::_('index.php?option=com_members&id=' . $juser->get('id') . '&active=account'),
				'There was an error linking your Purdue Career Account, please try again later.',
				'error');
		}
	}
}