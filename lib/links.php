<?php
if(!defined('DINNER')) die();

$ishttps = ($_SERVER['SERVER_PORT'] == 443);
$serverport = ($_SERVER['SERVER_PORT'] == ($ishttps?443:80)) ? '' : ':'.$_SERVER['SERVER_PORT'];

function getRefreshActionLink()
{
	$args = "ajax=1";

	if(isset($_GET["from"]))
		$args .= "&from=".$_GET["from"];

	return actionLink((isset($_GET["page"]) ? $_GET['page'] : 0), (isset($_GET['id']) ? $_GET["id"] : 0), $args);
}

function printRefreshCode()
{
	
}

function urlNamify($urlname)
{
	$urlname = strtolower($urlname);
	$urlname = str_replace("&", "and", $urlname);
	$urlname = preg_replace("/[^a-zA-Z0-9]/", "-", $urlname);
	$urlname = preg_replace("/-+/", "-", $urlname);
	$urlname = preg_replace("/^-/", "", $urlname);
	$urlname = preg_replace("/-$/", "", $urlname);
	return $urlname;
}

$urlNameCache = array();
function setUrlName($action, $id, $urlname)
{
	global $urlNameCache;
	$urlNameCache[$action."_".$id] = $urlname;
}

function actionLink($action, $id="", $args="", $urlname="")
{
	$boardroot = URL_ROOT;
	if($boardroot == "")
		$boardroot = "./";

	$res = "";

	if($action != MAIN_PAGE)
		$res .= "&page=$action";

	if($id != "")
		$res .= "&id=".urlencode($id);
	if($args)
		$res .= "&$args";

	if($res == "")
		return $boardroot;
	else
		return $boardroot."?".substr($res, 1);
}

function actionLinkTag($text, $action, $id=0, $args="", $urlname="", $icon="")
{
	return '<a href="'.htmlentities(actionLink($action, $id, $args, $urlname)).'">'.$text.'</a>';
}
function actionLinkTagItem($text, $action, $id=0, $args="", $urlname="", $icon="")
{
	return '<li><a href="'.htmlentities(actionLink($action, $id, $args, $urlname)).'">'.$text.'</a></li>';
}

function actionLinkTagConfirm($text, $prompt, $action, $id=0, $args="")
{
	return '<a onclick="return confirm(\''.$prompt.'\'); " href="'.htmlentities(actionLink($action, $id, $args)).'">'.$text.'</a>';
}
function actionLinkTagItemConfirm($text, $prompt, $action, $id=0, $args="")
{
	return '<li><a onclick="return confirm(\''.$prompt.'\'); " href="'.htmlentities(actionLink($action, $id, $args)).'">'.$text.'</a></li>';
}

function redirectAction($action, $id=0, $args="", $urlname="")
{
	redirect(actionLink($action, $id, $args, $urlname));
}

function redirect($url)
{
	header("Location: ".$url);
	die();
}

function resourceLink($what)
{
	return URL_ROOT.$what;
}

function themeResourceLink($what)
{
	global $theme;
	return URL_ROOT."themes/$theme/$what";
}

function getMinipicTag($user)
{
	if (!$user['minipic']) return '';
	$pic = str_replace('$root/', DATA_URL, $user['minipic']);
	$minipic = "<img src=\"".htmlspecialchars($pic)."\" alt=\"\" class=\"minipic\" />&nbsp;";
	return $minipic;
}

function userLink($user, $showMinipic = false, $customID = false)
{
	if(!$user['name'])
		return '<span class="userlink nuked">Deleted User</span>';
	
	$class = 'userlink';
	$style = '';
	
	$fgroup = $usergroups[$user['primarygroup']];
	$fname = ($user['displayname'] ? $user['displayname'] : $user['name']);
	$fname = htmlspecialchars($fname);
	$fname = str_replace(" ", "&nbsp;", $fname);
	
	$isbanned = $fgroup['id'] == BANNED_GROUP;
	
	if($isbanned)
		$class .= ' nuked';
	else
	{
		$minipic = "";
		if($showMinipic || ALWAYS_MINIPIC)
			$minipic = getMinipicTag($user);
		
		$fname = $minipic.$fname;
		
		if($user["hascolor"])
		{
			$color = htmlspecialchars($user["color"]);
			if ($color[0] !== "#")
				$color = '#'.$color;
			
			$style = 'style="color:'.$color.';"';
		} else
			$class .= ' defacto';
	}
	
	$title = $user["id"].": ".htmlspecialchars($user['name']);
	if ($user['pronouns'])
		$title .= " (".$user['pronouns'].")";
	
	return actionLinkTag('<span class="'.$class.'" '.$style.' title="'.$title.'">'.$minipic.$fname.'</span>', 'profile', $user['id'], '', $user['name']);
}

function userLinkById($id)
{
	global $userlinkCache;

	if(!isset($userlinkCache[$id]))
	{
		$rUser = Query("SELECT u.(_userfields) FROM {users} u WHERE u.id={0}", $id);
		if(NumRows($rUser))
			$userlinkCache[$id] = getDataPrefix(Fetch($rUser), "u_");
		else
			$userlinkCache[$id] = array('id' => 0, 'name' => "Unknown User", 'sex' => 0, 'primarygroup' => -1);
	}
	return UserLink($userlinkCache[$id]);
}

function makeThreadLink($thread)
{
	$tags = ParseThreadTags($thread["title"]);
	/*setUrlName("thread", $thread["id"], $tags[0]);
	$link = actionLinkTag($tags[0], "thread", $thread["id"], "", $tags[0]);*/
	$link = actionLinkTag($tags[0], 'thread', $thread['id'], '', HasPermission('forum.viewforum',$thread['forum'],true)?$tags[0]:'');
	$tags = $tags[1];

	if (TAGS_DIRECTION == 'Left')
		return $tags." ".$link;
	else
		return $link." ".$tags;
}

function makeFromUrl($url, $from)
{
	if($from == 0)
	{
		$url = preg_replace('@(?:&amp;|&|\?)\w+=$@', '', $url);
		return $url;
	}
	else return $url.$from;
}

function pageLinks($url, $epp, $from, $total)
{
	$url = htmlspecialchars($url);

	if($from < 0) $from = 0;
	if($from > $total-1) $from = $total-1;
	$from -= $from % $epp;

	$numPages = (int)ceil($total / $epp);
	$page = (int)ceil($from / $epp) + 1;

	$first = ($from > 0) ? "<a class=\"pagelink\" href=\"".makeFromUrl($url, 0)."\">&#x00AB;</a> " : "";
	$prev = $from - $epp;
	if($prev < 0) $prev = 0;
	$prev = ($from > 0) ? "<a class=\"pagelink\"  href=\"".makeFromUrl($url, $prev)."\">&#x2039;</a> " : "";
	$next = $from + $epp;
	$last = ($numPages * $epp) - $epp;
	if($next > $last) $next = $last;
	$next = ($from < $total - $epp) ? " <a class=\"pagelink\"  href=\"".makeFromUrl($url, $next)."\">&#x203A;</a>" : "";
	$last = ($from < $total - $epp) ? " <a class=\"pagelink\"  href=\"".makeFromUrl($url, $last)."\">&#x00BB;</a>" : "";

	$pageLinks = array();
	for($p = $page - 5; $p < $page + 5; $p++)
	{
		if($p < 1 || $p > $numPages)
			continue;
		if($p == $page || ($from == 0 && $p == 1))
			$pageLinks[] = "<span class=\"pagelink\">$p</span>";
		else
			$pageLinks[] = "<a class=\"pagelink\"  href=\"".makeFromUrl($url, (($p-1) * $epp))."\">".$p."</a>";
	}

	return $first.$prev.join($pageLinks, "").$next.$last;
}

function pageLinksInverted($url, $epp, $from, $total)
{
	$url = htmlspecialchars($url);

	if($from < 0) $from = 0;
	if($from > $total-1) $from = $total-1;
	$from -= $from % $epp;

	$numPages = (int)ceil($total / $epp);
	$page = (int)ceil($from / $epp) + 1;

	$first = ($from > 0) ? "<a class=\"pagelink\" href=\"".makeFromUrl($url, 0)."\">&#x00BB;</a> " : "";
	$prev = $from - $epp;
	if($prev < 0) $prev = 0;
	$prev = ($from > 0) ? "<a class=\"pagelink\"  href=\"".makeFromUrl($url, $prev)."\">&#x203A;</a> " : "";
	$next = $from + $epp;
	$last = ($numPages * $epp) - $epp;
	if($next > $last) $next = $last;
	$next = ($from < $total - $epp) ? " <a class=\"pagelink\"  href=\"".makeFromUrl($url, $next)."\">&#x2039;</a>" : "";
	$last = ($from < $total - $epp) ? " <a class=\"pagelink\"  href=\"".makeFromUrl($url, $last)."\">&#x00AB;</a>" : "";

	$pageLinks = array();
	for($p = $page + 5; $p >= $page - 5; $p--)
	{
		if($p < 1 || $p > $numPages)
			continue;
		if($p == $page || ($from == 0 && $p == 1))
			$pageLinks[] = "<span class=\"pagelink\">".($numPages+1-$p)."</span>";
		else
			$pageLinks[] = "<a class=\"pagelink\"  href=\"".makeFromUrl($url, (($p-1) * $epp))."\">".($numPages+1-$p)."</a>";
	}

	return $last.$next.join($pageLinks, "").$prev.$first;
}

function absoluteActionLink($action, $id=0, $args="")
{
	global $serverport;
    return ($https?"https":"http") . "://" . $_SERVER['SERVER_NAME'].$serverport.dirname($_SERVER['PHP_SELF']).substr(actionLink($action, $id, $args), 1);
}

function getRequestedURL()
{
    return $_SERVER['REQUEST_URI'];
}

function getServerDomainNoSlash($https = false)
{
	global $serverport;
	return ($https?"https":"http") . "://" . $_SERVER['SERVER_NAME'].$serverport;
}

function getServerURL($https = false)
{
    return getServerURLNoSlash($https)."/";
}

function getServerURLNoSlash($https = false)
{
    global $serverport;
    return ($https?"https":"http") . "://" . $_SERVER['SERVER_NAME'].$serverport . substr(URL_ROOT, 0, strlen(URL_ROOT)-1);
}

function getFullRequestedURL($https = false)
{
    return getServerURL($https) . $_SERVER['REQUEST_URI'];
}

function isHttps()
{
	return isset($_SERVER['HTTPS']) || $_SERVER["SERVER_PORT"] == 443;
}

function getFullURL()
{
	return getFullRequestedURL();
}

?>