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
 * @author    Alissa Nedossekina <alisa@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Publications\Models;

use Hubzero\Base\Object;

include_once(dirname(__FILE__) . DS . 'attachment.php');
include_once(dirname(__FILE__) . DS . 'status.php');

require_once(dirname(__DIR__) . DS . 'tables' . DS . 'attachment.php');

/**
 * Publications attachments class
 *
 */
class Attachments extends Object
{
	/**
	 * JDatabase
	 *
	 * @var object
	 */
	public $_db   		= NULL;

	/**
	* @var    array  Loaded elements
	*/
	protected $_types 	= array();

	/**
	* @var    array  Directories, where attachment types can be stored
	*/
	protected $_path 	= array();

	/**
	 * Constructor
	 *
	 * @param      object  &$db      	 JDatabase
	 * @return  void
	 */
	public function __construct(&$db)
	{
		$this->_db 		= $db;
		$this->_path[] 	= dirname(__FILE__) . DS . 'attachments';
	}

	/**
	 * Get attachments connector
	 *
	 * @return object
	 */
	public function connector($name)
	{
		// Load attachment type
		$type = $this->loadAttach($name);

		if ($type === false)
		{
			return false;
		}
		return $type->getConnector();
	}

	/**
	 * Get status for an attachment within publication
	 *
	 * @return object
	 */
	public function getStatus($name, $element = NULL, $elementId = 0, $attachments = NULL)
	{
		// Load attachment type
		$type = $this->loadAttach($name);

		if ($type === false)
		{
			$status = new \Components\Publications\Models\Status();
			$status->setError(Lang::txt('Attachment type not found') );
		}
		else
		{
			$attachments = isset($attachments['elements'][$elementId]) ? $attachments['elements'][$elementId] : NULL;

			// Sort out attachments for this element
			$attachments = self::getElementAttachments($elementId, $attachments, $name);

			$status = $type->getStatus($element, $attachments);
		}

		// Return status
		return $status;
	}

	/**
	 * Transfer data
	 *
	 * @return boolean
	 */
	public function transferData($name, $element = NULL, $elementId = 0,
		$pub = NULL, $params = NULL, $oldVersion, $newVersion)
	{
		// Load attachment type
		$type = $this->loadAttach($name);

		if ($type === false)
		{
			$status->setError(Lang::txt('Attachment type not found') );
		}
		else
		{
			$attachments = $pub->_attachments;
			$attachments = isset($attachments['elements'][$elementId]) ? $attachments['elements'][$elementId] : NULL;

			// Sort out attachments for this element
			$attachments = self::getElementAttachments($elementId, $attachments, $name);
			if ($attachments)
			{
				$type->transferData($element->params, $elementId, $pub, $params,
					$attachments, $oldVersion, $newVersion
				);
			}
		}

	}

	/**
	 * Attach items to publication
	 *
	 * @return object
	 */
	public function attach($name, $element = NULL, $elementId = 0, $pub = NULL, $params = NULL)
	{
		// Load attachment type
		$type = $this->loadAttach($name);

		if ($type === false)
		{
			return false;
		}

		// Save incoming selections
		if ($type->save($element, $elementId, $pub, $params))
		{
			if ($type->get('_message'))
			{
				$this->set('_message', $type->get('_message'));
			}

			return true;
		}

		return false;

	}

	/**
	 * Serve attachments within element
	 *
	 * @return object
	 */
	public function serve($name = NULL, $element = NULL,
		$elementId = 0, $pub = NULL, $params = NULL, $itemId = NULL)
	{
		if ($name === NULL)
		{
			return false;
		}

		// Load attachment type
		$type = $this->loadAttach($name);

		if ($type === false)
		{
			return false;
		}

		// Serve attachments
		if ($content = $type->serve($element, $elementId, $pub, $params, $itemId))
		{
			if ($type->get('_message'))
			{
				$this->set('_message', $type->get('_message'));
			}

			return $content;
		}

		return false;
	}

	/**
	 * Draw list of element items
	 *
	 * @return object
	 */
	public function listItems($elements = NULL, $pub = NULL, $authorized = true, $append = NULL)
	{
		if (empty($elements) || $pub === NULL)
		{
			return false;
		}

		$output = '<ul class="element-list">';
		$i = 0;
		$links = '';
		foreach ($elements as $element)
		{
			// Load attachment type
			$type = $this->loadAttach($element->manifest->params->type);

			if ($type === false)
			{
				return false;
			}

			$attachments = $pub->_attachments;
			$attachments = isset($attachments['elements'][$element->id])
						 ? $attachments['elements'][$element->id] : NULL;

			if ($attachments)
			{
				$i++;
			}
			// Draw link(s)
			$links .= $type->drawList(
				$attachments,
				$element->manifest,
				$element->id,
				$pub,
				$element->block,
				$authorized
			);
		}
		$output .= $links;
		$output .= $append;
		$output .= '</ul>';

		return trim($links) ? $output : false;
	}

	/**
	 * Draw launching button/link for element
	 *
	 * @return object
	 */
	public function drawLauncher($name = NULL, $pub = NULL, $element = NULL, $elements = NULL, $authorized = true)
	{
		if ($name === NULL || $element === NULL || $pub === NULL)
		{
			return false;
		}

		// Load attachment type
		$type = $this->loadAttach($name);

		if ($type === false)
		{
			return false;
		}

		// Draw link
		return $type->drawLauncher($element->manifest, $element->id, $pub, $element->block, $elements, $authorized);
	}

	/**
	 * Draws attachment
	 *
	 * @return  object
	 */
	public function drawAttachment( $name, $data = NULL, $typeParams = NULL, $handler = NULL )
	{
		// Load attachment type
		$type = $this->loadAttach($name);

		if ($type === false)
		{
			return false;
		}
		if (!$data)
		{
			return false;
		}

		// Draw
		return $type->drawAttachment($data, $typeParams, $handler);
	}

	/**
	 * Draws attachment
	 *
	 * @return  object
	 */
	public function buildDataObject( $name, $attachment, $view, $ordering = 1 )
	{
		// Load attachment type
		$type = $this->loadAttach($name);

		if ($type === false)
		{
			return false;
		}

		// Draw
		return $type->buildDataObject($attachment, $view, $ordering);
	}

	/**
	 * Update attachment record
	 *
	 * @return object
	 */
	public function update($name, $row, $pub, $actor, $elementId, $element, $params)
	{
		// Load attachment type
		$type = $this->loadAttach($name);

		if ($type === false)
		{
			return false;
		}

		// We do need attachment record
		if (!$row || !$row->id)
		{
			return false;
		}

		// Save incoming info
		if ($type->updateAttachment($row, $element->params, $elementId, $pub, $params))
		{
			if ($type->get('_message'))
			{
				$this->set('_message', $type->get('_message'));
			}

			return true;
		}

		return false;

	}

	/**
	 * Remove attachment
	 *
	 * @return object
	 */
	public function remove($name, $row, $pub, $actor, $elementId, $element, $params)
	{
		// Load attachment type
		$type = $this->loadAttach($name);

		if ($type === false)
		{
			return false;
		}

		// We do need attachment record
		if (!$row || !$row->id)
		{
			return false;
		}

		// Save incoming info
		if ($type->removeAttachment($row, $element->params, $elementId, $pub, $params))
		{
			if ($type->get('_message'))
			{
				$this->set('_message', $type->get('_message'));
			}

			return true;
		}

		return false;
	}

	/**
	 * Get element attachments (ween out inapplicable attachments)
	 *
	 * @return  object
	 */
	public function getElementAttachments( $elementId = 0, $attachments = array(),
		$type = '', $role = '', $includeUnattached = true )
	{
		$collect = array();

		if (!$attachments || !$elementId)
		{
			return $attachments;
		}

		foreach ($attachments as $attach)
		{
			// Fix up supporting docs
			$attach->role = $attach->role ? $attach->role : 2;

			// Skip items in different role
			if ($role && ($attach->role != $role))
			{
				continue;
			}

			// Skip items of different type
			if ($type && $attach->type != $type)
			{
				continue;
			}

			// Collect
			if (($attach->element_id == $elementId) || ($includeUnattached == true && !$attach->element_id))
			{
				$collect[] = $attach;
			}
		}

		return $collect;
	}

	/**
	 * Loads a block
	 *
	 * @return  object
	 */
	public function loadAttach( $name, $new = false )
	{
		$signature = md5($name);

		if ((isset($this->_types[$signature])
			&& !($this->_types[$signature] instanceof __PHP_Incomplete_Class))
			&& $new === false)
		{
			return	$this->_types[$signature];
		}

		$elementClass = __NAMESPACE__ . '\\Attachment\\' . ucfirst($name);
		if (!class_exists($elementClass))
		{
			if (isset($this->_path))
			{
				$dirs = $this->_path;
			}
			else
			{
				$dirs = array();
			}

			$file = \JFilterInput::getInstance()->clean(str_replace('_', DS, $name).'.php', 'path');

			jimport('joomla.filesystem.path');
			if ($elementFile = \JPath::find($dirs, $file))
			{
				include_once $elementFile;
			}
			else
			{
				$false = false;
				return $false;
			}
		}

		if (!class_exists($elementClass))
		{
			$false = false;
			return $false;
		}

		$this->_types[$signature] = new $elementClass($this);
		return $this->_types[$signature];
	}

	/**
	 * Bundle elements
	 *
	 * @return object
	 */
	public function bundleItems($zip = NULL, $elements = NULL,
		$pub = NULL, &$readme, $bundleDir)
	{
		if ($zip === NULL || empty($elements) || $pub === NULL)
		{
			return false;
		}

		foreach ($elements as $element)
		{
			// File?
			if ($element->manifest->params->type != 'file')
			{
			//	continue;
			}

			// Load attachment type
			$type = $this->loadAttach($element->manifest->params->type);

			if ($type === false)
			{
				return false;
			}

			$attachments = $pub->_attachments;
			$attachments = isset($attachments['elements'][$element->id])
						 ? $attachments['elements'][$element->id] : NULL;

			// Add to bundle
			$type->addToBundle(
				$zip,
				$attachments,
				$element->manifest,
				$element->id,
				$pub,
				$element->block,
				$readme,
				$bundleDir
			);
		}
		return;
	}

	/**
	 * Show bundle elements
	 *
	 * @return object
	 */
	public function showPackagedItems($elements = NULL, $pub = NULL)
	{
		if (empty($elements) || $pub === NULL)
		{
			return false;
		}

		$contents = NULL;
		foreach ($elements as $element)
		{
			// File?
			if ($element->manifest->params->type != 'file')
			{
			//	continue;
			}

			// Load attachment type
			$type = $this->loadAttach($element->manifest->params->type);

			if ($type === false)
			{
				return false;
			}

			$attachments = $pub->_attachments;
			$attachments = isset($attachments['elements'][$element->id])
						 ? $attachments['elements'][$element->id] : NULL;

			// Add to bundle
			$contents .= $type->drawPackageList(
				$attachments,
				$element->manifest,
				$element->id,
				$pub,
				$element->block,
				true
			);
		}
		return $contents;
	}
}