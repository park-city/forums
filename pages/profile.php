<?php
if(!defined('DINNER')) die();
AssertForbidden("viewProfile");

if(isset($_POST['id']))
	$_GET['id'] = $_POST['id'];

if(!isset($_GET['id']))
	$id = $loguserid;
else
	$id = (int)$_GET['id'];

$rUser = Query("select * from {users} where id={0}", $id);
if(NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill("User not found.");

if($loguserid && ($_GET['token'] == $loguser['token'] || $_POST['token'] == $loguser['token']))
{
	if(isset($_GET['block']))
	{
		AssertForbidden("blockLayouts");
		$block = (int)$_GET['block'];
		$rBlock = Query("select * from {blockedlayouts} where user={0} and blockee={1}", $id, $loguserid);
		$isBlocked = NumRows($rBlock);
		if($block && !$isBlocked && $loguserid != $id)
			$rBlock = Query("insert into {blockedlayouts} (user, blockee) values ({0}, {1})", $id, $loguserid);
		elseif(!$block && $isBlocked)
			$rBlock = Query("delete from {blockedlayouts} where user={0} and blockee={1} limit 1", $id, $loguserid);
		die(header("Location: ".actionLink("profile", $id)));
	}
}

$daysKnown = (time()-$user['regdate'])/86400;
$threads = FetchResult("select count(*) from {threads} where user={0}", $id);

$numposts = "0 posts";
$numthreads = "0 threads";

if($user["posts"])
	$numposts = actionLinkTag(Plural($user["posts"], "post"), "listposts", $id);
if($threads)
	$numthreads = actionLinkTag(Plural($threads, "thread"), "listthreads", $id);

$minipic = getMinipicTag($user);

if($user['rankset'])
{
	$currentRank = GetRank($user["rankset"], $user["posts"]);
	$toNextRank = GetToNextRank($user["rankset"], $user["posts"]);
	if($toNextRank)
		$toNextRank = " (".Plural($toNextRank, "post")." to rank up)";
}

if($user['tempbantime'])
{
	write(
"
	<div class=\"outline margin cell1 smallFonts\">
		This user has been temporarily banned until {0} (GMT). That's {1} left.
	</div>
",	gmdate("M jS Y, G:i:s",$user['tempbantime']), TimeUnits($user['tempbantime'] - time())
	);
}

$lastPost = Fetch(Query("
	SELECT
		p.id as pid, p.date as date,
		{threads}.title AS ttit, {threads}.id AS tid,
		{forums}.title AS ftit, {forums}.id AS fid, {forums}.minpower
	FROM {posts} p
		LEFT JOIN {users} u on u.id = p.user
		LEFT JOIN {threads} on {threads}.id = p.thread
		LEFT JOIN {forums} on {threads}.forum = {forums}.id
	WHERE p.user={0}
	ORDER BY p.date DESC
	LIMIT 0, 1", $user["id"]));

$profileParts = array();

$foo = array();

//if($currentRank)
//	$foo["Rank"] = $currentRank;
//if($toNextRank)
//	$foo["Rank"] .= $toNextRank;
$foo["Power"] = getPowerlevelName($user['powerlevel']);
$foo["Posts and threads"] = $numposts.' and '.$numthreads;
$foo["Registered"] = format("{0} ({1} ago)", formatdate($user['regdate']), TimeUnits($daysKnown*86400));

if($lastPost) {
	$thread = array();
	$thread["title"] = $lastPost["ttit"];
	$thread["id"] = $lastPost["tid"];

	$realpl = $loguser["powerlevel"];
	if($realpl < 0) $realpl = 0;
	if(!$lastPost["minpower"] > $realpl) {
		$pid = $lastPost["pid"];
		$place = actionLinkTag(" &raquo; ".$pid, "post", $pid);
	}
	$foo["Last post"] = format("{0} ({1} ago)", formatdate($lastPost["date"]), TimeUnits(time() - $lastPost["date"])).$place;
}

$foo["Last online"] = format("{0} ({1} ago)", formatdate($user['lastactivity']), TimeUnits(time() - $user['lastactivity']));

if($loguser['powerlevel'] > 3) {
	$foo["User agent"] = str_replace("-->", "", str_replace("<!--", " &mdash;", $user['lastknownbrowser']));
	$foo["IP address"] = formatIP($user['lastip']);
	if($user['email'])
		$foo["Email"] = $user['email'];
}

if(count($foo))
	$profileParts["General information"] = $foo;

$foo = array();

if ($user['pronouns'])
	$foo["Pronouns"] = htmlspecialchars($user['pronouns']);

if($user['birthday'])
	$foo["Birthday"] = formatBirthday($user['birthday']);
	
if(count($foo))
	$profileParts["Profile information"] = $foo;

for($i = 0; $i < 15; $i++)
{
	if(getSetting("profileExt".$i."t", true) != "" && getSetting("profileExt".$i."v", true) != "")
	{
		$profileParts["Profile information"][strip_tags(getSetting("profileExt".$i."t", true))] = CleanUpPost(getSetting("profileExt".$i."v", true));
	}
}

$lynx = new PipeMenu();

if(IsAllowed("editProfile") && $loguserid == $id)
	$lynx -> add(new PipeMenuLinkEntry("Edit profile", "editprofile", "", "", "pencil"));
else if(IsAllowed("editUser") && $loguser['powerlevel'] > 2)
	$lynx -> add(new PipeMenuLinkEntry("Edit user", "editprofile", $id, "", "pencil"));

if(IsAllowed("blockLayouts") && $loguserid)
{
	$rBlock = Query("select * from {blockedlayouts} where user={0} and blockee={1}", $id, $loguserid);
	$isBlocked = NumRows($rBlock);
	if($isBlocked)
		$lynx -> add(new PipeMenuLinkEntry("Unblock layout", "profile", $id, "block=0&token={$loguser['token']}", "ban-circle"));
	else if($id != $loguserid)
		$lynx -> add(new PipeMenuLinkEntry("Block layout", "profile", $id, "block=1&token={$loguser['token']}", "ban-circle"));
}

$uname = $user["name"];
if($user["displayname"])
	$uname = $user["displayname"];

$title = htmlspecialchars($uname);

if($user['bio'])
	$previewPost['text'] = $user['bio'];
else
	$previewPost['text'] = "&nbsp;";

$previewPost['num'] = "_";
$previewPost['id'] = "_";

foreach($user as $key => $value)
	$previewPost["u_".$key] = $value;

if($mobileLayout)
	$profileTitle = GetRank('levels', $user["posts"]);
else
	$profileTitle = 'Profile';

MakePost($previewPost, POST_SAMPLE, array('metatext'=>$profileTitle,'lynx'=>$lynx));

$cc = 0;
foreach($profileParts as $partName => $fields) {
	echo '<table class="outline margin mw700 mcenter"><tr class="header0"><th colspan="2">'.$partName.'</th></tr>';
	foreach($fields as $label => $value) {
		$cc = ($cc + 1) % 2;
		if($label)
			echo '<tr><td class="cell2" style="width:15%;">'.str_replace(" ", "&nbsp;", $label).'</td><td class="cell'.$cc.'">'.$value.'</td></tr>';
		else
			echo '<tr><td colspan=2" class="cell'.$cc.'">'.$value.'</td></tr>';
	}
}

?>