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

namespace Components\Projects\Models;

include_once(PATH_CORE . DS . 'components' . DS . 'com_wiki' . DS . 'models' . DS . 'book.php');
include_once(PATH_CORE . DS . 'components' . DS . 'com_wiki' . DS . 'helpers' . DS . 'editor.php');
include_once(PATH_CORE . DS . 'components' . DS . 'com_wiki' . DS . 'helpers' . DS . 'parser.php');

use Components\Projects\Tables;

/**
 * Project Note model
 */
class Note extends \Components\Wiki\Models\Book
{
	/**
	 * Project group cn
	 *
	 * @var string
	 */
	private $_group_cn = '';

	/**
	 * Project id
	 *
	 * @var string
	 */
	private $_project_id = '';

	/**
	 * Constructor
	 *
	 * @param   string $scope
	 * @return  void
	 */
	public function __construct($scope = '__site__', $group_cn = '', $project_id = 0)
	{
		$this->_db = \App::get('db');
		$this->_scope = $scope;
		$this->_tbl = new \Components\Wiki\Tables\Page($this->_db);
		$this->_group_cn = $group_cn;
		$this->_project_id = $project_id;

		parent::__construct($scope);
	}

	/**
	 * Set and get a specific page
	 *
	 * @param   mixed  $id Integer or string of tag to look up
	 * @return  object WikiModelPage
	 */
	public function page($id=null, $scope = '')
	{
		$scope = $scope ? $scope : $this->_scope;
		$this->_cache['page'] = \Components\Wiki\Models\Page::getInstance($id, $scope);

		return $this->_cache['page'];
	}

	/**
	 * Get public stamp for note
	 *
	 * @return    object
	 */
	public function getPublicStamp( $id = 0, $register = false, $listed = NULL )
	{
		if (!is_file(PATH_CORE . DS . 'components'.DS
			.'com_projects' . DS . 'tables' . DS . 'publicstamp.php') )
		{
			return false;
		}

		$page = $this->page($id);

		if (!$page)
		{
			return false;
		}

		require_once(PATH_CORE . DS . 'components'.DS
			.'com_projects' . DS . 'tables' . DS . 'publicstamp.php');

		$objSt = new Tables\Stamp( $this->_db );

		// Build reference for latest revision of page
		$reference = array(
			'pageid'   => $page->get('id'),
			'pagename' => $page->get('pagename'),
			'revision' => NULL
		);

		// Check valid stamp
		$objSt->checkStamp($this->_project_id, json_encode($reference), 'notes');
		$list = ($listed !== NULL && $listed != $objSt->listed) ? true : false;

		if ($list == true)
		{
			return 	$objSt->registerStamp($this->_project_id, json_encode($reference), 'notes', $listed);
		}

		// Register new stamp?
		if ((!$objSt->id && $register == true))
		{
			$objSt->registerStamp($this->_project_id, json_encode($reference), 'notes', $listed);
			$objSt->checkStamp($this->_project_id, json_encode($reference), 'notes');
		}

		return $objSt;
	}

	/**
	 * Get default project note
	 *
	 * @param      string $group cn of project group
	 * @param      string $masterscope
	 * @param      string $prefix
	 * @return     void
	 */
	public function getFirstNote( $prefix = '' )
	{
		$query = "SELECT p.pagename FROM #__wiki_page AS p
				  WHERE p.group_cn='" . $this->_group_cn . "' AND p.state!=2
				  AND p.scope='" . $this->_scope . "'";
		$query.= $prefix ? "AND p.pagename LIKE '" . $prefix . "%'" : "";
		$query.= " ORDER BY p.times_rated, p.id ASC LIMIT 1";

		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}

	/**
	 * Get a list of parent project notes
	 *
	 * @param      string $scope
	 * @return     void
	 */
	public function getParentNotes( $scope = '' )
	{
		$scope = $scope ? $scope : $this->_scope;
		$parts = explode ( '/', $scope );
		$remaining = array_slice($parts, 3);
		if ($remaining)
		{
			$query = "SELECT DISTINCT p.pagename, p.title, p.scope ";
			$query.= "FROM #__wiki_page AS p ";
			$query.= "WHERE p.group_cn='" . $this->_group_cn . "' AND p.state!=2 ";
			$k = 1;
			$where = '';
			foreach ($remaining as $r)
			{
				$where .= "p.pagename='" . trim($r) . "'";
				$where .= $k == count($remaining) ? '' : ' OR ';
				$k++;
			}
			$query.= "AND (".$where.")" ;
			$this->_db->setQuery($query);
			return $this->_db->loadObjectList();
		}
		else
		{
			return array();
		}
	}

	/**
	 * Get a list of project notes
	 *
	 * @param      int $limit
	 * @param      string $orderby
	 * @return     object list
	 */
	public function getNotes( $limit = 0, $orderby = 'p.scope, p.times_rated ASC, p.id' )
	{
		$query = "SELECT DISTINCT p.id, p.pagename, p.title, p.scope, p.times_rated
		          FROM #__wiki_page AS p
				  WHERE p.group_cn='" . $this->_group_cn . "'
				  AND p.scope LIKE '" . $this->_scope . "%'
				  AND p.pagename NOT LIKE 'Template:%'
				  AND p.state!=2
				  ORDER BY $orderby ";
		$query.= intval($limit) ? " LIMIT $limit" : '';

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	/**
	 * Get count of project notes
	 *
	 * @return     void
	 */
	public function getNoteCount()
	{
		$query = "SELECT COUNT(*) FROM #__wiki_page AS p
				  WHERE p.group_cn='" . $this->_group_cn . "' AND p.state!=2
				  AND p.pagename NOT LIKE 'Template:%'";
		$query.= $this->_scope ? " AND p.scope LIKE '" . $this->_scope . "%'" : "";

		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}

	/**
	 * Get project note
	 *
	 * @param      string $id
	 * @return     void
	 */
	public function getSelectedNote( $id = '' )
	{
		$query = "SELECT DISTINCT p.id, p.pagename, p.title, p.scope, p.times_rated,
	 		  	  (SELECT v.version FROM #__wiki_version as v WHERE v.pageid=p.id
				  ORDER by v.version DESC LIMIT 1) as version,
				  (SELECT vv.id FROM #__wiki_version as vv WHERE vv.pageid=p.id
				  ORDER by vv.id DESC LIMIT 1) as instance
			      FROM #__wiki_page AS p
				  WHERE p.group_cn='" . $this->_group_cn . "'
				  AND p.scope LIKE '" . $this->_scope . "%'
				  AND p.state!=2
				  AND p.pagename NOT LIKE 'Template:%'";
		$query.=  is_numeric($id) ? " AND p.id='$id' LIMIT 1" : " AND p.pagename='$id' LIMIT 1";

		$this->_db->setQuery($query);
		$result = $this->_db->loadObjectList();

		return $result ? $result[0] : NULL;
	}

	/**
	 * Get last note order
	 *
	 * @param      string $scope
	 * @return     void
	 */
	public function getLastNoteOrder( $scope = '' )
	{
		$scope = $scope ? $scope : $this->_scope;
		$query = "SELECT p.times_rated FROM #__wiki_page AS p
				  WHERE p.group_cn='" . $this->_group_cn . "'
				  AND p.scope='" . $scope . "'
				  ORDER BY p.times_rated DESC LIMIT 1";

		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}

	/**
	 * Fix scope paths after page rename
	 *
	 * @param      string $scope
	 * @param      string $oldpagename
	 * @param      string $newpagename
	 * @return     void
	 */
	public function fixScopePaths( $scope, $oldpagename, $newpagename )
	{
		$query = "UPDATE #__wiki_page AS p SET p.scope=replace(p.scope, '/" . $oldpagename . "', '/" . $newpagename . "')
				  WHERE p.group_cn='" . $this->_group_cn . "'";

		$this->_db->setQuery($query);
		if (!$this->_db->query())
		{
			return false;
		}
		return true;
	}

	/**
	 * Save note order
	 *
	 * @param      string $scope
	 * @param      int $order
	 * @return     void
	 */
	public function saveNoteOrder( $scope, $order = 0 )
	{
		$query = "UPDATE #__wiki_page AS p SET p.times_rated='" . $order . "'
				  WHERE p.group_cn='" . $this->_group_cn . "'
				  AND p.scope='" . $scope . "'
				  AND p.times_rated='0'";

		$this->_db->setQuery($query);
		if (!$this->_db->query())
		{
			return false;
		}
		return true;
	}

	/**
	 * Get path to wiki page images and files
	 *
	 * @param      int 	$page
	 *
	 * @return     string
	 */
	public function getWikiPath( $id = 0)
	{
		// Ensure we have an ID to work with
		$listdir = Request::getInt('lid', 0);
		$id = $id ? $id : $listdir;

		if (!$id)
		{
			return false;
		}

		// Load wiki configs
		$wiki_config = Component::params( 'com_wiki' );

		$path =  DS . trim($wiki_config->get('filepath', '/site/wiki'), DS) . DS . $id;

		if (!is_dir(PATH_APP . $path))
		{
			if (!Filesystem::makeDirectory(PATH_APP . $path))
			{
				return false;
			}
		}

		return $path;
	}
}

