<?php
define('DINNER', 'BLASTER');
$starttime = microtime(true);

$ajaxPage = false;
if(isset($_GET["ajax"])) $ajaxPage = true;

require(__DIR__.'/lib/common.php');

$useBuffering = true;

if(isset($argv))
{
	$_GET = array();
	$_GET["page"] = $argv[1];
	
	$_SERVER = array();
	$_SERVER["REMOTE_ADDR"] = "0.0.0.0";
	
	$ajaxPage = true;
	$useBuffering = false;
}

if (isset($_GET['page']))
	$page = $_GET["page"];
else
	$page = Settings::get('mainPage');
if(!ctype_alnum($page))
	$page = Settings::get('mainPage');

if($page == Settings::get('mainPage'))
{
	if(isset($_GET['fid']) && (int)$_GET['fid'] > 0 && !isset($_GET['action']))
		die(header("Location: ".actionLink("forum", (int)$_GET['fid'])));
	if(isset($_GET['tid']) && (int)$_GET['tid'] > 0)
		die(header("Location: ".actionLink("thread", (int)$_GET['tid'])));
	if(isset($_GET['uid']) && (int)$_GET['uid'] > 0)
		die(header("Location: ".actionLink("profile", (int)$_GET['uid'])));
	if(isset($_GET['pid']) && (int)$_GET['pid'] > 0)
		die(header("Location: ".actionLink("post", (int)$_GET['pid'])));
}

define('CURRENT_PAGE', $page);

if($useBuffering)
	ob_start();

try {
	try {
		$page = 'pages/'.$page.'.php';
		if(!file_exists($page))
			throw new Exception(404);
		include($page);
	}
	catch(Exception $e) {
		if ($e->getMessage() != 404)
		{
			throw $e;
		}
		require(__DIR__.'/pages/404.php');
	}
}
catch(KillException $e) {
}

if($ajaxPage) {	
	if($useBuffering) {
		header("Content-Type: text/plain");
		ob_end_flush();
	}
		
	die();
}

$layout_contents = ob_get_contents();
ob_end_clean();

$rViewCounter = Query("update {misc} set views = views + 1");
$misc['views']++;

setLastActivity();

ob_start();
ob_end_clean();

$layout_title = Settings::get('boardname');
if($title != "") $layout_title .= " &raquo; ".$title;

// HEADER

$banners = glob('img/banner/*.*');
$banner = array_rand($banners);

$layout_navigation  = actionLinkTagItem('Home', Settings::get('mainPage'));
if(Settings::get('mainPage') != 'board')
	$layout_navigation .= actionLinkTagItem('Forums', 'board');
$layout_navigation .= actionLinkTagItem('Members', 'memberlist');
$layout_navigation .= actionLinkTagItem('Feed', 'lastposts');
$layout_navigation .= actionLinkTagItem('Ranks', 'ranks');
$layout_navigation .= '<li><a href="https://library.park-city.club/">Library</a></li>';
$layout_navigation .= '<li><a href="https://radio.park-city.club/">Radio</a></li>';
$layout_navigation .= '<br>';

if($loguserid)
{
	$layout_navigation .= '<li>'.userLink($loguser).'</li>';
	$layout_navigation .= actionLinkTagItem('Edit profile', 'editprofile');
	if($loguser['powerlevel'] >= ADMIN_GROUP) $layout_navigation .= actionLinkTagItem('Admin', 'admin');

	if(!isset($_POST['id']) && isset($_GET['id']))
		$_POST['id'] = (int)$_GET['id'];
	
	$layout_navigation .= '<li><a href="/?page="onclick="document.forms[0].submit(); return false;">Logout</a></li>';
} else {
	$layout_navigation .= actionLinkTagItem('Register', 'register');
	$layout_navigation .= actionLinkTagItem('Login', 'login');
}

// BREADCRUMBS

$layout_crumbs = '';

if ($crumbs || $links) {
	$layout_crumbs .= '<table id="crumbs" class="outline margin"><tr class="cell0"><td>';

	if($mobileLayout) {
		if($links) {
			$links->setClass("toolbarMenu");
			$layout_crumbs .= '<div style="float: right;">'.$links->build(2).'</div>';
		}
		if($crumbs) {
			$last = $crumbs->pop();
			if($last == NULL)
				$now = '';
			else
				$now = $last->getText();

			$last2 = NULL;
			if($last != NULL && $last->getLink() == "")
			{
				$last2 = $crumbs->pop();
				if($last2 == NULL)
					$now2 = "";
				else
					$now2 = $last2->getText();
				$now = $now2."&nbsp;&nbsp;&mdash;&nbsp;&nbsp;&nbsp;".$now;
			}		
			if($last2 == NULL)
				$last2 = $crumbs->pop();
		
			$backurl = "";
			if($last2 != NULL)
			{
				$backurl = htmlspecialchars($last2->getLink());
				$now = "<i class=\"icon-chevron-left\">&nbsp;</i> ".$now;
			}
		
			$layout_crumbs .= '<a style="line-height:40px;" href="'.$backurl.'">'.$now.'</a>';
		}
	} else {
		$layout_crumbs .= '<div style="float: right;">';
		if($links)
			$layout_crumbs .= $links->build();
		$layout_crumbs .= '</div>';
		if($crumbs)
			$layout_crumbs .= $crumbs->build().'&nbsp;';
	}

	$layout_crumbs .= '</td></tr></table>';
}

// FOOTER

$statData = Fetch(Query("SELECT
	(SELECT COUNT(*) FROM {threads}) AS numThreads,
	(SELECT COUNT(*) FROM {posts}) AS numPosts,
	(SELECT COUNT(*) FROM {users}) AS numUsers,
	(select count(*) from {posts} where date > {0}) AS newToday,
	(select count(*) from {posts} where date > {1}) AS newLastHour,
	(select count(*) from {users} where lastposttime > {2}) AS numActive",
	time() - 86400, time() - 3600, time() - 1209600));

$percent = $statData["numUsers"] ? ceil((100 / $statData["numUsers"]) * $statData["numActive"]) : 0;

$layout_stats = $statData["numThreads"].' threads and '.$statData["numPosts"].' posts total, '.$statData["newToday"].' posts today ('.$statData["newLastHour"].' last hour)<br>'.$statData["numUsers"].' users, '.$statData["numActive"].' active ('.$percent.'%) - '.number_format($misc['views']).' views';

if ($mobileLayout)
	$layout_footer = '
<div class="center smallFonts"><a href="#" onclick="enableMobileLayout(-1); return false;" rel="nofollow">Disable mobile layout</a></div>';
else
	$layout_footer = '
<span style="float:right;text-align:right;">'.$layout_stats.'</span>
<a href="'.URL_ROOT.'"><img src="/img/btn/parkcity.gif" style="width:88px;height:31px;float:left;margin-right:4px;"></a>Acmlmboard XD 3.14<br>
<a href="#" onclick="enableMobileLayout(1); return false;" rel="nofollow">Enable mobile layout</a>';
			
// THEMES

$themefile = 'themes/'.$theme.'/style.css';
if(!file_exists($themefile))
	$themefile = 'themes/'.$theme.'/style.php';

$layout_css = '<link href="'.$themefile.'" rel="stylesheet" id="theme_css">';

if($mobileLayout)
	$layout_css .= '<link href="'.URL_ROOT.'css/mobile.css" rel="stylesheet">';
if($loguser['css'])
	$layout_css .= '<style>'.htmlspecialchars($loguser['css']).'</style>';

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?=$layout_title?></title>
	<meta name="keywords" content="<?=Settings::get('metaTags');?>">
	<meta name="description" content="<?=Settings::get('metaDescription');?>">

	<link href="<?=URL_ROOT;?>js/spectrum.css" rel="stylesheet">
	<link href="<?=URL_ROOT;?>css/font-awesome.min.css" rel="stylesheet">
	
	<script type="text/javascript" src="<?=URL_ROOT;?>js/jquery.js"></script>
	<script type="text/javascript" src="<?=URL_ROOT;?>js/tricks.js"></script>
	<script type="text/javascript" src="<?=URL_ROOT;?>js/jquery.tablednd_0_5.js"></script>
	<script type="text/javascript" src="<?=URL_ROOT;?>js/jquery.scrollTo-1.4.2-min.js"></script>
	<script type="text/javascript" src="<?=URL_ROOT;?>js/spectrum.js"></script>
	<script type="text/javascript">boardroot = <?=json_encode(URL_ROOT);?>;</script>

	<link href="/font/overpass.css" rel="stylesheet">
    <link href="/font/overpass-mono.css" rel="stylesheet">
	
	<link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
	
	<link rel="manifest" href="site.webmanifest">
    <meta name="msapplication-TileColor" content="#603cba">
	
	<?=$layout_css;?>
	
</head>
<body<?php if($mobileLayout) print ' id="mobile"'; ?>>
	<div class="container mcenter">
		<table class="outline margin center" id="header">
			<tr class="cell0">
				<td>
					<a href="<?=URL_ROOT?>"><img id="theme_banner" src="<?=$boardroot.$banners[$banner];?>" alt="<?=Settings::get('boardname');?>"></a>
				</td>
			</tr>
			<tr class="cell1">
				<td>
					<ul class="mainMenu"><?=$layout_navigation;?></ul>
				</td>
			</tr>
			<tr class="cell0">
				<td>
					<span class="smallFonts"><?=OnlineUsers();?></span>
				</td>
			</tr>
		</table>
		<form action="/?page=login" method="post" id="logout">
			<input type="hidden" name="action" value="logout" />
		</form>
		<?=$layout_crumbs;?>
		<div id="page_contents"><?=$layout_contents;?></div>
		<?=$layout_crumbs;?>
		<table id="footer" class="outline margin">
			<tr>
				<td>
					<?=$layout_footer;?>
				</td>
			</tr>
		</table>
	</div>
</body>
</html>