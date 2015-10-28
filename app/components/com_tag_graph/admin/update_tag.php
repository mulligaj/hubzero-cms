<?php
if (isset($_POST['tag']) && ($tid = (int)$_POST['tag']))
{
	$dbh =& JFactory::getDBO();
	$dbh->execute('UPDATE #__tags SET description = '.$dbh->quote($_POST['description']).' WHERE id = '.$tid);
	$tag = get_tag($dbh, $tid);
	$preload = $tag['raw_tag'];
	$normalize = create_function('$a', 'return preg_replace(\'/[^-_a-z0-9]/\', \'\', strtolower($a));'); 
	// reconcile post data with what we already know about a tag's relationships
	foreach (array(
		'labels'   => array(
			'INSERT INTO #__tags_object(tbl, label, tagid, objectid) VALUES (\'tags\', \'label\', %d, %d)',
			'DELETE FROM #__tags_object WHERE tbl = \'tags\' AND label = \'label\' AND tagid = %d AND objectid = %d'
		),
		'labeled'  => array(
			'INSERT INTO #__tags_object(tbl, label, objectid, tagid) VALUES (\'tags\', \'label\', %d, %d)',
			'DELETE FROM #__tags_object WHERE tbl = \'tags\' AND label = \'label\' AND objectid = %d AND tagid = %d'
		),
		'parents'  => array(
			'INSERT INTO #__tags_object(tbl, label, objectid, tagid) VALUES (\'tags\', \'parent\', %d, %d)',
			'DELETE FROM #__tags_object WHERE tbl = \'tags\' AND label = \'parent\' AND objectid = %d AND tagid = %d'
		),
		'children' => array(
			'INSERT INTO #__tags_object(tbl, label, tagid, objectid) VALUES (\'tags\', \'parent\', %d, %d)',
			'DELETE FROM #__tags_object WHERE tbl = \'tags\' AND label = \'parent\' AND tagid = %d AND objectid = %d'
		)) as $type=>$sql)
	{
		$ex = array_flip(array_map($normalize, $tag[$type]));
		if (isset($_POST[$type]))
			foreach ($_POST[$type] as $n_tag)
			{
				$norm_n_tag = preg_replace('/[^-_a-z0-9]/', '', strtolower($n_tag));

				// co-occurring tags neither need to be added nor deleted, just remove them from the pool and carry on
				if (isset($ex[$norm_n_tag]))
					unset($ex[$norm_n_tag]);
				// otherwise we need to add a new relationship
				else
				{
					$n_tag = get_tag($dbh, $n_tag, false);
					$dbh->execute(sprintf($sql[0], $tid, $n_tag['id']));
				}
			}
		// any tags that have not be unset were deleted on the form, so we need to reflect that in the database
		foreach ($ex as $e_tag=>$_v)
		{
			$e_tag = get_tag($dbh, $e_tag, false);
			$dbh->execute(sprintf($sql[1], $tid, $e_tag['id']));
		}
	}
}

require 'graph.php';
