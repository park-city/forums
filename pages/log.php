<?php

if($loguser['powerlevel'] < 3)
	Kill('Access denied.');

$title = 'Log';

$crumbo = array();
$crumbo['Admin'] = actionLink('admin');
$crumbo[$title] = '';
$layout_crumbs = MakeCrumbs($crumbo);

doLogList("1");
