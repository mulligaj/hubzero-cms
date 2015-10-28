<?php

$suggestions = array();

if (isset($_GET['term']))
{
	$dbh =& JFactory::getDBO();
	$dbh->setQuery('SELECT raw_tag FROM #__tags WHERE MATCH(raw_tag) AGAINST (\'*'.mysql_real_escape_string($_GET['term']).'*\' IN BOOLEAN MODE)');
	$later = array();
	foreach ($dbh->loadResultArray() as $tag)
	{
		$tag = stripslashes($tag);
		if (strpos($tag, $_GET['term']) === 0)
			$suggestions[] = $tag;
		else
			$later[] = $tag;
	}
	sort($suggestions);
	sort($later);
	$suggestions = array_merge($suggestions, $later);
	if (isset($_GET['limit']))
		$suggestions = array_slice($suggestions, 0, (int)$_GET['limit']);
	$re = '/('.preg_quote($_GET['text']).')/i';
#	foreach ($suggestions as &$tag)
#		$tag = preg_replace($re, '<span class="highlight">$1</span>', $tag);
}
header('Content-type: application/octet-stream'); 
echo json_encode($suggestions);
exit();
