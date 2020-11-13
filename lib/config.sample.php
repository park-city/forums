<?php
if(!defined('DINNER')) die();

$debugMode = 1;

$config = array();

$dbserv = 'localhost';
$dbuser = '';
$dbpass = '';
$dbname = ''; 
$dbpref = '';

$salt = "";

// Personalization
define('BOARD_NAME', 'ORYZA');
define('MAIN_PAGE', 'home');
define('DEFAULT_THEME', 'night');
define('META_DESC', '');
define('META_TAGS', '');
define('RSS_TITLE', 'ORYZA');
define('RSS_DESC', 'It kinda works!');

// Settings
define('TAGS_DIRECTION', 'Right');
define('ALWAYS_MINIPIC',  false); // Always show users' minipics!
define('DATE_FORM', 'm-d-y'); // Default date format
define('TIME_FORM', 'g:i A'); // Default time format

// Groups
define('DEFAULT_GROUP', 0);
define('ROOT_GROUP', 4);
define('BANNED_GROUP', -1);

// Forums
define('NEWS_FORUM',  7); // Set to 0 to disable.
define('TRASH_FORUM', 5);

// Features
define('ENABLE_SYNDROMES',true);
define('ENABLE_UPLOADER', true);
define('ENABLE_WIKI',     true);

define('HOME_CONTENT',
'
			<table class="outline margin mw700">
				<tr><th>ORYZA</th></tr>
				<tr>
					<td style="padding:16px;">
						This is the experimental prototype board of the future. Running off state-of-the-art Acmlmboard XD technology from 2014, ORYZA offers limitless potential!<br><br>
						Though if you want to post without bursting into flames, <a href="https://forum.park-city.club/">Park City</a> is a safer option.
					</td>
				</tr>
			</table>
			<table class="outline margin mw700">
				<tr>
					<td style="padding:16px;">
						Also check out <a href="http://comic.oryza.xyz">Where There\'s Smoke</a>!
					</td>
				</tr>
			</table>
');

define('HOME_FOOTER',
'
			
');

# unused
/*
$config['customTitleThreshold'] = 0;
$config['oldThreadThreshold'] = 3;
$config['viewcountInterval'] = 10000;
$config['ajax'] = true;
$config['guestLayouts'] = 1; // Show guests post layouts?
$config['breadcrumbsMainName'] = $config['boardname'];
$config['menuMainName'] = 'Main';
$config['mailResetSender'] = '';
$config['defaultLayout'] = '';
$config['showGender'] = 1;
$config['defaultLanguage'] = 'en_US';
$config['floodProtectionInterval'] = 1;
$config['nofollow'] = 0; // rel=nofollow
$config['showExtraSidebar'] = 1;
$config['showPoRA'] = 0;
$config['PoRATitle'] = 'News';
$config['PoRAText'] = 'Welcome to ABXD! Change this.';*/

# fix later

$config['boardname'] = BOARD_NAME;
$config['theme'] = DEFAULT_THEME;
$config['mainpage'] = MAIN_PAGE;
$config['metadesc'] = META_DESC;
$config['metatags'] = META_TAGS;
$config['newsforum'] = NEWS_FORUM;
$config['trashforum'] = TRASH_FORUM;
$config['homecontent'] = HOME_CONTENT;
$config['homefooter'] = HOME_FOOTER;
$config['tagsDirection'] = TAGS_DIRECTION;
$config['enableUploader'] = ENABLE_UPLOADER;
$config['enableWiki'] = ENABLE_WIKI;

$mainPage = MAIN_PAGE;

# dumb hack hour
define('FULL_FORM', DATE_FORM.' '.TIME_FORM);

?>