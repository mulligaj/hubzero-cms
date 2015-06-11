<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2014 Purdue University. All rights reserved.
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
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2014 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Hubzero\View\Helper;

use Hubzero\Document\Assets;

/**
 * Helper for pushing styles to the document.
 */
class Css extends AbstractHelper
{
	/**
	 * Push CSS to the document
	 *
	 * @param   string  $stylesheet  Stylesheet or styles to add
	 * @param   string  $extension   Extension name, e.g.: com_example, mod_example, plg_example_test
	 * @param   string  $element     Plugin element. Only used for plugins and if first argument is folder name.
	 * @return  object
	 */
	public function __invoke($stylesheet = '', $extension = null, $element = null)
	{
		$extension = $extension ?: $this->_extension();

		if ($element)
		{
			$extension = 'plg_' . $extension . '_' . $element;
		}

		if (substr($stylesheet, 0, strlen('http')) == 'http'
		 || substr($stylesheet, 0, strlen('://')) == '://')
		{
			\JFactory::getDocument()->addStyleDeclaration($stylesheet);
			return $this->getView();
		}

		// Adding style declarations
		if ($extension === true || strstr($stylesheet, '{') || strstr($stylesheet, '@'))
		{
			\JFactory::getDocument()->addStyleDeclaration($stylesheet);
			return $this->getView();
		}

		if ($stylesheet && substr($stylesheet, -4) != '.css')
		{
			$stylesheet .= '.css';
		}

		// Adding from an absolute path
		$dir = $this->_assetDir($stylesheet, 'css');
		if ($dir == '/')
		{
			Assets::addStylesheet($dir . $stylesheet);
			return $this->getView();
		}

		// Adding a system stylesheet
		if ($extension == 'system')
		{
			Assets::addSystemStylesheet($stylesheet, $dir);
			return $this->getView();
		}

		// Adding an extension stylesheet
		switch (substr($extension, 0, 4))
		{
			case 'com_': Assets::addComponentStylesheet($extension, $stylesheet, $dir);          break;
			case 'plg_':
				list($ex, $folder, $element) = explode('_', $extension);
				Assets::addPluginStylesheet($folder, $element, $stylesheet, $dir);
			break;
			case 'mod_': Assets::addModuleStylesheet($extension, $stylesheet, $dir);             break;
			default:     Assets::addComponentStylesheet('com_' . $extension, $stylesheet, $dir); break;
		}

		return $this->getView();
	}

	/**
	 * Determine the extension the view is being called from
	 *
	 * @return  string
	 */
	private function _extension()
	{
		if ($this->getView() instanceof \Hubzero\Plugin\View)
		{
			return 'plg_' . $this->getView()->getFolder() . '_' . $this->getView()->getElement();
		}
		return $this->getView()->get('option', \JRequest::getCmd('option'));
	}

	/**
	 * Determine the asset directory
	 *
	 * @param   string  $path     File path
	 * @param   string  $default  Default directory
	 * @return  string
	 */
	private function _assetDir(&$path, $default='')
	{
		if (substr($path, 0, 2) == './')
		{
			$path = substr($path, 2);

			return '';
		}

		if (substr($path, 0, 1) == '/')
		{
			$path = substr($path, 1);

			return '/';
		}

		return $default;
	}
}
