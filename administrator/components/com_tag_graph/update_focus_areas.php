<?php

$dbh =& JFactory::getDBO();
$dbh->setQuery('SELECT id, tag_id, mandatory_depth, multiple_depth FROM #__focus_areas');

$existing = $dbh->loadAssocList('id');

// rebuilding from the form data is easier than finding and resolving differences
$dbh->execute('TRUNCATE TABLE #__focus_area_resource_type_rel');
foreach ($existing as $id=>$fa)
{
	// no form field == deleted
	if (!isset($_POST['name-'.$id]))
	{
		$dbh->execute('DELETE FROM #__focus_areas WHERE id = '.$id);
		continue;
	}
	$new_tag = get_tag($dbh, $_POST['name-'.$id], false);
	$dbh->execute('UPDATE #__focus_areas SET 
		mandatory_depth = '.($_POST['mandatory-'.$id] === 'mandatory' ? 1 : ($_POST['mandatory-'.$id] === 'depth' ? (int)$_POST['mandatory-depth-'.$id] : 'NULL')).', 
		multiple_depth = '. ($_POST['multiple-'.$id]  === 'multiple'  ? 1 : ($_POST['multiple-'.$id]  === 'depth' ? (int)$_POST['multiple-depth-'.$id]  : 'NULL')).', 
		tag_id = '.$new_tag['id'].' 
		WHERE id = '.$id
	);
	foreach ($_POST['types-'.$id] as $type_id)
		$dbh->execute('INSERT INTO #__focus_area_resource_type_rel(focus_area_id, resource_type_id) VALUES ('.$id.', '.((int)$type_id).')');
}

for ($idx = 1; isset($_POST['name-new-'.$idx]); ++$idx)
{
	if (!trim($_POST['name-new-'.$idx]))
		continue;
	$tag = get_tag($dbh, $_POST['name-new-'.$idx], false);

	$dbh->execute('INSERT INTO #__focus_areas(mandatory_depth, multiple_depth, tag_id) VALUES ('.
		($_POST['mandatory-new-'.$idx] === 'mandatory' ? 1 : ($_POST['mandatory-new-'.$idx] === 'depth' ? (int)$_POST['mandatory-depth-new-'.$idx] : 'NULL')).', '.
		($_POST['multiple-new-'.$idx]  === 'multiple'  ? 1 : ($_POST['multiple-new-'.$idx]  === 'depth' ? (int)$_POST['multiple-depth-new-'.$idx]  : 'NULL')).', '.
		$tag['id'].')' 
	);
	$id = $dbh->insertid();
	foreach ($_POST['types-new-'.$idx] as $type_id)
		$dbh->execute('INSERT INTO #__focus_area_resource_type_rel(focus_area_id, resource_type_id) VALUES ('.$id.', '.((int)$type_id).')');
}

require 'meta.php';
