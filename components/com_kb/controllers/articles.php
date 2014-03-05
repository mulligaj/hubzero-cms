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
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

ximport('Hubzero_Controller');

/**
 * Knowledge Base controller
 */
class KbControllerArticles extends Hubzero_Controller
{
	/**
	 * Execute a task
	 * 
	 * @return     void
	 */
	public function execute()
	{
		$this->archive = new KbModelArchive();

		parent::execute();
	}

	/**
	 * Displays an overview of categories and articles in the knowledge base
	 * 
	 * @return     void
	 */
	public function displayTask()
	{
		$this->view->setLayout('display');
		
		// Add the CSS to the template
		$this->_getStyles();

		// Add any JS to the template
		$this->_getScripts('assets/js/kb');

		// Set the pathway
		$this->_buildPathway(null, null, null);

		// Set the page title
		$this->_buildTitle(null, null, null);

		// Output HTML
		$this->view->database = $this->database;
		$this->view->title    = JText::_('COM_KB');
		$this->view->catid    = 0;
		$this->view->config   = $this->config;
		$this->view->archive  = $this->archive;

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
	 * Displays a list of articles for a given category
	 * 
	 * @return     void
	 */
	public function categoryTask()
	{
		$this->view->setLayout('category');

		// Make sure we have an ID
		if (!($alias = JRequest::getVar('alias', ''))) 
		{
			$this->displayTask();
			return;
		}

		// Get the category
		$sect = -1;
		$cat  = 0;

		$this->view->category = new KbModelCategory($alias);
		$this->view->section  = new KbModelCategory($this->view->category->get('section'));
		if ($alias == 'all') 
		{
			$this->view->category->set('alias', 'all');
			$this->view->category->set('title', JText::_('All Articles'));
			$this->view->category->set('id', 0);
			$this->view->category->set('state', 1);
		} 
		else 
		{
			if ($this->view->category->get('section')) 
			{
				//$this->view->section  = new KbModelCategory($this->view->category->get('section'));

				$sect = $this->view->category->get('section');
				$cat  = $this->view->category->get('id');
			} 
			else 
			{
				$sect = $this->view->category->get('id');
			}
		}

		if (!$this->view->category->isPublished()) 
		{
			JError::raiseError(404, JText::_('COM_KB_ERROR_CATEGORY_NOT_FOUND'));
			return;
		}

		// Get configuration
		$jconfig = JFactory::getConfig();

		$this->view->filters = array();
		$this->view->filters['limit']    = JRequest::getInt('limit', $jconfig->getValue('config.list_limit'));
		$this->view->filters['start']    = JRequest::getInt('limitstart', 0);
		$this->view->filters['sort']    = JRequest::getWord('sort', 'recent');
		if (!in_array($this->view->filters['sort'], array('recent', 'popularity')))
		{
			$this->view->filters['sort'] = 'recent';
		}
		$this->view->filters['section']  = $sect;
		$this->view->filters['category'] = $cat;
		$this->view->filters['search']   = JRequest::getVar('search','');
		$this->view->filters['state']    = 1;
		if (!$this->juser->get('guest')) 
		{
			$this->view->filters['user_id'] = $this->juser->get('id');
		}
		
		// Get a record count
		$this->view->total = $this->view->category->articles('count', $this->view->filters);

		// Get the records
		$this->view->articles = $this->view->category->articles('list', $this->view->filters);

		// Initiate paging
		jimport('joomla.html.pagination');
		$this->view->pageNav = new JPagination(
			$this->view->total, 
			$this->view->filters['start'], 
			$this->view->filters['limit']
		);

		// Get all main categories for menu
		$this->view->categories = $this->archive->categories();

		// Add the CSS to the template
		$this->_getStyles();

		// Add any JS to the template
		$this->_getScripts('assets/js/kb');

		// Set the pathway
		$this->_buildPathway($this->view->section, $this->view->category, null);

		// Set the page title
		$this->_buildTitle($this->view->section, $this->view->category, null);

		// Output HTML
		$this->view->title  = JText::_('COM_KB');
		$this->view->catid  = $sect;
		$this->view->config = $this->config;
		$this->view->juser  = $this->juser;
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
	 * Displays a knowledge base article
	 * 
	 * @return     void
	 */
	public function articleTask()
	{
		$this->view->setLayout('article');

		// Incoming
		$alias = JRequest::getVar('alias', '');
		$id    = JRequest::getInt('id', 0);

		// Load the article
		$this->view->article = new KbModelArticle(($alias ? $alias : $id), JRequest::getVar('category'));

		if (!$this->view->article->exists()) 
		{
			JError::raiseError(404, JText::_('COM_KB_ERROR_ARTICLE_NOT_FOUND'));
			return;
		}

		if (!$this->view->article->isPublished()) 
		{
			JError::raiseError(404, JText::_('COM_KB_ERROR_ARTICLE_NOT_FOUND'));
			return;
		}

		// Is the user logged in?
		/*if (!$this->juser->get('guest')) 
		{
			ximport('Hubzero_Environment');
			
			// See if this person has already voted
			$h = new KbTableVote($this->database);
			$this->view->vote = $h->getVote(
				$this->view->article->get('id'), 
				$this->juser->get('id'), 
				Hubzero_Environment::ipAddress(), 
				'entry'
			);
		} 
		else 
		{*/
			$this->view->vote = strtolower(JRequest::getVar('vote', ''));
		//}

		// Load the category object
		$this->view->section = new KbModelCategory($this->view->article->get('section'));
		if (!$this->view->section->isPublished()) 
		{
			JError::raiseError(404, JText::_('COM_KB_ERROR_ARTICLE_NOT_FOUND'));
			return;
		}

		// Load the category object
		$this->view->category = $this->view->article->category();
		if ($this->view->category->exists() && !$this->view->category->isPublished()) 
		{
			JError::raiseError(404, JText::_('COM_KB_ERROR_ARTICLE_NOT_FOUND'));
			return;
		}

		// Get all main categories for menu
		$this->view->categories = $this->archive->categories('list');

		$this->view->subcategories = $this->view->section->children('list');

		$this->view->replyto = new KbModelComment(JRequest::getInt('reply', 0));

		// Add the CSS to the template
		$this->_getStyles();

		// Add any JS to the template
		$this->_getScripts('assets/js/kb');

		// Set the pathway
		$this->_buildPathway($this->view->section, $this->view->category, $this->view->article);

		// Set the page title
		$this->_buildTitle($this->view->section, $this->view->category, $this->view->article);

		// Output HTML
		$this->view->title   = JText::_('COM_KB');
		$this->view->juser   = $this->juser;
		$this->view->helpful = $this->helpful;
		$this->view->catid   = $this->view->section->get('id');
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
	 * Pushes items to the global breadcrumbs object
	 * 
	 * @param      object $section  KbTableCategory
	 * @param      object $category KbTableCategory
	 * @param      object $article  KbTableArticle
	 * @return     void
	 */
	protected function _buildPathway($section=null, $category=null, $article=null)
	{
		$app = JFactory::getApplication();
		$pathway = $app->getPathway();

		if (count($pathway->getPathWay()) <= 0) 
		{
			$pathway->addItem(
				JText::_(strtoupper($this->_option)),
				'index.php?option=' . $this->_option
			);
		}
		if (is_object($section) && $section->get('alias')) 
		{
			$pathway->addItem(
				stripslashes($section->get('title')),
				'index.php?option=' . $this->_option . '&section=' . $section->get('alias')
			);
		}
		if (is_object($category) && $category->get('alias')) 
		{
			$lnk  = 'index.php?option=' . $this->_option;
			$lnk .= (is_object($section) && $section->get('alias')) ? '&section=' . $section->get('alias') : '';
			$lnk .= '&category=' . $category->get('alias');

			$pathway->addItem(
				stripslashes($category->get('title')),
				$lnk
			);
		}
		if (is_object($article) && $article->get('alias')) 
		{
			$lnk = 'index.php?option=' . $this->_option . '&section=' . $section->get('alias');
			if (is_object($category) && $category->get('alias')) 
			{
				$lnk .= '&category=' . $category->get('alias');
			}
			$lnk .= '&alias=' . $article->get('alias');

			$pathway->addItem(
				stripslashes($article->get('title')),
				$lnk
			);
		}
	}

	/**
	 * Builds the document title
	 * 
	 * @param      object $section  KbTableCategory
	 * @param      object $category KbTableCategory
	 * @param      object $article  KbTableArticle
	 * @return     void
	 */
	protected function _buildTitle($section=null, $category=null, $article=null)
	{
		$this->_title = JText::_(strtoupper($this->_option));
		if (is_object($section) && $section->get('title') != '') 
		{
			$this->_title .= ': ' . stripslashes($section->get('title'));
		}
		if (is_object($category) && $category->get('title') != '') 
		{
			$this->_title .= ': ' . stripslashes($category->get('title'));
		}
		if (is_object($article)) 
		{
			$this->_title .= ': ' . stripslashes($article->get('title'));
		}
		$document = JFactory::getDocument();
		$document->setTitle($this->_title);
	}

	/**
	 * Records the vote (like/dislike) of either an article or comment
	 * AJAX call - Displays updated vote links
	 * Standard link - falls through to the article view
	 * 
	 * @return     void
	 */
	public function voteTask()
	{
		if ($this->juser->get('guest')) 
		{
			$return = JRequest::getVar('REQUEST_URI', JRoute::_('index.php?option=' . $this->_option), 'server');
			$this->setRedirect(
				JRoute::_('index.php?option=com_login&return=' . base64_encode($return))
			);
			return;
		}

		// Incoming
		$type = strtolower(JRequest::getVar('type', ''));
		$vote = strtolower(JRequest::getVar('vote', ''));
		$id   = JRequest::getInt('id', 0);

		// Did they vote?
		if (!$vote) 
		{
			// Already voted
			$this->setError(JText::_('COM_KB_USER_DIDNT_VOTE'));
			$this->articleTask();
			return;
		}

		if (!in_array($type, array('entry', 'comment'))) 
		{
			// Already voted
			$this->setError(JText::_('COM_KB_WRONG_VOTE_TYPE'));
			$this->displayTask();
			return;
		}

		// Load the article
		switch ($type)
		{
			case 'entry':
				$row = new KbModelArticle($id);
			break;
			case 'comment':
				$row = new KbModelComment($id);
			break;
		}

		if (!$row->vote($vote, $this->juser->get('id'))) 
		{
			$this->setError($row->getError());
			return;
		}

		if (JRequest::getInt('no_html', 0)) 
		{
			$this->view->setLayout('_vote');

			$this->view->item = $row;
			$this->view->type = $type;
			$this->view->vote = $vote;
			$this->view->id   = $id;
			if ($this->getError()) 
			{
				$this->view->setError($this->getError());
			}
			$this->view->display();
		} 
		else 
		{
			if ($type == 'entry') 
			{
				//JRequest::setVar('alias', $row->get('alias'));
				$this->setRedirect(
					JRoute::_($row->link())
				);
				return;
			}
			$this->articleTask();
		}
	}

	/**
	 * Saves a comment to an article
	 * Displays article
	 * 
	 * @return     void
	 */
	public function savecommentTask()
	{
		// Ensure the user is logged in
		if ($this->juser->get('guest')) 
		{
			$return = JRequest::getVar('REQUEST_URI', JRoute::_('index.php?option=' . $this->_option), 'server');
			$this->setRedirect(
				JRoute::_('index.php?option=com_login&return=' . base64_encode($return))
			);
			return;
		}

		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Incoming
		$comment = JRequest::getVar('comment', array(), 'post', 'none', 2);

		// Instantiate a new comment object and pass it the data
		$row = new KbModelComment($comment['id']);
		if (!$row->bind($comment)) 
		{
			$this->setError($row->getError());
			$this->articleTask();
			return;
		}

		// Store new content
		if (!$row->store(true)) 
		{
			$this->setError($row->getError());
			$this->articleTask();
			return;
		}

		$this->setRedirect(
			$row->link() . '#comments'
		);
	}

	/**
	 * Displays an RSS feed of comments for a given article
	 * 
	 * @return     void
	 */
	public function commentsTask()
	{
		if (!$this->config->get('feeds_enabled')) 
		{
			$this->_task = 'article';
			$this->articleTask();
			return;
		}

		include_once(JPATH_ROOT . DS . 'libraries' . DS . 'joomla' . DS . 'document' . DS . 'feed' . DS . 'feed.php');

		// Set the mime encoding for the document
		$jdoc = JFactory::getDocument();
		$jdoc->setMimeEncoding('application/rss+xml');

		// Start a new feed object
		$doc = new JDocumentFeed;
		$app = JFactory::getApplication();
		$params = $app->getParams();
		$doc->link = JRoute::_('index.php?option=' . $this->_option);

		// Incoming
		$alias = JRequest::getVar('alias', '');

		if (!$alias) 
		{
			return $this->displayTask();
		}

		$entry = new KbTableArticle($this->database);
		$entry->loadAlias($alias);

		if (!$entry->id) 
		{
			return $this->displayTask();
		}

		// Load the category object
		$section = new KbTableCategory($this->database);
		$section->load($entry->section);

		// Load the category object
		$category = new KbTableCategory($this->database);
		if ($entry->category) 
		{
			$category->load($entry->category);
		}

		// Load the comments
		$bc = new KbTableComment($this->database);
		$rows = $bc->getAllComments($entry->id);

		// Build some basic RSS document information
		$jconfig = JFactory::getConfig();
		$doc->title  = $jconfig->getValue('config.sitename') . ' - ' . JText::_(strtoupper($this->_option));
		$doc->title .= ($entry->title) ? ': ' . stripslashes($entry->title) : '';
		$doc->title .= ': ' . JText::_('COM_KB_COMMENTS');

		$doc->description = JText::sprintf('COM_KB_COMMENTS_RSS_DESCRIPTION', $jconfig->getValue('config.sitename'), stripslashes($entry->title));
		$doc->copyright = JText::sprintf('COM_KB_COMMENTS_RSS_COPYRIGHT', date("Y"), $jconfig->getValue('config.sitename'));

		// Start outputing results if any found
		if (count($rows) > 0) 
		{
			$wikiconfig = array(
				'option'   => $this->_option,
				'scope'    => '',
				'pagename' => $entry->get('alias'),
				'pageid'   => $entry->id,
				'filepath' => '',
				'domain'   => ''
			);
			ximport('Hubzero_Wiki_Parser');
			$p = Hubzero_Wiki_Parser::getInstance();

			foreach ($rows as $row)
			{
				// URL link to article
				$link = JRoute::_('index.php?option=' . $this->_option . '&section=' . $section->get('alias') . '&category=' . $category->get('alias') . '&alias=' . $entry->get('alias') . '#c' . $row->id);

				$author = JText::_('COM_KB_ANONYMOUS');
				if (!$row->anonymous) 
				{
					$cuser  = JUser::getInstance($row->created_by);
					$author = $cuser->get('name');
				}

				// Prepare the title
				$title = JText::sprintf('COM_KB_COMMENTS_RSS_COMMENT_TITLE', $author) . ' @ ' . JHTML::_('date', $row->created, JText::_('TIME_FORMAT_HZ1')) . ' on ' . JHTML::_('date', $row->created, JText::_('DATE_FORMAT_HZ1'));

				// Strip html from feed item description text
				if ($row->reports) 
				{
					$description = JText::_('COM_KB_COMMENT_REPORTED_AS_ABUSIVE');
				} 
				else 
				{
					$description = $p->parse($row->content, $wikiconfig);
				}
				$description = html_entity_decode(Hubzero_View_Helper_Html::purifyText($description));

				@$date = ($row->created ? date('r', strtotime($row->created)) : '');

				// Load individual item creator class
				$item = new JFeedItem();
				$item->title       = $title;
				$item->link        = $link;
				$item->description = $description;
				$item->date        = $date;
				$item->category    = '';
				$item->author      = $author;

				// Loads item info into rss array
				$doc->addItem($item);

				// Check for any replies
				if ($row->replies) 
				{
					foreach ($row->replies as $reply)
					{
						// URL link to article
						$link = JRoute::_('index.php?option=' . $this->_option . '&section=' . $section->get('alias') . '&category=' . $category->get('alias') . '&alias=' . $entry->get('alias') . '#c' . $reply->id);

						$author = JText::_('COM_KB_ANONYMOUS');
						if (!$reply->anonymous) 
						{
							$cuser  = JUser::getInstance($reply->created_by);
							$author = $cuser->get('name');
						}

						// Prepare the title
						$title = JText::sprintf('COM_KB_COMMENTS_RSS_REPLY_TITLE', $row->id, $author) . ' @ ' . JHTML::_('date', $reply->created, JText::_('TIME_FORMAT_HZ1')) . ' on ' . JHTML::_('date', $reply->created, JText::_('DATE_FORMAT_HZ1'));

						// Strip html from feed item description text
						if ($reply->reports) 
						{
							$description = JText::_('COM_KB_COMMENT_REPORTED_AS_ABUSIVE');
						} 
						else 
						{
							$description = (is_object($p)) ? $p->parse(stripslashes($reply->content)) : nl2br(stripslashes($reply->content));
						}
						$description = html_entity_decode(Hubzero_View_Helper_Html::purifyText($description));

						@$date = ($reply->created ? date('r', strtotime($reply->created)) : '');

						// Load individual item creator class
						$item = new JFeedItem();
						$item->title       = $title;
						$item->link        = $link;
						$item->description = $description;
						$item->date        = $date;
						$item->category    = '';
						$item->author      = $author;

						// Loads item info into rss array
						$doc->addItem($item);

						if ($reply->replies) 
						{
							foreach ($reply->replies as $response)
							{
								// URL link to article
								$link = JRoute::_('index.php?option=' . $this->_option . '&section=' . $section->get('alias') . '&category=' . $category->get('alias') . '&alias=' . $entry->get('alias') . '#c' . $response->id);

								$author = JText::_('COM_KB_ANONYMOUS');
								if (!$response->anonymous) 
								{
									$cuser  = JUser::getInstance($response->created_by);
									$author = $cuser->get('name');
								}

								// Prepare the title
								$title = JText::sprintf('COM_KB_COMMENTS_RSS_REPLY_TITLE', $reply->id, $author) . ' @ ' . JHTML::_('date', $response->created, JText::_('TIME_FORMAT_HZ1')) . ' on ' . JHTML::_('date', $response->created, JText::_('DATE_FORMAT_HZ1'));

								// Strip html from feed item description text
								if ($response->reports) 
								{
									$description = JText::_('COM_KB_COMMENT_REPORTED_AS_ABUSIVE');
								} 
								else 
								{
									$description = (is_object($p)) ? $p->parse(stripslashes($response->content)) : nl2br(stripslashes($response->content));
								}
								$description = html_entity_decode(Hubzero_View_Helper_Html::purifyText($description));

								@$date = ($response->created ? date('r', strtotime($response->created)) : '');

								// Load individual item creator class
								$item = new JFeedItem();
								$item->title       = $title;
								$item->link        = $link;
								$item->description = $description;
								$item->date        = $date;
								$item->category    = '';
								$item->author      = $author;

								// Loads item info into rss array
								$doc->addItem($item);
							}
						}
					}
				}
			}
		}

		// Output the feed
		echo $doc->render();
	}
}

