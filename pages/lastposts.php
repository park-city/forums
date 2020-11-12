<?php

$title = __("Feed");

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Feed", "lastposts"));
makeBreadcrumbs($crumbs);

doLastPosts(false, 200);

