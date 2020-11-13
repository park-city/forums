<?php
if(!defined('DINNER')) die();

function endsWith($a, $b){
	return substr($a, strlen($a) - strlen($b)) == $b;
}

function endsWithIns($a, $b){
	return endsWith(strtolower($a), strtolower($b));
}

function startsWith($a, $b){
	return substr($a, 0, strlen($b)) == $b;
}

function startsWithIns($a, $b){
	return startsWith(strtolower($a), strtolower($b));
}

function TimeUnits($sec)
{
	if($sec <    60) return "$sec sec.";
	if($sec <  3600) return floor($sec/60)." min.";
	if($sec < 86400) return floor($sec/3600)." hour".($sec >= 7200 ? "s" : "");
	return floor($sec/86400)." day".($sec >= 172800 ? "s" : "");
}

function cdate($format, $date = 0)
{
	global $loguser;
	//$format = 'g:i A - F j, Y';
	if($date == 0)
		$date = time();
	$hours = (int)($loguser['timezone']/3600);
	$minutes = floor(abs($loguser['timezone']/60)%60);
	$plusOrMinus = $hours < 0 ? "" : "+";
	$timeOffset = $plusOrMinus.$hours." hours, ".$minutes." minutes";
	return gmdate($format, strtotime($timeOffset, $date));
}

function Report($stuff, $hidden = 0, $severity = 0)
{

}

function SendSystemPM($to, $message, $title)
{

}

function Shake()
{
	$cset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
	$salt = "";
	$chct = strlen($cset) - 1;
	while (strlen($salt) < 16)
		$salt .= $cset[mt_rand(0, $chct)];
	return $salt;
}

function IniValToBytes($val)
{
	$val = trim($val);
	$last = strtolower($val[strlen($val)-1]);
	switch($last)
	{
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
	}

	return $val;
}

function BytesToSize($size, $retstring = '%01.2f&nbsp;%s')
{
	$sizes = array('B', 'KiB', 'MiB');
	$lastsizestring = end($sizes);
	foreach($sizes as $sizestring)
	{
		if($size < 1024)
			break;
		if($sizestring != $lastsizestring)
			$size /= 1024;
	}
	if($sizestring == $sizes[0])
		$retstring = '%01d %s'; // Bytes aren't normally fractional
	return sprintf($retstring, $size, $sizestring);
}

function makeThemeArrays()
{
	global $themes, $themefiles;
	$themes = array();
	$themefiles = array();
	$dir = @opendir("themes");
	while ($file = readdir($dir))
	{
		if ($file != "." && $file != "..")
		{
			$themefiles[] = $file;
			$name = explode("\n", @file_get_contents("./themes/".$file."/themeinfo.txt"));
			$themes[] = trim($name[0]);
		}
	}
	closedir($dir);
}

function getdateformat()
{
	global $loguserid, $loguser;

	if($loguserid)
		return $loguser['dateformat'].", ".$loguser['timeformat'];
	else
		return FULL_FORM;
}

function formatdate($date)
{
	return cdate(getdateformat(), $date);
}
function formatdatenow()
{
	return cdate(getdateformat());
}

function formatBirthday($b)
{
	return format("{0} ({1} old)", cdate("F j, Y", $b), Plural(floor((time() - $b) / 86400 / 365.2425), "year"));
}

//TODO Add caching if it's too slow.
function formatIP($ip)
{
	global $loguser;

	$res = $ip;
	$res .=  " " . IP2C($ip);
	$res = "<nobr>$res</nobr>";
	if(HasPermission('admin.ipsearch'))
		return actionLinkTag($res, "ipquery", $ip);
	else
		return $res;
}

function ip2long_better($ip)
{ 
	$v = explode('.', $ip); 
	return ($v[0]*16777216)+($v[1]*65536)+($v[2]*256)+$v[3];
}

//TODO: Optimize it so that it can be made with a join in online.php and other places.
function IP2C($ip)
{
	global $dblink;
	//This nonsense is because ips can be greater than 2^31, which will be interpreted as negative numbers by PHP.
	$ipl = ip2long($ip);
	$r = Fetch(Query("SELECT * 
				 FROM {ip2c}
				 WHERE ip_from <= {0s} 
				 ORDER BY ip_from DESC
				 LIMIT 1", 
				 sprintf("%u", $ipl)));

	if($r && $r["ip_to"] >= ip2long_better($ip))
		return " <img src=\"".resourceLink("img/flags/".strtolower($r['cc']).".png")."\" alt=\"".$r['cc']."\" title=\"".$r['cc']."\" />";
	else
		return "";
}

function getBirthdaysText($ret = true)
{
	global $luckybastards, $loguser;
	
	$luckybastards = array();
	$today = gmdate('m-d', time()+$loguser['timezone']);
	
	$rBirthdays = Query("select u.birthday, u.(_userfields) from {users} u where u.birthday > 0 and u.primarygroup!={0} order by u.name", Settings::get('bannedGroup'));
	$birthdays = array();
	while($user = Fetch($rBirthdays))
	{
		$b = $user['birthday'];
		if(gmdate("m-d", $b) == $today)
		{
			$luckybastards[] = $user['u_id'];
			if ($ret)
			{
				$y = gmdate("Y") - gmdate("Y", $b);
				$birthdays[] = UserLink(getDataPrefix($user, 'u_'))." (".$y.")";
			}
		}
	}
	if (!$ret) return '';
	if(count($birthdays))
		$birthdaysToday = implode(", ", $birthdays);
	if(isset($birthdaysToday))
		return __("Birthdays today:")." ".$birthdaysToday;
	else
		return "";
}

function getKeywords($stuff)
{
	$common = array('the', 'and', 'that', 'have', 'for', 'not', 'this');
	
	$stuff = strtolower($stuff);
	$stuff = str_replace('\'s', '', $stuff);
	$stuff = preg_replace('@[^\w\s]+@', '', $stuff);
	$stuff = preg_replace('@\s+@', ' ', $stuff);
	
	$stuff = explode(' ', $stuff);
	$stuff = array_unique($stuff);
	$finalstuff = '';
	foreach ($stuff as $word)
	{
		if (strlen($word) < 3 && !is_numeric($word)) continue;
		if (in_array($word, $common)) continue;
		
		$finalstuff .= $word.' ';
	}

	return substr($finalstuff,0,-1);
}

function forumRedirectURL($redir)
{
	if ($redir[0] == ':')
	{
		$redir = explode(':', $redir);
		return actionLink($redir[1], $redir[2], $redir[3], $redir[4]);
	}
	else
		return $redir;
}

function doStats()
{
	$statData = Fetch(Query("SELECT
	(SELECT COUNT(*) FROM {threads}) AS numThreads,
	(SELECT COUNT(*) FROM {posts}) AS numPosts,
	(SELECT COUNT(*) FROM {users}) AS numUsers,
	(select count(*) from {posts} where date > {0}) AS newToday,
	(select count(*) from {posts} where date > {1}) AS newLastHour,
	(select count(*) from {users} where lastposttime > {2}) AS numActive",
	time() - 86400, time() - 3600, time() - 1209600));

	$percent = $statData["numUsers"] ? ceil((100 / $statData["numUsers"]) * $statData["numActive"]) : 0;
	
	$stats = $statData["numThreads"].' threads and '.$statData["numPosts"].' posts total, '.$statData["newToday"].' posts today ('.$statData["newLastHour"].' last hour)<br>'.$statData["numUsers"].' users, '.$statData["numActive"].' active ('.$percent.'%) - '.number_format($misc['views']).' views';
	
	return $stats;
}

?>
