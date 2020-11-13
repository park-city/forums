<?php

$pluginSettings = array();
$plugins = array();
$pluginbuckets = array();
$pluginpages = array();

function registerSetting($settingname, $label, $check = false)
{
    // TODO: Make this function.
}

function getSetting($settingname, $useUser = false)
{
	global $pluginSettings, $user;
	if(!$useUser) //loguser
	{
		if(array_key_exists($settingname, $pluginSettings))
			return $pluginSettings[$settingname]["value"];
	}
	else if($user['pluginsettings'] != "");
	{
		$settings = unserialize($user['pluginsettings']);
		if(!is_array($settings))
			return "";
		if(array_key_exists($settingname, $settings))
			return stripslashes(urldecode($settings[$settingname]));
	}
	return "";
}

class BadPluginException extends Exception { }

if($loguser['pluginsettings'] != "")
{
	$settings = unserialize($loguser['pluginsettings']);
	if(!is_array($settings))
		$settings = array();
	foreach($settings as $setName => $setVal)
		if(array_key_exists($setName, $pluginSettings))
			$pluginSettings[$setName]["value"] = stripslashes(urldecode($setVal));
}

?>
