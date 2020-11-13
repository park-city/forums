<?php

if($loguser['powerlevel'] < 3)
	Kill('Access denied.');

$title = 'Update';
$crumbo = array('Admin' => actionLink('admin'), $title => actionLink('update'));
$layout_crumbs = MakeCrumbs($crumbo);

?>
<table class="outline margin"><tr><td><?php Upgrade(); ?></td></tr></table>