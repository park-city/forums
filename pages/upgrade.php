<?php
if(!defined('DINNER')) die();
if($loguser['powerlevel'] < 3)
	Kill(__("Access denied."));

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry(__("Upgrade"), "upgrade"));
makeBreadcrumbs($crumbs);

Upgrade();

?>

