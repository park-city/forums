<?php
if(!defined('DINNER')) die();

if(!$loguserid)
	Kill("You must be logged in to edit your profile.");

if ($loguser['powerlevel'] < 0)
	Kill("You're not allowed to edit your profile");

if (isset($_POST['action']) && $loguser['token'] != $_POST['key'])
	Kill("no");

if(isset($_POST['editusermode']) && $_POST['editusermode'] != 0)
	$_GET['id'] = $_POST['userid'];

if($loguser['powerlevel'] > 2)
	$userid = (isset($_GET['id'])) ? (int)$_GET['id'] : $loguserid;
else
	$userid = $loguserid;

$user = Fetch(Query("select * from {users} where id={0}", $userid));

$editUserMode = isset($_GET['id']) && $loguser['powerlevel'] > 2;

if($editUserMode && $user['powerlevel'] == 4 && $loguser['powerlevel'] != 4 && $loguserid != $userid)
	Kill("Cannot edit a root user.");

AssertForbidden($editUserMode ? "editUser" : "editProfile");

if($editUserMode) $title = 'Edit user';
else $title = 'Edit profile';

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Members", "memberlist"));
$crumbs->add(new PipeMenuHtmlEntry(userLink($user)));
$crumbs->add(new PipeMenuTextEntry("Edit profile"));
makeBreadcrumbs($crumbs);

echo "<script src=\"".resourceLink('js/zxcvbn.js')."\"></script>";
echo "<script src=\"".resourceLink('js/register.js')."\"></script>";

loadRanksets();
$ranksets = $ranksetNames;
$ranksets[""] = 'None';
$ranksets = array_reverse($ranksets);
unset($ranksets["levels"]);

foreach($dateformats as $format)
	$datelist[$format] = ($format ? $format.' ('.cdate($format).')':'');
foreach($timeformats as $format)
	$timelist[$format] = ($format ? $format.' ('.cdate($format).')':'');

$dispname = $user['displayname'] ? $user['displayname'] : $user['name'];

$powerlevels = array(-1 => "-1 - Banned", "0 - Normal user", "1 - Local mod", "2 - Moderator", "3 - Administrator", "4 - Root", "5 - System");

$general = array(
	"appearance" => array(
		"name" => "Appearance",
		"items" => array(
			"displayname" => array(
				"caption" => "Display name",
				"type" => "text",
				"width" => "98%",
				"length" => 16,
				"callback" => "HandleDisplayname",
			),
			"pronouns" => array(
				"caption" => "Pronouns",
				"width" => "98%",
				"length" => 20,
				"type" => "text",
			),
			/*"title" => array(
				"caption" => "Title",
				"type" => "text",
				"width" => "98%",
				"length" => 256,
			),*/
			"rankset" => array(
				"caption" => "Rankset",
				"type" => "select",
				"options" => $ranksets,
			),
			"color" => array(
				"caption" => "Name color",
				"type" => "color",
			),
			"hascolor" => array(
				"caption" => "Enable name color",
				"type" => "checkbox",
			),
		),
	),
	"avatar" => array(
		"name" => "Avatar",
		"items" => array(
			"picture" => array(
				"caption" => "Avatar",
				"type" => "displaypic",
				"errorname" => "picture",
				"extra" => "<br>Maximum: 400x400<br><a href=\"/?page=editavatars\" target=\"_blank\">Click here</a> to edit mood avatars.",
			),
		),
	),
	"presentation" => array(
		"name" => "Display",
		"items" => array(
			"css" => array(
				"caption" => "Custom CSS",
				"type" => "textarea",
				"extra" => "Make your own theme!",
			),
			"dateformat" => array(
				"caption" => "Date format",
				"type" => "datetime",
				"presets" => $datelist,
				"presetname" => "presetdate",
			),
			"timeformat" => array(
				"caption" => "Time format",
				"type" => "datetime",
				"presets" => $timelist,
				"presetname" => "presettime",
			),
			"timezone" => array(
				"caption" => "Timezone",
				"type" => "timezone",
				"extra" => "PDT is -7, PST is -8.",
			),
			"blocklayouts" => array(
				"caption" => "Hide post layouts",
				"type" => "checkbox",
			),
		),
	),
);

$personal = array(
	"personal" => array(
		"name" => "Profile",
		"items" => array(
			"birthday" => array(
				"caption" => "Birthday",
				"type" => "birthday",
				"width" => "98%",
				"length" => 60,
				"extra" => format(__("(example: {0})"), $birthdayExample),
			),
			"bio" => array(
				"caption" => "Bio",
				"type" => "textarea",
			),
		),
	),
);

$account = array(
	"confirm" => array(
		"name" => "Password confirmation",
		"items" => array(
			"info" => array(
				"caption" => "",
				"type" => "label",
				"value" => "Enter your password in order to edit account settings"
			),
			"currpassword" => array(
				"caption" => "Password",
				"type" => "passwordonce",
				"callback" => "",
			),
		),
	),
	"login" => array(
		"name" => "Account information",
		"class" => "needpass",
		"items" => array(
			"name" => array(
				"caption" => "Username",
				"type" => "text",
				"length" => 20,
				"callback" => "HandleUsername",
			),
			"email" => array(
				"caption" => "Email",
				"type" => "text",
				"length" => 60,
				"extra" => "Hidden to everyone. Used for password resets.",
			),
			"password" => array(
				"caption" => "New password",
				"type" => "password",
				"callback" => "HandlePassword",
			),
		),
	),
	"admin" => array(
		"name" => "Administration",
		"class" => "needpass",
		"items" => array(
			"powerlevel" => array(
				"caption" => "Power level",
				"type" => "select",
				"options" => $powerlevels,
				"callback" => "HandlePowerlevel",
			),
			"globalblock" => array(
				"caption" => "Ban post layout",
				"type" => "checkbox",
			),
		),
	),
);

$layout = array(
	"postlayout" => array(
		"name" => "Post layout",
		"items" => array(
			"postheader" => array(
				"caption" => "Post header",
				"extra" => "Put &lt;style&gt; tags here to pretty up your posts!",
				"type" => "textarea",
				"rows" => 16,
			),
			"signature" => array(
				"caption" => "Signature",
				"type" => "textarea",
				"extra" => "Comes immediately after your post's contents.",
				"rows" => 16,
			),
			"signsep" => array(
				"caption" => "Show signature separator",
				"type" => "checkbox",
				"negative" => true,
			),
		),
	),
);

function HandleExtraField($field, $item)
{
	global $pluginSettings;
	$i = $item['fieldnumber'];
	$t = $item['isCaption'] ? "t" : "v";
	$pluginSettings['profileExt'.$i.$t] = urlencode($_POST['extra'.$i.$t]);
	return true;
}

for($i = 0; $i < 15; $i++)
{
	$personal['personal']['items']['extra'.$i.'t'] = array(
		"caption" => format(__("Label #{0}"), $i+1),
		"type" => "text",
		"value" => getSetting("profileExt".$i."t", true),
		"callback" => "HandleExtraField",
		"width" => "98%",
		"fieldnumber" => $i,
		"isCaption" => true,
	);
	$personal['personal']['items']['extra'.$i.'v'] = array(
		"caption" => format(__("Data #{0}"), $i+1),
		"type" => "text",
		"value" => getSetting("profileExt".$i."v", true),
		"callback" => "HandleExtraField",
		"width" => "98%",
		"fieldnumber" => $i,
		"isCaption" => false,
	);
}

if(!$editUserMode)
{
	$account['login']['items']['name']['type'] = "label";
	$account['login']['items']['name']['value'] = $user["name"];
	unset($account['admin']);
}

// Now that we have everything set up, we can link 'em into a set of tabs.
$tabs = array(
	"general" => array(
		"name" => "General",
		"page" => $general,
	),
	"personal" => array(
		"name" => "Profile",
		"page" => $personal,
	),
	"account" => array(
		"name" => "Account",
		"page" => $account,
	),
	"postlayout" => array(
		"name" => "Post layout",
		"page" => $layout,
	),
	"theme" => array(
		"name" => "Theme",
	),
);


if (isset($_POST['theme']) && $user['id'] == $loguserid)
{
	$theme = $_POST['theme'];
	$themeFile = $theme.".css";
	if(!file_exists("css/".$themeFile))
		$themeFile = $theme.".php";
}

/* QUERY PART
 * ----------
 */

$failed = false;

if($_POST['action'] == "Edit profile")
{
	$passwordEntered = false;

	if($_POST["currpassword"] != "")
	{
		if (password_verify($_POST['currpassword'], $loguser['password']))
			$passwordEntered = true;
		else
		{
			Alert("Invalid password.");
			$failed = true;
			$selectedTab = "account";
			$tabs["account"]["page"]["confirm"]["items"]["currpassword"]["fail"] = true;
		}
	}

	$query = "UPDATE {$dbpref}users SET ";
	$sets = array();
	$pluginSettings = unserialize($user['pluginsettings']);

	foreach($tabs as $id => &$tab)
	{
		if(!isset($tab['page'])) continue;
		if($id == "account" && !$passwordEntered) continue;

		foreach($tab['page'] as $id => &$section)
		{
			foreach($section['items'] as $field => &$item)
			{
				if($item['callback'])
				{
					$ret = $item['callback']($field, $item);
					if($ret === true)
						continue;
					else if($ret != "")
					{
						Alert($ret, __('Error'));
						$failed = true;
						$selectedTab = $id;
						$item["fail"] = true;
					}
				}

				switch($item['type'])
				{
					case "label":
						break;
					case "color":
						$val = $_POST[$field];
						var_dump($val);
						if(!preg_match("/^#[0-9a-fA-F]*$/", $val))
							$val = "";
						$sets[] = $field." = '".SqlEscape($val)."'";
						break;
					case "text":
					case "textarea":
						$sets[] = $field." = '".SqlEscape($_POST[$field])."'";
					case "password":
						if($_POST[$field])
							$sets[] = $field." = '".SqlEscape($_POST[$field])."'";
						break;
					case "select":
						$val = $_POST[$field];
						if (array_key_exists($val, $item['options']))
							$sets[] = $field." = '".sqlEscape($val)."'";
						break;
					case "number":
						$num = (int)$_POST[$field];
						if($num < 1)
							$num = $item['min'];
						elseif($num > $item['max'])
							$num = $item['max'];
						$sets[] = $field." = ".$num;
						break;
					case "datetime":
						if($_POST[$item['presetname']] != -1)
							$_POST[$field] = $_POST[$item['presetname']];
						$sets[] = $field." = '".SqlEscape($_POST[$field])."'";
						break;
					case "timezone":
						$val = ((int)$_POST[$field.'H'] * 3600) + ((int)$_POST[$field.'M'] * 60) * ((int)$_POST[$field.'H'] < 0 ? -1 : 1);
						$sets[] = $field." = ".$val;
						break;
					case "checkbox":
						$val = (int)($_POST[$field] == "on");
						if($item['negative'])
							$val = (int)($_POST[$field] != "on");
						$sets[] = $field." = ".$val;
						break;
					case "radiogroup":
						if (array_key_exists($_POST[$field], $item['options']))
							$sets[] = $field." = '".SqlEscape($_POST[$field])."'";
						break;
					case "birthday":
						if($_POST[$field])
						{
							$val = @stringtotimestamp($_POST[$field]);
							if($val > time())
								$val = 0;
						}
						else
							$val = 0;
						$sets[] = $field." = '".$val."'";
						break;
					case "displaypic":
						if($_POST['remove'.$field])
						{
							@unlink($dataDir."avatars/$userid");
							$sets[] = $field." = ''";
							break;
						}
						if($_FILES[$field]['name'] == "" || $_FILES[$field]['error'] == UPLOAD_ERR_NO_FILE)
							break;
						$res = HandlePicture($field, 0, $item['errorname'], $user['powerlevel'] > 0 || $loguser['powerlevel'] > 0);
						if($res === true)
							$sets[] = $field." = '#INTERNAL#'";
						else
						{
							Alert($res);
							$failed = true;
							$item["fail"] = true;
						}
						break;
				}
			}
		}
	}

	//Force theme names to be alphanumeric to avoid possible directory traversal exploits ~Dirbaio
	if(preg_match("/^[a-zA-Z0-9_]+$/", $_POST['theme']))
		$sets[] = "theme = '".SqlEscape($_POST['theme'])."'";

	$sets[] = "pluginsettings = '".SqlEscape(serialize($pluginSettings))."'";
	if ((int)$_POST['powerlevel'] != $user['powerlevel']) $sets[] = "tempbantime = 0";

	$query .= join($sets, ", ")." WHERE id = ".$userid;
	if(!$failed)
	{
		RawQuery($query);
		if($loguserid == $userid)
			$loguser = Fetch(Query("select * from {users} where id={0}", $loguserid));

		logAction('edituser', array('user2' => $user['id']));
		redirectAction("profile", $userid);
	}
}

//If failed, get values from $_POST
//Else, get them from $user

foreach($tabs as &$tab)
{
	if(!isset($tab['page'])) continue;

	foreach($tab['page'] as &$section)
	{
		foreach($section['items'] as $field => &$item)
		{
			if ($item['type'] == "label" || $item['type'] == "password")
				continue;

			if(!$failed)
			{
				if(!isset($item["value"]))
					$item["value"] = $user[$field];
			}
			else
			{
				if ($item['type'] == 'checkbox')
					$item['value'] = ($_POST[$field] == 'on') ^ $item['negative'];
				elseif ($item['type'] == 'timezone')
					$item['value'] = ((int)$_POST[$field.'H'] * 3600) + ((int)$_POST[$field.'M'] * 60) * ((int)$_POST[$field.'H'] < 0 ? -1 : 1);
				elseif ($item['type'] == 'birthday')
					$item['value'] = @stringtotimestamp($_POST['birthday']);
				else
					$item['value'] = $_POST[$field];
			}
		}
		unset($item);
	}
	unset($section);
}
unset($tab);

if($failed)
	$loguser['theme'] = $_POST['theme'];

function HandlePicture($field, $type, $errorname, $allowOversize = false)
{
	global $userid, $dataDir;
	if($type == 0)
	{
		$extensions = array(".png",".jpg",".jpeg",".gif");
		$maxDim = 400;
		$maxSize = 500 * 1024;
	}
	else if($type == 1)
	{
		$extensions = array(".png", ".gif");
		$maxDim = 16;
		$maxSize = 100 * 1024;
	}

	$fileName = $_FILES[$field]['name'];
	$fileSize = $_FILES[$field]['size'];
	$tempFile = $_FILES[$field]['tmp_name'];
	list($width, $height, $fileType) = getimagesize($tempFile);

	if ($type == 0 && ($width > 400 || $height > 400))
		return __("Your avatar is too big.");

	$extension = strtolower(strrchr($fileName, "."));
	if(!in_array($extension, $extensions))
		return format(__("Invalid extension used for {0}. Allowed: {1}"), $errorname, join($extensions, ", "));

	if($fileSize > $maxSize && !$allowOversize)
		return format(__("File size for {0} is too high. The limit is {1} bytes, the uploaded image is {2} bytes."), $errorname, $maxSize, $fileSize)."</li>";

	switch($fileType)
	{
		case 1:
			$sourceImage = imagecreatefromgif($tempFile);
			break;
		case 2:
			$sourceImage = imagecreatefromjpeg($tempFile);
			break;
		case 3:
			$sourceImage = imagecreatefrompng($tempFile);
			break;
	}

	$oversize = ($width > $maxDim || $height > $maxDim);
	$targetFile = $dataDir."avatars/".$userid;

	if($allowOversize || !$oversize)
	{
		//Just copy it over.
		copy($tempFile, $targetFile);
	}
	else
	{
		//Resample that mother!
		$ratio = $width / $height;
		if($ratio > 1)
		{
			$targetImage = imagecreatetruecolor($maxDim, floor($maxDim / $ratio));
			imagecopyresampled($targetImage, $sourceImage, 0,0,0,0, $maxDim, $maxDim / $ratio, $width, $height);
		} else
		{
			$targetImage = imagecreatetruecolor(floor($maxDim * $ratio), $maxDim);
			imagecopyresampled($targetImage, $sourceImage, 0,0,0,0, $maxDim * $ratio, $maxDim, $width, $height);
		}
		imagepng($targetImage, $targetFile);
		imagedestroy($targetImage);
	}
	return true;
}

// Special field-specific callbacks
function HandlePassword($field, $item)
{
	global $sets, $salt, $user, $loguser, $loguserid;
	if($_POST[$field] != "" && $_POST['repeat'.$field] != "" && $_POST['repeat'.$field] !== $_POST[$field])
	{
		return __("To change your password, you must type it twice without error.");
	}

	if($_POST[$field] != "" && $_POST['repeat'.$field] == "")
		$_POST[$field] = "";

	if($_POST[$field])
	{
		$newsalt = Shake();
		$sha = doHash($_POST[$field].$salt.$newsalt);
		$sets[] = "pss = '".$newsalt."'";
		$_POST[$field] = password_hash($_POST[$field], PASSWORD_DEFAULT);

		//Now logout all the sessions that aren't this one, for security.
		Query("DELETE FROM {sessions} WHERE id != {0} and user = {1}", doHash($_COOKIE['logsession'].$salt), $user["id"]);
	}

	return false;
}

function HandleDisplayname($field, $item)
{
	global $user;
	if(!IsReallyEmpty($_POST[$field]) || $_POST[$field] == $user['name'])
	{
		// unset the display name if it's really empty or the same as the login name.
		$_POST[$field] = "";
	}
	else
	{
		$dispCheck = FetchResult("select count(*) from {users} where id != {0} and (name = {1} or displayname = {1})", $user['id'], $_POST[$field]);
		if($dispCheck)
		{

			return format(__("The display name you entered, \"{0}\", is already taken."), SqlEscape($_POST[$field]));
		}
		else if(strpos($_POST[$field], ";") !== false)
		{
			$user['displayname'] = str_replace(";", "", $_POST[$field]);

			return __("The display name you entered cannot contain semicolons.");
		}
		else if($_POST[$field] !== ($_POST[$field] = preg_replace('/(?! )[\pC\pZ]/u', '', $_POST[$field])))
		{

			return __("The display name you entered cannot contain control characters.");
		}
	}
}

function HandleUsername($field, $item)
{
	global $user;
	if(!IsReallyEmpty($_POST[$field]))
		$_POST[$field] = $user[$field];

	$dispCheck = FetchResult("select count(*) from {users} where id != {0} and (name = {1} or displayname = {1})", $user['id'], $_POST[$field]);
	if($dispCheck)
	{

		return format(__("The login name you entered, \"{0}\", is already taken."), SqlEscape($_POST[$field]));
	}
	else if(strpos($_POST[$field], ";") !== false)
	{
		$user['name'] = str_replace(";", "", $_POST[$field]);

		return __("The login name you entered cannot contain semicolons.");
	}
	else if($_POST[$field] !== ($_POST[$field] = preg_replace('/(?! )[\pC\pZ]/u', '', $_POST[$field])))
	{

		return __("The login name you entered cannot contain control characters.");
	}
}

function HandlePowerlevel($field, $item)
{
	global $user, $loguserid, $userid;
	$id = $userid;
	if($user['powerlevel'] != (int)$_POST['powerlevel'] && $id != $loguserid)
	{
		$newPL = (int)$_POST['powerlevel'];
		$oldPL = $user['powerlevel'];

		if($newPL == 5)
			; //Do nothing -- System won't pick up the phone.
		else if($newPL == -1)
		{
			SendSystemPM($id, __("If you don't know why this happened, feel free to ask the one most likely to have done this. Calmly, if possible."), __("You have been banned."));
		}
		else if($newPL == 0)
		{
			if($oldPL == -1)
				SendSystemPM($id, __("Try not to repeat whatever you did that got you banned."), __("You have been unbanned."));
			else if($oldPL > 0)
				SendSystemPM($id, __("Try not to take it personally."), __("You have been brought down to normal."));
		}
		else if($newPL == 4)
		{
			SendSystemPM($id, __("Your profile is now untouchable to anybody but you. You can give root status to anybody else, and can access the RAW UNFILTERED POWERRR of sql.php. Do not abuse this. Your root status can only be removed through sql.php."), __("You are now a root user."));
		}
		else
		{
			if($oldPL == -1)
				; //Do nothing.
			else if($oldPL > $newPL)
				SendSystemPM($id, __("Try not to take it personally."), __("You have been demoted."));
			else if($oldPL < $newPL)
				SendSystemPM($id, __("Congratulations. Don't forget to review the rules regarding your newfound powers."), __("You have been promoted."));
		}
	}
}


/* EDITOR PART
 * -----------
 */

$dir = "themes/";
$themeList = "";
$themes = array();

if (is_dir($dir))
{
    if ($dh = opendir($dir))
    {
        while (($file = readdir($dh)) !== false)
        {
            if(filetype($dir . $file) != "dir") continue;
            if($file == ".." || $file == ".") continue;
            $infofile = $dir.$file."/themeinfo.txt";

            if(file_exists($infofile))
            {
		        $themeinfo = file_get_contents($infofile);
		        $themeinfo = explode("\n", $themeinfo, 2);

		        $themes[$file]["name"] = trim($themeinfo[0]);
		        $themes[$file]["desc"] = trim($themeinfo[1]);
		    }
		    else
		    {
		        $themes[$file]["name"] = $file;
		        $themes[$file]["desc"] = "";
		    }
        }
        closedir($dh);
    }
}

asort($themes);

foreach($themes as $themeKey => $themeData)
{
	$themename = $themeData["name"];
	$themedesc = $themeData["desc"];

	$qCount = "select count(*) from {users} where theme='".$themeKey."'";
	$numUsers = FetchResult($qCount);

	if($themeKey == $user['theme'])
		$selected = ' checked="checked"';
	else
		$selected = '';
	
	if(file_exists($dir.$themeKey.'/preview.css'))
		$themeList .= '<link rel="stylesheet" href="themes/'.$themeKey.'/preview.css">';
	elseif(file_exists($dir.$themeKey.'/preview.php'))
		$themeList .= '<link rel="stylesheet" href="themes/'.$themeKey.'/preview.php">';

	$themeList .= '
		<div style="display:inline-block;" class="theme">
			<input style="display:none;" type="radio" name="theme" value="'.$themeKey.'"'.$selected.' id="'.$themeKey.'" onchange="ChangeTheme(this.value);">
			<label style="display:inline-block;clear:left;padding:0.5em;width:260px;vertical-align:top" onmousedown="void();" for="'.$themeKey.'">
				<table class="safe '.$themeKey.'">
					<tr>
						<th class="safe" colspan=2>'.$themename.'</th>
					</tr>
					<tr>
						<td class="safe cell2">&nbsp;</td>
						<td class="safe cell0">'.$themedesc.'</td>
					</tr>
					<tr>
						<td class="safe cell2">&nbsp;</td>
						<td class="safe cell1">'.$numUsers.' users</td>
					</tr>
				</table>
			</label>
		</div>
	';
}

if(!isset($selectedTab))
{
	$selectedTab = "general";
	foreach($tabs as $id => $tab)
	{
		if(isset($_GET[$id]))
		{
			$selectedTab = $id;
			break;
		}
	}
}
Write("<div class=\"mcenter\" style=\"max-width: 900px;\">");

Write("<div id=\"tabs\">");
foreach($tabs as $id => $tab)
{
	$selected = ($selectedTab == $id) ? " selected" : "";
	Write("
	<button id=\"{2}Button\" class=\"tab{1}\" onclick=\"showEditProfilePart('{2}');\">{0}</button>
	", $tab['name'], $selected, $id);
}
Write("
</div>
<form action=\"".actionLink("editprofile")."\" method=\"post\" enctype=\"multipart/form-data\">
");

foreach($tabs as $id => $tab)
{
	if(isset($tab['page']))
		BuildPage($tab['page'], $id);
	elseif($id == "theme")
		Write("
	<table class=\"outline margin eptable\" id=\"{0}\"{1}>
		<tr class=\"header0\"><th>Theme</th></tr>
		<tr class=\"cell0\"><td class=\"themeselector\">{2}</td></tr>
	</table>
",	$id, ($id != $selectedTab) ? " style=\"display: none;\"" : "",
	$themeList);
}

$editUserFields = "";
if($editUserMode)
{
	$editUserFields = format(
"
		<input type=\"hidden\" name=\"editusermode\" value=\"1\" />
		<input type=\"hidden\" name=\"userid\" value=\"{0}\" />
", $userid);
}

Write(
"
	<div class=\"margin center mcenter\" id=\"button\">
		{2}
		<input type=\"submit\" id=\"submit\" name=\"action\" value=\"Edit profile\" />
		<input type=\"hidden\" name=\"id\" value=\"{0}\" />
		<input type=\"hidden\" name=\"key\" value=\"{1}\" />
	</div>
</form>
", $id, $loguser['token'], $editUserFields);

function BuildPage($page, $id)
{
	global $selectedTab, $loguser, $user;

	//TODO: This should be done in JS.
	//So that a user who doesn't have Javascript will see all the tabs.
	$display = ($id != $selectedTab) ? " style=\"display: none;\"" : "";

	$cellClass = 0;
	$output = "<table class=\"outline margin eptable\" id=\"".$id."\"".$display.">\n";
	foreach($page as $pageID => $section)
	{
		$secClass = $section["class"];
		$output .= "<tr class=\"header0 $secClass\"><th colspan=\"2\">".$section['name']."</th></tr>\n";
		foreach($section['items'] as $field => $item)
		{
			$output .= "<tr class=\"cell$cellClass $secClass\" >\n";
			$output .= "<td>\n";
			if(isset($item["fail"])) $output .= "[ERROR] ";
			if($item['type'] != "checkbox")
				$output .= "<label for=\"".$field."\">".$item['caption']."</label>\n";

			$output .= "</td>\n";
			$output .= "<td>\n";

			if(isset($item['before']))
				$output .= " ".$item['before'];

			// Yes, some cases are missing the break; at the end.
			// This is intentional, but I don't think it's a good idea...
			switch($item['type'])
			{
				case "label":
					$output .= htmlspecialchars($item['value'])."\n";
					break;
				case "birthday":
					$item['type'] = "text";
					//$item['value'] = gmdate("F j, Y", $item['value']);
					$item['value'] = timestamptostring($item['value']);
				case "password":
					if($item['type'] == "password")
						$item['extra'] = "Repeat: <input type=\"password\" name=\"repeat".$field."\" size=\"".$item['size']."\" maxlength=\"".$item['length']."\" />";
				case "passwordonce":
					if(!isset($item['size']))
						$item['size'] = 13;
					if(!isset($item['length']))
						$item['length'] = 32;
					if($item["type"] == "passwordonce")
						$item["type"] = "password";
				case "color":
				case "text":
					$output .= "<input id=\"".$field."\" name=\"".$field."\" type=\"".$item['type']."\" value=\"".htmlspecialchars($item['value'])."\"";
					if(isset($item['size']))
						$output .= " size=\"".$item['size']."\"";
					if(isset($item['length']))
						$output .= " maxlength=\"".$item['length']."\"";
					if(isset($item['width']))
						$output .= " style=\"width: ".$item['width'].";\"";
					if(isset($item['more']))
						$output .= " ".$item['more'];
					$output .= " />\n";
					break;
				case "textarea":
					if(!isset($item['rows']))
						$item['rows'] = 8;
					$output .= "<textarea id=\"".$field."\" name=\"".$field."\" rows=\"".$item['rows']."\" style=\"width: 98%;\">".htmlspecialchars($item['value'])."</textarea>";
					break;
				case "checkbox":
					$output .= "<label><input id=\"".$field."\" name=\"".$field."\" type=\"checkbox\"";
					if((isset($item['negative']) && !$item['value']) || (!isset($item['negative']) && $item['value']))
						$output .= " checked=\"checked\"";
					$output .= " /> ".$item['caption']."</label>\n";
					break;
				case "select":
					$disabled = isset($item['disabled']) ? $item['disabled'] : false;
					$disabled = $disabled ? "disabled=\"disabled\" " : "";
					$checks = array();
					$checks[$item['value']] = " selected=\"selected\"";
					$options = "";
					foreach($item['options'] as $key => $val)
						$options .= format("<option value=\"{0}\"{1}>{2}</option>", $key, $checks[$key], $val);
					$output .= format("<select id=\"{0}\" name=\"{0}\" size=\"1\" {2}>\n{1}\n</select>\n", $field, $options, $disabled);
					break;
				case "radiogroup":
					$checks = array();
					$checks[$item['value']] = " checked=\"checked\"";
					foreach($item['options'] as $key => $val)
						$output .= format("<label><input type=\"radio\" name=\"{1}\" value=\"{0}\"{2} />{3}</label>", $key, $field, $checks[$key], $val);
					break;
				case "displaypic":
					$output .= "<input type=\"file\" id=\"".$field."\" name=\"".$field."\" style=\"width:40%;\"><label><input type=\"checkbox\" name=\"remove".$field."\" /> Remove</label>";
					break;
				case "number":
					//$output .= "<input type=\"number\" id=\"".$field."\" name=\"".$field."\" value=\"".$item['value']."\" />";
					$output .= "<input type=\"text\" id=\"".$field."\" name=\"".$field."\" value=\"".$item['value']."\" size=\"6\" maxlength=\"4\" />";
					break;
				case "datetime":
					$output .= "<input type=\"text\" id=\"".$field."\" name=\"".$field."\" value=\"".$item['value']."\" />\n";
					$output .= __("or preset:")."\n";
					$options = "<option value=\"-1\">".__("[select]")."</option>";
					foreach($item['presets'] as $key => $val)
						$options .= format("<option value=\"{0}\">{1}</option>", $key, $val);
					$output .= format("<select id=\"{0}\" name=\"{0}\" size=\"1\" >\n{1}\n</select>\n", $item['presetname'], $options);
					break;
				case "timezone":
					$output .= "<input type=\"text\" name=\"".$field."H\" size=\"2\" maxlength=\"3\" value=\"".(int)($item['value']/3600)."\" />\n";
					$output .= ":\n";
					$output .= "<input type=\"text\" name=\"".$field."M\" size=\"2\" maxlength=\"3\" value=\"".floor(abs($item['value']/60)%60)."\" />";
					break;
			}
			if(isset($item['extra']))
				$output .= " <span class=\"smallFonts\">".$item['extra']."</span>";

			$output .= "</td>\n";
			$output .= "</tr>\n";
			$cellClass = ($cellClass + 1) % 2;
		}
	}
	$output .= "</table>";
	Write($output);
}


function IsReallyEmpty($subject)
{
	$trimmed = trim(preg_replace("/&.*;/", "", $subject));
	return strlen($trimmed) != 0;
}

?>

<script type="text/javascript">
	var passwordChanged = function()
	{
		if($("#currpassword").val() == "")
			$("#passwordhide").html(".needpass {display:none;}");
		else
			$("#passwordhide").html("");
	};
	
	$(function() {
		$("#currpassword").keyup(passwordChanged);
		passwordChanged();
	});
	
</script>
<style type="text/css" id="passwordhide">
	
</style>
</div>