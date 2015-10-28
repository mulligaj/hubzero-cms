<?php

function get_tag($dbh, $tag_str, $detailed = true)
{
	$dbh->setQuery(
		is_int($tag_str) 
			? 'SELECT DISTINCT t.id, tag, raw_tag, description, COUNT(to1.id) AS count FROM #__tags t LEFT JOIN #__tags_object to1 ON to1.tagid = t.id WHERE t.id = '.$tag_str.' GROUP BY t.id, tag, raw_tag, description'
			: 'SELECT DISTINCT t.id, tag, raw_tag, description, COUNT(to1.id) AS count FROM #__tags t LEFT JOIN #__tags_object to1 ON to1.tagid = t.id WHERE tag = '.$dbh->quote($tag_str).' OR raw_tag = '.$dbh->quote($tag_str).' GROUP BY t.id, tag, raw_tag, description'
	);
	if (($tag = $dbh->loadAssoc()))
	{
		$rv = array(
			'id' => $tag['id'],
			'tag' => $tag['tag'],
			'count' => $tag['count'],
			'raw_tag' => $tag['raw_tag'],
			'description' => $tag['description'],
			'new' => false 
		);
		if (!$detailed)
			return $rv;

		$dbh->setQuery(
			'SELECT DISTINCT t.raw_tag
			FROM #__tags_object to1  
			INNER JOIN #__tags t ON t.id = to1.tagid 
			WHERE to1.tbl = \'tags\' AND to1.label = \'label\' AND to1.objectid = '.$tag['id']
		);
		$rv['labeled'] = $dbh->loadResultArray();

		$dbh->setQuery(
			'SELECT DISTINCT t.raw_tag
			FROM #__tags_object to1  
			INNER JOIN #__tags t ON t.id = to1.objectid 
			WHERE to1.tbl = \'tags\' AND to1.label = \'label\' AND to1.tagid = '.$tag['id']
		);
		$rv['labels'] = $dbh->loadResultArray();
		
		$dbh->setQuery(
			'SELECT DISTINCT t.raw_tag
			FROM #__tags_object to1  
			INNER JOIN #__tags t ON t.id = to1.tagid 
			WHERE to1.tbl = \'tags\' AND to1.label = \'parent\' AND to1.objectid = '.$tag['id']
		);
		$rv['parents'] = $dbh->loadResultArray();

		$dbh->setQuery(
			'SELECT DISTINCT t.raw_tag
			FROM #__tags_object to1  
			INNER JOIN #__tags t ON t.id = to1.objectid 
			WHERE to1.tbl = \'tags\' AND to1.label = \'parent\' AND to1.tagid = '.$tag['id']
		);
		$rv['children'] = $dbh->loadResultArray();
		$rv['description'] = stripslashes($rv['description']);

		return $rv;
	}
	$norm_tag = preg_replace('/[^-_a-z0-9]/', '', strtolower($tag_str));
	$dbh->execute('INSERT INTO #__tags(tag, raw_tag) VALUES(\''.$norm_tag.'\', '.$dbh->quote($tag_str).')');
	$id = $dbh->insertid();
	return array(
		'id' => $id,
		'tag' => $norm_tag,
		'raw_tag' => $tag_str,
		'description' => '',
		'new' => true,
		'labeled' => array(),
		'labels' => array(),
		'parents' => array(),
		'children' => array()
	);
}

$preload = null;
switch (isset($_GET['task']) ? $_GET['task'] : (isset($_POST['task']) ? $_POST['task'] : 'index'))
{
	case 'implicit':           require 'implicit-json.php';      break;
	case 'hierarchy':          require 'hierarchy-json.php';     break;
	case 'suggest':            require 'suggest-json.php';       break;
	case 'update':             require 'update_tag.php';         break;
	case 'meta':               require 'meta.php';               break;
	case 'delete':             require 'delete.php';             break;
	case 'update_focus_areas': require 'update_focus_areas.php'; break;
	default:                   require 'graph.php';
}
