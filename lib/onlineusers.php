<?php
if(!defined('DINNER')) die();

function OnlineUsers($forum = 0, $update = true)
{
	global $loguserid;
	$forumClause = "";
	$browseLocation = "online";

	if ($update)
	{
		if ($loguserid)
			Query("UPDATE {users} SET lastforum={0} WHERE id={1}", $forum, $loguserid);
		else
			Query("UPDATE {guests} SET lastforum={0} WHERE ip={1}", $forum, $_SERVER['REMOTE_ADDR']);
	}

	$rOnlineUsers = Query("select u.(_userfields) from {users} u where (lastactivity > {0} or lastposttime > {0}) and loggedin = 1 ".$forumClause." order by name", time()-300, $forum);
	$onlineUserCt = 0;
	$onlineUsers = "";
	while($user = Fetch($rOnlineUsers))
	{
		$user = getDataPrefix($user, "u_");
		$userLink = UserLink($user, true);
		$onlineUsers .= ($onlineUserCt ? ", " : "").$userLink;
		$onlineUserCt++;
	}

	$onlineUsers = Plural($onlineUserCt, "user")." ".$browseLocation.($onlineUserCt ? ": " : ".").$onlineUsers;

	$data = Fetch(Query("select 
		(select count(*) from {guests} where bot=0 and date > {0} $forumClause) as guests,
		(select count(*) from {guests} where bot=1 and date > {0} $forumClause) as bots
		", (time() - 300), $forum));
	
	$ghosts = $data["guests"]+$data["bots"];
	
	if($ghosts)
		$onlineUsers .= " | ".Plural($ghosts,"ghost");

	return $onlineUsers;
}

function getOnlineUsersText()
{
	$onlineUsers = OnlineUsers();

	return "<span id=\"onlineUsers\">$onlineUsers</span>";
}
?>
