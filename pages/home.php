<?php
$title = 'Home';

$pl = $loguser['powerlevel'];
if($pl < 0) $pl = 0;

$rFora = Query("select * from {forums} where id = {0}", $config['newsforum']);

if(NumRows($rFora))
	$forum = Fetch($rFora);
else
	Kill("News forum not found; check config");

$fid = $forum['id'];

if($mobileLayout)
	print $config['homecontent'];
else
	write('<table><tr><td style="background:transparent;width:50%;border:0px none;vertical-align:top;padding-right:1em; padding-bottom:1em;">');

$total = $forum['numthreads'];

if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;

$tpp = 5;

print "<h2 style='text-align:center;'>Updates</h2>";

$rThreads = Query("	SELECT
						t.id, t.title, t.closed, t.replies, t.lastpostid,
						p.date, p.options,
						pt.text,
						su.(_userfields),
						lu.(_userfields)
					FROM
						{threads} t
						LEFT JOIN {posts} p ON p.id=t.firstpostid
						LEFT JOIN {posts_text} pt ON pt.pid = p.id AND pt.revision = p.currentrevision
						LEFT JOIN {users} su ON su.id=t.user
						LEFT JOIN {users} lu ON lu.id=t.lastposter
					WHERE forum={0}
					ORDER BY sticky DESC, date DESC LIMIT {1u}, {2u}",
						$fid, $from, $tpp);

$numonpage = NumRows($rThreads);

$pagelinks = PageLinks(actionLink("home", "", "from="), $tpp, $from, $total);

if($pagelinks && $_GET["from"])
	Write("<div class=\"smallFonts pages\">Pages: {0}</div>", $pagelinks);

$haveStickies = 0;

while($thread = Fetch($rThreads))
{
	$starter = getDataPrefix($thread, "su_");
	$last = getDataPrefix($thread, "lu_");

	$tags = ParseThreadTags($thread['title']);

	if($thread['sticky'] && $haveStickies == 0) $haveStickies = 1;

	$lastLink = "";
	if($thread['lastpostid'])
		$lastLink = " ".actionLinkTag("&raquo;", "post", $thread['lastpostid']);

	if($thread['replies'] == 0) $lastLink = "";

	$postdate = formatdate($thread['date']);
	$posttext = CleanUpPost($thread['text'],$thread['u_name'], false, false);

	$comments = Plural($thread['replies'], "comment");
	$comments = actionLinkTag($comments, "thread", $thread['id'], "", $thread["title"]);

	$newreply = actionLinkTag("Post a comment", "newreply", $thread['id'], "", $thread["title"]);


	if($thread['sticky'])
	{
		$forumList .= "<table class='outline margin width100'>";
		$forumList .= "<tr class='cell1'><td style='border: 1px solid #000; padding:16px' colspan='2'>$posttext</td></tr>";
		$forumList .="</table>";
	}
	else
	{
		$forumList .= "<table class='outline margin width100'>";
		$forumList .= "
		<tr class=\"header1\" >
			<th style='text-align:left;padding:10px;'>".$tags[0]."<br><small style='font-weight:normal;'>by ".UserLink($starter)." on ".$postdate."</th>
		</tr>";
		$forumList .= "<tr class='cell1'><td style='padding:10px'>$posttext</td></tr>";
		$forumList .= "<tr class='cell0'><td style='text-align:right;'>$comments</td></tr>";
		$forumList .="</table>";
	}
}

Write($forumList);

if($pagelinks)
	Write("<div class=\"smallFonts pages\">Pages: {0}</div>", $pagelinks);

if(!$mobileLayout)
{
	echo '</td><td style="background:transparent;border:0 none;vertical-align:top;padding-bottom:1em;"><h2 style="text-align:center;">About</h2>'.$config['homecontent'];
	doLastPosts(true,15);
	echo $config['homefooter'].'</td></tr></table>';
}