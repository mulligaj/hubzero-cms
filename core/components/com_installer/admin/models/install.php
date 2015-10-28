<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @copyright Copyright 2005-2014 Open Source Matters, Inc.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 */

namespace Components\Installer\Admin\Models;

use Filesystem;
use Request;
use Config;
use Notify;
use User;
use Lang;
use App;

/**
 * Extension Manager Install Model
 */
class Install extends \JModelLegacy
{
	/**
	 * @var object JTable object
	 */
	protected $_table = null;

	/**
	 * @var object JTable object
	 */
	protected $_url = null;

	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_installer.install';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		// Initialise variables.
		$this->setState('message', User::getState('com_installer.message'));
		$this->setState('extension_message', User::getState('com_installer.extension_message'));

		User::setState('com_installer.message', '');
		User::setState('com_installer.extension_message', '');

		// Recall the 'Install from Directory' path.
		$path = Request::getState($this->_context . '.install_directory', 'install_directory', Config::get('tmp_path'));
		$this->setState('install.directory', $path);
		parent::populateState();
	}

	/**
	 * Install an extension from either folder, url or upload.
	 *
	 * @return	boolean result of install
	 * @since	1.5
	 */
	public function install()
	{
		$this->setState('action', 'install');

		// Set FTP credentials, if given.
		\JClientHelper::setCredentialsFromRequest('ftp');

		switch (Request::getWord('installtype'))
		{
			case 'folder':
				// Remember the 'Install from Directory' path.
				Request::getState($this->_context.'.install_directory', 'install_directory');
				$package = $this->_getPackageFromFolder();
				break;

			case 'upload':
				$package = $this->_getPackageFromUpload();
				break;

			case 'url':
				$package = $this->_getPackageFromUrl();
				break;

			default:
				User::setState('com_installer.message', Lang::txt('COM_INSTALLER_NO_INSTALL_TYPE_FOUND'));
				return false;
				break;
		}

		// Was the package unpacked?
		if (!$package)
		{
			User::setState('com_installer.message', Lang::txt('COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE'));
			return false;
		}

		// Get an installer instance
		$installer = \JInstaller::getInstance();

		// Install the package
		if (!$installer->install($package['dir']))
		{
			// There was an error installing the package
			Notify::error(Lang::txt('COM_INSTALLER_INSTALL_ERROR', Lang::txt('COM_INSTALLER_TYPE_TYPE_'.strtoupper($package['type']))));
			$result = false;
		}
		else
		{
			// Package installed sucessfully
			Notify::success(Lang::txt('COM_INSTALLER_INSTALL_SUCCESS', Lang::txt('COM_INSTALLER_TYPE_TYPE_'.strtoupper($package['type']))));
			$result = true;
		}

		// Set some model state values
		$this->setState('name', $installer->get('name'));
		$this->setState('result', $result);

		User::setState('com_installer.message', $installer->message);
		User::setState('com_installer.extension_message', $installer->get('extension_message'));
		User::setState('com_installer.redirect_url', $installer->get('redirect_url'));

		// Cleanup the install files
		if (!is_file($package['packagefile']))
		{
			$package['packagefile'] = Config::get('tmp_path') . '/' . $package['packagefile'];
		}

		\JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		return $result;
	}

	/**
	 * Works out an installation package from a HTTP upload
	 *
	 * @return package definition or false on failure
	 */
	protected function _getPackageFromUpload()
	{
		// Get the uploaded file information
		$userfile = Request::getVar('install_package', null, 'files', 'array');

		// Make sure that file uploads are enabled in php
		if (!(bool) ini_get('file_uploads'))
		{
			Notify::warning( Lang::txt('COM_INSTALLER_MSG_INSTALL_WARNINSTALLFILE'));
			return false;
		}

		// Make sure that zlib is loaded so that the package can be unpacked
		if (!extension_loaded('zlib'))
		{
			Notify::warning(Lang::txt('COM_INSTALLER_MSG_INSTALL_WARNINSTALLZLIB'));
			return false;
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile))
		{
			Notify::warning(Lang::txt('COM_INSTALLER_MSG_INSTALL_NO_FILE_SELECTED'));
			return false;
		}

		// Check if there was a problem uploading the file.
		if ($userfile['error'] || $userfile['size'] < 1)
		{
			Notify::warning(Lang::txt('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR'));
			return false;
		}

		// Build the appropriate paths
		$tmp_dest = Config::get('tmp_path') . '/' . $userfile['name'];
		$tmp_src  = $userfile['tmp_name'];

		// Move uploaded file
		$uploaded = Filesystem::upload($tmp_src, $tmp_dest);

		// Unpack the downloaded package file
		$package = \JInstallerHelper::unpack($tmp_dest);

		return $package;
	}

	/**
	 * Install an extension from a directory
	 *
	 * @return	Package details or false on failure
	 * @since	1.5
	 */
	protected function _getPackageFromFolder()
	{
		// Get the path to the package to install
		$p_dir = Request::getString('install_directory');
		$p_dir = Filesystem::cleanPath($p_dir);

		// Did you give us a valid directory?
		if (!is_dir($p_dir))
		{
			Notify::warning( Lang::txt('COM_INSTALLER_MSG_INSTALL_PLEASE_ENTER_A_PACKAGE_DIRECTORY'));
			return false;
		}

		// Detect the package type
		$type = \JInstallerHelper::detectType($p_dir);

		// Did you give us a valid package?
		if (!$type)
		{
			Notify::warning(Lang::txt('COM_INSTALLER_MSG_INSTALL_PATH_DOES_NOT_HAVE_A_VALID_PACKAGE'));
			return false;
		}

		$package['packagefile'] = null;
		$package['extractdir'] = null;
		$package['dir'] = $p_dir;
		$package['type'] = $type;

		return $package;
	}

	/**
	 * Install an extension from a URL
	 *
	 * @return	Package details or false on failure
	 * @since	1.5
	 */
	protected function _getPackageFromUrl()
	{
		// Get the URL of the package to install
		$url = Request::getString('install_url');

		// Did you give us a URL?
		if (!$url)
		{
			Notify::warning(Lang::txt('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL'));
			return false;
		}

		// Download the package at the URL given
		$p_file = \JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file)
		{
			Notify::warning(Lang::txt('COM_INSTALLER_MSG_INSTALL_INVALID_URL'));
			return false;
		}

		$tmp_dest = Config::get('tmp_path');

		// Unpack the downloaded package file
		$package = \JInstallerHelper::unpack($tmp_dest . '/' . $p_file);

		return $package;
	}
}
