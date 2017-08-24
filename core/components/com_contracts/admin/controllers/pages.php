<?php
namespace Components\Contracts\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\Contracts\Models\Contract;
use Components\Contracts\Models\Page;
use Request;
use Config;
use Notify;
use Route;
use User;
use Lang;
use Date;
use App;

/**
 * Drwho controller for show characters
 * 
 * Accepts an array of configuration values to the constructor. If no config 
 * passed, it will automatically determine the component and controller names.
 * Internally, sets the $database, $user, $view, and component $config.
 * 
 * Executable tasks are determined by method name. All public methods that end in 
 * "Task" (e.g., displayTask, editTask) are callable by the end user.
 * 
 * View name defaults to controller name with layout defaulting to task name. So,
 * a $controller of "One" and a $task of "two" will map to:
 *
 * /{component name}
 *     /{client name}
 *         /views
 *             /one
 *                 /tmpl
 *                     /two.php
 */
class Pages extends AdminController
{
	/**
	 * Execute a task
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Here we're aliasing the task 'add' to 'edit'. When examing
		// this controller, you should not find any method called 'addTask'.
		// Instead, we're telling the controller to execute the 'edit' task
		// whenever a task of 'add' is called.
		$this->registerTask('add', 'edit');
		// Call the parent execute() method. Important! Otherwise, the
		// controller will never actually execute anything.
		parent::execute();
	}

	public function editTask()
	{
		if (!User::authorise('core.edit', $this->_option)
         && !User::authorise('core.create', $this->_option))
        {
            App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
        }

		$contractId = Request::getVar('contract_id', 0);
		$pageId = Request::getVar('id', 0);
		$pageOrder = Request::getArray('currentOrder');
		$contract = Contract::oneOrNew($contractId);
		$page = Page::oneOrNew($pageId);
		if ($page->isNew())
		{
			$page->set('contract_id', $contract->id);
			$page->save();
		}
		$layout = $this->_task == 'add' ? '_page' : '_form';
		$editor = $this->view->setLayout($layout)
					->set('page', $page)
					->set('pageNum', 1)
					->set('task', $this->_task)
					->loadTemplate();
		
		$content = array(
			'id' => $page->get('id'),
			'content' => $editor
		);
		header('Content-Type: application/json');
		echo json_encode($content);
		exit();
	}

	public function saveTask()
	{
		if (!User::authorise('core.edit', $this->_option)
         && !User::authorise('core.create', $this->_option))
        {
            App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
        }
		Request::checkToken();
		$pageId = Request::getVar('id', 0);
		$text = Request::getVar('content');
		$page = Page::oneOrNew($pageId);
		$page->set('content', $text);
		$page->save();
		$content = array(
			'content' => $this->view->escape($page->get('content'))
		);
		header('Content-Type: application/json');
		echo json_encode($content);
		exit();
	}

	public function orderTask()
	{
		$pageOrder = Request::getArray('orderedItems', array());
		$pages = Page::all()->whereIn('id', $pageOrder)->rows();
		foreach ($pageOrder as $index => $page)
		{
			$orderValue = $index + 1;
			$pages->seek($page)->set('ordering', $orderValue);
		}

		if ($pages->save())
		{
			header('Content-Type: application/json');
			echo json_encode($pages->toJson());
			exit();
		}
		
	}

	/**
	 * Delete one or more entries
	 *
	 * @return  void
	 */
	public function removeTask()
	{
		if (!User::authorise('core.delete', $this->_option))
        {
            App::abort(403, Lang::txt('JERROR_ALERTNOAUTHOR'));
        }

		Request::checkToken('get');

		$id = Request::getVar('id', '');

		$entry = Page::one(intval($id));
		$contractId = $entry->contract_id;
		
		if (!$entry->destroy())
		{
			Notify::error($entry->getError());
		}	
		else
		{
			Notify::success(Lang::txt('COM_DRWHO_ENTRIES_DELETED'));
		}
		App::redirect(Route::url('index.php?option=' . $this->_option . '&controller=contracts&task=edit&id=' . $contractId, false));
	}
}
