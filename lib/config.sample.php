<?php

$config = array();

$dbserv = 'localhost';
$dbuser = '';
$dbpass = '';
$dbname = '';
$dbpref = '';

$salt = "";

$config['title'] = "Park City";
$config['theme'] = "parkcity";
$config['mainpage'] = 'board';

define('RSS_TITLE', $config['title']);
define('RSS_DESC', 'It kinda works!');

define('BANNED_GROUP', -1);
define('ALWAYS_MINIPIC', 0);

?>