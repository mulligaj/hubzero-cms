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
 * @author    Steve Snyder <snyder13@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

use Components\Members\Models\Member;

require_once Component::path('com_members') . DS . 'models' . DS . 'member.php';

/**
 * Short description for 'ContributionSorter'
 *
 * Long description (if any) ...
 */
class ContributionSorter
{
	/**
	 * Short description for 'sort'
	 *
	 * Long description (if any) ...
	 *
	 * @param      object $a Parameter description (if any) ...
	 * @param      object $b Parameter description (if any) ...
	 * @return     integer Return description (if any) ...
	 */
	public static function sort($a, $b)
	{
		$sec_diff = strcmp($a->get_section(), $b->get_section());
		if ($sec_diff < 0)
		{
			return -1;
		}
		if ($sec_diff > 0)
		{
			return 1;
		}
		$a_ord = $a->get('ordering');
		$b_ord = $b->get('ordering');
		return $a_ord == $b_ord ? 0 : $a_ord < $b_ord ? -1 : 1;
	}

	/**
	 * Short description for 'sort_weight'
	 *
	 * Long description (if any) ...
	 *
	 * @param      object $a Parameter description (if any) ...
	 * @param      object $b Parameter description (if any) ...
	 * @return     integer Return description (if any) ...
	 */
	public static function sort_weight($a, $b)
	{
		$aw = $a->get_weight();
		$bw = $b->get_weight();
		if ($aw == $bw)
		{
			return 0;
		}
		return $aw > $bw ? -1 : 1;
	}

	/**
	 * Short description for 'sort_title'
	 *
	 * Long description (if any) ...
	 *
	 * @param      object $a Parameter description (if any) ...
	 * @param      object $b Parameter description (if any) ...
	 * @return     object Return description (if any) ...
	 */
	public static function sort_title($a, $b)
	{
		return strcmp($a->get_title(), $b->get_title());
	}
}

/**
 * Search members
 */
class plgSearchMembers extends \Hubzero\Plugin\Plugin
{
	/****************************
	Query-time / General Methods
	****************************/

	/**
	 * onGetTypes - Announces the available hubtype
	 *
	 * @param mixed $type
	 * @access public
	 * @return void
	 */
	public function onGetTypes($type = null)
	{
		// The name of the hubtype
		$hubtype = 'member';

		if (isset($type) && $type == $hubtype)
		{
			return $hubtype;
		}
		elseif (!isset($type))
		{
			return $hubtype;
		}
	}

	/**
	 * onGetModel 
	 * 
	 * @param string $type 
	 * @access public
	 * @return void
	 */
	public function onGetModel($type = '')
	{
		if ($type == 'member')
		{
			return new Member;
		}
	}

	/*********************
		Index-time methods
	*********************/
	/**
	 * onProcessFields - Set SearchDocument fields which have conditional processing
	 *
	 * @param mixed $type 
	 * @param mixed $row
	 * @access public
	 * @return void
	 */
	public function onProcessFields($type, $row, &$db)
	{
		if ($type == 'member')
		{
			// Instantiate new $fields object
			$fields = new stdClass;

			// Format the date for SOLR
			$date = Date::of($row->registerDate)->format('Y-m-d');
			$date .= 'T';
			$date .= Date::of($row->registerDate)->format('h:m:s') . 'Z';
			$fields->date = $date;

			$fields->title = $row->name;
			$fields->alias = $row->username;
			$fields->address = $row->email;

			$fields->owner_type = 'user';
			$fields->owner = $row->id;
			$fields->type = $row->usertype;

			if ($row->access == 1 && $row->block == 0 && $row->approved == 2)
			{
				$fields->access_level = 'public';
			}
			else
			{
				$fields->access_level = 'private';
			}

			$fields->url = '/members/' . $row->id;

			return $fields;
		}
	}
	/**
	 * Build search query and add it to the $results
	 *
	 * @param      object $request  \Components\Search\Models\Basic\Request
	 * @param      object &$results \Components\Search\Models\Basic\Result\Set
	 * @param      object $authz    \Components\Search\Models\Basic\Authorization
	 * @return     void
	 */
	public static function onSearch($request, &$results, $authz)
	{
		$terms = $request->get_term_ar();
		$weight = '(match(p.name) against (\'' . join(' ', $terms['stemmed']) . '\') + match(b.bio) against(\'' . join(' ', $terms['stemmed']) . '\'))';

		$addtl_where = array();
		foreach ($terms['mandatory'] as $mand)
		{
			$addtl_where[] = "(p.name LIKE '%$mand%' OR b.bio LIKE '%$mand%')";
		}
		foreach ($terms['forbidden'] as $forb)
		{
			$addtl_where[] = "(p.name NOT LIKE '%$forb%' AND b.bio NOT LIKE '%$forb%')";
		}

		$results->add(new \Components\Search\Models\Basic\Result\Sql(
			"SELECT
				p.uidNumber AS id,
				p.name AS title,
				coalesce(b.bio, '') AS description,
				concat('index.php?option=com_members&id=', CASE WHEN p.uidNumber > 0 THEN p.uidNumber ELSE concat('n', abs(p.uidNumber)) END) AS link,
				$weight AS weight,
				NULL AS date,
				'Members' AS section,
				CASE WHEN p.picture IS NOT NULL THEN concat('/site/members/', lpad(p.uidNumber, 5, '0'), '/', p.picture) ELSE NULL END AS img_href
			FROM #__xprofiles p
			LEFT JOIN #__xprofiles_bio b
				ON b.uidNumber = p.uidNumber
			WHERE
				public AND $weight > 0" .
				($addtl_where ? ' AND ' . join(' AND ', $addtl_where) : '') .
			" ORDER BY $weight DESC"
		));
	}

	/**
	 * Build search query and add it to the $results
	 *
	 * @param      object $request  YSearchModelRequest
	 * @param      object &$results YSearchModelResultSet
	 * @return     void
	 */
	public static function onSearchCustom($request, &$results)
	{
		if (($section = $request->get_terms()->get_section()) && $section[0] != 'members')
		{
			return;
		}

		$terms = $request->get_term_ar();
		$addtl_where = array();
		foreach (array($terms['mandatory'], $terms['optional']) as $pos)
		{
			foreach ($pos as $term)
			{
				$addtl_where[] = "(p.name LIKE '%$term%')";
			}
		}
		foreach ($terms['forbidden'] as $forb)
		{
			$addtl_where[] = "(p.name NOT LIKE '%$forb%')";
		}

		$sql = new \Components\Search\Models\Basic\Result\Sql(
			"SELECT
				p.uidNumber AS id,
				p.name AS title,
				coalesce(b.bio, '') AS description,
				concat('index.php?option=com_members&id=', CASE WHEN p.uidNumber > 0 THEN p.uidNumber ELSE concat('n', abs(p.uidNumber)) END) AS link,
				NULL AS date,
				'Members' AS section,
				CASE WHEN p.picture IS NOT NULL THEN concat('/site/members/', lpad(p.uidNumber, 5, '0'), '/', p.picture) ELSE NULL END AS img_href
			FROM #__xprofiles p
			LEFT JOIN #__xprofiles_bio b
				ON b.uidNumber = p.uidNumber
			WHERE
				public AND " . join(' AND ', $addtl_where)
		);
		$assoc = $sql->to_associative();
		if (!count($assoc))
		{
			return false;
		}

		$when = "c.alias THEN
			concat(
				CASE WHEN c.alias THEN concat('/', c.alias) ELSE '' END
			)";

		$resp = array();
		foreach ($assoc as $row)
		{
			$query = "SELECT
					CASE WHEN aa.subtable = 'resources' THEN
						r.title
					ELSE
						c.title
					END AS title,
					CASE
						WHEN aa.subtable = 'resources' THEN
							concat(coalesce(r.introtext, ''), coalesce(r.`fulltxt`, ''))
						ELSE
							concat(coalesce(c.introtext, ''), coalesce(c.`fulltext`, ''))
					END AS description,
					CASE
						WHEN aa.subtable = 'resources' THEN
							concat('/resources/', r.id)
						ELSE
							CASE
								WHEN $when
								ELSE concat('/content/article/', c.id)
							END
					END AS link,
					1 AS weight,
					CASE
						WHEN aa.subtable = 'resources' THEN
							rt.type
						ELSE";
			$query .= " s.alias";

			$query .= " END AS section,
					CASE
						WHEN aa.subtable = 'resources' THEN
							ra.ordering
						ELSE
							-1
					END AS ordering
					FROM #__author_assoc aa
					LEFT JOIN #__resources r
						ON aa.subtable = 'resources' AND r.id = aa.subid AND r.published = 1
					LEFT JOIN #__resource_assoc ra
						ON ra.child_id = r.id
					LEFT JOIN #__resource_types rt
						ON rt.id = r.type";

			$query .= " LEFT JOIN #__content c
						ON aa.subtable = 'content' AND c.id = aa.subid AND c.state = 1
					LEFT JOIN #__categories s
						ON s.id = c.sectionid
					LEFT JOIN #__categories ca
						ON ca.id = c.catid";

			$query .= " WHERE aa.authorid = " . $row->get('id');
			$work = new \Components\Search\Models\Basic\Result\Sql($query);
			$work_assoc = $work->to_associative();

			$added = array();
			foreach ($work_assoc as $wrow)
			{
				$link = $wrow->get_link();
				if (array_key_exists($link, $added))
				{
					continue;
				}
				$row->add_child($wrow);
				$row->add_weight(1);
				$added[$link] = 1;
			}
			$row->sort_children(array('ContributionSorter', 'sort'));

			$workp = new \Components\Search\Models\Basic\Result\Sql(
				"SELECT
					r.publication_id AS id,
					r.title AS title,
					concat(coalesce(r.description, ''), coalesce(r.abstract, '')) AS description,
					concat('/publications/', r.id) AS link,
					1 AS weight,
					rt.alias AS section,
					aa.ordering
					FROM #__publication_authors aa
					LEFT JOIN #__publication_versions r
						ON aa.publication_version_id = r.id AND r.state = 1
					LEFT JOIN #__publications p
						ON p.id = r.publication_id
					LEFT JOIN #__publication_categories rt
						ON rt.id = p.category
					WHERE aa.user_id = " . $row->get('id')
			);
			$workp_assoc = $workp->to_associative();

			foreach ($workp_assoc as $wrow)
			{
				$link = $wrow->get_link();
				if (array_key_exists($link, $added))
				{
					continue;
				}
				$row->add_child($wrow);
				$row->add_weight(1);
				$added[$link] = 1;
			}
			$row->sort_children(array('ContributionSorter', 'sort'));

			$resp[] = $row;
		}
		usort($resp, array('ContributionSorter', 'sort_weight'));
		foreach ($resp as $row)
		{
			$results->add($row);
		}
		return false;
	}

	/**
	 * Generate an <img> tag with the user's picture, if set
	 * Otherwise, use default image
	 *
	 * @param      object $res YSearchResult
	 * @return     string
	 */
	public static function onBeforeSearchRenderMembers($res)
	{
		if (!($href = $res->get('img_href')) || !is_file(PATH_APP . $href))
		{
			$href = rtrim(Request::base(true), '/') . '/components/com_members/assets/img/profile_thumb.gif';
		}

		return '<img src="' . $href . '" alt="' . htmlentities($res->get_title()) . '" title="' . htmlentities($res->get_title()) . '" />';
	}
}

