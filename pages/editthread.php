<?php

$title = "Edit thread";

AssertForbidden("editThread");

if (isset($_REQUEST['action']) && $loguser['token'] != $_REQUEST['key'])
		Kill(__("No."));

if(!$loguserid) //Not logged in?
	Kill("You must be logged in to edit threads.");

if(isset($_POST['id']))
	$_GET['id'] = $_POST['id'];

if(!isset($_GET['id']))
	Kill("Thread ID unspecified.");

$tid = (int)$_GET['id'];

$rThread = Query("select * from {threads} where id={0}", $tid);
if(NumRows($rThread))
	$thread = Fetch($rThread);
else
	Kill(__("Unknown thread ID."));

$canMod = CanMod($loguserid, $thread['forum']);

if(!$canMod && $thread['user'] != $loguserid)
	Kill(__("You are not allowed to edit threads."));

if(!$canMod && $thread['closed'])
	Kill(__("You are not allowed to edit closed threads."));

$OnlineUsersFid = $thread['forum'];

$fid = $thread["forum"];
$rFora = Query("select id, minpower, title, catid from {forums} where id={0}", $fid);

if(NumRows($rFora))
	$forum = Fetch($rFora);
else
	Kill(__("Unknown forum ID."));

if($forum['minpower'] > $loguser['powerlevel'])
	Kill(__("You are not allowed to edit threads."));
$tags = ParseThreadTags($thread['title']);
setUrlName("thread", $thread["id"], $thread["title"]);

$crumbs = new PipeMenu();
makeForumCrumbs($crumbs, $forum);
$crumbs->add(new PipeMenuHtmlEntry(makeThreadLink($thread)));
$crumbs->add(new PipeMenuTextEntry(__("Edit thread")));
makeBreadcrumbs($crumbs);

if(isset($_POST["action"]))
	$_GET["action"] = $_POST["action"];

if($canMod)
{
	if($_GET['action'] == "close")
	{
		$rThread = Query("update {threads} set closed=1 where id={0}", $tid);
		logAction('closethread', array('forum' => $fid, 'thread' => $tid, 'user2' => $thread["user"]));

		redirectAction("thread", $tid);
	}
	elseif($_GET['action'] == "open")
	{
		$rThread = Query("update {threads} set closed=0 where id={0}", $tid);
		logAction('openthread', array('forum' => $fid, 'thread' => $tid, 'user2' => $thread["user"]));

		redirectAction("thread", $tid);
	}
	elseif($_GET['action'] == "stick")
	{
		$rThread = Query("update {threads} set sticky=1 where id={0}", $tid);
		logAction('stickthread', array('forum' => $fid, 'thread' => $tid, 'user2' => $thread["user"]));

		redirectAction("thread", $tid);
	}
	elseif($_GET['action'] == "unstick")
	{
		$rThread = Query("update {threads} set sticky=0 where id={0}", $tid);
		logAction('unstickthread', array('forum' => $fid, 'thread' => $tid, 'user2' => $thread["user"]));

		redirectAction("thread", $tid);
	}

	// Move thread!
	if($_GET['action'] == "edit" || $_GET['action'] == "delete" || $_GET['action'] == "trash")
	{
		if($_GET["action"] == "trash")
			$_POST["moveTo"] = 5;
		if($_GET["action"] == "delete")
			$_POST["moveTo"] = 5;

		if($thread["forum"] != $_POST["moveTo"])
		{
			$moveto = (int)$_POST['moveTo'];
			$dest = Fetch(Query("select * from {forums} where id={0}", $moveto));
			if(!$dest)
			{
				if($_GET['action'] == "delete")
					Kill(__("Couldn't find deleted thread forum. Please specify one in the board's settings."));
				else if($_GET['action'] == "trash")
					Kill(__("Couldn't find trash forum. Please specify one in the board's settings."));
				else
					Kill(__("Unknown forum ID."));
			}
			
			//Tweak forum counters
			$rForum = Query("update {forums} set numthreads=numthreads-1, numposts=numposts-{0} where id={1}", 
							$thread['replies']+1, $thread['forum']);

			$rForum = Query("update {forums} set numthreads=numthreads+1, numposts=numposts+{0} where id={1}", 
							$thread['replies']+1, $moveto);

			$rThread = Query("update {threads} set forum={0} where id={1}", 
							(int)$_POST['moveTo'], $tid);

			// Tweak forum counters #2
			Query("	UPDATE {forums} LEFT JOIN {threads}
					ON {forums}.id={threads}.forum AND {threads}.lastpostdate=(SELECT MAX(nt.lastpostdate) FROM {threads} nt WHERE nt.forum={forums}.id)
					SET {forums}.lastpostdate=IFNULL({threads}.lastpostdate,0), {forums}.lastpostuser=IFNULL({threads}.lastposter,0), {forums}.lastpostid=IFNULL({threads}.lastpostid,0)
					WHERE {forums}.id={0} OR {forums}.id={1}", $thread['forum'], $moveto);

			if($_GET['action'] == "delete")
				logAction('deletethread', array('forum' => $fid, 'thread' => $tid, 'user2' => $thread["user"]));
			else if($_GET['action'] == "trash")
				logAction('trashthread', array('forum' => $fid, 'thread' => $tid, 'user2' => $thread["user"]));
			else
				logAction('movethread', array('forum' => $fid, 'thread' => $tid, 'forum2' => $moveto, 'user2' => $thread["user"]));
		}
	}

	//Close and unstick thread if deleting or trashing.
	if($_GET['action'] == "delete" || $_GET['action'] == "trash")
	{
		$rThread = Query("update {threads} set sticky=0, closed=1 where id={0}", $tid);
		redirectAction("forum", $fid);
	}

	//Editpost open/close.
	if($_GET['action'] == "edit")
	{
		$isClosed = (isset($_POST['isClosed']) ? 1 : 0);
		$isSticky = (isset($_POST['isSticky']) ? 1 : 0);

		if(!$thread["sticky"] && $isSticky)
			logAction('stickthread', array('forum' => $fid, 'thread' => $tid, 'user2' => $thread["user"]));
		if($thread["sticky"] && !$isSticky)
			logAction('unstickthread', array('forum' => $fid, 'thread' => $tid, 'user2' => $thread["user"]));
		if(!$thread["closed"] && $isClosed)
			logAction('closethread', array('forum' => $fid, 'thread' => $tid, 'user2' => $thread["user"]));
		if($thread["closed"] && !$isClosed)
			logAction('openthread', array('forum' => $fid, 'thread' => $tid, 'user2' => $thread["user"]));
		
		Query("update {threads} set closed={0}, sticky={1} where id={2} limit 1", $isClosed, $isSticky, $tid);
	}
}

//Edit thread title and icon. Both mods AND thread-owners can do this.
if($_GET['action'] == "edit")
{
	$trimmedTitle = trim(str_replace('&nbsp;', ' ', $_POST['title']));
	if($trimmedTitle != "")
	{
		if($thread["title"] != $_POST['title'])
			logAction('editthread', array('forum' => $fid, 'thread' => $tid, 'user2' => $thread["user"]));

		$rThreads = Query("update {threads} set title={0} where id={1} limit 1", $_POST['title'], $tid);

		redirectAction("thread", $tid);
	}
	else
		Alert("Your thread title is empty.");
}


//Fetch thread again in case something above has changed.
$rThread = Query("select * from {threads} where id={0}", $tid);
if(NumRows($rThread))
	$thread = Fetch($rThread);
else
	Kill("Thread not found.");

$canMod = CanMod($loguserid, $thread['forum']);

if(!$canMod && $thread['user'] != $loguserid)
	Kill("You can't do that.");

$OnlineUsersFid = $thread['forum'];

$fid = $thread["forum"];
$rFora = Query("select id, minpower, title from {forums} where id={0}", $fid);

if(NumRows($rFora))
	$forum = Fetch($rFora);
else
	Kill("Unknown forum.");

if($forum['minpower'] > $loguser['powerlevel'])
	Kill("You can't do this.");


//Recover data from POST
if(!isset($_POST['title']))
	$_POST['title'] = $thread['title'];

if($canMod)
{
	echo "
	<script src=\"".resourceLink("js/threadtagging.js")."\"></script>
	<form action=\"".actionLink("editthread")."\" method=\"post\">
		<table class=\"outline margin\" style=\"width: 100%;\">
			<tr class=\"header1\">
				<th colspan=\"2\">
					Edit thread
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					<label for=\"tit\">Title</label>
				</td>
				<td id=\"threadTitleContainer\">
					<input type=\"text\" id=\"tit\" name=\"title\" style=\"width:98%;\" maxlength=\"60\" value=\"".htmlspecialchars($_POST['title'])."\" />
				</td>
			</tr>
			<tr class=\"cell0\">
				<td>Toggles</td>
				<td>
					<label>
						<input type=\"checkbox\" name=\"isClosed\" ".($thread['closed'] ? " checked=\"checked\"" : "")." />
						Locked
					</label>
					<label>
						<input type=\"checkbox\" name=\"isSticky\" ".($thread['sticky'] ? " checked=\"checked\"" : "")." />
						Pinned
					</label>
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>Move</td>
				<td>
					".makeForumList('moveTo', $thread["forum"])."
				</td>
			</tr>
			<tr class=\"cell2\">
				<td></td>
				<td>
					<input type=\"submit\" name=\"asdf\" value=\"".__("Edit")."\" />
					<input type=\"hidden\" name=\"id\" value=\"$tid\" />
					<input type=\"hidden\" name=\"key\" value=\"${loguser["token"]}\" />
					<input type=\"hidden\" name=\"action\" value=\"edit\" />
				</td>
			</tr>
		</table>
	</form>";
}
else
{
	echo "
	<script src=\"".resourceLink("js/threadtagging.js")."\"></script>
	<form action=\"".actionLink("editthread")."\" method=\"post\">
		<table class=\"outline margin width50\">
			<tr class=\"cell0\">
				<td>
					<label for=\"tit\">Title</label>
				</td>
				<td>
					<input type=\"text\" id=\"tit\" name=\"title\" style=\"width: 98%;\" maxlength=\"60\" value=\"".htmlspecialchars($_POST['title'])."\" />
				</td>
			</tr>
			<tr class=\"cell2\">
				<td></td>
				<td>
					<input type=\"submit\" name=\"asdf\" value=\"".__("Edit")."\" />
					<input type=\"hidden\" name=\"id\" value=\"$tid\" />
					<input type=\"hidden\" name=\"key\" value=\"${loguser["token"]}\" />
					<input type=\"hidden\" name=\"action\" value=\"edit\" />
				</td>
			</tr>
		</table>
	</form>";
}

?>
