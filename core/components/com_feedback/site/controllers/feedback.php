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

namespace Components\Feedback\Site\Controllers;

use Components\Feedback\Tables\Quote;
use Hubzero\Component\SiteController;
use Hubzero\User\Profile;
use Hubzero\Utility\Number;
use Hubzero\Utility\String;
use Hubzero\Utility\Sanitize;
use DirectoryIterator;
use Filesystem;
use Component;
use Pathway;
use Request;
use Config;
use Route;
use Lang;
use User;
use Date;

/**
 * Feedback controller class
 */
class Feedback extends SiteController
{
	/**
	 * Determine task and execute it
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->registerTask('success_story', 'story');

		parent::execute();
	}

	/**
	 * Set the pathway (breadcrumbs)
	 *
	 * @return  void
	 */
	protected function _buildPathway()
	{
		if (Pathway::count() <= 0)
		{
			Pathway::append(
				Lang::txt(strtoupper($this->_option)),
				'index.php?option=' . $this->_option
			);
		}
		if ($this->_task && in_array($this->_task, array('story', 'poll', 'sendstory', 'suggestions')))
		{
			Pathway::append(
				Lang::txt(strtoupper($this->_option) . '_' . strtoupper($this->_task)),
				'index.php?option=' . $this->_option . '&task=' . $this->_task
			);
		}
	}

	/**
	 * Set the page title
	 *
	 * @return  void
	 */
	protected function _buildTitle()
	{
		$this->_title = Lang::txt(strtoupper($this->_option));
		if ($this->_task && in_array($this->_task, array('story', 'poll', 'sendstory', 'suggestions')))
		{
			$this->_title .= ': ' . Lang::txt(strtoupper($this->_option) . '_' . strtoupper($this->_task));
		}

		\Document::setTitle($this->_title);
	}

	/**
	 * Display the main page
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Check if wishlistcomponent entry is there
		$this->view->wishlist = Component::isEnabled('com_wishlist', true);

		// Check if poll component entry is there
		$this->view->poll = Component::isEnabled('com_poll', true);

		// Set page title
		$this->_buildTitle();
		$this->view->title = $this->_title;

		// Set the pathway
		$this->_buildPathway();

		// Set any messages
		foreach ($this->getErrors() as $error)
		{
			$this->view->setError($error);
		}

		// Output HTML
		$this->view->display();
	}

	/**
	 * Show a list of quotes
	 *
	 * @return  void
	 */
	public function quotesTask()
	{
		// Get quotes
		$sq = new Quote($this->database);
		$this->view->quotes = $sq->find('list', array(
			'notable_quote' => 1
		));

		$this->view->path    = trim($this->config->get('uploadpath', '/site/quotes'), DS) . DS;
		$this->view->quoteId = Request::getInt('quoteid', null);

		$this->view->display();
	}

	/**
	 * Show a form for sending a success story
	 *
	 * @return  void
	 */
	public function storyTask($row=null)
	{
		// Check to see if the user temp folder for holding pics is there, if so then remove it
		if (is_dir($this->tmpPath() . DS . User::get('id')))
		{
			Filesystem::deleteDirectory($this->tmpPath() . DS . User::get('id'));
		}

		if (User::isGuest())
		{
			$here = Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller . '&task=' . $this->_task);
			App::redirect(
				Route::url('index.php?option=com_users&view=login&return=' . base64_encode($here)),
				Lang::txt('COM_FEEDBACK_STORY_LOGIN'),
				'warning'
			);
			return;
		}

		// Incoming
		$this->view->quote = array(
			'long'  => Request::getVar('quote', '', 'post'),
			'short' => Request::getVar('short_quote', '', 'post')
		);

		// Set page title
		$this->_buildTitle();
		$this->view->title = $this->_title;

		// Set the pathway
		$this->_buildPathway();

		$this->view->user = Profile::getInstance(User::get('id'));

		if (!is_object($row))
		{
			$row = new Quote($this->database);
			$row->org      = $this->view->user->get('organization');
			$row->fullname = $this->view->user->get('name');
		}

		$this->view->row = $row;

		// Set error messages
		foreach ($this->getErrors() as $error)
		{
			$this->view->setError($error);
		}

		// Output HTML
		$this->view
			->setLayout('story')
			->display();
	}

	/**
	 * Show the latest poll
	 *
	 * @return  void
	 */
	public function pollTask()
	{
		// Set page title
		$this->_buildTitle();
		$this->view->title = $this->_title;

		// Set the pathway
		$this->_buildPathway();

		// Set error messages
		foreach ($this->getErrors() as $error)
		{
			$this->view->setError($error);
		}

		// Output HTML
		$this->view->display();
	}

	/**
	 * Save a success story and show a thank you message
	 *
	 * @return  void
	 */
	public function sendstoryTask()
	{
		if (User::isGuest())
		{
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=' . $this->_controller . '&task=' . $this->_task)
			);
			return;
		}

		Request::checkToken();

		$fields = Request::getVar('fields', array(), 'post');
		$fields = array_map('trim', $fields);

		// Initiate class and bind posted items to database fields
		$row = new Quote($this->database);

		$fields['user_id']   = User::get('id');
		$fields['useremail'] = User::get('email');

		$dir  = String::pad($fields['user_id']);
		$path = $row->filespace(false) . DS . $dir;

		if (!$row->bind($fields))
		{
			$this->setError($row->getError());
			$this->storyTask($row);
			return;
		}

		// Check that a story was entered
		if (!$row->quote)
		{
			$this->setError(Lang::txt('COM_FEEDBACK_ERROR_MISSING_STORY'));
			$this->storyTask($row);
			return;
		}

		// Check for an author
		if (!$row->fullname)
		{
			$this->setError(Lang::txt('COM_FEEDBACK_ERROR_MISSING_AUTHOR'));
			$this->storyTask($row);
			return;
		}

		// Check for an organization
		if (!$row->org)
		{
			$this->setError(Lang::txt('COM_FEEDBACK_ERROR_MISSING_ORGANIZATION'));
			$this->storyTask($row);
			return;
		}

		// Code cleaner for xhtml transitional compliance
		$row->quote = Sanitize::stripAll($row->quote);
		$row->quote = str_replace('<br>', '<br />', $row->quote);
		$row->date  = Date::toSql();

		// Check content
		if (!$row->check())
		{
			$this->setError($row->getError());
			$this->storyTask($row);
			return;
		}

		// Store new content
		if (!$row->store())
		{
			$this->setError($row->getError());
			$this->storyTask($row);
			return;
		}

		$files = $_FILES;
		$addedPictures = array();

		$path = $row->filespace() . DS . $row->id;
		if (!is_dir($path))
		{
			if (!Filesystem::makeDirectory($path))
			{
				$this->setError(Lang::txt('COM_FEEDBACK_ERROR_UNABLE_TO_CREATE_UPLOAD_PATH'));
			}
		}

		// If there is a temp dir for this user then copy the contents to the newly created folder
		$tempDir = $this->tmpPath() . DS . User::get('id');

		if (is_dir($tempDir))
		{
			$dirIterator = new DirectoryIterator($tempDir);

			foreach ($dirIterator as $file)
			{
				if ($file->isDot() || $file->isDir())
				{
					continue;
				}

				$name = $file->getFilename();

				if ($file->isFile())
				{
					if ('cvs' == strtolower($name)
					 || '.svn' == strtolower($name))
					{
						continue;
					}

					if (Filesystem::move($tempDir . DS . $name, $path . DS . $name))
					{
						array_push($addedPictures, $name);
					}
				}
			}

			// Remove temp folder
			Filesystem::deleteDirectory($tempDir);
		}

		$this->view->addedPictures = $addedPictures;
		$this->view->path   = ltrim($row->filespace(), DS) . DS . $row->id;

		// Output HTML
		$this->view->user   = User::getRoot();
		$this->view->row    = $row;
		$this->view->config = $this->config;

		// Set page title
		$this->_buildTitle();
		$this->view->title = $this->_title;

		// Set the pathway
		$this->_buildPathway();

		// Set error messages
		foreach ($this->getErrors() as $error)
		{
			$this->view->setError($error);
		}

		// Output HTML
		$this->view
			->setLayout('thanks')
			->display();
	}

	/**
	 * Show a form for submitting suggestions
	 *
	 * @return  void
	 */
	public function suggestionsTask()
	{
		App::redirect(
			Route::url('index.php?option=com_wishlist')
		);
	}

	/**
	 * Takes recieved files and saves them to a temporary directory specific
	 * directory then returns a json object with those file names.
	 *
	 * @return  void
	 */
	public function uploadImageTask()
	{
		// Check if they're logged in
		if (User::isGuest())
		{
			echo json_encode(array('error' => Lang::txt('COM_FEEDBACK_STORY_LOGIN')));
			return;
		}

		// Max upload size
		$sizeLimit = $this->config->get('maxAllowed', 40000000);

		// Get the file
		if (isset($_GET['qqfile']))
		{
			$stream = true;
			$file = $_GET['qqfile'];
			$size = (int) $_SERVER["CONTENT_LENGTH"];
		}
		elseif (isset($_FILES['qqfile']))
		{
			$stream = false;
			$file = $_FILES['qqfile']['name'];
			$size = (int) $_FILES['qqfile']['size'];
		}
		else
		{
			echo json_encode(array('error' => Lang::txt('COM_FEEDBACK_ERROR_FILE_NOT_FOUND')));
			return;
		}

		// Define upload directory and make sure its writable
		$path = $this->tmpPath() . DS . User::get('id');

		if (!is_dir($path))
		{
			if (!Filesystem::makeDirectory($path))
			{
				echo json_encode(array('error' => Lang::txt('COM_FEEDBACK_ERROR_UNABLE_TO_CREATE_UPLOAD_PATH')));
				return;
			}
		}

		if (!is_writable($path))
		{
			echo json_encode(array('error' => Lang::txt('COM_FEEDBACK_ERROR_UPLOAD_PATH_IS_NOT_WRITABLE')));
			return;
		}

		// Check to make sure we have a file and its not too big
		if ($size == 0)
		{
			echo json_encode(array('error' => Lang::txt('COM_FEEDBACK_ERROR_EMPTY_FILE')));
			return;
		}
		if ($size > $sizeLimit)
		{
			$max = preg_replace('/<abbr \w+=\\"\w+\\">(\w{1,3})<\\/abbr>/', '$1', Number::formatBytes($sizeLimit));
			echo json_encode(array('error' => Lang::txt('COM_FEEDBACK_ERROR_FILE_TOO_LARGE', $max)));
			return;
		}

		// Don't overwrite previous files that were uploaded
		$pathinfo = pathinfo($file);
		$filename = $pathinfo['filename'];

		// Make the filename safe
		$filename = urldecode($filename);
		$filename = Filesystem::clean($filename);
		$filename = str_replace(' ', '_', $filename);

		$ext = $pathinfo['extension'];
		while (file_exists($path . DS . $filename . '.' . $ext))
		{
			$filename .= rand(10, 99);
		}

		$file = $path . DS . $filename . '.' . $ext;

		if ($stream)
		{
			// Read the php input stream to upload file
			$input = fopen("php://input", "r");
			$temp = tmpfile();
			$realSize = stream_copy_to_stream($input, $temp);
			fclose($input);

			// Move from temp location to target location which is user folder
			$target = fopen($file , "w");
			fseek($temp, 0, SEEK_SET);
			stream_copy_to_stream($temp, $target);
			fclose($target);
		}
		else
		{
			move_uploaded_file($_FILES['qqfile']['tmp_name'], $file);
		}

		if (!Filesystem::isSafe($file))
		{
			if (Filesystem::delete($file))
			{
				echo json_encode(array(
					'success' => false,
					'error'  => Lang::txt('COM_FEEDBACK_ERROR_FILE_FAILED_VIRUS_SCAN')
				));
				return;
			}
		}

		// Output result
		echo json_encode(array(
			'success'    => true,
			'file'       => $filename . '.' . $ext,
			'directory'  => str_replace(PATH_APP, '', $path),
		));
	}

	/**
	 * Path to the temp directory
	 *
	 * @return  string
	 */
	protected function tmpPath()
	{
		return Config::get('tmp_path') . DS . 'feedback';
	}
}

