<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Config\Models;

use JModelForm;
use JAccessRules;
use JAccess;
use JFactory;
use JConfig;
use JPath;
use Lang;
use JTable;
use Hubzero\Config\Registry;
use JClientHelper;
use JFilterOutput;

jimport('joomla.application.component.modelform');

/**
 * Model class for Application config
 */
class Application extends JModelForm
{
	/**
	 * Method to get a form object.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 * @return  mixed    A JForm object on success, false on failure
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_config.application', 'application', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the configuration data.
	 *
	 * This method will load the global configuration data straight from
	 * JConfig. If configuration data has been saved in the session, that
	 * data will be merged into the original data, overwriting it.
	 *
	 * @return  array  An array containg all global config data.
	 * @since   1.6
	 */
	public function getData()
	{
		// Get the config data.
		//$config = new JConfig();
		$data = Config::getRoot()->toArray(); //\Hubzero\Utility\Arr::fromObject($config);

		// Prime the asset_id for the rules.
		$data['asset_id'] = 1;

		// Get the text filter data
		$params = \Component::params('com_config');
		$data['filters'] = \Hubzero\Utility\Arr::fromObject($params->get('filters'));

		// If no filter data found, get from com_content (update of 1.6/1.7 site)
		if (empty($data['filters']))
		{
			$contentParams = \Component::params('com_content');
			$data['filters'] = \Hubzero\Utility\Arr::fromObject($contentParams->get('filters'));
		}

		// Check for data in the session.
		$temp = User::getState('com_config.config.global.data');

		// Merge in the session data.
		if (!empty($temp))
		{
			$data = array_merge($data, $temp);
		}

		return $data;
	}

	/**
	 * Method to save the configuration data.
	 *
	 * @param   array  An array containing all global config data.
	 * @return  bool   True on success, false on failure.
	 * @since   1.6
	 */
	public function save($data)
	{
		// Save the rules
		if (isset($data['rules']))
		{
			$rules = new JAccessRules($data['rules']);

			// Check that we aren't removing our Super User permission
			// Need to get groups from database, since they might have changed
			$myGroups = JAccess::getGroupsByUser(\User::get('id'));
			$myRules = $rules->getData();
			$hasSuperAdmin = $myRules['core.admin']->allow($myGroups);
			if (!$hasSuperAdmin)
			{
				$this->setError(Lang::txt('COM_CONFIG_ERROR_REMOVING_SUPER_ADMIN'));
				return false;
			}

			$asset = JTable::getInstance('asset');
			if ($asset->loadByName('root.1'))
			{
				$asset->rules = (string) $rules;

				if (!$asset->check() || !$asset->store())
				{
					Notify::error('SOME_ERROR_CODE', $asset->getError());
				}
			}
			else
			{
				$this->setError(Lang::txt('COM_CONFIG_ERROR_ROOT_ASSET_NOT_FOUND'));
				return false;
			}
			unset($data['rules']);
		}

		// Save the text filters
		if (isset($data['filters']))
		{
			$registry = new Registry(array('filters' => $data['filters']));

			$extension = JTable::getInstance('extension');

			// Get extension_id
			$extension_id = $extension->find(array('name' => 'com_config'));

			if ($extension->load((int) $extension_id))
			{
				$extension->params = (string) $registry;
				if (!$extension->check() || !$extension->store())
				{
					Notify::error('SOME_ERROR_CODE', $extension->getError());
				}
			}
			else
			{
				$this->setError(Lang::txt('COM_CONFIG_ERROR_CONFIG_EXTENSION_NOT_FOUND'));
				return false;
			}
			unset($data['filters']);
		}

		// Get the previous configuration.
		$config = new \Hubzero\Config\Repository('site');

		$prev = $config->toArray();

		/*$extras = array();
		foreach ($prev as $key => $val)
		{
			$found = false;

			foreach ($data as $group => $values)
			{
				if (in_array($key, $values))
				{
					$found = true;
				}
			}

			if (!$found)
			{
				$extras[$key] = $val;
			}
		}

		// Merge the new data in. We do this to preserve values that were not in the form.
		$data['app'] = array_merge($data['app'], $extras);*/

		// Perform miscellaneous options based on configuration settings/changes.
		// Escape the offline message if present.
		if (isset($data['offline']['offline_message']))
		{
			$data['offline']['offline_message'] = \Hubzero\Utility\String::ampReplace($data['offline']['offline_message']);
		}

		// Purge the database session table if we are changing to the database handler.
		if ($prev['session']['session_handler'] != 'database' && $data['session']['session_handler'] == 'database')
		{
			$table = JTable::getInstance('session');
			$table->purge(-1);
		}

		if (empty($data['cache']['cache_handler']))
		{
			$data['cache']['caching'] = 0;
		}

		// Clean the cache if disabled but previously enabled.
		if (!$data['cache']['caching'] && $prev['cache']['caching'])
		{
			\Cache::clean();
		}

		foreach ($data as $group => $values)
		{
			foreach ($values as $key => $value)
			{
				if (!isset($prev[$group]))
				{
					$prev[$group] = array();
				}
				$prev[$group][$key] = $value;
			}
		}

		// Create the new configuration object.
		//$config = new Registry($data);

		// Overwrite the old FTP credentials with the new ones.
		if (isset($data['ftp']))
		{
			$temp = \Config::getRoot();
			$temp->set('ftp.ftp_enable', $data['ftp']['ftp_enable']);
			$temp->set('ftp.ftp_host', $data['ftp']['ftp_host']);
			$temp->set('ftp.ftp_port', $data['ftp']['ftp_port']);
			$temp->set('ftp.ftp_user', $data['ftp']['ftp_user']);
			$temp->set('ftp.ftp_pass', $data['ftp']['ftp_pass']);
			$temp->set('ftp.ftp_root', $data['ftp']['ftp_root']);
		}

		// Clear cache of com_config component.
		$this->cleanCache('_system');

		// Write the configuration file.
		return $this->writeConfigFile($prev);
	}

	/**
	 * Method to unset the root_user value from configuration data.
	 *
	 * This method will load the global configuration data straight from
	 * JConfig and remove the root_user value for security, then save the configuration.
	 *
	 * @return  boolean
	 * @since   1.6
	 */
	public function removeroot()
	{
		// Get the previous configuration.
		$prev = new JConfig();
		$prev = \Hubzero\Utility\Arr::fromObject($prev);

		// Create the new configuration object, and unset the root_user property
		unset($prev['root_user']);

		$config = new Registry($prev);

		// Write the configuration file.
		return $this->writeConfigFile($config);
	}

	/**
	 * Method to write the configuration to a file.
	 *
	 * @param   object  $config  A Registry object containing all global config data.
	 * @return  bool    True on success, false on failure.
	 * @since   2.5.4
	 */
	private function writeConfigFile($data)
	{
		if ($data instanceof \Hubzero\Config\Repository)
		{
			$data = $data->toArray();
		}

		// Attempt to write the configuration files
		$writer = new \Hubzero\Config\FileWriter(
			'php',
			PATH_APP . DS . 'config'
		);

		$client = null;

		foreach ($data as $group => $values)
		{
			if (!$writer->write($values, $group, $client))
			{
				$this->setError(Lang::txt('COM_CONFIG_ERROR_WRITE_FAILED'));
				return false;
			}
		}

		$legacy = new \Hubzero\Config\Legacy();
		if ($legacy->exists())
		{
			$legacy->reset();

			foreach ($data as $group => $values)
			{
				foreach ($values as $key => $val)
				{
					$legacy->set($key, $val);
				}
			}

			$legacy->update();
		}

		return true;
	}
}
