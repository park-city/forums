<?php
if(!defined('DINNER')) die();

header('Cache-control: no-cache, private');
header('X-Frame-Options: DENY');

error_reporting(E_ALL ^ E_NOTICE | E_STRICT);

define('BOARD_ROOT', dirname(__DIR__).'/');
define('DATA_DIR', BOARD_ROOT.'data/');

$boardroot = preg_replace('{/[^/]*$}', '/', $_SERVER['SCRIPT_NAME']);

define('URL_ROOT', $boardroot);
define('DATA_URL', URL_ROOT.'data/');

$dataDir = "data/";
$dataUrl = $boardroot."data/";

if (get_magic_quotes_gpc())
{
	function AutoDeslash($val)
	{
		if (is_array($val))
			return array_map('AutoDeslash', $val);
		else if (is_string($val))
			return stripslashes($val);
		else
			return $val;
	}

	$_REQUEST = array_map('AutoDeslash', $_REQUEST);
	$_GET = array_map('AutoDeslash', $_GET);
	$_POST = array_map('AutoDeslash', $_POST);
	$_COOKIE = array_map('AutoDeslash', $_COOKIE);
}

function usectime()
{
	$t = gettimeofday();
	return $t['sec'] + ($t['usec'] / 1000000);
}

if (!function_exists('password_hash'))
	require_once('password.php');

include(__DIR__."/config.php");
include(__DIR__."/debug.php");
include(__DIR__."/mysql.php");

if(!sqlConnect())
	die("Can't connect to the database!");
if(!fetch(query("SHOW TABLES LIKE '{misc}'")))
	die("Can't show tables like misc!");

include(__DIR__."/mysqlfunctions.php");
include(__DIR__."/settings.php");
Settings::load();
Settings::checkPlugin("main");
include(__DIR__."/feedback.php");
include(__DIR__."/language.php");
include(__DIR__."/write.php");
include(__DIR__."/snippets.php");
include(__DIR__."/links.php");

class KillException extends Exception { }
date_default_timezone_set("GMT");
$timeStart = usectime();

$title = "";

$thisURL = $_SERVER['SCRIPT_NAME'];
if($q = $_SERVER['QUERY_STRING'])
	$thisURL .= "?$q";

include(__DIR__."/browsers.php");
include(__DIR__."/pluginsystem.php");
loadFieldLists();
include(__DIR__."/loguser.php");
include(__DIR__."/permissions.php");
include(__DIR__."/ranksets.php");
include(__DIR__."/post.php");
include(__DIR__."/log.php");
include(__DIR__."/onlineusers.php");
include(__DIR__."/htmlfilter.php");
include(__DIR__."/smilies.php");

$theme = $loguser['theme'];

include(__DIR__."/layout.php");
include(__DIR__."/pipemenubuilder.php");
include(__DIR__."/lists.php");