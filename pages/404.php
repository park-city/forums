<?php
if(!defined('DINNER')) die();

header('HTTP/1.1 404 Not Found');
header('Status: 404 Not Found');

$title = "404 Not Found";

Kill('The page you were looking for was not found.<br><br>
<a href="'.actionLink(MAIN_PAGE).'">Return to index</a>', '404 Not Found');

?>