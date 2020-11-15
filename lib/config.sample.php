<?php
if(!defined('DINNER')) die();

//$debugMode = 1;

$dbserv = 'localhost';
$dbuser = '';
$dbpass = '';
$dbname = '';
$dbpref = '';

$salt = "";

define('DEFAULT_THEME', 'parkcity');

// Power levels, avoid touching
define('BANNED_GROUP', -1);
define('DEFAULT_GROUP', 0);
define('LMOD_GROUP', 1);
define('MOD_GROUP', 2);
define('ADMIN_GROUP', 3);
define('ROOT_GROUP', 4);

?>