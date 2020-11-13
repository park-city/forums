<?php
if(!defined('DINNER')) die();

// stolen from Arisotura's system from Blargboard

require __DIR__."/permstrings.php";

$usergroups = array();
$grouplist = array();
$res = Query("SELECT * FROM {usergroups} ORDER BY id");
while ($g = Fetch($res))
{
	$usergroups[$g['id']] = $g;
	$grouplist[$g['id']] = $g['title'];
}

function LoadPermset($res)
{
	$perms = array();
	$permord = array();
	
	while ($perm = Fetch($res))
	{
		if ($perm['value'] == 0) continue;
		
		$k = $perm['perm'];
		if ($perm['arg']) $k .= '_'.$perm['arg'];
		
		if ($perm['ord'] > $permord[$k] || $perms[$k] != -1)
			$perms[$k] = $perm['value'];
		
		$permord[$k] = $perm['ord'];
	}
	
	return $perms;
}

function LoadGroups()
{
	global $usergroups, $loguserid, $loguser, $loguserGroup, $loguserPermset;
	global $guestPerms, $guestGroup, $guestPermset;
	
	$guestGroup = $usergroups[DEFAULT_GROUP];
	$res = Query("SELECT *, 1 ord FROM {permissions} WHERE applyto=0 AND id={0} AND perm IN ({1c})", $guestGroup['id'], $guestPerms);
	$guestPermset = LoadPermset($res);
	
	if (!$loguserid)
	{
		$loguserGroup = $guestGroup;
		$loguserPermset = $guestPermset;
		
		$loguser['banned'] = false;
		$loguser['root'] = false;
		return;
	}
	
	$secgroups = array();
	$loguserGroup = $usergroups[$loguser['powerlevel']];
	
	$res = Query("SELECT groupid FROM {secondarygroups} WHERE userid={0}", $loguserid);
	while ($sg = Fetch($res)) $secgroups[] = $sg['groupid'];
	
	$res = Query("	SELECT *, 1 ord FROM {permissions} WHERE applyto=0 AND id={0}
					UNION SELECT *, 2 ord FROM {permissions} WHERE applyto=0 AND id IN ({1c})
					UNION SELECT *, 3 ord FROM {permissions} WHERE applyto=1 AND id={2}
					ORDER BY ord", 
		$loguserGroup['id'], $secgroups, $loguserid);
	$loguserPermset = LoadPermset($res);
	
	$loguser['banned'] = ($loguserGroup['id'] == BANNED_GROUP);
	$loguser['root'] = ($loguserGroup['id'] == ROOT_GROUP);
}

function HasPermission($perm, $arg=0, $guest=false)
{
	global $guestPermset, $loguserPermset;
	
	$permset = $guest ? $guestPermset : $loguserPermset;

	// check general permission first
	if ($permset[$perm] == -1)
		return false;
		
	$needspecific = !$permset[$perm];
	if ($needspecific && $arg == 0)
		return false;
	
	// then arg-specific permission
	// if it's set to revoke it revokes the general permission
	if ($arg)
	{
		$perm .= '_'.$arg;
		if ($needspecific)
		{
			if ($permset[$perm] != 1)
				return false;
		}
		else
		{
			if ($permset[$perm] == -1)
				return false;
		}
	}
	
	return true;
}

function CheckPermission($perm, $arg=0, $guest=false)
{
	global $loguserid, $loguser;
	
	if (!HasPermission($perm, $arg, $guest))
	{
		if (!$loguserid)
			Kill(__('You must be logged in to perform this action.'));
		else if ($loguser['banned'])
			Kill(__('You may not perform this action because you are banned.'));
		else
			Kill(__('You may not perform this action.'));
	}
}

function ForumsWithPermission($perm, $guest=false)
{
	global $guestPermset, $loguserPermset;
	static $fpermcache = array();
	
	if ($guest)
	{
		$permset = $guestPermset;
		$cperm = 'guest_'.$perm;
	}
	else
	{
		$permset = $loguserPermset;
		$cperm = $perm;
	}
	
	if (isset($fpermcache[$cperm]))
		return $fpermcache[$cperm];
	
	$ret = array();
	
	// if the general permission is set to deny, no need to check for specific permissions
	if ($permset[$perm] == -1)
	{
		$fpermcache[$cperm] = $ret;
		return $ret;
	}
	
	$forumlist = Query("SELECT id FROM {forums}");
	
	// if the general permission is set to grant, we need to check for forums for which it'd be revoked
	// otherwise we need to check for forums for which it'd be granted
	if ($permset[$perm] == 1)
	{
		while ($forum = Fetch($forumlist))
		{
			if ($permset[$perm.'_'.$forum['id']] != -1)
				$ret[] = $forum['id'];
		}
	}
	else
	{
		while ($forum = Fetch($forumlist))
		{
			if ($permset[$perm.'_'.$forum['id']] == 1)
				$ret[] = $forum['id'];
		}
	}

	$fpermcache[$cperm] = $ret;
	return $ret;
}

// retrieves the given permissions for the given users
// retrieves all possible permissions if $perms is left out
function GetUserPermissions($users, $perms=null)
{
	if (is_array($users))
		$userclause = 'IN ({0c})';
	else
		$userclause = '= {0}';
	
	// retrieve all the groups those users belong to
	$allgroups = Query("	
				SELECT powerlevel gid, id uid, 0 type FROM {users} WHERE id {$userclause}
		UNION 	SELECT groupid gid, userid uid, 1 type FROM {secondarygroups} WHERE userid {$userclause}",
		$users);
		
	$primgroups = array();	// primary group IDs
	$secgroups = array();	// secondary group IDs
	$groupusers = array();	// array of user IDs for each group
	
	while ($g = Fetch($allgroups))
	{
		if ($g['type'])
			$secgroups[] = $g['gid'];
		else
			$primgroups[] = $g['gid'];
		
		$groupusers[$g['gid']][] = $g['uid'];
	}
	
	// remove duplicate group IDs. This is faster than using array_unique.
	$primgroups = array_flip(array_flip($primgroups));
	$secgroups = array_flip(array_flip($secgroups));
	
	if (is_array($perms))
		$permclause = 'AND perm IN ({3c})';
	else if ($perms)
		$permclause = 'AND perm = {3}';
	else
		$permclause = '';
	
	// retrieve all the permissions related to those users and groups
	$res = Query("	
				SELECT *, 1 ord FROM {permissions} WHERE applyto=0 AND id IN ({1c}) {$permclause}
		UNION 	SELECT *, 2 ord FROM {permissions} WHERE applyto=0 AND id IN ({2c}) {$permclause}
		UNION 	SELECT *, 3 ord FROM {permissions} WHERE applyto=1 AND id {$userclause} {$permclause}
				ORDER BY ord", 
		$users, $primgroups, $secgroups, $perms);
		
	$permdata = array();
	$permord = array();
	
	// compile all the resulting permission lists for all the requested users
	while ($p = Fetch($res))
	{
		if ($p['value'] == 0) continue;
		
		$k = $p['perm'];
		if ($p['arg']) $k .= '_'.$p['arg'];
		
		if ($p['applyto'] == 0)	// group perm -- apply it to all the matching users
		{
			foreach ($groupusers[$p['id']] as $uid)
			{
				if ($p['ord'] > $permord[$uid][$k] || $permdata[$uid][$k] != -1)
					$permdata[$uid][$k] = $p['value'];
				
				$permord[$uid][$k] = $p['ord'];
			}
		}
		else	// user perm
		{
			$uid = $p['id'];
			
			if ($p['ord'] > $permord[$uid][$k] || $permdata[$uid][$k] != -1)
				$permdata[$uid][$k] = $p['value'];
			
			$permord[$uid][$k] = $p['ord'];
		}
	}
	
	unset($permord);
	return $permdata;
}

LoadGroups();

// Old shit from here on

function CanMod($userid, $fid)
{
	global $loguser;
	// Private messages. You cannot moderate them
	if (!$fid)
		return false;
	if($loguser['powerlevel'] > 1)
		return true;
	if($loguser['powerlevel'] == 1)
	{
		$rMods = Query("select * from {forummods} where forum={0} and user={1}", $fid, $userid);
		if(NumRows($rMods))
			return true;
	}
	return false;
}


function AssertForbidden($to, $specifically = 0)
{
	global $loguser, $forbidden;
	if(!isset($forbidden))
		$forbidden = explode(" ", $loguser['forbiddens']);
	$caught = 0;
	if(in_array($to, $forbidden))
		$caught = 1;
	else
	{
		$specific = $to."[".$specifically."]";
		if(in_array($specific, $forbidden))
			$caught = 2;
	}

	if($caught)
	{
		$not = __("You are not allowed to {0}.");
		$messages = array
		(
			"addRanks" => __("add new ranks"),
			"blockLayouts" => __("block layouts"),
			"deleteComments" => __("delete usercomments"),
			"editCats" => __("edit the forum categories"),
			"editForum" => __("edit the forum list"),
			"editIPBans" => __("edit the IP ban list"),
			"editMods" => __("edit Local Moderator assignments"),
			"editMoods" => __("edit your mood avatars"),
			"editPoRA" => __("edit the PoRA box"),
			"editPost" => __("edit posts"),
			"editProfile" => __("edit your profile"),
			"editSettings" => __("edit the board settings"),
			"editSmilies" => __("edit the smiley list"),
			"editThread" => __("edit threads"),
			"editUser" => __("edit users"),
			"haveCookie" => __("have a cookie"),
			"listPosts" => __("see all posts by a given user"),
			"makeComments" => __("post usercomments"),
			"makeReply" => __("reply to threads"),
			"makeThread" => __("start new threads"),
			"optimize" => __("optimize the tables"),
			"purgeRevs" => __("purge old revisions"),
			"recalculate" => __("recalculate the board counters"),
			"search" => __("use the search function"),
			"sendPM" => __("send private messages"),
			"snoopPM" => __("view other users' private messages"),
			"useUploader" => __("upload files"),
			"viewAdminRoom" => __("see the admin room"),
			"viewAvatars" => __("see the avatar library"),
			"viewCalendar" => __("see the calendar"),
			"viewForum" => __("view fora"),
			"viewLKB" => __("see the Last Known Browser table"),
			"viewMembers" => __("see the memberlist"),
			"viewOnline" => __("see who's online"),
			"viewPM" => __("view private messages"),
			"viewProfile" => __("view user profiles"),
			"viewRanks" => __("see the rank lists"),
			"viewRecords" => __("see the top scores and DB usage"),
			"viewThread" => __("read threads"),
			"viewUploader" => __("see the uploader"),
			"vote" => __("vote"),
		);
		$messages2 = array
		(
			"viewForum" => __("see this forum"),
			"viewThread" => __("read this thread"),
			"makeReply" => __("reply in this thread"),
			"editUser" => __("edit this user"),
		);
		if($caught == 2 && array_key_exists($to, $messages2))
			Kill(format($not, $messages2[$to]), __("Permission denied."));
		Kill(format($not, $messages[$to]), __("Permission denied."));
	}
}

function IsAllowed($to, $specifically = 0)
{
	global $loguser, $forbidden;
	if(!isset($forbidden))
		$forbidden = explode(" ", $loguser['forbiddens']);
	if(in_array($to, $forbidden))
		return FALSE;
	else
	{
		$specific = $to."[".$specifically."]";
		if(in_array($specific, $forbidden))
			return FALSE;
	}
	return TRUE;
}

