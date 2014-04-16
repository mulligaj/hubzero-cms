<?php
defined('JPATH_BASE') or die(); 

/*
[!] - (zooley) Added NotFoundError class because PHP was throwing 
	fatal error that the class was not found (irony?).
*/
if (!class_exists('NotFoundError'))
{
	class NotFoundError extends InvalidArgumentException
	{
	}
}

$base = isset($_SERVER['SCRIPT_URL']) ? $_SERVER['SCRIPT_URL'] : $_SERVER['REDIRECT_SCRIPT_URL']; 
$basePath = preg_replace('#^'.preg_quote(JPATH_BASE).'#', '', dirname(__FILE__));

$doc = JFactory::getDocument();
//$doc->addScript('/jquery.js');
$doc->addScript($basePath.'/resources/hubgraph.js');
$doc->addStyleSheet($basePath.'/resources/hubgraph.css');

$path = JFactory::getApplication()->getPathway();
$path->addItem('Search', $base);

function hgView($view, $args = array()) {
	header('Content-type: text/json');
	echo HubgraphClient::execView($view, array_merge($_GET, $args));
	exit();
}

function h($str) {
	return htmlentities($str);
}

function a($str) {
	return str_replace('"', '&quot;', $str);
}

function assertSuperAdmin() {
	if (JFactory::getUser()->usertype != 'Super Administrator') {
		JError::raiseError(405, 'Forbidden');
	}
}

function createNonce() {
//	set_include_path(get_include_path() . PATH_SEPARATOR . JPATH_BASE.'/libraries/openid');
//	require_once 'Auth/OpenID/Nonce.php';
	$now = time();
	$_SESSION['hg_nonce'] = sha1($now); //Auth_OpenID_mkNonce($now);
	Db::execute('INSERT INTO jos_oauthp_nonces(created, nonce, stamp) VALUES (CURRENT_TIMESTAMP, ?, 0)', array($_SESSION['hg_nonce']));
	return $_SESSION['hg_nonce'];
}

function consumeNonce($form) {
	$now = time();
	if (!isset($form['nonce']) || $form['nonce'] != $_SESSION['hg_nonce']
		|| !preg_match('/^\d\d\d\d-\d\d-\d\dT\d\d:\d\d:\d\dZ/', $form['nonce'], $ma)
		|| !($timestamp = strtotime($ma[0]))
		|| $timestamp > $now
		|| $timestamp < $now - 60 * 60
		|| Db::scalarQuery('SELECT stamp FROM jos_oauthp_nonces WHERE nonce = ?', array($form['nonce']))) {
//		JError::raiseError(405, 'Bad token');
	}
	Db::execute('UPDATE jos_oauthp_nonces SET stamp = 1 WHERE nonce = ?', array($form['nonce']));
	unset($_SESSION['hg_nonce']);
}

require_once 'client.php';
require_once 'request.php';
$req = new HubgraphRequest($_GET);
$conf = HubgraphConfiguration::instance();
$perPage = 40;

try {
	switch (!defined('HG_INLINE') && isset($_REQUEST['task']) ? $_REQUEST['task'] : 'index') {
		case 'complete':
			hgView('complete', array('limit' => 20, 'threshold' => 3, 'tagLimit' => 100));
		case 'getRelated':
			hgView('related', $req->getTransportCriteria(array('limit' => 5, 'domain' => $_GET['domain'], 'id' => $_GET['id'])));
		case 'index':
			$results = $req->anyCriteria() 
				? json_decode(HubgraphClient::execView('search', $req->getTransportCriteria(array('limit' => $perPage))), TRUE) 
				: NULL;
			require 'views/index.html.php';
		break;
		case 'update':
			$results = $req->anyCriteria() 
				? json_decode(HubgraphClient::execView('search', $req->getTransportCriteria(array('limit' => $perPage))), TRUE) 
				: NULL;
			define('HG_AJAX', 1);
			require 'views/index.html.php';
			exit();
		break;
		case 'settings':
			assertSuperAdmin();
			require 'views/settings.html.php';
		break;
		case 'updateSettings':
			assertSuperAdmin();
			consumeNonce($_POST);
			$conf->bind($_POST)->save();
			header('Location: /hubgraph?task=settings');
			exit();	
		break;
		default:
			throw new NotFoundError('no such task');
	}
}
catch (Exception $ex) {
	if (!defined('HG_INLINE')) {
		header('Location: '.JRoute::_('index.php?option=com_search' . (isset($_GET['terms']) ? '&terms='.$_GET['terms'] : '')));
		exit();
	}
}
