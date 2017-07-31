<?php
// Declare the namespace.
namespace Components\Contracts\Site;

// The "Show" model pulls in all other models used throughout the
// component.
//
// NOTE: We're using the __DIR__ constant. This is a constant
// automatically defined in PHP 5.3+. Its value is the absolute
// path up to the directory that this file is in. Using this 
// instead of a fully, manually delcared path keeps our 
// code a little more flexible, allowing us to move files or even
// entire components with fewer changes.
include_once dirname(__DIR__) . DS . 'models' . DS . 'contract.php';

include_once __DIR__ . DS . 'controllers' . DS . 'contracts.php';

$controllerName = __NAMESPACE__ . '\\Controllers\\' . 'Contracts';

// Instantiate the controller
$component = new $controllerName();

// This detects the incoming task and executes it if it can. If no task 
// is set, it will execute a default task of "display" which maps to a 
// method of "displayTask" in the controller.
$component->execute();
