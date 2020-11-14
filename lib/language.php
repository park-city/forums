<?php
if(!defined('DINNER')) die();

$language = "en_US";

setlocale(LC_ALL, "en_US");

$birthdayExample = "December 21, 1949";

$dateformats = array("", "m-d-y", "d-m-y", "y-m-d", "Y-m-d", "m/d/Y", "d.m.y", "M j Y", "D jS M Y");
$timeformats = array("", "h:i A", "h:i:s A", "H:i", "H:i:s");

$months = array(
	"",
	"January",
	"February",
	"March",
	"April",
	"May",
	"June",
	"July",
	"August",
	"September",
	"October",
	"November",
	"December",
);

$days = array(
	"",
	"Sunday",
	"Monday",
	"Tuesday",
	"Wednesday",
	"Thursday",
	"Friday",
	"Saturday",
);

function Plural($i, $s)
{
	if($i == 1)
		return $i." ".$s;

	if(substr($s,-1) == "y") //poopy
		$s = substr($s, 0, strlen($s)-1)."ies"; //poopies
	else if(substr($s,-3) == "tch")
		$s = $s."es"; //match -> matches
	else
		$s .= "s"; //record -> records

	return $i." ".$s;
}

function stringtotimestamp($str)
{
	global $months;
	$parts = explode(" ", $str);
	$day = (int)$parts[1];
	$month = $parts[0];
	$month = str_replace(",", "", $month);
	$year = (int)$parts[2];
	for($m = 1; $m <= 12; $m++)
	{
		if(strcasecmp($month, $months[$m]) == 0)
		{
			$month = $m;
			break;
		}
	}
	if((int)$month != $month)
		return 0;
	return mktime(12,0,0, $month, $day, $year);
}

function timestamptostring($t)
{
	if($t == 0)
		return "";
	return strftime("%B %#d, %Y", $t);
}

function __($english, $flags = 0)
{
	return $english;
}

function importLanguagePack($file)
{
	global $languagePack;
	$f = file_get_contents($file);
	$f = explode("\n", $f);
	for($i = 0; $i < count($f); $i++)
	{
		$k = trim($f[$i]);
		if($k == "" || $k[0] == "#")
			continue;
		$i++;
		$v = trim($f[$i]);
		if($v == "")
			continue;
		$languagePack[$k] = $v;
	}
}

function importPluginLanguagePacks($file)
{
	$pluginsDir = @opendir("plugins");
	if($pluginsDir !== FALSE)
	while(($plugin = readdir($pluginsDir)) !== FALSE)
	{
		if($plugin == "." || $plugin == "..") continue;
		if(is_dir("./plugins/".$plugin))
		{
			$foo = "./plugins/".$plugin."/".$file;
			if(file_exists($foo))
				importLanguagePack($foo);
		}
	}
}

?>
