<?php
$tag = isset($_GET['tag']) ? $_GET['tag'] : 0;
$dbh =& JFactory::getDBO();
$nodes = array();
$links = array();
$id = null;
$descr = '';

$rv = get_tag($dbh, $tag) ;
$nodes[] = array('id' => $rv['id'], 'tag' => $rv['tag'], 'raw_tag' => $rv['raw_tag']);
if (!$rv['new'])
{
	$t_idx = 0;
	$idx_map = array($tag['id'] => $t_idx);
	$dbh->setQuery(
		'SELECT DISTINCT t.id, t.tag, t.raw_tag, to1.label
		FROM #__tags_object to1
		INNER JOIN #__tags t ON t.id = to1.tagid
		WHERE to1.label IN (\'parent\', \'label\') AND to1.tbl = \'tags\' AND to1.objectid = '.$rv['id'].' 
		UNION
		SELECT DISTINCT t.id, t.tag, t.raw_tag, to1.label
		FROM #__tags_object to1
		INNER JOIN #__tags t ON t.id = to1.objectid
		WHERE to1.label = \'label\' AND to1.tbl = \'tags\' AND to1.tagid = '.$rv['id']
	);
	foreach ($dbh->loadAssocList() as $row)
	{
		$idx_map[$row['id']] = ++$t_idx;
		$row['type'] = $row['label'];
		$nodes[] = $row;
		$links[] = array('source' => $t_idx, 'target' => 0);
	}

	$dbh->setQuery(
		'SELECT DISTINCT t.id, t.tag, t.raw_tag
		FROM #__tags_object to1
		INNER JOIN #__tags t ON t.id = to1.objectid
		WHERE to1.label = \'parent\' AND to1.tbl = \'tags\' AND to1.tagid = '.$rv['id']
	);
	foreach ($dbh->loadAssocList() as $row)
	{
		$idx_map[$row['id']] = ++$t_idx;
		$row['type'] = 'child';
		$nodes[] = $row;
		$links[] = array('source' => $t_idx, 'target' => 0);
		$dbh->setQuery(
			'SELECT DISTINCT t.id, t.tag, t.raw_tag
			FROM #__tags_object to1
			INNER JOIN #__tags t ON t.id = to1.objectid
			WHERE to1.label = \'parent\' AND to1.tbl = \'tags\' AND to1.tagid = '.$row['id']
		);
		foreach ($dbh->loadAssocList() as $inner_row)
		{
			$idx_map[$inner_row['id']] = ++$t_idx;
			$inner_row['type'] = 'child';
			$nodes[] = $inner_row;
			$links[] = array('source' => $t_idx, 'target' => $idx_map[$row['id']]);
			$dbh->setQuery(
				'SELECT DISTINCT t.id, t.tag, t.raw_tag
				FROM #__tags_object to1
				INNER JOIN #__tags t ON t.id = to1.objectid
				WHERE to1.label = \'parent\' AND to1.tbl = \'tags\' AND to1.tagid = '.$inner_row['id']
			);
			foreach ($dbh->loadAssocList() as $inner_inner_row)
			{
				$idx_map[$inner_inner_row['id']] = ++$t_idx;
				$inner_inner_row['type'] = 'child';
				$nodes[] = $inner_inner_row;
				$links[] = array('source' => $t_idx, 'target' => $idx_map[$inner_row['id']]);
			}
		}
	}
}

header('Content-type: application/octet-stream');
$rv['nodes'] = $nodes;
$rv['links'] = $links;
echo json_encode($rv);
exit();

