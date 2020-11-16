<?php
if(!defined('DINNER')) die();
$title = __("Latest posts");

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Latest posts", "lastposts"));
makeBreadcrumbs($crumbs);

doLastPosts(false, 200);

