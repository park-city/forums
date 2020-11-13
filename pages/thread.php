<?php
if(isset($_GET['pid']))
{
	header("HTTP/1.1 301 Moved Permanently");
	header("Status: 301 Moved Permanently");
	redirectAction("post", $_GET["pid"]);
}

if(isset($_GET['id']))
	$tid = (int)$_GET['id'];
else
	Kill("Thread not found.");

AssertForbidden("viewThread", $tid);

$rThread = Query("select * from {threads} where id={0}", $tid);

if(NumRows($rThread))
	$thread = Fetch($rThread);
else
	Kill("Thread not found.");

$fid = $thread['forum'];
AssertForbidden("viewForum", $fid);

$pl = $loguser['powerlevel'];
if($pl < 0) $pl = 0;

$rFora = Query("select * from {forums} where id={0}", $fid);
if(NumRows($rFora))
{
	$forum = Fetch($rFora);
	if($forum['minpower'] > $pl)
	{
		if($forum["id"] == $config['trashforum'])
			Kill("This thread does not exist.");
		else
			Kill("You are not allowed to browse this forum.");
	}
}
else
	Kill("Forum not found.");

setUrlName("newreply", $tid, $thread["title"]);
setUrlName("editthread", $tid, $thread["title"]);

$threadtags = ParseThreadTags($thread['title']);
$title = $threadtags[0];

Query("update {threads} set views=views+1 where id={0} limit 1", $tid);

if(isset($_GET['from']))
	$fromstring = "from=".(int)$_GET["from"];
else
	$fromstring = "";

$links = array();
if($loguserid)
{
	if(IsAllowed("makeReply", $tid) && (!$thread['closed'] || $loguser['powerlevel'] > 2))
		$links[] = actionLinkTagItem('New reply', 'newreply', $tid, '', 'edit');

	if(CanMod($loguserid,$forum['id']) && IsAllowed("editThread", $tid))
	{
		$links[] = actionLinkTagItem('Edit thread', 'editthread', $tid, '', 'pencil');
		if($thread['closed'])
			$links[] = actionLinkTagItem('Open', 'editthread', $tid, 'action=open&key='.$loguser['token'], 'unlock');
		else
			$links[] = actionLinkTagItem('Close', 'editthread', $tid, 'action=close&key='.$loguser['token'], 'unlock');
		if($thread['sticky'])
			$links[] = actionLinkTagItem('Unstick', 'editthread', $tid, 'action=unstick&key='.$loguser['token'], 'pushpin');
		else
			$links[] = actionLinkTagItem('Stick', 'editthread', $tid, 'action=stick&key='.$loguser['token'], 'pushpin');
		if($forum['id'] != $config['trashforum'])
			$links[] = actionLinkTagItem("Trash", "editthread", $tid, "action=trash&key=".$loguser['token'], "trash");
	}
	else if($thread['user'] == $loguserid)
		$links[] = actionLinkTagItem('Edit thread', 'editthread', $tid, '', 'pencil');
}

$crumbs = array();
if(MAIN_PAGE != 'board') $crumbs['Forums'] = actionLink('board');
$crumbs[$forum['title']] = actionLink('forum', $fid);
$crumbs[$title] = actionLink('thread', $tid);

makeCrumbs($crumbs, $links);

write(
"
	<script type=\"text/javascript\">
			window.addEventListener(\"load\",  hookUpControls, false);
	</script>
");

$rRead = Query("insert into {threadsread} (id,thread,date) values ({0}, {1}, {2}) on duplicate key update date={2}", $loguserid, $tid, time());

$total = $thread['replies'] + 1; //+1 for the OP
$ppp = 20;
if(isset($_GET['from']))
	$from = $_GET['from'];
else
	$from = 0;

$rPosts = Query("
			SELECT
				p.*,
				pt.text, pt.revision, pt.user AS revuser, pt.date AS revdate,
				u.(_userfields), u.(rankset,title,picture,posts,postheader,signature,signsep,lastposttime,lastactivity,regdate,globalblock),
				ru.(_userfields),
				du.(_userfields)
			FROM
				{posts} p
				LEFT JOIN {posts_text} pt ON pt.pid = p.id AND pt.revision = p.currentrevision
				LEFT JOIN {users} u ON u.id = p.user
				LEFT JOIN {users} ru ON ru.id=pt.user
				LEFT JOIN {users} du ON du.id=p.deletedby
			WHERE thread={1}
			ORDER BY date ASC LIMIT {2u}, {3u}", $loguserid, $tid, $from, $ppp);

$numonpage = NumRows($rPosts);

// Do the page

if($total > $ppp) echo '<table class="outline margin"><tr><td class="smallFonts center">'.PageLinks(actionLink("thread", $tid, "from="), $ppp, $from, $total).'</td></tr></table>';

if(NumRows($rPosts))
{
	while($post = Fetch($rPosts))
	{
		$post['closed'] = $thread['closed'];
		MakePost($post, POST_NORMAL, array('tid'=>$tid, 'fid'=>$fid));
	}
}

if($total > $ppp) echo '<table class="outline margin"><tr><td class="smallFonts center">'.PageLinks(actionLink("thread", $tid, "from="), $ppp, $from, $total).'</td></tr></table>';

// Quick reply

$lepost = array();

if($loguserid && $loguser['powerlevel'] >= $forum['minpowerreply'] && (!$thread['closed'] || $loguser['powerlevel'] > 0) && !isset($replyWarning))
{
	// Ninja
	
	$ninja = FetchResult("select id from {posts} where thread={0} order by date desc limit 0, 1", $tid);
	
	// Mod options
	
	$lepost['mod'] = '';
	
	if(CanMod($loguserid, $fid))
	{
		if(!$thread['closed'])
			$lepost['mod'] .= "<label><input type=\"checkbox\" name=\"lock\">&nbsp;Lock thread</label>\n";
		else
			$lepost['mod'] .= "<label><input type=\"checkbox\" name=\"unlock\">&nbsp;Unlock thread</label>\n";
		if(!$thread['sticky'])
			$lepost['mod'] .= "<label><input type=\"checkbox\" name=\"stick\">&nbsp;Stick thread</label>\n";
		else
			$lepost['mod'] .= "<label><input type=\"checkbox\" name=\"unstick\">&nbsp;Unstick thread</label>\n";
	}
	
	// Mood avatars
	
	$lepost['moodoptions'] = '';
	
	$moodOptions = "<option ".$moodSelects[0]."value=\"0\">Default avatar</option>\n";
	
	$rMoods = Query("select mid, name from {moodavatars} where uid={0} order by mid asc", $loguserid);
	
	while($mood = Fetch($rMoods))
		$moodOptions .= format("<option {0} value=\"{1}\">{2}</option>", $moodSelects[$mood['mid']], $mood['mid'], htmlspecialchars($mood['name']));
		
	$lepost['type'] = 'newreply';
	$lepost['id'] = $tid;
	$lepost['action'] = actionLink($lepost['type'], $tid);
	$lepost['heading'] = $title;
	$lepost['top'] = '<input type="hidden" name="ninja" value="'.$ninja.'">';
	$lepost['prefill'] = $prefill;
	$lepost['moodoptions'] = $moodOptions;
	$lepost['nopl'] = '';
	$lepost['nosm'] = '';
	$lepost['nofocus'] = 1;

	newPostForm($lepost);
}

?>