<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_HZEXEC_') or die();

/**
 * Templates component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @since		1.6
 */
class TemplatesHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		Submenu::addEntry(
			Lang::txt('COM_TEMPLATES_SUBMENU_STYLES'),
			Route::url('index.php?option=com_templates&view=styles'),
			$vName == 'styles'
		);
		Submenu::addEntry(
			Lang::txt('COM_TEMPLATES_SUBMENU_TEMPLATES'),
			Route::url('index.php?option=com_templates&view=templates'),
			$vName == 'templates'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	Object
	 */
	public static function getActions()
	{
		$result = new \Hubzero\Base\Object;

		$actions = JAccess::getActions('com_templates');

		foreach ($actions as $action)
		{
			$result->set($action->name, User::authorise($action->name, 'com_templates'));
		}

		return $result;
	}

	/**
	 * Get a list of filter options for the application clients.
	 *
	 * @return	array	An array of JHtmlOption elements.
	 */
	public static function getClientOptions()
	{
		// Build the filter options.
		$options = array();
		$options[] = Html::select('option', '0', Lang::txt('JSITE'));
		$options[] = Html::select('option', '1', Lang::txt('JADMINISTRATOR'));

		return $options;
	}

	/**
	 * Get a list of filter options for the templates with styles.
	 *
	 * @return	array	An array of JHtmlOption elements.
	 */
	public static function getTemplateOptions($clientId = '*')
	{
		// Build the filter options.
		$db = App::get('db');
		$query = $db->getQuery(true);

		if ($clientId != '*')
		{
			$query->where('client_id='.(int) $clientId);
		}

		$query->select('element as value, name as text, extension_id as e_id');
		$query->from('#__extensions');
		$query->where('type='.$db->quote('template'));
		$query->where('enabled=1');
		$query->order('client_id');
		$query->order('name');
		$db->setQuery($query);
		$options = $db->loadObjectList();
		return $options;
	}

	public static function parseXMLTemplateFile($templateBaseDir, $templateDir)
	{
		$data = new \Hubzero\Base\Object;

		// Check of the xml file exists
		$filePath = Filesystem::cleanPath($templateBaseDir.'/templates/'.$templateDir.'/templateDetails.xml');
		if (is_file($filePath))
		{
			$xml = JInstaller::parseXMLInstallFile($filePath);

			if ($xml['type'] != 'template')
			{
				return false;
			}

			foreach ($xml as $key => $value)
			{
				$data->set($key, $value);
			}
		}

		return $data;
	}
}
