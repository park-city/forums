<?php
$starttime = microtime(true);
define('DINNER', 1);

$ajaxPage = false;
if(isset($_GET["ajax"])) $ajaxPage = true;

require(__DIR__.'/lib/common.php');

if (isset($_GET['page']))
	$page = $_GET["page"];
else
	$page = MAIN_PAGE;
if(!ctype_alnum($page))
	$page = MAIN_PAGE;

if($page == MAIN_PAGE)
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

ob_start();

$layout_crumbs = "";
$layout_actionlinks = "";

try
{
	try
	{
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
catch(KillException $e) {}

if($ajaxPage)
{
	ob_end_flush();
	die();
}

$layout_contents = ob_get_contents();
ob_end_clean();

if(!$isBot)
{
	$rViewCounter = Query("update {misc} set views = views + 1");
	$misc['views']++;
}

setLastActivity();

ob_start();
ob_end_clean();

// HEADER

$layout_title = BOARD_NAME;
if($title != "") $layout_title .= " &raquo; ".$title;

$banners = glob('img/banner/*.*');
$banner = array_rand($banners);

$layout_navigation  = '<li><a href="/?page='.MAIN_PAGE.'">Home</a></li>';
if(MAIN_PAGE != 'board')
	$layout_navigation .= '<li><a href="/?page=board">Forums</a></li>';
$layout_navigation .= '<li><a href="/?page=memberlist">Members</a></li>';
$layout_navigation .= '<li><a href="/?page=latestposts">Latest posts</a></li>';
$layout_navigation .= '<li><a href="/?page=ranks">Ranks</a></li>';
$layout_navigation .= '<br>';

if($loguserid)
{
	$layout_navigation .= '<li>'.userLink($loguser).'</li>';
	$layout_navigation .= '<li><a href="/?page=editprofile">Edit profile</a></li>';
	
	if(HasPermission('admin.viewadminpanel'))
		$layout_navigation .= '<li><a href="/?page=admin">Admin</a></li>';
	
	if(!isset($_POST['id']) && isset($_GET['id']))
		$_POST['id'] = (int)$_GET['id'];
	
	$layout_navigation .= '<li><a href="/?page="onclick="document.forms[0].submit(); return false;">Logout</a></li>';
} else
	$layout_navigation .= '<li><a href="/?page=register">Register</a></li><li><a href="/?page=login">Login</a></li>';

// BREADCRUMBS

if($layout_crumbs || $layout_actionlinks)
	$layout_crumbs = '<table id="crumbs" class="outline margin"><tr><td>'.$layout_actionlinks.$layout_crumbs.'</td></tr></table>';

// THEMES

$themefile = 'themes/'.$theme.'/style.css';
if(!file_exists($themefile))
	$themefile = 'themes/'.$theme.'/style.php';

$layout_css = '<link href="'.$themefile.'" rel="stylesheet" id="theme_css">';

if($mobileLayout)
	$layout_css .= '<link href="'.URL_ROOT.'css/mobile.css" rel="stylesheet">';
if($loguser['css'])
	$layout_css .= '<style>'.htmlspecialchars($loguser['css']).'</style>';

// FOOTER

if($mobileLayout)
	$layout_footer = '<div class="center smallFonts"><a href="#" onclick="enableMobileLayout(-1); return false;" rel="nofollow">Disable mobile layout</a><br>'.doStats().'</div>';
else
	$layout_footer = '<span style="float:right;text-align:right;">'.doStats().'</span><a href="'.$boardroot.'"><img src="/img/btn/oryza.png" class="btn" style="float:left;margin-right:4px;"></a>Acmlmboard XD 3.60 <span style="color:pink;">UNSTABLE</span><br><a href="#" onclick="enableMobileLayout(1); return false;" rel="nofollow">Enable mobile layout</a>';

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?=$layout_title?></title>
	<meta name="keywords" content="<?=META_TAGS;?>">
	<meta name="description" content="<?=META_DESC;?>">

	<link href="<?=URL_ROOT;?>js/spectrum.css" rel="stylesheet">
	<link href="<?=URL_ROOT;?>font/fontawesome.css" rel="stylesheet">
	
	<script type="text/javascript" src="<?=URL_ROOT;?>js/jquery.js"></script>
	<script type="text/javascript" src="<?=URL_ROOT;?>js/tricks.js"></script>
	<script type="text/javascript" src="<?=URL_ROOT;?>js/jquery.tablednd_0_5.js"></script>
	<script type="text/javascript" src="<?=URL_ROOT;?>js/jquery.scrollTo-1.4.2-min.js"></script>
	<script type="text/javascript" src="<?=URL_ROOT;?>js/spectrum.js"></script>
	<script type="text/javascript">boardroot = <?=json_encode(URL_ROOT);?>;</script>
	
	<?=$layout_css; ?>
</head>
<body<?php if($mobileLayout) print ' id="mobile"'; ?>>
	<div class="container mcenter">
		<table class="outline margin center" id="header">
			<tr class="cell0">
				<td>
					<a href="/">
						<img id="theme_banner" src="<?=$boardroot.$banners[$banner];?>" alt="<?=BOARD_NAME;?>">
					</a>
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