<?php

AssertForbidden("viewRanks");

loadRanksets();
if(count($ranksetData) == 0)
	Kill("No ranksets found :(");

$title = "Ranks";
$crumbo = array($title => actionLink("ranks"));
$layout_crumbs = MakeCrumbs($crumbo);

if(!isset($_GET["id"]))
{
	$rankset = $loguser['rankset'];
	if(!$rankset || !isset($ranksetData[$rankset]))
	{
		$rankset = array_keys($ranksetData);
		$rankset = $rankset[0];
	}
	
	die(header("Location: ".actionLink("ranks", $rankset)));
}

$rankset = $_GET['id'];
if(!isset($ranksetData[$rankset]))
	Kill("Rankset not found.");

if(count($ranksetNames) > 1)
{
	$ranksets = '';
	foreach($ranksetNames as $name => $cockandballs)
		if($name == $rankset)
			$ranksets .= '<li>'.$cockandballs.'</li>';
		else
			$ranksets .= actionLinkTagItem($cockandballs, 'ranks', $name);

	echo "
		<table class=\"outline margin mcenter mw600\">
			<tr class=\"header0\">
				<th colspan=\"2\">
					Ranksets
				</th>
			</tr>
			<tr class=\"cell0 center\">
				<td>
					<ul class=\"pipemenu\">
						".$ranksets."
					</ul>
				</td>
		</table>";
}

$users = array();
$rUsers = Query("select u.(_userfields), u.posts as u_posts from {users} u order by id asc");
while($user = Fetch($rUsers))
	$users[$user['u_id']] = getDataPrefix($user, "u_");

$ranks = $ranksetData[$rankset];

$ranklist = "";
for($i = 0; $i < count($ranks); $i++)
{
	$rank = $ranks[$i];
	$nextRank = $ranks[$i+1];
	if($nextRank['num'] == 0)
		$nextRank['num'] = $ranks[$i]['num'] + 1;
	$members = array();
	foreach($users as $user)
	{
		if($user['posts'] >= $rank['num'] && $user['posts'] < $nextRank['num'])
			$members[] = UserLink($user);
	}
	$showRank = $loguser['powerlevel'] > 0 || $loguser['posts'] >= $rank['num'] || count($members) > 0;
	if($showRank)
		$rankText = getRankHtml($rankset, $rank);
	else
		$rankText = "???";

	if(count($members) == 0)
		$members = "&nbsp;";
	else
		$members = join(", ", $members);

	$cellClass = ($cellClass+1) % 2;

	$ranklist .= format(
"
	<tr class=\"cell{0}\">
		<td class=\"cell2\" style=\"width:20%\">{1}</td>
		<td style=\"width:10%\">{2}</td>
		<td style=\"width:70%\">{3}</td>
	</tr>
", $cellClass, $rankText, $rank['num'], $members);
}
echo '
<table class="outline margin mcenter width75">
	<tr class="header1">
		<th>
			Rank
		</th>
		<th>
			Posts
		</th>
		<th>
			Users
		</th>
	</tr>
	'.$ranklist.'
</table>';

?>