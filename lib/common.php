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
	
$dataDir = "data/";
$dataUrl = $boardroot."data/";

include("config.php");
include("debug.php");
include("mysql.php");

if(!sqlConnect())
	die("Can't connect to the database!");
if(!fetch(query("SHOW TABLES LIKE '{misc}'")))
	die("Can't show tables like misc!");

include("mysqlfunctions.php");
include("feedback.php");
include("language.php");
include("write.php");
include("snippets.php");
include("links.php");

class KillException extends Exception { }
date_default_timezone_set("GMT");
$timeStart = usectime();

$title = "";

$thisURL = $_SERVER['SCRIPT_NAME'];
if($q = $_SERVER['QUERY_STRING'])
	$thisURL .= "?$q";

include("browsers.php");
include("pluginsystem.php");
loadFieldLists();
include("loguser.php");
include("permissions.php");
include("ranksets.php");
include("post.php");
include("log.php");
include("onlineusers.php");
include("htmlfilter.php");
include("smilies.php");

$theme = $loguser['theme'];

include('layout.php');
include("pipemenubuilder.php");
include("lists.php");