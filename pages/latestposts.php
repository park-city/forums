<?php

$title = 'Latest posts';

$crumbo = array($title => actionLink('latestposts'));
$layout_crumbs = MakeCrumbs($crumbo);

doLastPosts(false, 200);

