<?php
if(!defined('DINNER')) die();

$settings = array(
	"boardname" => array (
		"type" => "text",
		"default" => "Acmlmboard XD",
		"name" => "Board name"
	),
	"metaDescription" => array (
		"type" => "text",
		"default" => "",
		"name" => "Meta description"
	),
	"metaTags" => array (
		"type" => "text",
		"default" => "",
		"name" => "Meta tags"
	),
	"dateformat" => array (
		"type" => "text",
		"default" => "m-d-y",
		"name" => "Default date format"
	),
	"timeformat" => array (
		"type" => "text",
		"default" => "g:i A",
		"name" => "Default time format"
	),
	"mainPage" => array (
		"type" => "text",
		"default" => "board",
		"name" => "Main page"
	),
	"alwaysminipic" => array (
		"type" => "boolean",
		"default" => "0",
		"name" => "Always show minipics"
	),
	"guestLayouts" => array (
		"type" => "boolean",
		"default" => "1",
		"name" => "Show post layouts to guests"
	),
	"ajax" => array (
		"type" => "boolean",
		"default" => "1",
		"name" => "Enable AJAX"
	),
	"enableSyndromes" => array(
		"type" => "boolean",
		"default" => "1",
		"name" => "Enable syndromes",
		"help" => "User titles that appear during posting sprees"
	),
	"enableUploader" => array(
		"type" => "boolean",
		"default" => "0",
		"name" => "Enable user file uploader"
	),
	"enableWiki" => array(
		"type" => "boolean",
		"default" => "0",
		"name" => "Enable wiki"
	),
	"defaultTheme" => array (
		"type" => "theme",
		"default" => "garbg",
		"name" => "Default theme",
	),
	"floodProtectionInterval" => array (
		"type" => "integer",
		"default" => "10",
		"name" => "Minimum time between user posts"
	),
	"nofollow" => array (
		"type" => "boolean",
		"default" => "0",
		"name" => "Add rel=nofollow to all user-posted links"
	),
	"tagsDirection" => array (
		"type" => "options",
		"options" => array('Left' => 'Left', 'Right' => 'Right'),
		"default" => 'Right',
		"name" => "Direction of thread tags.",
	),

	"newsForum" => array (
		"type" => "forum",
		"default" => "7",
		"name" => "News forum",
	),
	"trashForum" => array (
		"type" => "forum",
		"default" => "5",
		"name" => "Trash forum",
	),
	
	"rssTitle" => array (
		"type" => "text",
		"default" => "",
		"name" => "RSS title",
		"help" => "Keep blank to disable RSS"
	),
	"rssDesc" => array(
		"type" => "text",
		"default" => "",
		"name" => "RSS description"
	),
);
?>