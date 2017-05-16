<?php
namespace Components\PressForward\Admin;

// This is a permissions check to make sure the current logged-in
// user has permission to even access this component. Components
// can be blocked from users in a specific access group. This is 
// particularly important for components that can have potentially
// dramatic effects on users and the site (such as the members 
// component or plugin manager).
if (!\User::authorise('core.manage', 'com_pressforward'))
{
	return \App::abort(403, \Lang::txt('JERROR_ALERTNOAUTHOR'));
}

// WordPress compatibility functions
require_once(dirname(__DIR__) . DS . 'helpers' . DS . 'wordpress.php');

// PressForward helpers
require_once(dirname(__DIR__) . DS . 'helpers' . DS . 'pressforward.php');

// Permissions helpers
require_once(dirname(__DIR__) . DS . 'helpers' . DS . 'permissions.php');

// Models
require_once(dirname(__DIR__) . DS . 'models' . DS . 'post.php');

// Make extra sure that controller exists
$task = \Request::getCmd('task');
$controllerName = \Request::getCmd('controller', 'items');
if (!file_exists(__DIR__ . DS . 'controllers' . DS . $controllerName . '.php'))
{
	\App::abort(404, \Lang::txt('PF_ERROR_CONTROLLER_NOT_FOUND'));
}
require_once(__DIR__ . DS . 'controllers' . DS . $controllerName . '.php');

// Add some submenu items
\Submenu::addEntry(
	\Lang::txt('PF_CONTENT'),
	\Route::url('index.php?option=com_pressforward&controller=items'),
	($controllerName == 'items' && $task != 'nominated')
);
\Submenu::addEntry(
	\Lang::txt('PF_NOMINATED'),
	\Route::url('index.php?option=com_pressforward&controller=items&task=nominated'),
	($controllerName == 'items' && $task == 'nominated')
);
\Submenu::addEntry(
	\Lang::txt('PF_POSTS'),
	\Route::url('index.php?option=com_pressforward&controller=posts'),
	($controllerName == 'posts')
);
\Submenu::addEntry(
	\Lang::txt('PF_FEEDS'),
	\Route::url('index.php?option=com_pressforward&controller=feeds'),
	($controllerName == 'feeds')
);
\Submenu::addEntry(
	\Lang::txt('PF_TOOLS'),
	\Route::url('index.php?option=com_pressforward&controller=tools'),
	($controllerName == 'tools')
);
\Submenu::addEntry(
	\Lang::txt('PF_FOLDERS'),
	\Route::url('index.php?option=com_pressforward&controller=folders'),
	($controllerName == 'folders')
);
\Submenu::addEntry(
	\Lang::txt('PF_LOGS'),
	\Route::url('index.php?option=com_pressforward&controller=logs'),
	($controllerName == 'logs')
);

// Build the class name
$controllerName = __NAMESPACE__ . '\\Controllers\\' . ucfirst(strtolower($controllerName));

// Instantiate controller
$controller = new $controllerName();

// This detects the incoming task and executes it if it can. If no task 
// is set, it will execute a default task of "display" which maps to a 
// method of "displayTask" in the controller.
$controller->execute();
