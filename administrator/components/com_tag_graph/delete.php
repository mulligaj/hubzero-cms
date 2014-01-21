<?php

if (isset($_POST['tag']) && ($tid = (int)$_POST['tag']))
{
	$dbh =& JFactory::getDBO();
	$tag = get_tag($dbh, $tid, false);
	if (isset($_POST['really']) && $_POST['really'] === 'on')
	{
		if (isset($_POST['do_merge']) && $_POST['do_merge'] === 'on')
		{
			if (!isset($_POST['merge_tag']))
				JError::raiseError(404, 'Merge target not found');
			$dbh->setQuery('SELECT id FROM #__tags WHERE tag = \''.preg_replace('/[^-_a-z0-9]/', '', strtolower(trim($_POST['merge_tag']))).'\'');
			if (!($merge_id = $dbh->loadResult()))
				JError::raiseError(404, 'Merge target not found');
			$dbh->execute('UPDATE #__tags_object SET tagid = '.$merge_id.' WHERE tagid = '.$tid);
		}
		else
			$dbh->execute('DELETE FROM #__tags_object WHERE tagid = '.$tid);
		$dbh->execute('DELETE FROM #__tags WHERE id = '.$tid);
	}
	else
		$preload = $tag['raw_tag'];
}
require 'graph.php';
