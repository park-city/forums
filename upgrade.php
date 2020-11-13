<?php
define('DINNER', 1);
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
require(__DIR__.'/lib/config.php');
require(__DIR__.'/lib/debug.php');
require(__DIR__.'/lib/mysql.php');
require(__DIR__.'/lib/mysqlfunctions.php');
Upgrade();
echo "Done!";
?>