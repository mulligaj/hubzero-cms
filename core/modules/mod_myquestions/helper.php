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

namespace Modules\MyQuestions;

use Hubzero\Module\Module;
use Component;
use Route;
use Lang;
use User;

/**
 * Module class for displaying a user's questions
 * Requires com_answers component
 */
class Helper extends Module
{
	/**
	 * Format the tags
	 *
	 * @param   string   $string  String of comma-separated tags
	 * @param   number   $num     Number of tags to display
	 * @param   integer  $max     Max character length
	 * @return  string   HTML
	 */
	private function _formatTags($string='', $num=3, $max=25)
	{
		$out = '';
		$tags = explode(',', $string);

		if (count($tags) > 0)
		{
			$out .= '<span class="taggi">' . "\n";
			$counter = 0;

			for ($i=0; $i< count($tags); $i++)
			{
				$counter = $counter + strlen(stripslashes($tags[$i]));
				if ($counter > $max)
				{
					$num = $num - 1;
				}
				if ($i < $num)
				{
					// display tag
					$normalized = preg_replace("/[^a-zA-Z0-9]/", '', $tags[$i]);
					$normalized = strtolower($normalized);
					$out .= "\t" . '<a href="' . Route::url('index.php?option=com_tags&tag=' . $normalized) . '">' . stripslashes($tags[$i]) . '</a> ' . "\n";
				}
			}
			if ($i > $num)
			{
				$out .= ' (&#8230;)';
			}
			$out .= '</span>' . "\n";
		}

		return $out;
	}

	/**
	 * Looks up a user's interests (tags)
	 *
	 * @param   integer  $cloud  Output as tagcloud (defaults to no)
	 * @return  string   List of tags as either a tagcloud or comma-delimitated string
	 */
	private function _getInterests($cloud=0)
	{
		$database = \App::get('db');

		require_once(Component::path('com_members') . DS . 'models' . DS . 'tags.php');

		// Get tags of interest
		$mt = new \Components\Members\Models\Tags(User::get('id'));
		if ($cloud)
		{
			$tags = $mt->render();
		}
		else
		{
			$tags = $mt->render('string');
		}

		return $tags;
	}

	/**
	 * Retrieves a user's questions
	 *
	 * @param   string  $kind       The kind of results to retrieve
	 * @param   array   $interests  Array of tags
	 * @return  array   Database results
	 */
	private function _getQuestions($kind='open', $interests=array())
	{
		$database = \App::get('db');

		// Get some classes we need
		require_once(Component::path('com_answers') . DS . 'models' . DS . 'question.php');
		require_once(Component::path('com_answers') . DS . 'tables' . DS . 'response.php');
		require_once(Component::path('com_answers') . DS . 'tables' . DS . 'log.php');
		require_once(Component::path('com_answers') . DS . 'tables' . DS . 'questionslog.php');
		require_once(Component::path('com_answers') . DS . 'helpers' . DS . 'economy.php');

		$aq = new \Components\Answers\Tables\Question($database);
		if ($this->banking)
		{
			$AE = new \Components\Answers\Helpers\Economy($database);
			$BT = new \Hubzero\Bank\Transaction($database);
		}

		$params =& $this->params;
		$moduleclass = $params->get('moduleclass');
		$limit = intval($params->get('limit', 10));
		$limit = ($limit) ? $limit : 10;

		$filters = array(
			'limit'    => $limit,
			'start'    => 0,
			'tag'      => '',
			'filterby' => 'open',
			'sortby'   => 'date'
		);

		switch ($kind)
		{
			case 'mine':
				$filters['mine'] = 1;
				$filters['sortby'] = 'responses';
			break;

			case 'assigned':
				$filters['mine'] = 0;
				require_once(Component::path('com_tools') . DS . 'tables' . DS . 'author.php');

				$TA = new \Components\Tools\Tables\Author($database);
				$tools = $TA->getToolContributions(User::get('id'));
				if ($tools)
				{
					foreach ($tools as $tool)
					{
						$filters['tag'] .= 'tool'.$tool->toolname.',';
					}
				}
				if (!$filters['tag'])
				{
					$filters['filterby'] = 'none';
				}
			break;

			case 'interest':
				$filters['mine'] = 0;
				$interests = (count($interests) <= 0) ? $this->_getInterests() : $interests;
				$filters['filterby'] = (!$interests) ? 'none' : 'open';
				$filters['tag'] = $interests;
			break;
		}

		$results = $aq->getResults($filters);
		if ($this->banking && $results)
		{
			$awards = array();

			foreach ($results as $result)
			{
				// Calculate max award
				$result->marketvalue = round($AE->calculate_marketvalue($result->id, 'maxaward'));
				$result->maxaward = round(2*(($result->marketvalue)/3));
				if ($kind != 'mine')
				{
					$result->maxaward = $result->maxaward + $result->reward;
				}
				$awards[] = ($result->maxaward) ? $result->maxaward : 0;
			}

			// re-sort by max reponses
			array_multisort($awards, SORT_DESC, $results);
		}

		foreach ($results as $k => $result)
		{
			$results[$k] = new \Components\Answers\Models\Question($result);
		}

		return $results;
	}

	/**
	 * Queries the database for user's questions and preps any data for display
	 *
	 * @return  void
	 */
	public function display()
	{
		$this->banking = Component::params('com_members')->get('bankAccounts');

		// show assigned?
		$show_assigned = intval($this->params->get('show_assigned'));
		$show_assigned = $show_assigned ? $show_assigned : 0;
		$this->show_assigned = $show_assigned;

		// show interests?
		$show_interests = intval($this->params->get('show_interests'));
		$show_interests = $show_interests ? $show_interests : 0;
		$this->show_interests = $show_interests;

		// max num of questions
		$max = intval($this->params->get('max_questions'));
		$max= $max ? $max : 12;
		$c = 1;

		// Build the HTML
		//$foundresults = false;
		$assignedcount = 0;
		$othercount = 0;

		// Get Open Questions User Asked
		$this->openquestions = $this->_getQuestions('mine');
		$opencount = ($this->openquestions) ? count($this->openquestions) : 0;

		// Get Questions related to user contributions
		if ($this->show_assigned)
		{
			$c++;
			$this->assigned = $this->_getQuestions('assigned');
			$assignedcount = ($this->assigned) ? count($this->assigned) : 0;
		}

		// Get interest tags
		if ($this->show_interests)
		{
			$c++;
			$this->interests = $this->_getInterests();
			if (!$this->interests)
			{
				$this->intext = Lang::txt('MOD_MYQUESTIONS_NA');
			}
			else
			{
				$this->intext = $this->_formatTags($this->interests);
			}

			// Get questions of interest
			$this->otherquestions = $this->_getQuestions("interest", $this->interests);
			$othercount = ($this->otherquestions) ? count($this->otherquestions) : 0;
		}

		// Limit number of shown questions
		$totalq = $opencount + $assignedcount + $othercount;
		$limit_mine = $max;
		$breaker = $max/$c;
		$this->limit_mine     = ($totalq - $opencount) >= $breaker * ($c-1)     ? $breaker : $max - ($totalq - $opencount);
		$this->limit_assigned = ($totalq - $assignedcount) >= $breaker * ($c-1) ? $breaker : $max - ($totalq - $assignedcount);
		$this->limit_interest = ($totalq - $othercount) >= $breaker * ($c-1)    ? $breaker : $max - ($totalq - $othercount);

		require $this->getLayoutPath();
	}
}

