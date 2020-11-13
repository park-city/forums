<?php

if(!$loguserid) Kill("You must be logged in to post.");
if($loguser['powerlevel'] < 0) Kill("You're banned. You can't post.");

if(isset($_POST['id']))
	$_GET['id'] = $_POST['id'];

if(!isset($_GET['id'])) Kill("Thread ID unspecified.");

$tid = (int)$_GET['id'];
AssertForbidden("viewThread", $tid);
AssertForbidden("makeReply", $tid);

$rThread = Query("select * from {threads} where id={0}", $tid);
if(NumRows($rThread))
{
	$thread = Fetch($rThread);
	$fid = $thread['forum'];
}
else
	Kill("Unknown thread ID.");

$rFora = Query("select * from {forums} where id={0}", $fid);
if(NumRows($rFora))
	$forum = Fetch($rFora);
else
	Kill("Unknown forum ID.");
$fid = $forum['id'];
AssertForbidden("viewForum", $fid);

$isHidden = (int)($forum['minpower'] > 0);

if($forum['minpowerreply'] > $loguser['powerlevel'])
	Kill("Your power is not enough.");

if($thread['closed'] && $loguser['powerlevel'] < 3)
	Kill("This thread is locked.");

$title = "New reply";

$OnlineUsersFid = $fid;

write(
"
	<script type=\"text/javascript\">
			window.addEventListener(\"load\",  hookUpControls, false);
	</script>
");

$tags = ParseThreadTags($thread['title']);
setUrlName("thread", $thread["id"], $thread["title"]);

$crumbo = array();
if($config['mainpage'] != 'board') $crumbo['Forums'] = actionLink('board');
$crumbo[$forum['title']] = actionLink('forum', $fid);
$crumbo[$thread['title']] = actionLink('thread', $tid);
$crumbo[$title] = '';

$layout_crumbs = MakeCrumbs($crumbo, $linko);


if(isset($_POST['actionpreview']))
{
	$previewPost['text'] = $_POST["text"];
	$previewPost['num'] = $loguser['posts']+1;
	$previewPost['posts'] = $loguser['posts']+1;
	$previewPost['id'] = "_";
	$previewPost['options'] = 0;
	if($_POST['nopl']) $previewPost['options'] |= 1;
	if($_POST['nosm']) $previewPost['options'] |= 2;
	$previewPost['mood'] = (int)$_POST['mood'];
	foreach($loguser as $key => $value)
		$previewPost["u_".$key] = $value;

	MakePost($previewPost, POST_SAMPLE, array('forcepostnum'=>1, 'metatext'=>"Preview"));
}
else if(isset($_POST['actionpost']))
{
	//Now check if the post is acceptable.
	$rejected = false;

	if(!$_POST['text'])
	{
		Alert(__("Enter a message and try again."), __("Your post is empty."));
		$rejected = true;
	}
	//else if($thread['lastposter']==$loguserid && $thread['lastpostdate']>=time()-43200 && $loguser['powerlevel']<3)
	//{
	//	Alert(__("You can't double post until it's been at least twelve hours."), __("dude"));
	//	$rejected = true;
	//}
	else
	{
		$lastPost = time() - $loguser['lastposttime'];
		if($lastPost < 1)
		{
			//Check for last post the user posted.
			$lastPost = Fetch(Query("SELECT * FROM {posts} WHERE user={0} ORDER BY date DESC LIMIT 1", $loguserid));

			//If it looks similar to this one, assume the user has double-clicked the button.
			if($lastPost["thread"] == $tid)
			{
				$pid = $lastPost["id"];
				redirectAction("thread", 0, "pid=".$pid."#".$pid);
			}

			$rejected = true;
			Alert(__("You're going too damn fast! Slow down a little."), __("Hold your horses."));
		}
	}

	if(!$rejected)
	{
		$ninja = FetchResult("select id from {posts} where thread={0} order by date desc limit 0, 1", $tid);
		if(isset($_POST['ninja']) && $_POST['ninja'] != $ninja)
		{
			Alert("There's been a new post since you typed this. Or you double-clicked? See below.");
			$rejected = true;
		}
	}

	if(!$rejected)
	{
		$post = $_POST['text'];

		$options = 0;
		if($_POST['nopl']) $options |= 1;
		if($_POST['nosm']) $options |= 2;

		if(CanMod($loguserid, $forum['id']))
		{
			if($_POST['lock'])
				$mod.= ", closed = 1";
			else if($_POST['unlock'])
				$mod.= ", closed = 0";
			if($_POST['stick'])
				$mod.= ", sticky = 1";
			else if($_POST['unstick'])
				$mod.= ", sticky = 0";
		}


		$now = time();

		$rUsers = Query("update {users} set posts=posts+1, lastposttime={0} where id={1} limit 1",
			time(), $loguserid);

		$rPosts = Query("insert into {posts} (thread, user, date, ip, num, options, mood) values ({0},{1},{2},{3},{4}, {5}, {6})",
			$tid, $loguserid, $now, $_SERVER['REMOTE_ADDR'], ($loguser['posts']+1), $options, (int)$_POST['mood']);

		$pid = InsertId();

		$rPostsText = Query("insert into {posts_text} (pid,text,revision,user,date) values ({0}, {1}, {2}, {3}, {4})", $pid, $post, 0, $loguserid, time());

		$rFora = Query("update {forums} set numposts=numposts+1, lastpostdate={0}, lastpostuser={1}, lastpostid={2} where id={3} limit 1",
			$now, $loguserid, $pid, $fid);

		$rThreads = Query("update {threads} set lastposter={0}, lastpostdate={1}, replies=replies+1, lastpostid={2}".$mod." where id={3} limit 1",
			$loguserid, $now, $pid, $tid);

		logAction('newreply', array('forum' => $fid, 'thread' => $tid, 'post' => $pid));

		redirectAction("post", $pid);
	}
}

$lepost = array();

// Prefill, quotations

$prefill = htmlspecialchars($_POST['text']);

if($_GET['link'])
{
	$prefill = ">>".(int)$_GET['link']."\r\n\r\n";
}
else if($_GET['quote'])
{
	$rQuote = Query("	select
					p.id, p.deleted, pt.text,
					f.minpower,
					u.name poster
				from {posts} p
					left join {posts_text} pt on pt.pid = p.id and pt.revision = p.currentrevision
					left join {threads} t on t.id=p.thread
					left join {forums} f on f.id=t.forum
					left join {users} u on u.id=p.user
				where p.id={0}", (int)$_GET['quote']);

	if(NumRows($rQuote))
	{
		$quote = Fetch($rQuote);

		//SPY CHECK!
		//Do we need to translate this line? It's not even displayed in its true form ._.
		if($quote['minpower'] > $loguser['powerlevel'])
			$quote['text'] = str_rot13("Pools closed due to not enough power. Prosecutors will be violated.");

		if ($quote['deleted'])
			$quote['text'] = __("Post is deleted");

		$prefill = "[quote=\"".htmlspecialchars($quote['poster'])."\" id=\"".$quote['id']."\"]".htmlspecialchars($quote['text'])."[/quote]";
		$prefill = str_replace("/me ", "[b]* ".htmlspecialchars($quote['poster'])."[/b]", $prefill);
	}
}

function getCheck($name)
{
	if(isset($_POST[$name]) && $_POST[$name])
		return "checked=\"checked\"";
	else return "";
}

// Mood avatars

$lepost['moodoptions'] = '';

if($_POST['mood'])
	$moodSelects[(int)$_POST['mood']] = "selected=\"selected\" ";
$moodOptions = "<option ".$moodSelects[0]."value=\"0\">Default avatar</option>\n";

$rMoods = Query("select mid, name from {moodavatars} where uid={0} order by mid asc", $loguserid);

while($mood = Fetch($rMoods))
	$moodOptions .= format(
"
	<option {0} value=\"{1}\">{2}</option>
",	$moodSelects[$mood['mid']], $mood['mid'], htmlspecialchars($mood['name']));

// Ninja

$ninja = FetchResult("select id from {posts} where thread={0} order by date desc limit 0, 1", $tid);

// Mod options

$lepost['mod'] = '';

if(CanMod($loguserid, $fid))
{
	$mod = '';
	if(!$thread['closed'])
		$mod .= "<label><input type=\"checkbox\" ".getCheck("lock")." name=\"lock\">&nbsp;Lock thread</label>\n";
	else
		$mod .= "<label><input type=\"checkbox\" ".getCheck("unlock")."  name=\"unlock\">&nbsp;Unlock thread</label>\n";

	if(!$thread['sticky'])
		$mod .= "<label><input type=\"checkbox\" ".getCheck("stick")."  name=\"stick\">&nbsp;Pin thread</label>\n";
	else
		$mod .= "<label><input type=\"checkbox\" ".getCheck("unstick")."  name=\"unstick\">&nbsp;Unpin thread</label>\n";

	$lepost['mod'] = $mod;
}

// Ok motherfucker let's do this

$lepost['type'] = 'newreply';
$lepost['id'] = $tid;
$lepost['action'] = actionLink($lepost['type'], $tid);
$lepost['heading'] = $title;
$lepost['top'] = '<input type="hidden" name="ninja" value="'.$ninja.'">';
$lepost['prefill'] = $prefill;
$lepost['moodoptions'] = $moodOptions;
$lepost['nopl'] = getCheck('nopl');
$lepost['nosm'] = getCheck('nosm');

newPostForm($lepost);
doThreadPreview($lepost['id']);
