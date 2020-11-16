<?php
if(!defined('DINNER')) die();
if($loguser['powerlevel'] < 3) header('Location: https://www.youtube.com/watch?v=DNm5eb97Y0g');

$adminLinks = '';

$adminLinks .= actionLinkTagItem('Edit settings', 'editsettings');
$adminLinks .= actionLinkTagItem('Edit forums', 'editfora');
$adminLinks .= actionLinkTagItem('Edit smilies', 'editsmilies');
$adminLinks .= actionLinkTagItem('Log', 'log');
$adminLinks .= actionLinkTagItem('Online users', 'online');
$adminLinks .= actionLinkTagItem('User agents and IPs', 'lastknownbrowsers');
$adminLinks .= actionLinkTagItem('Recalculate statistics', 'recalc');
$adminLinks .= actionLinkTagItem('Optimize tables', 'optimize');
$adminLinks .= actionLinkTagItem('Upgrade database', 'upgrade');

?>
<table class="outline margin mw700 mcenter">
	<tr><th>Admin</th></tr>
	<tr><td>
		<ul>
			<?=$adminLinks;?>
		</ul>
	</td></tr>
</table>