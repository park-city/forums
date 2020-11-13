<?php

AssertForbidden("listPosts");

if(!isset($_GET['id']))
	Kill(__("User ID unspecified."));

$id = (int)$_GET['id'];

$rUser = Query("select * from {users} where id={0}", $id);
if(NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill(__("Unknown user ID."));

$total = FetchResult("
			SELECT
				count(p.id)
			FROM
				{posts} p
				LEFT JOIN {threads} t ON t.id=p.thread
				LEFT JOIN {forums} f ON f.id=t.forum
			WHERE p.user={0} AND ".forumAccessControlSql(),
		$id);

$ppp = $loguser['postsperpage'];
if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;

if(!$ppp) $ppp = 25;


$rPosts = Query("
	SELECT
		p.*,
		pt.text, pt.revision, pt.user AS revuser, pt.date AS revdate,
		u.(_userfields), u.(rankset,title,picture,posts,postheader,signature,signsep,lastposttime,lastactivity,regdate,globalblock),
		ru.(_userfields),
		du.(_userfields),
		t.id thread, t.title threadname,
		f.id fid
	FROM
		{posts} p
		LEFT JOIN {posts_text} pt ON pt.pid = p.id AND pt.revision = p.currentrevision
		LEFT JOIN {users} u ON u.id = p.user
		LEFT JOIN {users} ru ON ru.id=pt.user
		LEFT JOIN {users} du ON du.id=p.deletedby
		LEFT JOIN {threads} t ON t.id=p.thread
		LEFT JOIN {forums} f ON f.id=t.forum
	WHERE u.id={1} AND ".forumAccessControlSql()."
	ORDER BY date ASC LIMIT {2u}, {3u}", 
	$loguserid, $id, $from, $ppp);

$numonpage = NumRows($rPosts);

$uname = $user["name"];
if($user["displayname"])
	$uname = $user["displayname"];

$title = $uname."'s posts";

$crumbo = array();
$crumbo['Members'] = actionLink('memberlist');
$crumbo[$uname] = actionLink("profile", $user['id']);
$crumbo['Posts'] = '';
$layout_crumbs = MakeCrumbs($crumbo);

if($total == 0)
	Kill("This user hasn't made any posts.");

$pagelinks = PageLinks(actionLink("listposts", $id, "from="), $ppp, $from, $total);

if($pagelinks)
	write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);

if(NumRows($rPosts))
{
	while($post = Fetch($rPosts))
		MakePost($post, POST_NORMAL, array('threadlink'=>1, 'tid'=>$post['thread'], 'fid'=>$post['fid'], 'noreplylinks'=>1));
}

if($pagelinks)
	write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);

?>
