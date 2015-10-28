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

namespace Hubzero\Item\Comment;

/**
 * Table class for comments
 */
class File extends \JTable
{
	/**
	 * Upload path
	 *
	 * @var  string
	 */
	protected $_uploadDir    = '/sites/comments';

	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database
	 * @return  void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__item_comment_files', 'id', $db);
	}

	/**
	 * Validate data
	 *
	 * @return  boolean  True if data is valid
	 */
	public function check()
	{
		$this->filename = trim($this->filename);
		if (!$this->filename)
		{
			$this->setError(\Lang::txt('Please provide a file name'));
			return false;
		}

		$this->filename = $this->_checkFileName($this->_getUploadDir(), $this->filename);

		$this->comment_id = intval($this->comment_id);
		if (!$this->comment_id)
		{
			$this->setError(\Lang::txt('Missing comment ID.'));
			return false;
		}

		return true;
	}

	/**
	 * Set the upload path
	 *
	 * @param   string  $path  Path to set to
	 * @return  void
	 */
	public function setUploadDir($path)
	{
		$path = trim($path);

		$path = \Hubzero\Filesystem\Util::normalizePath($path);
		$path = str_replace(' ', '_', $path);

		$this->_uploadDir = ($path) ? $path : $this->_uploadDir;
	}

	/**
	 * Get the upload path
	 *
	 * @return  string
	 */
	private function _getUploadDir()
	{
		return PATH_APP . DS . ltrim($this->_uploadDir, DS);
	}

	/**
	 * Ensure no conflicting file names
	 *
	 * @param   string  $uploadDir  Upload path
	 * @param   string  $fileName   File name
	 * @return  string
	 */
	private function _checkFileName($uploadDir, $fileName)
	{
		$ext = strrchr($fileName, '.');
		$prefix = substr($fileName, 0, -strlen($ext));

		// rename file if exists
		$i = 1;
		while (is_file($uploadDir . DS . $fileName))
		{
			$fileName = $prefix . ++$i . $ext;
		}
		return $fileName;
	}

	/**
	 * Build query method
	 *
	 * @param   array   $filters
	 * @return  string  database query
	 */
	private function _buildQuery($filters=array())
	{
		$query = " FROM $this->_tbl AS f";

		$where = array();
		if (isset($filters['comment_id']))
		{
			$where[] = "f.`comment_id`=" . $this->_db->quote($filters['comment_id']);
		}
		if (isset($filters['filename']))
		{
			$where[] = "f.`filename`=" . $this->_db->quote($filters['filename']);
		}
		if (isset($filters['search']) && $filters['search'])
		{
			$where[] = "LOWER(f.`filename`) LIKE " . $this->_db->quote('%' . strtolower($filters['search']) . '%');
		}

		if (count($where) > 0)
		{
			$query .= " WHERE " . implode(" AND ", $where);
		}

		return $query;
	}

	/**
	 * Get an object list of course units
	 *
	 * @param   array   $filters
	 * @return  object  Return course units
	 */
	public function count($filters=array())
	{
		$query  = "SELECT COUNT(*)" . $this->_buildquery($filters);

		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}

	/**
	 * Get an object list of course units
	 *
	 * @param   array   $filters
	 * @return  object  Return course units
	 */
	public function find($filters=array())
	{
		$query  = "SELECT f.*" . $this->_buildquery($filters);

		if (!isset($filters['sort']) || !$filters['sort'])
		{
			$filters['sort'] = 'filename';
		}
		if (!isset($filters['sort_Dir']) || !in_array(strtoupper($filters['sort_Dir']), array('ASC', 'DESC')))
		{
			$filters['sort_Dir'] = 'ASC';
		}
		$query .= " ORDER BY " . $filters['sort'] . " " . $filters['sort_Dir'];
		if (!empty($filters['start']) && !empty($filters['limit']))
		{
			$query .= " LIMIT " . $filters['start'] . "," . $filters['limit'];
		}

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}


	/**
	 * Get Attachment by Comment ID
	 *
	 * @param   integer  $comment_id  ID of parent comment
	 * @return  boolean  true if successful otherwise returns and error message
	 */
	public function loadByComment($comment_id=NULL)
	{
		$this->_db->setQuery("SELECT * FROM $this->_tbl WHERE comment_id=" . $this->_db->quote((int) $comment_id));
		return $this->_db->loadObject();
	}


	/**
	 * Delete records by comment ID
	 *
	 * @param   integer  $comment_id  ID of parent comment
	 * @return  boolean  true if successful otherwise returns and error message
	 */
	public function deleteByComment($comment_id=NULL)
	{
		if ($comment_id === null)
		{
			$this->setError(\Lang::txt('Missing argument: comment ID'));
			return false;
		}

		$this->_db->setQuery("DELETE FROM $this->_tbl WHERE comment_id=" . $this->_db->quote((int) $comment_id));
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return true;
	}

	/**
	 * Delete a file
	 *
	 * @param   integer  $id   ID of parent comment
	 * @return  boolean  true if successful otherwise returns and error message
	 */
	public function delete($oid=null)
	{
		if (!$oid)
		{
			$oid = $this->id;
		}

		if (!$this->deleteFile($oid))
		{
			return false;
		}

		return parent::delete($oid);
	}

	/**
	 * Delete records by comment ID
	 *
	 * @param   integer  $comment_id  ID of parent comment
	 * @return  boolean  true  if successful otherwise returns and error message
	 */
	public function deleteFile($filename=NULL)
	{
		if ($filename === null)
		{
			$filename = $this->filename;
		}
		else if (is_numeric($filename) && $filename != $this->id)
		{
			$tbl = new self($this->_db);
			$tbl->load($filename);
			$filename = $tbl->filename;
		}

		if (file_exists($this->_getUploadDir() . DS . $filename))
		{
			if (!\Filesystem::delete($this->_getUploadDir() . DS . $filename))
			{
				$this->setError(\Lang::txt('Unable to delete file.'));
				return false;
			}
		}
		return true;
	}
}
