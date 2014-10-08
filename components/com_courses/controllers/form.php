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
 * @author    Sam Wilson <samwilson@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Include required forms models
require_once(JPATH_COMPONENT . DS . 'models' . DS . 'form.php');
require_once(JPATH_COMPONENT . DS . 'models' . DS . 'formRespondent.php');
require_once(JPATH_COMPONENT . DS . 'models' . DS . 'formDeployment.php');

/**
 * Courses form controller class
 */
class CoursesControllerForm extends \Hubzero\Component\SiteController
{
	/**
	 * Execute a task
	 *
	 * @return     void
	 */
	public function execute()
	{
		$this->getCourseInfo();

		// Get the courses member
		$this->juser  = JFactory::getUser();
		$this->member = $this->course->offering()->section()->member($this->juser->get('id'))->get('id');
		if (!$this->member || !is_numeric($this->member))
		{
			$this->member = $this->course->offering()->member($this->juser->get('id'))->get('id');
			if (!$this->member || !is_numeric($this->member))
			{
				JError::raiseError(422, 'No user found');
				return false;
			}
		}

		// Set the base path
		$this->base = "index.php?option=com_courses&controller=form&gid={$this->course->get('alias')}&offering={$this->course->offering()->alias()}";

		parent::execute();
	}

	/**
	 * Method to set the document path
	 *
	 * @return     void
	 */
	public function _buildPathway()
	{
		$pathway = JFactory::getApplication()->getPathway();

		if (count($pathway->getPathWay()) <= 0)
		{
			$pathway->addItem(
				JText::_(strtoupper($this->_option)),
				'index.php?option=' . $this->_option
			);
		}

		$pathway->addItem(
			JText::_(ucfirst($this->course->get('title'))),
			'index.php?option=com_courses&controller=form&gid=' . $this->course->get('alias')
		);

		$pathway->addItem(
			JText::_(ucfirst($this->course->offering()->get('title'))),
			$this->base
		);

		if ($this->_task != 'index')
		{
			$pathway->addItem(
				JText::_(ucfirst($this->_task)),
				'index.php?option=' . $this->_option . '&controller=form&task=' . $this->_task
			);
		}
	}

	/**
	 * Method to build and set the document title
	 *
	 * @return     void
	 */
	public function _buildTitle($append='')
	{
		// Set the title used in the view
		$this->_title = JText::_('COM_COURSES_FORMS');

		if (!empty($append))
		{
			$this->_title .= ': ' . $append;
		}

		//set title of browser window
		$document = JFactory::getDocument();
		$document->setTitle($this->_title);
	}

	/**
	 * Default index view of all forms
	 *
	 * @return     void
	 */
	public function indexTask()
	{
		// Check authorization
		// @FIXME: only admins should see ALL exams
		$this->authorize();

		// Set the title and pathway
		$this->_buildTitle('Upload a PDF');
		$this->_buildPathway();

		if (!isset($this->view->errors))
		{
			$this->view->errors = array();
		}

		$this->view->title = $this->_title;
		$this->view->base  = $this->base;

		// Display
		$this->view->display();
	}

	/**
	 * Upload a PDF and render images
	 *
	 * @return     void
	 */
	public function uploadTask()
	{
		// Check authorization
		$this->authorize();
		$pdf = PdfForm::fromPostedFile('pdf');

		// No error, then render the images
		if (!$pdf->hasErrors())
		{
			$pdf->renderPageImages();
		}

		// If there were errors, jump back to the index view and display them
		if ($pdf->hasErrors())
		{
			$this->setView('form', 'index');
			$this->view->errors = $pdf->getErrors();
			$this->indexTask();
		}
		else
		{
			// Just return JSON
			if (JRequest::getInt('no_html', false))
			{
				echo json_encode(array('success'=>true, 'id'=>$pdf->getId()));
				exit();
			}
			else // Otherwise, redirect
			{
				$this->setRedirect(
					JRoute::_('index.php?option=com_courses&controller=form&task=layout&formId=' . $pdf->getId(), false),
					JText::_('COM_COURSES_PDF_UPLOAD_SUCCESSFUL'),
					'passed'
				);
				return;
			}
		}
	}

	/**
	 * PDF layout view, annotate rendered images
	 *
	 * @return     void
	 */
	public function layoutTask()
	{
		// Check authorization
		$this->authorize();

		// Set the title and pathway
		$this->_buildTitle();
		$this->_buildPathway();

		$this->view->pdf      = new PdfForm($this->assertFormId());
		$this->view->title    = $this->view->pdf->getTitle();
		$this->view->readonly = JRequest::getInt('readonly', false);
		$this->view->base     = $this->base;
		$this->view->display();
	}

	/**
	 * Save layout
	 *
	 * @return     void
	 */
	public function saveLayoutTask()
	{
		// Check authorization
		$this->authorize();

		$pdf = $this->assertExistentForm();

		if (JRequest::getVar('title', false))
		{
			$pdf->setTitle($_POST['title']);
		}

		if (isset($_POST['pages']))
		{
			$pdf->setPageLayout($_POST['pages']);
		}

		if (isset($_FILES['pdf']))
		{
			$pdf->setFname((is_array($_FILES['pdf']['tmp_name'])) ? $_FILES['pdf']['tmp_name'][0] : $_FILES['pdf']['tmp_name']);
			$pdf->renderPageImages();
		}

		echo json_encode(array('result'=>'success'));
		exit();
	}

	/**
	 * Deploy form view
	 *
	 * @return     void
	 */
	public function deployTask($dep=NULL)
	{
		// Check authorization
		$this->authorize();

		// Set the title and pathway
		$this->_buildTitle();
		$this->_buildPathway();

		$this->view->pdf   = $this->assertExistentForm();
		$this->view->dep   = ($dep) ? $dep : new PdfFormDeployment;
		$this->view->title = $this->view->pdf->getTitle();
		$this->view->base  = $this->base;

		$this->view->display();
	}

	/**
	 * Create deployment
	 *
	 * @return     void
	 */
	public function createDeploymentTask()
	{
		if (!$deployment = JRequest::getVar('deployment'))
		{
			JError::raiseError(422, JText::_('COM_COURSES_ERROR_MISSING_DEPLOYMENT'));
		}

		$pdf = $this->assertExistentForm();
		$dep = PdfFormDeployment::fromFormData($pdf->getId(), $deployment);

		if ($dep->hasErrors())
		{
			$this->setView('form', 'deploy');
			$this->view->task = 'deployTask';
			$this->deployTask($dep);
			return;
		}
		else
		{
			if (JRequest::getInt('no_html', false))
			{
				echo json_encode(array('success'=>true, 'id'=>$dep->save(), 'formId'=>$pdf->getId()));
				exit();
			}
			else
			{
				$tmpl = (JRequest::getWord('tmpl', false)) ? '&tmpl=component' : '';
				$this->setRedirect(
					JRoute::_($this->base . '&task=form.showDeployment&id='.$dep->save().'&formId='.$pdf->getId().$tmpl, false),
					JText::_('COM_COURSES_DEPLOYMENT_CREATED'),
					'passed'
				);
				return;
			}
		}
	}

	/**
	 * Update an existing deployment
	 *
	 * @return     void
	 */
	public function updateDeploymentTask()
	{
		if (!$deployment = JRequest::getVar('deployment'))
		{
			JError::raiseError(422, JText::_('COM_COURSES_ERROR_MISSING_DEPLOYMENT'));
		}

		if (!$deploymentId = JRequest::getInt('deploymentId'))
		{
			JError::raiseError(422, JText::_('COM_COURSES_ERROR_MISSING_DEPLOYMENT_ID'));
		}

		$pdf = $this->assertExistentForm();
		$dep = PdfFormDeployment::fromFormData($pdf->getId(), $deployment);

		if ($dep->hasErrors(NULL, TRUE))
		{
			$this->setView('form', 'showDeployment');
			$dep->setId($deploymentId);
			$this->showDeploymentTask($dep);
			return;
		}
		else
		{
			$tmpl = (JRequest::getWord('tmpl', false)) ? '&tmpl=component' : '';
			$this->setRedirect(
				JRoute::_($this->base . '&task=form.showDeployment&id='.$dep->save($deploymentId).'&formId='.$pdf->getId().$tmpl, false),
				JText::_('COM_COURSES_DEPLOYMENT_UPDATED'),
				'passed'
			);
			return;
		}
	}

	/**
	 * Show deployment
	 *
	 * @return     void
	 */
	public function showDeploymentTask($dep=NULL)
	{
		if (!$id = JRequest::getInt('id', false))
		{
			JError::raiseError(422, JText::_('COM_COURSES_ERROR_MISSING_IDENTIFIER'));
		}

		// Set the title and pathway
		$this->_buildTitle();
		$this->_buildPathway();

		$this->view->pdf   = $this->assertExistentForm();
		$this->view->title = $this->view->pdf->getTitle();
		$this->view->dep   = ($dep) ? $dep : PdfFormDeployment::load($id);
		$this->view->base  = $this->base;

		// Display
		$this->view->display();
	}

	/**
	 * Take a form/exam
	 *
	 * @return     void
	 */
	public function completeTask()
	{
		if (!$crumb = JRequest::getVar('crumb', false))
		{
			JError::raiseError(422);
		}

		$dep = PdfFormDeployment::fromCrumb($crumb, $this->course->offering()->section()->get('id'));
		$dbg = JRequest::getVar('dbg', false);

		switch ($dep->getState())
		{
			case 'pending':
				JError::raiseError(422, JText::_('COM_COURSES_DEPLOYMENT_UNAVAILABLE'));
			break;

			case 'expired':
				$attempt = JRequest::getInt('attempt', 1);

				// Make sure they're not trying to take the form too many times
				if ($attempt > $dep->getAllowedAttempts())
				{
					JError::raiseError(403, JText::_('COM_COURSES_WARNING_EXCEEDED_ATTEMPTS'));
				}

				$this->setView('results', $dep->getResultsClosed());

				// Set the title and pathway
				$this->_buildTitle();
				$this->_buildPathway();

				$this->view->dep   = $dep;
				$this->view->resp  = $dep->getRespondent($this->member, $attempt);
				$this->view->pdf   = $dep->getForm();
				$this->view->title = $this->view->pdf->getTitle();
				$this->view->base  = $this->base;
				$this->view->dep   = $dep;

				// Display
				$this->view->display();
			break;

			case 'active':
				$attempt = JRequest::getInt('attempt', 1);

				// Make sure they're not trying to take the form too many times
				if ($attempt > $dep->getAllowedAttempts())
				{
					JError::raiseError(403, JText::_('COM_COURSES_WARNING_EXCEEDED_ATTEMPTS'));
				}

				$resp = $dep->getRespondent($this->member, $attempt);

				if ($resp->getEndTime())
				{
					$this->setView('results', $dep->getResultsOpen());

					// Set the title and pathway
					$this->_buildTitle();
					$this->_buildPathway();

					$this->view->incomplete = array();
					$this->view->pdf        = $dep->getForm();
					$this->view->title      = $this->view->pdf->getTitle();
					$this->view->base       = $this->base;
					$this->view->dep        = $dep;
					$this->view->resp       = $resp;
					$this->view->display();
					return;
				}
				else
				{
					// Set the title and pathway
					$this->_buildTitle();
					$this->_buildPathway();

					$this->view->pdf        = $dep->getForm();
					$this->view->title      = $this->view->pdf->getTitle();
					$this->view->base       = $this->base;
					$this->view->dep        = $dep;
					$this->view->resp       = $resp;
					$this->view->incomplete = (isset($this->view->incomplete)) ? $this->view->incomplete : array();
					$this->view->display();
				}
			break;
		}
	}

	/**
	 * Mark the start of a time form
	 *
	 * @return     void
	 */
	public function startWorkTask()
	{
		if (!$crumb = JRequest::getVar('crumb', false))
		{
			JError::raiseError(422);
		}

		$attempt = JRequest::getInt('attempt', 1);

		PdfFormDeployment::fromCrumb($crumb)->getRespondent($this->member, $attempt)->markStart();

		$tmpl = (JRequest::getWord('tmpl', false)) ? '&tmpl=' . JRequest::getWord('tmpl') : '';
		$att  = ($attempt > 1) ? '&attempt='.$attempt : '';

		$this->setRedirect(
			JRoute::_($this->base . '&task=form.complete&crumb=' . $crumb . $att . $tmpl, false)
		);
		return;
	}

	/**
	 * Save progress, called via JS ajax
	 *
	 * @return     void
	 */
	public function saveProgressTask()
	{
		if (!isset($_POST['crumb']) || !isset($_POST['question']) || !isset($_POST['answer']))
		{
			echo JText::_('COM_COURSES_ERROR_MISSING_CRUMB_QUESTION_OR_ANSWER');
			exit();
		}

		$attempt = JRequest::getInt('attempt', 1);

		$resp = PdfFormDeployment::fromCrumb($_POST['crumb'])->getRespondent($this->member, $attempt);

		if (!$resp->getEndTime())
		{
			$resp->saveProgress($_POST['question'], $_POST['answer']);
		}

		echo json_encode(array("result"=>"success"));
		exit();
	}

	/**
	 * Submit and save a form response
	 *
	 * @return     void
	 */
	public function submitTask()
	{
		if (!$crumb = JRequest::getVar('crumb', false))
		{
			JError::raiseError(422);
		}

		$attempt = JRequest::getInt('attempt', 1);
		$att     = ($attempt > 1) ? '&attempt='.$attempt : '';
		$dep     = PdfFormDeployment::fromCrumb($crumb);
		$ended   = false;

		// Make sure they're not trying to take the form too many times
		if ($attempt > $dep->getAllowedAttempts())
		{
			JError::raiseError(403, JText::_('COM_COURSES_WARNING_EXCEEDED_ATTEMPTS'));
		}

		// Check to see if the time limit has been reached
		if ($limit = $dep->getTimeLimit())
		{
			$resp = $dep->getRespondent($this->member, $attempt);

			$now   = strtotime(JFactory::getDate());
			$start = strtotime($resp->getStartTime());
			$end   = strtotime($dep->getEndTime());
			$dur   = $limit * 60;

			if ($now > ($start + $dur) || ($dep->getEndTime() && $end < $now))
			{
				$ended = true;
			}
		}

		list($complete, $answers) = $dep->getForm()->getQuestionAnswerMap($_POST, $ended);

		if ($complete)
		{
			$resp = $dep->getRespondent($this->member, $attempt);
			if (!$resp->getEndTime())
			{
				$resp->saveAnswers($_POST)->markEnd();
			}

			$this->setRedirect(JRoute::_($this->base . '&task=form.complete&crumb='.$crumb.$att, false));
			return;
		}
		else
		{
			$this->setView('form', 'complete');
			$this->_task = 'complete';
			$this->view->incomplete = array_filter($answers, function($ans) { return is_null($ans[0]); });
			$this->completeTask();
		}
	}

	/**
	 * Check authorization
	 *
	 * @return     void
	 */
	public function authorize()
	{
		// Make sure they're logged in
		if ($this->juser->get('guest'))
		{
			$return = base64_encode(JRoute::_('index.php?option=' . $this->_option . '&controller=form'));
			$this->setRedirect(
				JRoute::_('index.php?option=com_users&view=login&return=' . $return),
				$message,
				'warning'
			);
			return;
		}

		// Check for super admins
		if ($this->juser->usertype == 'Super Administrator')
		{
			// Let them through
		}
		else
		{
			// If they're not a super admin, they can only view this page if they're looking at a form associated with a course that they're authorized on
			if (!$this->authorizeCourse())
			{
				// Otherwise, a course id should be provided, and we need to make sure they are authorized
				JError::raiseError(403, JText::_('COM_COURSES_NOT_AUTH'));
			}
		}
	}

	/**
	 * Get form ID
	 *
	 * @return     int
	 */
	public function assertFormId()
	{
		if (isset($_POST['formId']))
		{
			return $_POST['formId'];
		}
		if (isset($_GET['formId']))
		{
			return $_GET['formId'];
		}

		JError::raiseError(422, JText::_('COM_COURSES_ERROR_MISSING_IDENTIFIER'));
	}

	/**
	 * Check that form ID exists
	 *
	 * @return     object
	 */
	public function assertExistentForm()
	{
		$pdf = new PdfForm($this->assertFormId());

		if (!$pdf->isStored())
		{
			JError::raiseError(404, JText::_('COM_COURSES_ERROR_UNKNOWN_IDENTIFIER'));
		}

		return $pdf;
	}

	/**
	 * Get course info from route
	 *
	 * @return bool
	 */
	public function getCourseInfo()
	{
		$gid      = JRequest::getVar('gid');
		$offering = JRequest::getVar('offering');
		$section  = JRequest::getVar('section');

		$this->course = new CoursesModelCourse($gid);
		$this->course->offering($offering);
		$this->course->offering()->section($section);
	}

	/**
	 * Check if form is part of a course
	 *
	 * @return bool
	 */
	public function authorizeCourse()
	{
		return $this->course->access('manage');
	}
}

/**
 * Form Helper class
 */
class FormHelper
{
	/**
	 * Time remaining (in human readable language)
	 *
	 * @return     string
	 */
	public static function timeDiff($secs)
	{
		$seconds = array(1, 'second');
		$minutes = array(60 * $seconds[0], 'minute');
		$hours   = array(60 * $minutes[0], 'hour');
		$days    = array(24 * $hours[0],   'day');
		$weeks   = array(7  * $days[0],    'week');
		$rv      = array();

		foreach (array($weeks, $days, $hours, $minutes, $seconds) as $step)
		{
			list($sec, $unit) = $step;
			$times = floor($secs / $sec);

			if ($times > 0)
			{
				$secs -= $sec * $times;
				$rv[] = $times . ' ' . $unit . ($times == 1 ? '' : 's');

				if (count($rv) == 2)
				{
					break;
				}
			}
			else if (count($rv))
			{
				break;
			}
		}

		return join(', ', $rv);
	}

	/**
	 * Convert integer to ordinal number
	 *
	 * @return     string
	 */
	public static function toOrdinal($int)
	{
		$ends = array('th','st','nd','rd','th','th','th','th','th','th');

		if (($int %100) >= 11 && ($int%100) <= 13)
		{
			$abbreviation = $int. 'th';
		}
		else
		{
			$abbreviation = $int. $ends[$int % 10];
		}

		return $abbreviation;
	}
}
