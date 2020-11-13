<?php
if(!defined('DINNER')) die();

if($loguserid && isset($_GET['action']) && $_GET['action'] == "markallread")
{
	Query("REPLACE INTO {threadsread} (id,thread,date) SELECT {0}, {threads}.id, {1} FROM {threads}", $loguserid, time());
	redirectAction("board");
}

$links = array();
if ($loguserid)
	$links[] = actionLinkTag("Mark all forums read", "board", 0, "action=markallread", '', 'ok');

if(MAIN_PAGE != 'board')
{
	$title = 'Forums';
	$crumbs = array($title => actionLink("board"));
}

makeCrumbs($crumbs, $links);

printRefreshCode();
makeForumListing(0);

?>