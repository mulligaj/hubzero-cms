<?php
$tag = isset($_GET['tag']) ? $_GET['tag'] : 0;
$dbh =& JFactory::getDBO();
$nodes = array();
$links = array();
$id = null;
$descr = '';

$rv = get_tag($dbh, $tag);
$nodes[] = array('id' => $rv['id'], 'tag' => $rv['tag'], 'raw_tag' => $rv['raw_tag']);
if (!$rv['new'])
{
	$dbh->setQuery(
		'SELECT t.id, t.tag, t.raw_tag, count(t.id) AS count FROM #__tags_object to1
		LEFT JOIN #__tags_object to2 ON to2.tbl = to1.tbl AND to2.objectid = to1.objectid
		INNER JOIN #__tags t ON t.id = to2.tagid
		WHERE to1.tagid = '.$rv['id'].' AND t.id != '.$rv['id'].' AND to1.label IS NULL   
		GROUP BY t.id, t.tag, t.raw_tag
		ORDER BY count DESC
		LIMIT 20'
	);
	$t_idx = 0;
	$idx_map = array($rv['id'] => $t_idx);
	$follow = array();
	$max_weight = null;
	foreach ($dbh->loadAssocList() as $idx=>$row)
	{
		if (is_null($max_weight))
			$max_weight = $row['count'];

		if ($row['count'] == 1)
			break;
		$nodes[] = $row;
		$idx_map[$row['id']] = ++$t_idx;
		$links[] = array('source' => $idx + 1, 'target' => 0, 'value' => $row['count']);
		$follow[$idx + 1] = $row['id'];
	}
	foreach ($follow as $idx=>$tag_id)
	{
		$dbh->setQuery(
			'SELECT t.id, t.tag, t.raw_tag, count(t.id) AS count FROM #__tags_object to1
			LEFT JOIN #__tags_object to2 ON to2.tbl = to1.tbl AND to2.objectid = to1.objectid
			INNER JOIN #__tags t ON t.id = to2.tagid
			WHERE to1.tagid = '.$tag_id.' AND t.id != '.$tag_id.' AND to1.label IS NULL 
			GROUP BY t.id, t.tag, t.raw_tag
			ORDER BY count DESC
			LIMIT 10'
		);
		foreach ($dbh->loadAssocList() as $inner_idx=>$row)
		{
			$max_weight = max($max_weight, $row['count']);
			if ($row['count'] == 1)
				break;
			if (isset($idx_map[$row['id']]))
				$target_idx = $idx_map[$row['id']];
			else
			{
				$nodes[] = $row;
				$target_idx = ++$t_idx;
				$idx_map[$row['id']] = $t_idx;
			}
			$links[] = array('source' => $idx, 'target' => $target_idx, 'value' => $row['count']);
		}
		foreach ($links as &$link)
			$link['value'] /= $max_weight;
	}
}
header('Content-type: application/octet-stream');
$rv['nodes'] = $nodes;
$rv['links'] = $links;
echo json_encode($rv);
exit();

