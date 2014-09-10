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
 * @author    Alissa Nedossekina <alisa@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Answers controller class for questions
 */
class AnswersControllerQuestions extends \Hubzero\Component\SiteController
{
	/**
	 * Execute a task
	 *
	 * @return     void
	 */
	public function execute()
	{
		$this->config->set('banking', JComponentHelper::getParams('com_members')->get('bankAccounts'));

		$this->registerTask('__default', 'search');
		$this->registerTask('latest', 'latest.rss');

		parent::execute();
	}

	/**
	 * Build the document pathway (breadcrumbs)
	 *
	 * @param      object $question AnswersTableQuestion
	 * @return     void
	 */
	protected function _buildPathway($question=null)
	{
		$pathway = JFactory::getApplication()->getPathway();

		if (count($pathway->getPathWay()) <= 0)
		{
			$pathway->addItem(
				JText::_(strtoupper($this->_option)),
				'index.php?option=' . $this->_option
			);
		}
		if ($this->_task && in_array($this->_task, array('new', 'myquestions', 'search')))
		{
			$pathway->addItem(
				JText::_(strtoupper($this->_option) . '_' . strtoupper($this->_task)),
				'index.php?option=' . $this->_option . '&task=' . $this->_task
			);
		}
		if (is_object($question) && $question->get('subject'))
		{
			$pathway->addItem(
				\Hubzero\Utility\String::truncate($question->subject('clean'), 50),
				$question->link()
			);
		}
	}

	/**
	 * Build the document title
	 *
	 * @param      object $question AnswersTableQuestion
	 * @return     void
	 */
	protected function _buildTitle($question=null)
	{
		$this->view->title = JText::_(strtoupper($this->_option));
		if ($this->_task && $this->_task != 'view')
		{
			$this->view->title .= ': ' . JText::_(strtoupper($this->_option) . '_' . strtoupper($this->_task));
		}
		if (is_object($question) && $question->get('subject'))
		{
			$this->view->title .= ': ' . \Hubzero\Utility\String::truncate($question->subject('clean'), 50);
		}
		$document = JFactory::getDocument();
		$document->setTitle($this->view->title);
	}

	/**
	 * Display the latest entries
	 *
	 * @return     void
	 */
	public function displayTask()
	{
		$this->view->setLayout('search');
		return $this->searchTask();
	}

	/**
	 * Redirect to login form
	 *
	 * @return     void
	 */
	public function loginTask()
	{
		$rtrn = JRequest::getVar('REQUEST_URI', JRoute::_('index.php?option=' . $this->_option . '&controller=' . $this->_controller, false, true), 'server');

		$this->setRedirect(
			JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($rtrn), false)
		);
	}

	/**
	 * Save a reply
	 *
	 * @return     void
	 */
	public function savereplyTask()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Is the user logged in?
		if ($this->juser->get('guest'))
		{
			$this->setError(JText::_('COM_ANSWERS_LOGIN_TO_COMMENT'));
			$this->loginTask();
			return;
		}

		// Incoming
		$comment = JRequest::getVar('comment', array(), 'post', 'none', 2);

		if (!$comment['item_id'])
		{
			JError::raiseError(500, JText::_('COM_ANSWERS_ERROR_QUESTION_ID_NOT_FOUND'));
			return;
		}

		if ($comment['item_type'])
		{
			$row = new AnswersModelComment(0);
			if (!$row->bind($comment))
			{
				JError::raiseError(500, $row->getError());
				return;
			}

			// Perform some text cleaning, etc.
			$row->set('content', nl2br($row->get('content')));
			$row->set('anonymous', ($row->get('anonymous') ? 1 : 0));
			$row->set('created', JFactory::getDate()->toSql());
			$row->set('state', 0);
			$row->set('created_by', $this->juser->get('id'));

			// Save the data
			if (!$row->store(true))
			{
				JError::raiseError(500, $row->getError());
				return;
			}
		}

		$this->setRedirect(
			JRoute::_('index.php?option=' . $this->_option . '&task=question&id=' . JRequest::getInt('rid', 0))
		);
	}

	/**
	 * Reply to an answer
	 *
	 * @return     void
	 */
	public function replyTask()
	{
		// Is the user logged in?
		if ($this->juser->get('guest'))
		{
			$this->setError(JText::_('COM_ANSWERS_LOGIN_TO_COMMENT'));
			$this->loginTask();
			return;
		}

		// Retrieve a review or comment ID and category
		$id    = JRequest::getInt('id', 0);
		$refid = JRequest::getInt('refid', 0);
		$cat   = JRequest::getVar('category', '');

		// Do we have an ID?
		if (!$id)
		{
			// Cannot proceed
			$this->setRedirect(
				JRoute::_('index.php?option=' . $this->_option)
			);
			return;
		}

		// Do we have a category?
		if (!$cat)
		{
			// Cannot proceed
			$this->setRedirect(
				JRoute::_('index.php?option=' . $this->_option . '&task=question&id=' . $id)
			);
			return;
		}

		// Store the comment object in our registry
		$this->category = $cat;
		$this->referenceid = $refid;
		$this->qid = $id;
		$this->questionTask();
	}

	/**
	 * Rate an item
	 *
	 * @return     void
	 */
	public function rateitemTask()
	{
		$no_html = JRequest::getInt('no_html', 0);

		// Is the user logged in?
		if ($this->juser->get('guest'))
		{
			if (!$no_html)
			{
				$this->addComponentMessage(JText::_('COM_ANSWERS_PLEASE_LOGIN_TO_VOTE'));
				$this->loginTask();
			}
			return;
		}

		// Incoming
		$id      = JRequest::getInt('refid', 0);
		$cat     = JRequest::getVar('category', '');
		$vote    = JRequest::getVar('vote', '');
		$ip      = JRequest::ip();

		// Check for reference ID
		if (!$id)
		{
			// cannot proceed
			if (!$no_html)
			{
				$this->setRedirect(
					JRoute::_('index.php?option=' . $this->_option),
					JText::_('No ID provided.'),
					'error'
				);
			}
			return;
		}

		// load answer
		$row = new AnswersModelResponse($id);

		$qid = $row->get('question_id');

		// Can't vote for your own comment
		if ($row->get('created_by') == $this->juser->get('username'))
		{
			if (!$no_html)
			{
				$this->setRedirect(
					JRoute::_('index.php?option=' . $this->_option . '&task=question&id=' . $qid),
					JText::_('Cannot vote for your own entries.'),
					'warning'
				);
			}
			return;
		}

		// Can't vote for your own comment
		if (!$vote)
		{
			if (!$no_html)
			{
				$this->setRedirect(
					JRoute::_('index.php?option=' . $this->_option . '&task=question&id=' . $qid),
					JText::_('No vote provided.'),
					'warning'
				);
			}
			return;
		}

		// Get vote log
		$al = new AnswersTableLog($this->database);
		$al->loadByIp($id, $ip);

		if (!$al->id)
		{
			// new vote;
			// record if it was helpful or not
			switch ($vote)
			{
				case 'yes':
				case 'like':
				case 'up':
				case 1:
					$row->set('helpful', $row->get('helpful') + 1);
				break;

				case 'no':
				case 'dislike':
				case 'down':
				case -1:
					$row->set('nothelpful', $row->get('nothelpful') + 1);
				break;
			}
		}
		else if ($al->helpful != $vote)
		{
			// changing vote;
			// Adjust values to reflect vote change
			switch ($vote)
			{
				case 'yes':
				case 'like':
				case 'up':
				case 1:
					$row->set('helpful', $row->get('helpful') + 1);
					$row->set('nothelpful', $row->get('nothelpful') - 1);
				break;

				case 'no':
				case 'dislike':
				case 'down':
				case -1:
					$row->set('helpful', $row->get('helpful') - 1);
					$row->set('nothelpful', $row->get('nothelpful') + 1);
				break;
			}
		}
		else
		{
			// no vote change;
		}

		if (!$row->store(false))
		{
			$this->setError($row->getError());
			return;
		}

		// Record user's vote (old way)
		$al->response_id = $row->get('id');
		$al->ip      = $ip;
		$al->helpful = $vote;
		if (!$al->check())
		{
			echo $al->getError();
			$this->setError($al->getError());
			return;
		}
		if (!$al->store())
		{
			echo $al->getError();
			$this->setError($al->getError());
			return;
		}

		// Record user's vote (new way)
		if ($cat)
		{
			require_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . $this->_option . DS . 'vote.class.php');

			$v = new Vote($this->database);
			$v->referenceid = $row->get('id');
			$v->category    = $cat;
			$v->voter       = $this->juser->get('id');
			$v->ip          = $ip;
			$v->voted       = JFactory::getDate()->toSql();
			$v->helpful     = $vote;
			if (!$v->check())
			{
				echo $v->getError();
				$this->setError($v->getError());
				return;
			}
			if (!$v->store())
			{
				echo $v->getError();
				$this->setError($v->getError());
				return;
			}
		}

		// update display
		if ($no_html)
		{
			//$response = $row->getResponse($id, $ip);
			$row->set('vote', $vote);

			$this->view->option = $this->_option;
			$this->view->item   = $row; //new AnswersModelResponse($response[0]);
			if ($this->getError())
			{
				foreach ($this->getErrors() as $error)
				{
					$this->view->setError($error);
				}
			}
			$this->view->display();
		}
		else
		{
			$this->setRedirect(
				JRoute::_('index.php?option=' . $this->_option . '&task=question&id=' . $qid)
			);
		}
	}

	/**
	 * Search entries
	 *
	 * @return     void
	 */
	public function searchTask()
	{
		$this->view->config = $this->config;
		$this->view->task   = $this->_task;

		// Get configuration
		$jconfig = JFactory::getConfig();

		// Incoming
		$this->view->filters = array();
		$this->view->filters['limit']    = JRequest::getInt('limit', $jconfig->getValue('config.list_limit'));
		$this->view->filters['start']    = JRequest::getInt('limitstart', 0);
		$this->view->filters['tag']      = JRequest::getVar('tags', '');
		$this->view->filters['tag']      = ($this->view->filters['tag']) ? $this->view->filters['tag'] : JRequest::getVar('tag', '');
		$this->view->filters['q']        = JRequest::getVar('q', '');

		$this->view->filters['filterby'] = JRequest::getWord('filterby', '');
		if ($this->view->filters['filterby']
		 && !in_array($this->view->filters['filterby'], array('open', 'closed')))
		{
			$this->view->filters['filterby'] = '';
		}

		$this->view->filters['sortby']   = JRequest::getWord('sortby', 'date');
		if (!in_array($this->view->filters['sortby'], array('date', 'votes', 'rewards')))
		{
			$this->view->filters['sortby'] = 'date';
		}

		$this->view->filters['sort_Dir']   = JRequest::getWord('sortdir', 'DESC');

		$this->view->filters['area']     = JRequest::getVar('area', '');
		if ($this->view->filters['area']
		 && !in_array($this->view->filters['area'], array('mine', 'assigned', 'interest')))
		{
			$this->view->filters['area'] = '';
		}

		// Get questions of interest
		if ($this->view->filters['area'] == 'interest')
		{
			require_once(JPATH_ROOT . DS . 'components' . DS . 'com_members' . DS . 'helpers' . DS . 'tags.php');

			// Get tags of interest
			$mt = new MembersTags($this->database);
			$mytags  = $mt->get_tag_string($this->juser->get('id'));

			$this->view->filters['tag']  = ($this->view->filters['tag']) ? $this->view->filters['tag'] : $mytags;
			$this->view->filters['mine'] = 0;
		}

		// Get assigned questions
		if ($this->view->filters['area'] == 'assigned')
		{
			require_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_tools' . DS . 'tables' . DS . 'author.php');

			// What tools did this user contribute?
			$TA = new ToolAuthor($this->database);
			$tools = $TA->getToolContributions($this->juser->get('id'));
			$mytooltags = array();
			if ($tools)
			{
				foreach ($tools as $tool)
				{
					$mytooltags[] = 'tool' . $tool->toolname;
				}
			}

			$this->view->filters['tag'] = ($this->view->filters['tag']) ? $this->view->filters['tag'] : implode(',', $mytooltags);

			$this->view->filters['mine'] = 0;
		}

		if ($this->view->filters['area'] == 'mine')
		{
			$this->view->filters['mine'] = 1;
		}

		// Instantiate a Questions object
		$aq = new AnswersTableQuestion($this->database);

		if (($this->view->filters['area'] == 'interest' || $this->view->filters['area'] == 'assigned') && !$this->view->filters['tag'])
		{
			// Get a record count
			$this->view->total = 0;

			// Get records
			$this->view->results = array();
		}
		else
		{
			// Get a record count
			$this->view->total = $aq->getCount($this->view->filters);

			// Get records
			$this->view->results = $aq->getResults($this->view->filters);
		}

		// Did we get any results?
		if (count($this->view->results) > 0)
		{
			// Do some processing on the results
			foreach ($this->view->results as $i => $result)
			{
				$this->view->results[$i] = new AnswersModelQuestion($result);
			}
		}

		// Initiate paging
		jimport('joomla.html.pagination');
		$this->view->pageNav = new JPagination(
			$this->view->total,
			$this->view->filters['start'],
			$this->view->filters['limit']
		);

		// Set the page title
		$this->_buildTitle();

		// Set the pathway
		$this->_buildPathway();

		// Output HTML
		if ($this->getError())
		{
			foreach ($this->getErrors() as $error)
			{
				$this->view->setError($error);
			}
		}
		$this->view->display();
	}

	/**
	 * Display a question
	 *
	 * @return     void
	 */
	public function questionTask()
	{
		$this->view->setLayout('question');

		// Incoming
		$this->view->id   = JRequest::getInt('id', 0);
		$this->view->note = $this->_note(JRequest::getInt('note', 0));

		$this->view->question = AnswersModelQuestion::getInstance($this->view->id);

		// Ensure we have an ID to work with
		if (!$this->view->id)
		{
			JError::raiseError(404, JText::_('COM_ANSWERS_ERROR_QUESTION_ID_NOT_FOUND'));
			return;
		}

		// Check if person voted
		$this->view->voted = 0;
		if (!$this->juser->get('guest'))
		{
			$this->view->voted = $this->view->question->voted();
		}

		// Set the page title
		$this->_buildTitle($this->view->question);

		// Set the pathway
		$this->_buildPathway($this->view->question);

		// Output HTML
		$this->view->config = $this->config;
		$this->view->juser  = $this->juser;

		if (!isset($this->view->responding))
		{
			$this->view->responding = 0;
		}

		$this->view->notifications = ($this->getComponentMessage()) ? $this->getComponentMessage() : array();

		if ($this->getError())
		{
			foreach ($this->getErrors() as $error)
			{
				$this->view->setError($error);
			}
		}

		$this->view->display();
	}

	/**
	 * Show a form for answering a question
	 *
	 * @return     void
	 */
	public function answerTask()
	{
		$this->view->responding = 1;
		$this->questionTask();
	}

	/**
	 * Show a confirmation form for deleting a question
	 *
	 * @return     void
	 */
	public function deleteTask()
	{
		$this->view->responding = 4;
		$this->questionTask();
	}

	/**
	 * Create a new question
	 *
	 * @return     void
	 */
	public function newTask($question = null)
	{
		// Login required
		if ($this->juser->get('guest'))
		{
			$this->addComponentMessage(JText::_('COM_ANSWERS_PLEASE_LOGIN'), 'warning');
			$this->loginTask();
			return;
		}

		$this->view->setLayout('new');

		// Instantiate a new view
		$this->view->config = $this->config;
		$this->view->task   = $this->_task;

		// Incoming
		$this->view->tag = JRequest::getVar('tag', '');

		if (is_object($question))
		{
			$this->view->question = $question;
		}
		else
		{
			$this->view->question = new AnswersModelQuestion(0);
		}

		// Is banking turned on?
		$this->view->funds = 0;
		if ($this->config->get('banking'))
		{
			$BTL = new \Hubzero\Bank\Teller($this->database, $this->juser->get('id'));
			$funds = $BTL->summary() - $BTL->credit_summary();
			$this->view->funds = ($funds > 0) ? $funds : 0;
		}

		// Set the page title
		$this->_buildTitle();

		// Set the pathway
		$this->_buildPathway();

		// Output HTML
		$this->view->notifications = ($this->getComponentMessage()) ? $this->getComponentMessage() : array();
		$this->view->display();
	}

	/**
	 * Save a question
	 *
	 * @return     void
	 */
	public function saveqTask()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Login required
		if ($this->juser->get('guest'))
		{
			$this->addComponentMessage(JText::_('COM_ANSWERS_PLEASE_LOGIN'), 'warning');
			$this->loginTask();
			return;
		}

		// Incoming
		$fields = JRequest::getVar('fields', array(), 'post', 'none', 2);
		$tags   = JRequest::getVar('tags', '');
		if (!isset($fields['reward']))
		{
			$fields['reward'] = 0;
		}

		// If offering a reward, do some checks
		if ($fields['reward'])
		{
			// Is it an actual number?
			if (!is_numeric($fields['reward']))
			{
				JError::raiseError(500, JText::_('COM_ANSWERS_REWARD_MUST_BE_NUMERIC'));
				return;
			}
			// Are they offering more than they can afford?
			if ($fields['reward'] > $fields['funds'])
			{
				JError::raiseError(500, JText::_('COM_ANSWERS_INSUFFICIENT_FUNDS'));
				return;
			}
		}

		// clean input
		array_walk($fields, function($field, $key)
		{
			$fields[$key] = \Hubzero\Utility\Sanitize::stripScripts($field);
			$fields[$key] = \Hubzero\Utility\Sanitize::clean($field);
		});

		// Initiate class and bind posted items to database fields
		$row = new AnswersModelQuestion($fields['id']);
		if (!$row->bind($fields))
		{
			JError::raiseError(500, $row->getError());
			return;
		}

		if ($fields['reward'] && $this->config->get('banking'))
		{
			$row->set('reward', 1);
		}

		// Ensure the user added a tag
		if (!$tags)
		{
			$this->addComponentMessage(JText::_('COM_ANSWERS_QUESTION_MUST_HAVE_TAG'), 'error');
			$this->newTask($row);
			return;
		}

		// Store new content
		if (!$row->store(true))
		{
			JRequest::setVar('tag', $tags);

			$this->addComponentMessage($row->getError(), 'error');
			$this->newTask($row);
			return;
		}

		// Hold the reward for this question if we're banking
		if ($fields['reward'] && $this->config->get('banking'))
		{
			$BTL = new \Hubzero\Bank\Teller($this->database, $this->juser->get('id'));
			$BTL->hold(
				$fields['reward'],
				JText::_('COM_ANSWERS_HOLD_REWARD_FOR_BEST_ANSWER'),
				'answers',
				$row->get('id')
			);
		}

		// Add the tags
		$row->tag($tags);

		// Get users who need to be notified on every question
		$apu = $this->config->get('notify_users', '');
		$apu = explode(',', $apu);
		$apu = array_map('trim',$apu);

		$receivers = array();

		// Get tool contributors if question is about a tool
		if ($tags)
		{
			$tags = explode(',', $tags);
			if (count($tags) > 0)
			{
				require_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_tools' . DS . 'tables' . DS . 'author.php');
				require_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_tools' . DS . 'tables' . DS . 'version.php');

				$TA = new ToolAuthor($this->database);
				$objV = new ToolVersion($this->database);

				foreach ($tags as $tag)
				{
					if ($tag == '')
					{
						continue;
					}
					if (preg_match('/tool:/', $tag))
					{
						$toolname = preg_replace('/tool:/', '', $tag);
						if (trim($toolname))
						{
							$rev = $objV->getCurrentVersionProperty ($toolname, 'revision');
							$authors = $TA->getToolAuthors('', 0, $toolname, $rev);
							if (count($authors) > 0)
							{
								foreach ($authors as $author)
								{
									$receivers[] = $author->uidNumber;
								}
							}
						}
					}
				}
			}
		}

		if (!empty($apu))
		{
			foreach ($apu as $u)
			{
				$user = JUser::getInstance($u);
				if ($user)
				{
					$receivers[] = $user->get('id');
				}
			}
		}
		$receivers = array_unique($receivers);

		// Send the message
		if (!empty($receivers))
		{
			// Send a message about the new question to authorized users (specified admins or related content authors)
			$jconfig = JFactory::getConfig();
			$from = array(
				'email'     => $jconfig->getValue('config.mailfrom'),
				'name'      => $jconfig->getValue('config.sitename') . ' ' . JText::_('COM_ANSWERS_ANSWERS'),
				'multipart' => md5(date('U'))
			);

			// Build the message subject
			$subject = JText::_('COM_ANSWERS_ANSWERS') . ', ' . JText::_('new question about content you author or manage');

			$message = array();

			// Plain text message
			$eview = new \Hubzero\Component\View(array(
				'name'   => 'emails',
				'layout' => 'question_plaintext'
			));
			$eview->option   = $this->_option;
			$eview->jconfig  = $jconfig;
			$eview->sitename = $jconfig->getValue('config.sitename');
			$eview->juser    = $this->juser;
			$eview->question = $row;
			$eview->id       = $row->get('id', 0);
			$eview->boundary = $from['multipart'];

			$message['plaintext'] = $eview->loadTemplate();
			$message['plaintext'] = str_replace("\n", "\r\n", $message['plaintext']);

			// HTML message
			$eview->setLayout('question_html');

			$message['multipart'] = $eview->loadTemplate();
			$message['multipart'] = str_replace("\n", "\r\n", $message['multipart']);

			JPluginHelper::importPlugin('xmessage');
			$dispatcher = JDispatcher::getInstance();
			if (!$dispatcher->trigger('onSendMessage', array('new_question_admin', $subject, $message, $from, $receivers, $this->_option)))
			{
				$this->setError(JText::_('COM_ANSWERS_MESSAGE_FAILED'));
			}
		}

		// Redirect to the question
		$this->setRedirect(
			JRoute::_('index.php?option=' . $this->_option . '&task=question&id=' . $row->get('id')),
			JText::_('COM_ANSWERS_NOTICE_QUESTION_POSTED_THANKS')
		);
	}

	/**
	 * Delete a question
	 *
	 * @return     void
	 */
	public function deleteqTask()
	{
		// Login required
		if ($this->juser->get('guest'))
		{
			$this->addComponentMessage(JText::_('COM_ANSWERS_PLEASE_LOGIN'), 'warning');
			$this->loginTask();
			return;
		}

		// Incoming
		$id = JRequest::getInt('qid', 0);
		$ip = (!$this->juser->get('guest')) ? JRequest::ip() : '';

		$reward = 0;
		if ($this->config->get('banking'))
		{
			$BT = new \Hubzero\Bank\Transaction($this->database);
			$reward = $BT->getAmount('answers', 'hold', $id);
		}
		$email = 0;

		$question = new AnswersTableQuestion($this->database);
		$question->load($id);

		// Check if user is authorized to delete
		if ($question->created_by != $this->juser->get('id'))
		{
			$this->setRedirect(
				JRoute::_('index.php?option=' . $this->_option . '&task=question&id=' . $id . '&note=3')
			);
			return;
		}
		else if ($question->state == 1)
		{
			$this->setRedirect(
				JRoute::_('index.php?option=' . $this->_option . '&task=question&id=' . $id . '&note=2')
			);
			return;
		}

		$question->state  = 2;  // Deleted by user
		$question->reward = 0;

		// Store new content
		if (!$question->store())
		{
			JError::raiseError(500, $question->getError());
			return;
		}

		// Get all the answers for this question
		$ar = new AnswersTableResponse($this->database);
		$responses = $ar->getRecords(array(
			'ip'  => $ip,
			'question_id' => $id
		));

		if ($reward && $this->config->get('banking'))
		{
			if ($responses)
			{
				$jconfig = JFactory::getConfig();

				$users = array();
				foreach ($responses as $r)
				{
					$user = JUser::getInstance($r->created_by);
					if (!is_object($user))
					{
						continue;
					}
					$users[] = $user->get('id');
				}

				// Build the "from" info
				$from = array(
					'email'     => $jconfig->getValue('config.mailfrom'),
					'name'      => $jconfig->getValue('config.sitename') . ' ' . JText::_('COM_ANSWERS_ANSWERS'),
					'multipart' => md5(date('U'))
				);

				// Build the message subject
				$subject = $jconfig->getValue('config.sitename') . ' ' . JText::_('COM_ANSWERS_ANSWERS') . ', ' . JText::_('COM_ANSWERS_QUESTION') . ' #' . $id . ' ' . JText::_('COM_ANSWERS_WAS_REMOVED');

				$message = array();

				// Plain text message
				$eview = new \Hubzero\Component\View(array(
					'name'   => 'emails',
					'layout' => 'removed_plaintext'
				));
				$eview->option   = $this->_option;
				$eview->jconfig  = $jconfig;
				$eview->sitename = $jconfig->getValue('config.sitename');
				$eview->juser    = $this->juser;
				$eview->question = new AnswersModelQuestion($question);
				$eview->id       = $question->get('id');
				$eview->boundary = $from['multipart'];

				$message['plaintext'] = $eview->loadTemplate();
				$message['plaintext'] = str_replace("\n", "\r\n", $message['plaintext']);

				// HTML message
				$eview->setLayout('removed_html');

				$message['multipart'] = $eview->loadTemplate();
				$message['multipart'] = str_replace("\n", "\r\n", $message['multipart']);

				// Send the message
				JPluginHelper::importPlugin('xmessage');
				$dispatcher = JDispatcher::getInstance();
				if (!$dispatcher->trigger('onSendMessage', array('answers_question_deleted', $subject, $message, $from, $users, $this->_option)))
				{
					$this->setError(JText::_('COM_ANSWERS_MESSAGE_FAILED'));
				}
			}

			// Remove hold
			$BT->deleteRecords('answers', 'hold', $id);

			// Make credit adjustment
			$BTL_Q = new \Hubzero\Bank\Teller($this->database, $this->juser->get('id'));
			$adjusted = $BTL_Q->credit_summary() - $reward;
			$BTL_Q->credit_adjustment($adjusted);
		}

		// Delete all tag associations
		/*$tagging = new AnswersModelTags($this->database);
		$tagging->remove_all_tags($id);

		// Get all the answers for this question
		if ($responses)
		{
			$al = new AnswersTableLog($this->database);
			foreach ($responses as $answer)
			{
				// Delete votes
				$al->deleteLog($answer->id);

				// Delete response
				$ar->deleteResponse($answer->id);
			}
		}*/

		// Redirect to the question
		$this->setRedirect(
			JRoute::_('index.php?option=' . $this->_option)
		);
	}

	/**
	 * Save an answer (reply to question)
	 *
	 * @return     void
	 */
	public function saveaTask()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Login required
		if ($this->juser->get('guest'))
		{
			$this->addComponentMessage(JText::_('COM_ANSWERS_PLEASE_LOGIN'), 'warning');
			$this->loginTask();
			return;
		}

		// Incoming
		$response = JRequest::getVar('response', array(), 'post', 'none', 2);

		// Initiate class and bind posted items to database fields
		$row = new AnswersModelResponse($response['id']);
		if (!$row->bind($response))
		{
			JError::raiseError(500, $row->getError());
			return;
		}

		// Store new content
		if (!$row->store(true))
		{
			JError::raiseError(500, $row->getError());
			return;
		}

		// Load the question
		$question = new AnswersModelQuestion($row->get('question_id'));

		$jconfig = JFactory::getConfig();

		// ---

		// Build the "from" info
		$from = array(
			'email'     => $jconfig->getValue('config.mailfrom'),
			'name'      => $jconfig->getValue('config.sitename') . ' ' . JText::_('COM_ANSWERS_ANSWERS'),
			'multipart' => md5(date('U'))
		);

		// Build the message subject
		$subject = $jconfig->getValue('config.sitename') . ' ' . JText::_('COM_ANSWERS_ANSWERS') . ', ' . JText::_('COM_ANSWERS_QUESTION') . ' #' . $question->get('id') . ' ' . JText::_('COM_ANSWERS_RESPONSE');

		$message = array();

		// Plain text message
		$eview = new \Hubzero\Component\View(array(
			'name'   => 'emails',
			'layout' => 'response_plaintext'
		));
		$eview->option   = $this->_option;
		$eview->jconfig  = $jconfig;
		$eview->sitename = $jconfig->getValue('config.sitename');
		$eview->juser    = $this->juser;
		$eview->question = $question;
		$eview->row      = $row;
		$eview->id       = $response['question_id'];
		$eview->boundary = $from['multipart'];

		$message['plaintext'] = $eview->loadTemplate();
		$message['plaintext'] = str_replace("\n", "\r\n", $message['plaintext']);

		// HTML message
		$eview->setLayout('response_html');

		$message['multipart'] = $eview->loadTemplate();
		$message['multipart'] = str_replace("\n", "\r\n", $message['multipart']);

		// ---

		$authorid = $question->creator('id');

		$apu = $this->config->get('notify_users', '');
		$apu = explode(',', $apu);
		$apu = array_map('trim', $apu);

		$receivers = array();

		if (!empty($apu))
		{
			foreach ($apu as $u)
			{
				$user = JUser::getInstance($u);
				if ($user)
				{
					$receivers[] = $user->get('id');
				}
			}
			$receivers = array_unique($receivers);
		}

		// Send the message
		JPluginHelper::importPlugin('xmessage');
		$dispatcher = JDispatcher::getInstance();

		if (!in_array($authorid, $receivers))
		{
			if (!$dispatcher->trigger('onSendMessage', array('answers_reply_submitted', $subject, $message, $from, array($authorid), $this->_option)))
			{
				$this->setError(JText::_('COM_ANSWERS_MESSAGE_FAILED'));
			}
		}

		if (!empty($receivers))
		{
			if (!$dispatcher->trigger('onSendMessage', array('new_answer_admin', $subject, $message, $from, $receivers, $this->_option)))
			{
				$this->setError(JText::_('COM_ANSWERS_MESSAGE_FAILED'));
			}
		}

		// Redirect to the question
		$this->setRedirect(
			JRoute::_($question->link()),
			JText::_('COM_ANSWERS_NOTICE_POSTED_THANKS'),
			'success'
		);
	}

	/**
	 * Mark an answer as accepted
	 *
	 * @return     void
	 */
	public function acceptTask()
	{
		// Login required
		if ($this->juser->get('guest'))
		{
			$this->addComponentMessage(JText::_('COM_ANSWERS_PLEASE_LOGIN'), 'warning');
			$this->loginTask();
			return;
		}

		// Incoming
		$id  = JRequest::getInt('id', 0);
		$rid = JRequest::getInt('rid', 0);

		$question = new AnswersModelQuestion($id);

		// Check changes
		if (!$question->accept($rid))
		{
			$this->setError($question->getError());
		}

		// Load the plugins
		JPluginHelper::importPlugin('xmessage');
		$dispatcher = JDispatcher::getInstance();

		// Call the plugin
		if (!$dispatcher->trigger('onTakeAction', array('answers_reply_submitted', array($this->juser->get('id')), $this->_option, $rid)))
		{
			$this->setError(JText::_('COM_ANSWERS_ACTION_FAILED'));
		}

		// Redirect to the question
		$this->setRedirect(
			JRoute::_($question->link() . '&note=10'),
			JText::_('COM_ANSWERS_NOTICE_QUESTION_CLOSED'),
			'success'
		);
	}

	/**
	 * Vote for an item
	 *
	 * @return     void
	 */
	public function voteTask()
	{
		$no_html = JRequest::getInt('no_html', 0);
		$id   = JRequest::getInt('id', 0);
		$vote = JRequest::getInt('vote', 0);

		// Login required
		if ($this->juser->get('guest'))
		{
			if (!$no_html)
			{
				$this->addComponentMessage(JText::_('COM_ANSWERS_PLEASE_LOGIN_TO_VOTE'), 'warning');
				$this->loginTask();
			}
			return;
		}

		// Load the question
		$row = new AnswersModelQuestion($id);

		// Record the vote
		if (!$row->vote($vote))
		{
			if ($no_html)
			{
				$response = new stdClass;
				$response->success = false;
				$response->message = $row->getError();
				echo json_encode($response);
				return;
			}
			else
			{
				$this->setRedirect(
					JRoute::_($row->link()),
					$row->getError(),
					'warning'
				);
				return;
			}
		}

		// Update display
		if ($no_html)
		{
			$this->qid = $id;

			$this->view->question = $row;
			$this->view->voted    = $vote;
			if ($this->getError())
			{
				foreach ($this->getErrors() as $error)
				{
					$this->view->setError($error);
				}
			}
			$this->view->display();
		}
		else
		{
			$this->setRedirect(
				JRoute::_($row->link())
			);
		}
	}

	/**
	 * Authorization check
	 *
	 * @param      string  $assetType Asset type to authorize
	 * @param      integer $assetId   ID of asset to authorize
	 * @return     void
	 */
	protected function _authorize($assetType='component', $assetId=null)
	{
		$this->config->set('access-view-' . $assetType, true);
		if (!$this->juser->get('guest'))
		{
			$asset  = $this->_option;
			if ($assetId)
			{
				$asset .= ($assetType != 'component') ? '.' . $assetType : '';
				$asset .= ($assetId) ? '.' . $assetId : '';
			}

			$at = '';
			if ($assetType != 'component')
			{
				$at .= '.' . $assetType;
			}

			// Admin
			$this->config->set('access-admin-' . $assetType, $this->juser->authorise('core.admin', $asset));
			$this->config->set('access-manage-' . $assetType, $this->juser->authorise('core.manage', $asset));
			// Permissions
			$this->config->set('access-create-' . $assetType, $this->juser->authorise('core.create' . $at, $asset));
			$this->config->set('access-delete-' . $assetType, $this->juser->authorise('core.delete' . $at, $asset));
			$this->config->set('access-edit-' . $assetType, $this->juser->authorise('core.edit' . $at, $asset));
			$this->config->set('access-edit-state-' . $assetType, $this->juser->authorise('core.edit.state' . $at, $asset));
			$this->config->set('access-edit-own-' . $assetType, $this->juser->authorise('core.edit.own' . $at, $asset));
		}
	}

	/**
	 * Get a message
	 *
	 * @param      integer $type Note ID
	 * @param      array   $note Array to populate
	 * @return     array
	 */
	private function _note($type, $note=array('msg'=>'','class'=>'warning'))
	{
		switch ($type)
		{
			case '1' :  // question was removed
				$note['msg'] = JText::_('COM_ANSWERS_NOTICE_QUESTION_REMOVED');
				$note['class'] = 'info';
			break;
			case '2' : // can't delete a closed question
				$note['msg'] = JText::_('COM_ANSWERS_WARNING_CANT_DELETE_CLOSED');
			break;
			case '3' : // not authorized to delete question
				$note['msg'] = JText::_('COM_ANSWERS_WARNING_CANT_DELETE');
			break;
			case '4' : // answer posted
				$note['msg'] = JText::_('COM_ANSWERS_NOTICE_POSTED_THANKS');
				$note['class'] = 'passed';
			break;
			case '5' : // question posted
				$note['msg'] = JText::_('COM_ANSWERS_NOTICE_QUESTION_POSTED_THANKS');
				$note['class'] = 'passed';
			break;
			case '6' : // can't answer own question
				$note['msg'] = JText::_('COM_ANSWERS_NOTICE_CANT_ANSWER_OWN_QUESTION');
			break;
			case '7' : // can't delete question
				$note['msg'] = JText::_('COM_ANSWERS_NOTICE_CANNOT_DELETE');
			break;
			case '8' : // can't vote again
				$note['msg'] = JText::_('COM_ANSWERS_NOTICE_ALREADY_VOTED_FOR_QUESTION');
			break;
			case '9' : // can't vote for own question
				$note['msg'] = JText::_('COM_ANSWERS_NOTICE_RECOMMEND_OWN_QUESTION');
			break;
			case '10' : // answer accepted
				$note['msg'] = JText::_('COM_ANSWERS_NOTICE_QUESTION_CLOSED');
			break;
		}
		return $note;
	}

	/**
	 * Latest Questions Feed
	 *
	 * @return     string XML
	 */
	public function latestTask()
	{
		//get the joomla document
		$jdoc = JFactory::getDocument();

		//load joomla config
		$jconfig = JFactory::getConfig();

		//instantiate database object
		$database = JFactory::getDBO();

		//get the id of module so we get the right params
		$mid = JRequest::getInt("m", 0);

		//get module params
		$params = \Hubzero\Module\Helper::getParams($mid);

		//include feed lib
		include_once(JPATH_ROOT . DS . 'libraries' . DS . 'joomla' . DS . 'document' . DS . 'feed' . DS . 'feed.php');

		//force mime type of document to be rss
		$jdoc->setMimeEncoding('application/rss+xml');

		// Start a new feed object
		$doc = new JDocumentFeed;

		//set rss feed attribs
		$doc->link 			= JRoute::_('index.php?option=com_answers');
		$doc->title  		= JText::sprintf('COM_ANSWERS_LATEST_QUESTIONS_RSS_TITLE', $jconfig->getValue('config.sitename'));
		$doc->description 	= JText::sprintf('COM_ANSWERS_LATEST_QUESTIONS_RSS_DESCRIPTION', $jconfig->getValue('config.sitename'));
		$doc->copyright 	= JText::sprintf('COM_ANSWERS_LATEST_QUESTIONS_RSS_COPYRIGHT', date("Y"), $jconfig->getValue('config.sitename'));
		$doc->category 		= JText::_('COM_ANSWERS_LATEST_QUESTIONS_RSS_CATEGORY');

		//number of questions to get
		$limit = intval($params->get('limit', 5));

		//open, closed, or both
		$state = $params->get('state', 'both');
		switch ($state)
		{
			case 'open': 	$st = "a.state=0"; 		break;
			case 'closed': 	$st = "a.state=1"; 		break;
			case 'both': 	$st = "a.state<2";		break;
		}

		//get questions based on params
		$sql = "SELECT
					a.id, a.subject, a.question, a.state, a.created, a.created_by, a.anonymous,
					(SELECT COUNT(*) FROM #__answers_responses AS r WHERE r.question_id=a.id) AS rcount
				FROM #__answers_questions AS a
				WHERE {$st}
				ORDER BY a.created DESC
				LIMIT {$limit}";
		$database->setQuery($sql);
		$questions = $database->loadAssocList();

		//add each question to the feed
		foreach ($questions as $question)
		{
			//get the authors name
			$a = JFactory::getUser($question['created_by']);
			$author = ($a) ? $a->get("name") : "";
			$author = ($question['anonymous']) ? "Anonymous" : $author;

			$link = JRoute::_('index.php?option=com_answers&task=question&id='.$question['id']);

			//set feed item attibs and add item to feed
			$item 				= new JFeedItem();
			$item->title 		= html_entity_decode(\Hubzero\Utility\Sanitize::stripAll(stripslashes($question['subject'])));
			$item->link 		= $link;
			$item->description 	= html_entity_decode(\Hubzero\Utility\Sanitize::stripAll(stripslashes($question['question'])));
			$item->date        	= date("r", strtotime($question['created']));
			$item->category   	= 'Recent Question';
			$item->author     	= $author;
			$doc->addItem($item);
		}

		//render feed
		echo $doc->render();
	}
}

