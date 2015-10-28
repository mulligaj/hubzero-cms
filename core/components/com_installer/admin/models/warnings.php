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

use Lang;

jimport('joomla.application.component.modellist');

/**
 * Extension Manager Templates Model
 */
class Warnings extends \JModelList
{
	/**
	 * Extension Type
	 * @var	string
	 */
	var $type = 'warnings';

	/**
	 * Return the byte value of a particular string.
	 *
	 * @param	string	String optionally with G, M or K suffix
	 *
	 * @return	int		size in bytes
	 *
	 * @since 1.6
	 */
	public function return_bytes($val)
	{
		$val = trim($val);
		$last = strtolower($val{strlen($val)-1});
		switch ($last)
		{
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}

	/**
	 * Load the data.
	 *
	 * @since	1.6
	 */
	public function getItems()
	{
		static $messages;

		if ($messages)
		{
			return $messages;
		}

		$messages = array();
		$file_uploads = ini_get('file_uploads');
		if (!$file_uploads)
		{
			$messages[] = array('message'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_FILEUPLOADSDISABLED'), 'description'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_FILEUPLOADISDISABLEDDESC'));
		}

		$upload_dir = ini_get('upload_tmp_dir');
		if (!$upload_dir)
		{
			$messages[] = array('message'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTSET'), 'description'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTSETDESC'));
		}
		else
		{
			if (!is_writeable($upload_dir))
			{
				$messages[] = array('message'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTWRITEABLE'), 'description'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTWRITEABLEDESC', $upload_dir));
			}
		}

		$tmp_path = Config::get('tmp_path');
		if (!$tmp_path)
		{
			$messages[] = array('message'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTSET'), 'description'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTSETDESC'));
		}
		else
		{
			if (!is_writeable($tmp_path))
			{
				$messages[] = array('message'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTWRITEABLE'), 'description'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTWRITEABLEDESC', $tmp_path));
			}
		}

		$memory_limit = $this->return_bytes(ini_get('memory_limit'));
		if ($memory_limit < (8 * 1024 * 1024) && $memory_limit != -1)
		{ // 8MB
			$messages[] = array('message'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_LOWMEMORYWARN'), 'description'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_LOWMEMORYDESC'));
		}
		elseif ($memory_limit < (16 * 1024 * 1024) && $memory_limit != -1)
		{ //16MB
			$messages[] = array('message'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_MEDMEMORYWARN'), 'description'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_MEDMEMORYDESC'));
		}


		$post_max_size = $this->return_bytes(ini_get('post_max_size'));
		$upload_max_filesize = $this->return_bytes(ini_get('upload_max_filesize'));

		if ($post_max_size < $upload_max_filesize)
		{
			$messages[] = array('message'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_UPLOADBIGGERTHANPOST'), 'description'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_UPLOADBIGGERTHANPOSTDESC'));
		}

		if ($post_max_size < (4 * 1024 * 1024)) // 4MB
		{
			$messages[] = array('message'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_SMALLPOSTSIZE'), 'description'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_SMALLPOSTSIZEDESC'));
		}

		if ($upload_max_filesize < (4 * 1024 * 1024)) // 4MB
		{
			$messages[] = array('message'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_SMALLUPLOADSIZE'), 'description'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_SMALLUPLOADSIZEDESC'));
		}

		if (!class_exists('imagick'))
		{
			$messages[] = array('message'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_MISSING_IMAGICK'), 'description'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_MISSING_IMAGICK_DESC'));
		}

		if (!file_exists(DS . 'usr' . DS . 'bin' . DS . 'unzip'))
		{
			$messages[] = array('message'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_MISSING_UNZIP'), 'description'=>Lang::txt('COM_INSTALLER_MSG_WARNINGS_MISSING_UNZIP_DESC'));
		}

		return $messages;
	}
}
