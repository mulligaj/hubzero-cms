<?php
/**
 * @package     hubzero.cms.site
 * @subpackage  com_dataviewer
 *
 * @author      Sudheera R. Fernando srf@xconsole.org
 * @copyright   Copyright 2010-2015 HUBzero Foundation, LLC.
 * @license     http://opensource.org/licenses/MIT MIT or later; see LICENSE.txt
 */

defined('_HZEXEC_') or die();

jimport('joomla.application.component.helper');


require_once(JPATH_COMPONENT . DS . 'controller.php');
require_once(JPATH_COMPONENT . DS . 'dv_config.php');

require_once(JPATH_COMPONENT . DS . 'lib' . DS. 'html.php');

require_once(JPATH_COMPONENT . DS . 'lib/db.php');
require_once(JPATH_COMPONENT . DS . 'lib/dl.php');

$document = App::get('document');

global $html_path, $com_name, $dv_conf;

$html_path = str_replace(JPATH_BASE, '', JPATH_COMPONENT) . '/html';
$com_name = str_replace(PATH_CORE.'/components/', '', dirname(__DIR__));
$com_name = str_replace('com_', '' , $com_name);
$com_name = trim($com_name, DS);
$dv_conf['settings']['com_name'] = $com_name;

controller();
return;
?>
