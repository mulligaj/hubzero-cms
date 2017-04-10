<?php
// No direct access
defined('_HZEXEC_') or die();

$path = Component::path($this->option) . '/pressforward/parts/nominate-this.tpl.php';
if (file_exists($path))
{
	$context = 'as_paragraph';

	include_once $path;
}