<?php
if(!defined('DINNER')) die();

//$debugMode = 1;

$dbserv = 'localhost';
$dbuser = '';
$dbpass = '';
$dbname = '';
$dbpref = '';

$salt = "";

define('BOARD_NAME', 'Park City');
define('MAIN_PAGE', 'board');
define('DEFAULT_THEME', 'parkcity');
define('META_DESC', 'Park City is an internet community for folks who pine for community in the isolated modern netscape. we\'re a group of friends, creative partners, and limitless imagination.');
define('META_TAGS', '');
define('RSS_TITLE', 'Park City RSS');
define('RSS_DESC', 'It seemingly works!');

// Settings
define('TAGS_DIRECTION', 'Right');
define('ALWAYS_MINIPIC',  false); // Always show users' minipics!
define('DATE_FORM', 'm-d-y'); // Default date format
define('TIME_FORM', 'g:i A'); // Default time format

// Power levels
define('DEFAULT_GROUP', 0);
define('ROOT_GROUP', 4);
define('BANNED_GROUP', -1);

// Forums
define('NEWS_FORUM',  7); // Set to 0 to disable.
define('TRASH_FORUM', 5);

// Features
define('ENABLE_SYNDROMES',true);
define('ENABLE_UPLOADER', true);
define('ENABLE_WIKI',     true);

// dumb compatibility no touchy

$config = array();

$config['boardname'] = BOARD_NAME;
$config['title'] = BOARD_NAME;
$config['theme'] = DEFAULT_THEME;
$config['mainpage'] = MAIN_PAGE;
$config['metadesc'] = META_DESC;
$config['metatags'] = META_TAGS;
$config['newsforum'] = NEWS_FORUM;
$config['trashforum'] = TRASH_FORUM;
$config['homecontent'] = HOME_CONTENT;
$config['homefooter'] = HOME_FOOTER;
$config['tagsDirection'] = TAGS_DIRECTION;
$config['enableUploader'] = ENABLE_UPLOADER;
$config['enableWiki'] = ENABLE_WIKI;

$mainPage = MAIN_PAGE;

?>