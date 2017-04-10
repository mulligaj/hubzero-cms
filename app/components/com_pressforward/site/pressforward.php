<?php
namespace Components\Pressforward\Site;

// WordPress compatibility functions
require_once(dirname(__DIR__) . DS . 'helpers' . DS . 'wordpress.php');

// PressForward helpers
require_once(dirname(__DIR__) . DS . 'helpers' . DS . 'pressforward.php');

// Models
require_once(dirname(__DIR__) . DS . 'models' . DS . 'post.php');

// Make extra sure that controller exists
$controllerName = \Request::getCmd('controller', \Request::getCmd('view', 'posts'));
if (\Request::getWord('pf') == 'opml')
{
	$controllerName = 'feeds';
}
if (!file_exists(__DIR__ . DS . 'controllers' . DS . $controllerName . '.php'))
{
	$controllerName = 'posts';
}
require_once(__DIR__ . DS . 'controllers' . DS . $controllerName . '.php');
$controllerName = __NAMESPACE__ . '\\Controllers\\' . ucfirst(strtolower($controllerName));

// Instantiate controller
$controller = new $controllerName();
$controller->execute();
