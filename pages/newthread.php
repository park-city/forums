<?php

$title = "New thread";

AssertForbidden("makeThread");

if(!$loguserid) Kill("You must be logged in to post.");

if(isset($_POST['id'])) $_GET['id'] = $_POST['id'];

if(!isset($_GET['id'])) Kill("Forum ID unspecified.");

$fid = (int)$_GET['id'];

if($loguser['powerlevel'] < 0) Kill("You're banned.");

$rFora = Query("select * from {forums} where id={0}", $fid);
if(NumRows($rFora))
	$forum = Fetch($rFora);
else
	Kill("Unknown forum ID.");

if($forum['locked']) Kill("This forum is locked.");

if($forum['minpowerthread'] > $loguser['powerlevel'])
	Kill("You are not allowed to post threads in this forum.");

$OnlineUsersFid = $fid;

$crumbo = array();
if($config['mainpage'] != 'board') $crumbo['Forums'] = actionLink('board');
$crumbo[$forum['title']] = actionLink('forum', $fid);
$crumbo[$title] = actionLink('newthread');

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

	MakePost($previewPost, POST_SAMPLE, array('forcepostnum'=>1, 'metatext'=>__("Preview")));
}
else if(isset($_POST['actionpost']))
{
	$titletags = parseThreadTags($_POST['title']);
	$trimmedTitle = trim(str_replace('&nbsp;', ' ', $titletags[0]));

	//Now check if the thread is acceptable.
	$rejected = false;

	if(!$_POST['text'])
	{
		Alert('Enter a message and try again.', 'Your post is empty.');
		$rejected = true;
	}
	else if(!$trimmedTitle)
	{
		Alert('Enter a thread title and try again.', 'Your thread is untitled.');
		$rejected = true;
	}
	else
	{
		$lastPost = time() - $loguser['lastposttime'];
		if($lastPost < 1)
		{
			//Check for last thread the user posted.
			$lastThread = Fetch(Query("SELECT * FROM {threads} WHERE user={0} ORDER BY id DESC LIMIT 1", $loguserid));

			//If it looks similar to this one, assume the user has double-clicked the button.
			if($lastThread["forum"] == $fid && $lastThread["title"] == $_POST["title"])
				redirectAction("thread", $lastThread["id"]);

			$rejected = true;
			Alert(__("You're going too damn fast! Slow down a little."), __("Hold your horses."));
		}
	}

	if(!$rejected)
	{
		$post = $_POST['text'];

		$options = 0;
		if($_POST['nopl']) $options |= 1;
		if($_POST['nosm']) $options |= 2;

		$closed = 0;
		$sticky = 0;
		if(CanMod($loguserid, $forum['id']))
		{
			$closed = ($_POST['lock'] == 'on') ? '1':'0';
			$sticky = ($_POST['stick'] == 'on') ? '1':'0';
		}

		$now = time();
		
		$rThreads = Query("insert into {threads} (forum, user, title,  lastpostdate, lastposter, closed, sticky)
										  values ({0},   {1},  {2},    {3},          {1},        {4},   {5})",
										    $fid, $loguserid, $_POST['title'], time(), $closed, $sticky);
		$tid = InsertId();

		$rUsers = Query("update {users} set posts={0}, lastposttime={1} where id={2} limit 1", ($loguser['posts']+1), time(), $loguserid);

		$rPosts = Query("insert into {posts} (thread, user, date, ip, num, options, mood)
									  values ({0},{1},{2},{3},{4}, {5}, {6})", $tid, $loguserid, $now, $_SERVER['REMOTE_ADDR'], ($loguser['posts']+1), $options, (int)$_POST['mood']);
		$pid = InsertId();

		$rPostsText = Query("insert into {posts_text} (pid,text) values ({0},{1})", $pid, $post);

		$rFora = Query("update {forums} set numthreads=numthreads+1, numposts=numposts+1, lastpostdate={0}, lastpostuser={1}, lastpostid={2} where id={3} limit 1", time(), $loguserid, $pid, $fid);

		Query("update {threads} set firstpostid = {0}, lastpostid = {0}, date = {1} where id = {2}", $pid, $now, $tid);

		logAction('newthread', array('forum' => $fid, 'thread' => $tid));

		//newthread bucket
		$postingAsUser = $loguser;
		$thread["title"] = $_POST['title'];
		$thread["id"] = $tid;

		redirectAction("thread", $tid);
	}
}

$lepost = array();

// Prefill

$prefill = htmlspecialchars($_POST['text']);
$trefill = htmlspecialchars($_POST['title']);

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
$moodOptions = "<option ".$moodSelects[0]."value=\"0\">".__("[Default avatar]")."</option>\n";
$rMoods = Query("select mid, name from {moodavatars} where uid={0} order by mid asc", $loguserid);
while($mood = Fetch($rMoods))
	$moodOptions .= format(
"
	<option {0} value=\"{1}\">{2}</option>
",	$moodSelects[$mood['mid']], $mood['mid'], htmlspecialchars($mood['name']));

// MOD Pizza

$lepost['mod'] = '';

if(CanMod($loguserid, $forum['id']))
{
	$mod .= "<label><input type=\"checkbox\" ".getCheck("lock")." name=\"lock\">&nbsp;Lock thread</label>\n";
	$mod .= "<label><input type=\"checkbox\" ".getCheck("stick")."  name=\"stick\">&nbsp;Pin thread</label>\n";
	
	$lepost['mod'] = $mod;
}

$lepost['type'] = 'newthread';
$lepost['id'] = $fid;
$lepost['action'] = actionLink($lepost['type'], $fid);
$lepost['heading'] = $title;
$lepost['top'] = '<script src="'.resourceLink("js/threadtagging.js").'"></script><script type="text/javascript">window.addEventListener("load",  hookUpControls, false);</script>
				<tr colspan=2 class="cell0">
					<td>
						<label for="tit">
							Title
						</label>
					</td>
					<td id="threadTitleContainer">
						<input type="text" id="tit" name="title" style="width:98%" maxlength="50" value="'.$trefill.'">
					</td>
				</tr>';
$lepost['prefill'] = $prefill;
$lepost['moodoptions'] = $moodOptions;
$lepost['nopl'] = getCheck('nopl');
$lepost['nosm'] = getCheck('nosm');

newPostForm($lepost);

?>