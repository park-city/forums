<?php
if(!defined('DINNER')) die();

$title = "Register";

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Register", "register"));
makeBreadcrumbs($crumbs);

$haveSecurimage = is_file("securimage/securimage.php");
if($haveSecurimage)
	session_start();

if(isset($_POST['name']))
{
	$name = trim($_POST['name']);
	$cname = str_replace(" ","", strtolower($name));

	$rUsers = Query("select name, displayname from {users}");
	while($user = Fetch($rUsers))
	{
		$uname = trim(str_replace(" ", "", strtolower($user['name'])));
		if($uname == $cname)
			break;
		$uname = trim(str_replace(" ", "", strtolower($user['displayname'])));
		if($uname == $cname)
			break;
	}

	$ipKnown = FetchResult("select COUNT(*) from {users} where lastip={0}", $_SERVER['REMOTE_ADDR']);

	//This makes testing faster.
	if($_SERVER['REMOTE_ADDR'] == "127.0.0.1" || $_SERVER['REMOTE_ADDR'] == "::1")
		$ipKnown = 0;
		
	if($uname == $cname)
		$err = "This username is already taken. Please choose another.";
	else if($name == "" || $cname == "")
		$err = "The username must not be empty. Please choose one.";
	else if(strpos($name, ";") !== false)
		$err = "The username cannot contain semicolons.";
	//elseif($ipKnown >= 2)
	//	$err = "Another user is already using this IP address.";
	else if ($_POST['pass'] !== $_POST['pass2'])
		$err = "The passwords you entered don't match.";
	else if($haveSecurimage)
	{
		include("securimage/securimage.php");
		$securimage = new Securimage();
		if($securimage->check($_POST['captcha_code']) == false)
			$err = "You got the CAPTCHA wrong.";
	}

	if($err)
	{
		Alert($err);
	}
	else
	{
		$newsalt = Shake();
		$password = password_hash($_POST['pass'], PASSWORD_DEFAULT);

		$rUsers = Query("insert into {users} (name, password, pss, regdate, lastactivity, lastip, email, theme) values ({0}, {1}, {2}, {3}, {3}, {4}, {5}, {6})", $_POST['name'], $password, $newsalt, time(), $_SERVER['REMOTE_ADDR'], $_POST['email'], Settings::get('defaultTheme'));
		
		$uid = insertId();
		
		logAction('register', array('user' => $uid));

		$user = Fetch(Query("select * from {users} where id={0}", $uid));
		$user["rawpass"] = $_POST["pass"];

		$sessionID = Shake();
		setcookie("logsession", $sessionID, 0, $boardroot, "", false, true);
		Query("INSERT INTO {sessions} (id, user, autoexpire) VALUES ({0}, {1}, {2})", doHash($sessionID.$salt), $user["id"], 0);
		redirectAction("board");
	}
}


$name = "";
if(isset($_POST["name"]))
	$name = htmlspecialchars($_POST["name"]);
$email = "";
if(isset($_POST["email"]))
	$email = htmlspecialchars($_POST["email"]);
echo "
<script src=\"".resourceLink('js/register.js')."\"></script>
<script src=\"".resourceLink('js/zxcvbn.js')."\"></script>
<form action=\"".actionLink("register")."\" method=\"post\">
	<table class=\"outline margin mcenter mw600\">
		<tr class=\"header0\">
			<th colspan=\"2\">
				Register
			</th>
		</tr>
		<tr>
			<td class=\"cell2\">
				<label for=\"un\">Username</label>
			</td>
			<td class=\"cell0\">
				<input type=\"text\" id=\"un\" name=\"name\" value=\"$name\" maxlength=\"20\" style=\"width: 98%;\"  class=\"required\" />
			</td>
		</tr>
		<tr>
			<td class=\"cell2\">
				<label for=\"pw\">Password</label>
			</td>
			<td class=\"cell1\">
				<input type=\"password\" id=\"pw\" name=\"pass\" size=\"13\" maxlength=\"32\" class=\"required\" /> Repeat: <input type=\"password\" id=\"pw2\" name=\"pass2\" size=\"13\" maxlength=\"32\" class=\"required\" />
			</td>
		</tr>
		<tr>
			<td class=\"cell2\">
				<label for=\"email\">Email</label>
			</td>
			<td class=\"cell0\">
				<input type=\"email\" id=\"email\" name=\"email\" value=\"$email\" style=\"width: 98%;\" maxlength=\"60\" /><br>
				<span class=\"smallFonts\">Not required, but used for password resets. Hidden to everyone.</span>
			</td>
		</tr>";

if($haveSecurimage)
{
	echo "
		<tr>
			<td class=\"cell2\">
				r u human
			</td>
			<td class=\"cell1\">
				<img width=\"200\" height=\"80\" id=\"captcha\" src=\"".actionLink("captcha", shake())."\" alt=\"CAPTCHA Image\" />
				<button onclick=\"document.getElementById('captcha').src = '".actionLink("captcha", shake())."?' + Math.random(); return false;\">New</button><br />
				<input type=\"text\" name=\"captcha_code\" size=\"10\" maxlength=\"6\" class=\"required\" />
			</td>
		</tr>";
}

echo "
		<tr class=\"cell2 center\">
			<td colspan=2>
				<input type=\"submit\" name=\"action\" value=\"Register\"/>
			</td>
		</tr>
	</table>
</form>";

function MakeOptions($fieldName, $checkedIndex, $choicesList)
{
	$checks[$checkedIndex] = " checked=\"checked\"";
	foreach($choicesList as $key=>$val)
		$result .= format("
					<label>
						<input type=\"radio\" name=\"{1}\" value=\"{0}\"{2} />
						{3}
					</label>", $key, $fieldName, $checks[$key], $val);
	return $result;
}

