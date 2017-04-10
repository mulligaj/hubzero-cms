<?php
// No direct access
defined('_HZEXEC_') or die();

$path = Component::path($this->option) . '/pressforward/parts/pf-tools/tab-reset-refresh.tpl.php';
if (file_exists($path))
{
	include_once $path;
}