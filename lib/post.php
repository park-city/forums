<?php

include_once("write.php");

function ParseThreadTags($title)
{
	preg_match_all("/\[(.*?)\]/", $title, $matches);
	foreach($matches[1] as $tag)
	{
		$title = str_replace("[".$tag."]", "", $title);
		$tag = htmlspecialchars(strtolower($tag));

		//Start at a hue that makes "18" red.
		$hash = -105;
		for($i = 0; $i < strlen($tag); $i++)
			$hash += ord($tag[$i]);

		//That multiplier is only there to make "nsfw" and "18" the same color.
		$color = "hsl(".(($hash * 57) % 360).", 70%, 40%)";

		$tags .= "<span class=\"threadTag\" style=\"background-color: ".$color.";\">".$tag."</span>";
	}
	if($tags)
		$tags = " ".$tags;

	$title = str_replace("<", "&lt;", $title);
	$title = str_replace(">", "&gt;", $title);
	return array(trim($title), $tags);
}

function filterPollColors($input)
{
	return preg_replace("@[^#0123456789abcdef]@si", "", $input);
}

function loadBlockLayouts()
{
	global $blocklayouts, $loguserid;

	if(isset($blocklayouts))
		return;

	$rBlocks = Query("select * from {blockedlayouts} where blockee = {0}", $loguserid);
	$blocklayouts = array();

	while($block = Fetch($rBlocks))
		$blocklayouts[$block['user']] = 1;
}

function getSyndrome($activity)
{
	include("syndromes.php");
	$soFar = "";
	foreach($syndromes as $minAct => $syndrome)
		if($activity >= $minAct)
			$soFar = '<span class="'.$syndrome[1].'">'.$syndrome[0].'</span><br>';
	return $soFar;
}

function applyTags($text, $tags)
{
	if(!stristr($text, "&"))
		return $text;
	$s = $text;
	foreach($tags as $tag => $val)
		$s = str_replace("&".$tag."&", $val, $s);
	if(is_numeric($tags['postcount']))
		$s = preg_replace_callback('@&(\d+)&@si', array(new MaxPosts($tags), 'max_posts_callback'), $s);
	else
		$s = preg_replace("'&(\d+)&'si", "preview", $s);
	return $s;
}

// hax for anonymous function
class MaxPosts {
    var $tags;
    function __construct($tags) {
        $this->tags = $tags;
    }

    function max_posts_callback($results) {
        return max($results[1] - $this->tags['postcount'], 0);
    }
}

$activityCache = array();
function getActivity($id)
{
	global $activityCache;

	if(!isset($activityCache[$id]))
		$activityCache[$id] = FetchResult("select count(*) from {posts} where user = {0} and date > {1}", $id, (time() - 86400));

	return $activityCache[$id];
}

$layouCache = array();

function makePostText($post)
{
	global $loguser, $loguserid, $layoutCache, $blocklayouts, $mobileLayout;

	LoadBlockLayouts();
	$poster = getDataPrefix($post, "u_");
	$isBlocked = $poster['globalblock'] || $loguser['blocklayouts'] || $post['options'] & 1 || isset($blocklayouts[$poster['id']]);

	$noSmilies = $post['options'] & 2;
	$noBr = $post['options'] & 4;

	//Do Ampersand Tags
	$tags = array
	(
//This tag breaks because of layout caching.
//		"postnum" => $post['num'],
		"postcount" => $poster['posts'],
		"numdays" => floor((time()-$poster['regdate'])/86400),
		"date" => formatdate($post['date']),
		"rank" => GetRank($poster["rankset"], $poster["posts"]),
	);

	$postText = $post['text'];
	$postText = ApplyTags($postText, $tags);
	$postText = CleanUpPost($postText, $poster['name'], $noSmilies);

	//Post header and footer.
	$magicString = "###POSTTEXTGOESHEREOMG###";
	$separator = "";

	if($isBlocked || $mobileLayout)
		$postLayout = $magicString;
	else
	{
		if(!isset($layoutCache[$poster["id"]]))
		{
			$postLayout = $poster['postheader'].$magicString.$poster['signature'];
			$postLayout = ApplyTags($postLayout, $tags);
			$postLayout = CleanUpPost($postLayout, $poster['name']);
			$layoutCache[$poster["id"]] = $postLayout;
		}
		else
			$postLayout = $layoutCache[$poster["id"]];

		if($poster['signature'])
			if(!$poster['signsep'])
				$separator = "<br>_________________________<br>";
			else
				$separator = "<br>";
	}

	$postText = str_replace($magicString, $postText.$separator, $postLayout);
	return $postText;
}

define('POST_NORMAL', 0);			// standard post box
define('POST_PM', 1);				// PM post box
define('POST_DELETED_SNOOP', 2);	// post box with close/undelete (for mods 'view deleted post' feature)
define('POST_SAMPLE', 3);			// sample post box (profile sample post, newreply post preview, etc)
define('POST_PROFILE', 4);

function makePostLinks($post, $type, $params=array())
{
	global $loguser, $loguserid, $mobileLayout;

	if($params['lynx'])
	{
		$links = $params['lynx'];
	}
	else
	{
		$forum = $params['fid'];
		$thread = $params['tid'];
		$canMod = CanMod($loguserid, $forum);
		$canReply = ($canMod || (!$post['closed'] && $loguser['powerlevel'] > -1)) && $loguserid;

		$links = new PipeMenu();
		
		if($post['deleted'])
		{
			if($canMod || ($post["u_id"] == $loguserid && $post["deletedby"] == $loguserid))
				$links->add(new PipeMenuLinkEntry('Restore', "", "", "", "undo", "deletePost(".$post["id"].", '".$loguser["token"]."', 2);return false;"));

			if($canMod || $post["u_id"] == $loguserid)
			{
				if($type == POST_DELETED_SNOOP)
					$links->add(new PipeMenuLinkEntry('Close', "", "", "", "chevron-up", "replacePost(".$post['id'].", false); return false;"));
				else
					$links->add(new PipeMenuLinkEntry('View', "", "", "", "chevron-down", "replacePost(".$post['id'].", true); return false;"));
			}
		}
		else
		{
			$links->add(new PipeMenuLinkEntry('Link', "post", $post['id'], "", "link"));

			if ($canReply && !$params['noreplylinks'])
				$links->add(new PipeMenuLinkEntry('Reply', "newreply", $thread, "quote=".$post['id'], "quote-left"));
	
			if ($canMod || ($post['user'] == $loguserid && $loguser['powerlevel'] > -1 && !$post['closed']))
				$links->add(new PipeMenuLinkEntry('Edit', "editpost", $post['id'], "", "pencil"));

			if ($post['user'] == $loguserid && $loguser['powerlevel'] > -1)
				$links->add(new PipeMenuLinkEntry('Delete', "", "", "", "remove", "deletePost(".$post["id"].", '".$loguser["token"]."', 1);return false;"));
			else if ($canMod)
				$links->add(new PipeMenuLinkEntry('Delete', "", "", "", "remove", "deletePost(".$post["id"].", '".$loguser["token"]."', 1);return false;"));

			if(!$mobileLayout && $type != 3)
			{
				$links->add(new PipeMenuTextEntry(format('#{0}', $post['id'])));

				if ($canMod)
					$links->add(new PipeMenuTextEntry($post['ip']));
			}
		}
	}

	return $links;
}

// $post: post data (typically returned by SQL queries or forms)
// $type: one of the POST_XXX constants
// $params: an array of extra parameters, depending on the post box type. Possible parameters:
//		* tid: the ID of the thread the post is in (POST_NORMAL and POST_DELETED_SNOOP only)
//		* fid: the ID of the forum the thread containing the post is in (POST_NORMAL and POST_DELETED_SNOOP only)
// 		* threadlink: if set, a link to the thread is added next to 'Posted on blahblah' (POST_NORMAL and POST_DELETED_SNOOP only)
//		* noreplylinks: if set, no links to newreply.php (Quote/ID) are placed in the metabar (POST_NORMAL only)
//		* forcepostnum: if set, forces sidebar to show "Posts: X/X" (POST_SAMPLE only)
//		* metatext: if non-empty, this text is displayed in the metabar instead of 'Sample post' (POST_SAMPLE only)
function makePost($post, $type, $params=array())
{
	global $loguser, $loguserid, $blocklayouts, $dataDir, $dataUrl, $mobileLayout;
	
	$sideBarStuff = "";
	$poster = getDataPrefix($post, "u_");
	LoadBlockLayouts();
	$isBlocked = $poster['globalblock'] || $loguser['blocklayouts'] || $post['options'] & 1 || isset($blocklayouts[$poster['id']]);

	if(!$poster['name']) $voiduser = true;

	$links = makePostLinks($post, $type, $params);
	
	$pictureUrl = '';
	$pronouns = '';
	
	// avatar
	
	if($post['mood'] > 0)
	{
		if(file_exists("${dataDir}avatars/".$poster['id']."_".$post['mood']))
			$pictureUrl = "${dataUrl}avatars/".$poster['id']."_".$post['mood'];
	}
	else
	{
		if($poster["picture"] == "#INTERNAL#")
			$pictureUrl = "${dataUrl}avatars/".$poster['id'];
		else if($poster["picture"])
			$pictureUrl = $poster["picture"];
	}
	
	// post count
	
	if(!$params['forcepostnum'] && $type == POST_SAMPLE)
		$metaposts = $poster['posts'];
	else
		$metaposts = $post['num']."/".$poster['posts'];
		
	// mobile shit
	
	if($mobileLayout) {
		$links->setClass("toolbarMenu");
		
		if($pictureUrl)
			$picture = '<a href="/?page=profile&id='.$poster['id'].'"><img src="'.htmlspecialchars($pictureUrl).'"></a>';
		else
			$picture = '';
			
		if($poster['pronouns'])
			$pronouns = '<span class="lower"> - '.$poster['pronouns'].'</span>';
	}
	
	// Unfortunately, people who do not exist cannot have pronouns.
	if(!$voiduser)
		$mobilehead = $pronouns.' - '.$poster['posts'].' posts';
	else
		$mobilehead = '';
	
	// ====================
	// DELETED POSTS
	
	if($post['deleted'] && $type == POST_NORMAL)
	{
		$meta = '';
		
		if(!$mobileLayout)
			$meta = 'Posted on ';
			
		$meta .= formatdate($post['date']).', deleted';
		if ($post['deletedby'])
		{
			$db_link = UserLink(getDataPrefix($post, "du_"));
			$meta .= ' by '.$db_link;

			if ($post['reason'])
				$meta .= ': '.htmlspecialchars($post['reason']);
		}
		
		if($mobileLayout) {
			echo '
				<table class="outline margin" id="post'.$post['id'].'">
					<tr>
						<td class="cell2 mobile-postheader deleted" id="dyna_'.$post['id'].'">
							'.$links->build(2).'
							<div class="smallFonts">'.userLink($poster).$mobilehead.'<br>'.$meta.'</div>
						</td>
					</tr>
				</table>';
			return;
		} else {
			echo "
				<table class=\"outline margin post\" id=\"post{$post['id']}\">
					<tr>
						<td class=\"side userlink\">
							".userLink($poster)."
						</td>
						<td class=\"smallFonts meta right\">
							<div style=\"float:left\">
								$meta
							</div>
							".$links->build()."
						</td>
					</tr>
				</table>";
			return;
		}
	}
	
	// ====================
	// LIVING POSTS

	if ($type == POST_SAMPLE)
		$meta = $params['metatext'] ? $params['metatext'] : 'Sample post';
	else
	{
		$forum = $params['fid'];
		$thread = $params['tid'];
		$canMod = CanMod($loguserid, $forum);
		$canReply = ($canMod || (!$post['closed'] && $loguser['powerlevel'] > -1)) && $loguserid;
		
		$meta = '';
		
		if(!$mobileLayout)
			$meta = 'Posted on ';
			
		$meta .= formatdate($post['date']);

		// threadlinks for listpost.php
		if ($params['threadlink'])
		{
			$thread = array();
			$thread["id"] = $post["thread"];
			$thread["title"] = $post["threadname"];

			$meta .= " in ".makeThreadLink($thread);
		}

		// revisions
		if($post['revision'])
		{
			if($post['revuser'] && !$mobileLayout)
			{
				$ru_link = UserLink(getDataPrefix($post, "ru_"));
				$revdetail = ' by '.$ru_link.' on '.formatdate($post['revdate']);
			}
			else
				$revdetail = '';
			
			// TODO make actually work on mobile layout. javascript issue
			if ($canMod)
				$meta .= " (<a href=\"javascript:void(0);\" onclick=\"showRevisions(".$post['id'].")\">".format(__("rev. {0}"), $post['revision'])."</a>".$revdetail.")";
			else
				$meta .= " (".format(__("rev. {0}"), $post['revision']).$revdetail.")";
		}
	}

	// POST SIDEBAR

	if(!$mobileLayout)
	{
		if($voiduser)
		{
			$pTable = "table0 safe";
			$row1 = "row0_1 safe";
			$row2 = "row0_2 safe";
			$topBar1 = "topbar0_1 safe";
			$topBar2 = "topbar0_2 safe";
			$sideBar = "sidebar0 safe";
			$mainBar = "mainbar0 safe";

			$sideBarStuff = 'This user does not exist.';
		}
		else
		{
			if($poster['rankset'] && $poster['rankset'] != 'levels')
				$sideBarStuff .= GetRank($poster["rankset"], $poster["posts"])."<br>";
				
			if($pictureUrl)
				$sideBarStuff .= "<a href=\"/?page=profile&id=".$poster['id']."\"><img style=\"max-width:150px;\" src=\"".htmlspecialchars($pictureUrl)."\" alt=\"\" /></a><br>";

			$sideBarStuff .= GetRank('levels', $poster["posts"])."<br>";
			$sideBarStuff .= GetSyndrome(getActivity($poster["id"]));
			
			$lastpost = ($poster['lastposttime'] ? timeunits(time() - $poster['lastposttime']) : "none");
			$lastview = timeunits(time() - $poster['lastactivity']);
			
			if($poster['pronouns'])
				$sideBarStuff .='<br>Pronouns: <span class="lower">'.$poster['pronouns'].'</span>';

			$sideBarStuff .= "<br>Posts: ".$metaposts;

			$sideBarStuff .= "<br>Joined: ".cdate($loguser['dateformat'], $poster['regdate'])."<br />";

			$sideBarStuff .= "<br>Last post: ".$lastpost;
			$sideBarStuff .= "<br>Last view: ".$lastview;
			
			if(!$isBlocked)
			{
				$pTable = "table".$poster['id'];
				$row1 = "row".$poster['id']."_1";
				$row2 = "row".$poster['id']."_2";
				$topBar1 = "topbar".$poster['id']."_1";
				$topBar2 = "topbar".$poster['id']."_2";
				$sideBar = "sidebar".$poster['id'];
				$mainBar = "mainbar".$poster['id'];

				if($poster['postheader'])
				{
					$pTable .= " safe";
					$row1 .= " safe";
					$row2 .= " safe";
					$topBar1 .= " safe";
					$topBar2 .= " safe";
					$sideBar .= " safe";
					$mainBar .= " safe";
				}
			}
		}
	}

	// OTHER STUFF

	if($type == POST_NORMAL)
		$anchor = "<i name=\"".$post['id']."\"></i>";

	$postText = makePostText($post);

	// PRINT THE POST!

	if($mobileLayout)
		echo '
				<table class="outline margin" id="post'.$post['id'].'">
					<tr>
						<td class="cell2 mobile-postheader" id="dyna_'.$post['id'].'">
							'.$links->build(2).'
							'.$picture.'<div class="smallFonts">'.userLink($poster).$mobilehead.'<br>'.$meta.'</div>
						</td>
					</tr>
					<tr>
						<td colspan=3 class="cell0 mobile-postbox">
							'.$postText.'
						</td>
					</tr>
				</table>';
	else
		echo "
			<table class=\"post margin $pTable\" id=\"post${post['id']}\">
				<tr class=\"$row1\">
					<td class=\"side userlink $topBar1\">
						$anchor
						".UserLink($poster)."
					</td>
					<td class=\"meta right $topBar2\">
						<div style=\"float: left;\" id=\"meta_${post['id']}\">
							$meta
						</div>
						<div style=\"float: left; text-align:left; display: none;\" id=\"dyna_${post['id']}\">
							Hi.
						</div>
						" . $links->build() . "
					</td>
				</tr>
				<tr class=\"".$row2."\">
					<td class=\"side $sideBar\">
						<div class=\"smallFonts\">
							$sideBarStuff
						</div>
					</td>
					<td class=\"post $mainBar\" id=\"post_${post['id']}\">
						<div>
							$postText
						</div>
					</td>
				</tr>
			</table>";
}
?>