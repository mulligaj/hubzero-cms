<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
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
 * @author    Steve Snyder <snyder13@purdue.edu>
 * @author    Sam Wilson <samwilson@purdue.edu>
 * @copyright Copyright 2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class PdfFormRespondent
{
	/**
	 * Form respondent id
	 *
	 * @var int
	 **/
	private $id;

	/**
	 * Deployment ID
	 *
	 * @var int
	 **/
	private $depId;

	/**
	 * Member ID
	 *
	 * @var int
	 **/
	private $member_id;

	/**
	 * Bool indicating whether the form has been started
	 *
	 * @var date/time
	 **/
	private $started;

	/**
	 * Bool indicating whether the form has been finished
	 *
	 * @var date/time
	 **/
	private $finished;

	/**
	 * Number of latest attempt
	 *
	 * @var int
	 **/
	private $attempt;

	/**
	 * Construtor
	 *
	 * @return void
	 **/
	public function __construct($depId, $member_id, $attempt=1)
	{
		if (!$member_id)
		{
			JError::raiseError(403, 'This area requires authentication');
		}

		$dbh = JFactory::getDBO();
		$query  = 'SELECT id, started, finished, attempt FROM `#__courses_form_respondents`';
		$query .= ' WHERE deployment_id = '.(int)$depId.' AND member_id = '.(int)$member_id.' AND attempt='.(int)$attempt;
		$dbh->setQuery($query);

		// Set deployment id
		$this->depId = (int)$depId;
		$this->member_id = (int)$member_id;

		if (($res = $dbh->loadAssoc()))
		{
			$this->id       = $res['id'];
			$this->started  = $res['started'];
			$this->finished = $res['finished'];
			$this->attempt  = $res['attempt'];
		}
		else
		{
			$dbh->setQuery('INSERT INTO #__courses_form_respondents(deployment_id, member_id, attempt) VALUES ('.(int)$depId.', '.(int)$member_id.', '.(int)$attempt.')');
			$dbh->query();
			$this->id = $dbh->insertid();
			$this->attempt = (int) $attempt;
		}
	}

	/**
	 * Get confirmation code
	 *
	 * @return MD5 hash of confirmation code
	 **/
	public function getConfirmationCode($crumb)
	{
		return md5($this->id.$crumb);
	}

	/**
	 * Save answers
	 *
	 * @return object
	 **/
	public function saveAnswers($answers)
	{
		$dbh = JFactory::getDBO();

		$questions = $this->getQuestions();

		foreach ($questions as $question)
		{
			$key    = 'question-' . $question->id;
			$answer = (isset($answers[$key])) ? $answers[$key] : 0;

			$dbh->setQuery('INSERT INTO #__courses_form_responses(respondent_id, question_id, answer_id) VALUES ('.$this->id.', '.$question->id.', '.(int)$answer.')');
			$dbh->query();
		}

		$this->saveToGradebook();

		return $this;
	}

	/**
	 * Save score to gradebook
	 *
	 * @return void
	 **/
	public function saveToGradebook()
	{
		require_once JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_courses' . DS . 'tables' . DS . 'grade.book.php';

		$database  = JFactory::getDBO();

		// Get the asset id
		$query  = "SELECT `asset_id`";
		$query .= " FROM `#__courses_form_respondents` cfr";
		$query .= " JOIN `#__courses_form_deployments` cfd ON cfr.deployment_id = cfd.id";
		$query .= " JOIN `#__courses_forms` cf ON cfd.form_id = cf.id";
		$query .= " WHERE cfr.id = " . $database->quote($this->id);

		$database->setQuery($query);
		$asset_id = $database->loadResult();

		// Get score
		$results = $this->getAnswers();
		$score   = $results['summary']['score'];

		// Load gradebook entry
		$gradebook = new CoursesTableGradeBook($database);
		$gradebook->loadByUserAndAssetId($this->member_id, $asset_id);

		if (!$gradebook->get('id'))
		{
			$grade = array(
				'member_id' => $this->member_id,
				'score'     => $score,
				'scope'     => 'asset',
				'scope_id'  => $asset_id
			);

			$gradebook->save($grade);
		}
		elseif ($score > $gradebook->get('score'))
		{
			$gradebook->save(array('score'=>$score));
		}
	}

	/**
	 * Get questions
	 *
	 * @return array
	 **/
	public function getQuestions()
	{
		$dbh = JFactory::getDBO();

		$version = $this->getVersionNumber();

		$query  = "SELECT cfq.id";
		$query .= " FROM `#__courses_form_questions` cfq";
		$query .= " JOIN `#__courses_forms` cf ON cfq.form_id = cf.id";
		$query .= " JOIN `#__courses_form_deployments` cfd ON cfd.form_id = cf.id";
		$query .= " WHERE cfd.id = " . $dbh->quote($this->depId);
		$query .= " AND version = " . $dbh->quote($version);

		$dbh->setQuery($query);

		$questions = $dbh->loadObjectList();

		return $questions;
	}

	/**
	 * Get answers
	 *
	 * @return array
	 **/
	public function getAnswers()
	{
		$dbh = JFactory::getDBO();
		$dbh->setQuery('SELECT pfr.question_id, answer_id, pfa.id AS correct_answer_id, version FROM #__courses_form_latest_responses_view pfr INNER JOIN #__courses_form_questions pfq ON pfq.id = pfr.question_id INNER JOIN #__courses_form_answers pfa ON pfr.question_id = pfa.question_id AND pfa.correct WHERE pfr.respondent_id = '.$this->id);

		$rv = array(
			'summary' => array('correct' => 0, 'total' => 0, 'score' => NULL, 'version' => NULL),
			'detail' => array()
		);

		// NOTE: added this to allow a scenario where there are multiple correct answers.
		//       In that scenario, we still want to increment the correct count, but we don't
		//       want to increment the total count, as that question has already been counted.
		$questionIds = array();

		foreach ($dbh->loadAssocList() as $answer)
		{
			// If the answer is correct, increment correct count
			if ($answer['answer_id'] == $answer['correct_answer_id'])
			{
				++$rv['summary']['correct'];
			}

			// If this question has already been used, carry on
			if (in_array($answer['question_id'], $questionIds))
			{
				// Before carrying on though, if this is the users correct answer, update answer details
				if ($answer['answer_id'] == $answer['correct_answer_id'])
				{
					$rv['detail'][$answer['question_id']] = $answer;
				}

				// We're done
				continue;
			}
			else
			{
				// Set version and and answer details to array
				$rv['summary']['version'] = $answer['version'];
				$rv['detail'][$answer['question_id']] = $answer;

				// Increment total and mark as used
				++$rv['summary']['total'];
				$questionIds[] = $answer['question_id'];
			}
		}

		$rv['summary']['score'] = ($rv['summary']['total']>0) ? number_format($rv['summary']['correct']*100/$rv['summary']['total'], 1) : 0;

		return $rv;
	}

	/**
	 * Save current progress in form
	 *
	 * @return object
	 **/
	public function saveProgress($qid, $aid)
	{
		$dbh = JFactory::getDBO();
		$dbh->setQuery('DELETE FROM #__courses_form_respondent_progress WHERE respondent_id = '.(int)$this->id.' AND question_id = '.(int)$qid);
		$dbh->query();
		$dbh->setQuery('INSERT INTO #__courses_form_respondent_progress(respondent_id, question_id, answer_id, submitted) VALUES ('.(int)$this->id.', '.(int)$qid.', '.(int)$aid.', '.$dbh->Quote(JFactory::getDate()->toSql()).')');
		$dbh->query();

		return $this;
	}

	/**
	 * Get current progress in form
	 *
	 * @return array
	 **/
	public function getProgress()
	{
		$dbh = JFactory::getDBO();
		$dbh->setQuery('SELECT question_id, answer_id FROM #__courses_form_respondent_progress WHERE respondent_id = '.(int)$this->id);
		return $dbh->loadAssocList('question_id');
	}

	/**
	 * Get date/time form was started
	 *
	 * @return date/time
	 **/
	public function getStartTime()
	{
		return $this->started;
	}

	/**
	 * Get date/time form was ended
	 *
	 * @return date/time
	 **/
	public function getEndTime()
	{
		return $this->finished;
	}

	/**
	 * Get attempt #
	 *
	 * @return integer
	 **/
	public function getAttemptNumber()
	{
		return $this->attempt;
	}

	/**
	 * Get version # for which this respondent is completing the form
	 *
	 * @return integer
	 **/
	public function getVersionNumber()
	{
		$dbh = JFactory::getDBO();

		$query  = "SELECT max(version) AS version";
		$query .= " FROM `#__courses_form_questions` cfq";
		$query .= " JOIN `#__courses_forms` cf ON cfq.form_id = cf.id";
		$query .= " JOIN `#__courses_form_deployments` cfd ON cfd.form_id = cf.id";
		$query .= " WHERE cfd.id = " . $dbh->quote($this->depId);

		$dbh->setQuery($query);

		return $dbh->loadResult();
	}

	/**
	 * Mark time someone starts form
	 *
	 * @return object
	 **/
	public function markStart()
	{
		$this->started = JFactory::getDate()->toSql();
		$dbh = JFactory::getDBO();
		$dbh->setQuery('UPDATE #__courses_form_respondents SET started = \''.$this->started.'\' WHERE started IS NULL AND id = '.(int)$this->id);
		$dbh->query();

		return $this;
	}

	/**
	 * Mark time someone finishes form
	 *
	 * @return object
	 **/
	public function markEnd()
	{
		$this->started = JFactory::getDate()->toSql();
		$dbh = JFactory::getDBO();
		$dbh->setQuery('UPDATE #__courses_form_respondents SET finished = \''.$this->started.'\' WHERE id = '.(int)$this->id);
		$dbh->query();

		return $this;
	}
}