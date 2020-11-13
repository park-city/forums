<?php
if(!defined('DINNER')) die();

CheckPermission('admin.viewadminpanel');

$title = __('Admin');
makeCrumbs(array($title => actionLink('admin')));

?>
<table class="outline margin mw700 mcenter">
	<tr><th>Admin</th></tr>
	<tr><td>
		<ul>
<?php
if ($loguser['root'])
	echo '<li><a href="/?page=recalc">Recalculate statistics</a></li>';
if (HasPermission('admin.manageipbans'))
	echo '<li><a href="/?page=ipbans">Manage IP bans</a></li>';
if (HasPermission('admin.editforums'))
	echo '<li><a href="/?page=editfora">Manage forums</a></li>';
if (HasPermission('admin.editsmilies'))
	echo '<li><a href="/?page=editsmilies">Edit smilies</a></li>';
if ($loguser['root'])
	echo '<li><a href="/?page=optimize">Optimize tables</a></li>';
if (HasPermission('admin.viewlog'))
	echo '<li><a href="/?page=log">View log</a></li>';
if (HasPermission('admin.ipsearch'))
	echo '<li><a href="/?page=reregs">Rereg radar</a></li>';
?>
		</ul>
	</td></tr>
</table>