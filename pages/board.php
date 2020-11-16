<?php
if(!defined('DINNER')) die();
$title = 'Forums';

if($loguserid && isset($_GET['action']) && $_GET['action'] == "markallread")
{
	Query("REPLACE INTO {threadsread} (id,thread,date) SELECT {0}, {threads}.id, {1} FROM {threads}", $loguserid, time());
	redirectAction("board");
}

$links = new PipeMenu();
if ($loguserid)
	$links->add(new PipeMenuLinkEntry("Mark all forums read", "board", 0, "action=markallread", "ok"));

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Forums", "board"));
makeBreadcrumbs($crumbs);

makeLinks($links);
makeBreadcrumbs(new PipeMenu());

printRefreshCode();
makeForumListing(0);

?>
