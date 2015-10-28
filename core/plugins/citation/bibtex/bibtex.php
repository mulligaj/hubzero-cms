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
 * @author    Christopher Smoak <csmoak@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

/**
 * Citations plugin class for bibtex
 */
class plgCitationBibtex extends \Hubzero\Plugin\Plugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var  boolean
	 */
	protected $_autoloadLanguage = true;

	/**
	 * Return file type
	 *
	 * @return  string  HTML
	 */
	public function onImportAcceptedFiles()
	{
		return '.bib <small>(' . Lang::txt('PLG_CITATION_BIBTEX_FILE') . ')</small>';
	}

	/**
	 * Import data from a file
	 *
	 * @param   array  $file
	 * @return  array
	 */
	public function onImport($file, $scope = NULL, $scope_id = NULL)
	{
		//file type
		$active = 'bib';

		//get the file extension
		$extension = $file->getClientOriginalExtension();

		//make sure we have a .bib file
		if ($active != $extension)
		{
			return;
		}

		//include bibtex file
		include_once(PATH_CORE . DS . 'components' . DS . 'com_citations' . DS . 'helpers' . DS . 'BibTex.php');

		//create bibtex object
		$bibtex = new Structures_BibTex();

		//feed bibtex lib the file
		$bibtex->loadFile($file->getPathname());

		//parse file
		$bibtex->parse();

		//get parsed citations
		$citations = $bibtex->data;

		//fix authors
		for ($i=0;$i<count($citations); $i++)
		{
			$authors = array();
			$auths   = isset($citations[$i]['author']) ? $citations[$i]['author'] : '';
			if ($auths != '')
			{
			 foreach ($auths as $a)
			 {
				 if (isset($a['jr']) && $a['jr'] != '')
				 {
					 $authors[] = $a['last'] . ' ' . $a['jr'] . ', ' . $a['first'];
				 }
				 else
				 {
					 $authors[] = $a['last'] . ', ' . $a['first'];
				 }
			 }
			 $citations[$i]['author'] = implode('; ', $authors);
			} //end if 
		 }

		//array to hold final citataions
		$final = array();

		//check for duplicates
		for ($i = 0; $i < count($citations); $i++)
		{
			$duplicate = $this->checkDuplicateCitation($citations[$i], $scope, $scope_id);

			if ($duplicate)
			{
				$citations[$i]['duplicate'] = $duplicate;
				$final['attention'][] = $citations[$i];
			}
			else
			{
				$citations[$i]['duplicate'] = 0;
				$final['no_attention'][] = $citations[$i];
			}
		}

		return $final;
	}

	/**
	 * Check if a citation is a duplicate
	 *
	 * @param   array    $citation
	 * @param		integer	 $scope_id
	 * @param		string	 $scope
	 *
	 * @return  integer
	 */
	protected function checkDuplicateCitation($citation, $scope = NULL, $scope_id = NULL)
	{
		//vars
		$title = '';
		$doi   = '';
		$isbn  = '';
		$match = 0;

		//default percentage to match title
		$default_title_match = 90;

		//get the % amount that titles should be alike to be considered a duplicate
		$title_match = $this->params->get('title_match_percent', $default_title_match);

		//force title match percent to be integer and remove any unnecessary % signs
		$title_match = (int) str_replace('%', '', $title_match);

		//make sure 0 is not the %
		$title_match = ($title_match == 0) ? $default_title_match : $title_match;

		//database object
		$db = \App::get('db');

		//query
		$sql = "SELECT id, title, doi, isbn, scope, scope_id FROM `#__citations`";

		//set the query
		$db->setQuery($sql);

		//get the result
		$result = $db->loadObjectList();

		//loop through all current citations
		foreach ($result as $r)
		{
			$id    = $r->id;
			$title = $r->title;
			$doi   = $r->doi;
			$isbn  = $r->isbn;
			$cScope = $r->scope;
			$cScope_id = $r->scope_id;

			if (!isset($scope))
			{
				//direct matches on doi
				if (isset($citation['doi']) && $doi == $citation['doi'] && $doi != '')
				{
					$match = $id;
					break;
				}

				//direct matches on isbn
				if (isset($citation['isbn']) && $isbn == $citation['isbn'] && $isbn != '')
				{
					$match = $id;
					break;
				}

				//match titles based on percect param
				similar_text($title, $citation['title'], $similar);
				if ($similar >= $title_match)
				{
					$match = $id;
					break;
				}
			}
			elseif (isset($scope) && isset($scope_id))
			{
				//matching within a scope domain
				if ($cScope == $scope && $cScope_id == $scope_id)
				{
						//direct matches on doi
						if (isset($citation['doi']) && $doi == $citation['doi'] && $doi != '')
					 {
						 $match = $id;
						 break;
					 }

					 //direct matches on isbn
					 if (isset($citation['isbn']) && $isbn == $citation['isbn'] && $isbn != '')
					 {
						 $match = $id;
						 break;
					 }

					 //match titles based on percect param
					 similar_text($title, $citation['title'], $similar);
					 if ($similar >= $title_match)
					 {
						 $match = $id;
						 break;
					 }
				}
			}
		} //end foreach result as r

		return $match;
	}
}
