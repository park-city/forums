<?php
if(!defined('DINNER')) die();

if(!isset($_GET['id']))
	Kill('Forum not found.');

$fid = (int)$_GET['id'];

if($loguserid && $_GET['action'] == "markasread")
{
	Query("REPLACE INTO {threadsread} (id,thread,date) SELECT {0}, {threads}.id, {1} FROM {threads} WHERE {threads}.forum={2}",
		$loguserid, time(), $fid);

	redirectAction("board");
}

AssertForbidden("viewForum", $fid);

$pl = $loguser['powerlevel'];
if($pl < 0) $pl = 0;

$rFora = Query("select * from {forums} where id={0}", $fid);
if(NumRows($rFora))
{
	$forum = Fetch($rFora);
	
	if($forum['minpower'] > $pl)
		Kill('You can\'t view this forum.');
} else
	Kill('Forum not found.');

$title = $forum['title'];

setUrlName("newthread", $fid, $forum["title"]);

if ($loguserid)
{
	$isIgnored = FetchResult("select count(*) from {ignoredforums} where uid={0} and fid={1}", $loguserid, $fid) == 1;
	if(isset($_GET['ignore']))
	{
		if(!$isIgnored)
			Query("insert into {ignoredforums} values ({0}, {1})", $loguserid, $fid);
		redirectAction("forum", $fid);
	}
	else if(isset($_GET['unignore']))
	{
		if($isIgnored)
			Query("delete from {ignoredforums} where uid={0} and fid={1}", $loguserid, $fid);
		redirectAction("forum", $fid);
	}
}

$links = new PipeMenu();

if($loguserid)
{
	$links->add(new PipeMenuLinkEntry("Mark forum read", "forum", $fid, "action=markasread", "ok"));
	
	if($forum['minpowerthread'] <= $loguser['powerlevel'])
		$links->add(new PipeMenuLinkEntry("New thread", "newthread", $fid, "", "edit"));
}

makeLinks($links);

$crumbs = new PipeMenu();
makeForumCrumbs($crumbs, $forum);
makeBreadcrumbs($crumbs);

$OnlineUsersFid = $fid;

makeForumListing($fid);

$total = $forum['numthreads'];
$tpp = 50;
if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;

$rThreads = Query("	SELECT
						t.*,
						".($loguserid ? "tr.date readdate," : '')."
						su.(_userfields),
						lu.(_userfields)
					FROM
						{threads} t
						".($loguserid ? "LEFT JOIN {threadsread} tr ON tr.thread=t.id AND tr.id={3}" : '')."
						LEFT JOIN {users} su ON su.id=t.user
						LEFT JOIN {users} lu ON lu.id=t.lastposter
					WHERE forum={0}
					ORDER BY sticky DESC, lastpostdate DESC LIMIT {1u}, {2u}", $fid, $from, $tpp, $loguserid);

$numonpage = NumRows($rThreads);

$pagelinks = PageLinks(actionLink("forum", $fid, "from="), $tpp, $from, $total);

if($pagelinks)
	echo "<div class=\"smallFonts pages\">Pages: ".$pagelinks."</div>";

if(NumRows($rThreads))
	echo listThreads($rThreads, true, false);
else
	if($forum['minpowerthread'] > $loguser['powerlevel'])
		Alert(__("You cannot start any threads here."), __("Empty forum"));
	elseif($loguserid)
		Alert(format(__("Would you like to {0}?"), actionLinkTag(__("post something"), "newthread", $fid)), __("Empty forum"));
	else
		Alert(format(__("{0} so you can post something."), actionLinkTag(__("Log in"), "login")), __("Empty forum"));

if($pagelinks)
	Write("<div class=\"smallFonts pages\">Pages: {0}</div>", $pagelinks);

printRefreshCode();

function fj_forumBlock($fora, $catid, $selID, $indent)
{
	$ret = '';
	
	foreach ($fora[$catid] as $forum)
	{
		$ret .=
'				<option value="'.htmlentities(actionLink('forum', $forum['id'])).'"'.($forum['id'] == $selID ? ' selected="selected"':'').'>'
	.str_repeat('&nbsp; &nbsp; ', $indent).htmlspecialchars($forum['title'])
	.'</option>
';
		if (!empty($fora[-$forum['id']]))
			$ret .= fj_forumBlock($fora, -$forum['id'], $selID, $indent+1);
	}
	
	return $ret;
}

?>