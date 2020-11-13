<?php
if(!defined('DINNER')) die();

function RenderTemplate($template, $options=null)
{
	global $tpl, $mobileLayout, $plugintemplates, $plugins;
	
	if (array_key_exists($template, $plugintemplates))
	{
		$plugin = $plugintemplates[$template];
		$self = $plugins[$plugin];
		
		$tplroot = __DIR__.'/../plugins/'.$self['dir'].'/templates/';
	}
	else
		$tplroot = __DIR__.'/../templates/';
	
	if ($mobileLayout)
	{
		$tplname = $tplroot.'mobile/'.$template.'.tpl';
		if (!file_exists($tplname)) 
			$tplname = $tplroot.$template.'.tpl';
	}
	else
		$tplname = $tplroot.$template.'.tpl';
	
	if ($options)
		$tpl->assign($options);
	
	$tpl->display($tplname);
}

function makeForumListing($parent, $page=0)
{
	global $loguserid, $loguser, $mobileLayout;
	
	$pl = $loguser['powerlevel'];
	if ($pl < 0) $pl = 0;

	$lastCatID = -1;
	$firstCat = true;
	$rFora = Query("	SELECT f.*,
							c.name cname,
							".($loguserid ? "(NOT ISNULL(i.fid))" : "0")." ignored,
							(SELECT COUNT(*) FROM {threads} t".($loguserid ? " LEFT JOIN {threadsread} tr ON tr.thread=t.id AND tr.id={0}" : "")."
								WHERE t.forum=f.id AND t.lastpostdate>".($loguserid ? "IFNULL(tr.date,0)" : time()-900).") numnew,
							lu.(_userfields)
						FROM {forums} f
							LEFT JOIN {categories} c ON c.id=f.catid
							".($loguserid ? "LEFT JOIN {ignoredforums} i ON i.fid=f.id AND i.uid={0}" : "")."
							LEFT JOIN {users} lu ON lu.id=f.lastpostuser
						WHERE ".forumAccessControlSQL().' AND '.($parent==0 ? 'f.catid>0 ' : 'f.catid={1}').(($pl < 1) ? " AND f.hidden=0" : '')."
						ORDER BY c.corder, c.id, f.forder, f.id", 
						$loguserid, -$parent, $page);
	if (!NumRows($rFora))
		return;

	$theList = "";
	while($forum = Fetch($rFora))
	{
		$skipThisOne = false;
		if($skipThisOne)
			continue;

		if($firstCat || $forum['catid'] != $lastCatID )
		{
			if($mobileLayout)
			{
				$theList .= format(
	"
			".($firstCat ? '':'</tbody></table>')."
		<table class=\"outline margin\">
			<tbody>
				<tr class=\"header1\">
					<th>{0}</th>
					<th style=\"min-width:150px; width:15%;\">Last post</th>
				</tr>
			</tbody>
			<tbody>
	", ($parent==0)?$forum['cname']:'Subforums');
	
				$lastCatID = $forum['catid'];
				$firstCat = false;
			}
			else
			{
			$theList .= format(
"
		".($firstCat ? '':'</tbody></table>')."
	<table class=\"outline margin\">
		<tbody>
			<tr class=\"header1\">
				<th style=\"width:32px;\"></th>
				<th>{0} <a href=\"#\" id=\"cat_{1}_lolz\"{3}>[expand]</a></th>
				<th style=\"width:75px;\">Threads</th>
				<th style=\"width:50px;\">Posts</th>
				<th style=\"min-width:150px; width:15%;\">Last post</th>
			</tr>
		</tbody>
		<tbody id=\"cat_{1}\"{2}>
", ($parent==0)?$forum['cname']:'Subforums', $forum['catid'], 
	$_COOKIE['catstate'][$forum['catid']] ? ' style="display:none;"':'',
	$_COOKIE['catstate'][$forum['catid']] ? '':' style="display:none;"');

			$lastCatID = $forum['catid'];
			$firstCat = false;
			}
		}

		$NewIcon = "";
		$subforaList = '';

		if ($forum['numnew'] > 0) {
			if ($mobileLayout)
				$NewIcon = '<span class="newMarker">('.gfxnumber($forum['numnew']).') </span>';
			else
				$NewIcon = '<span class="newMarker">NEW<br>'.gfxnumber($forum['numnew']).'</span>';
		}
		
		if($forum['lastpostdate'])
		{
			$user = getDataPrefix($forum, "lu_");

			$lastLink = "";
			if($forum['lastpostid'])
				$lastLink = actionLinkTag("&raquo;", "post", $forum['lastpostid']);
			$lastLink = format("<span class=\"nom\">{0}<br>by </span>{1} {2}", formatdate($forum['lastpostdate']), UserLink($user), $lastLink);
		}
		else
			$lastLink = "----";
		
		if ($mobileLayout) {
			$theList .= '
		<tr class="cell1">
			<td>
				<span class="bold">
					'.$NewIcon.actionLinkTag($forum['title'], 'forum',  $forum['id']).'
				</span><br>
				<span class="nom smallFonts">
					'.$forum['description'].$subforaList.'
				</span>
			</td>
			<td class="cell0 smallFonts center">
				'.$lastLink.'
			</td>
		</tr>';
		} else {
		$theList .=
"
		<tr class=\"cell1\">
			<td class=\"cell2 threadIcon newMarker\">$NewIcon</td>
			<td>
				<span>".actionLinkTag($forum['title'], "forum",  $forum['id'])."<br>
					{$forum['description']}
					$localMods
					$subforaList
					$postcountmsg
				</span>
			</td>
			<td class=\"center cell2\">
				{$forum['numthreads']}
			</td>
			<td class=\"center cell2\">
				{$forum['numposts']}
			</td>
			<td class=\"smallFonts center\">
				$lastLink
			</td>
		</tr>";
		}
	}

	write(
"
	{0}
	</tbody>
</table>
",	$theList);
}

function listThread($thread, $cellClass, $dostickies = true, $showforum = false)
{
	global $haveStickies, $loguserid, $loguser, $misc, $mobileLayout;

	$forumList = "";

	$starter = getDataPrefix($thread, "su_");
	$last = getDataPrefix($thread, "lu_");

	$threadlink .= makeThreadLink($thread);
	
	$NewIcon = '';
	$newstuff = 0;
	if((!$loguserid && $thread['lastpostdate'] > time() - 900) || ($loguserid && $thread['lastpostdate'] > $thread['readdate']) && !$isIgnored)
		$NewIcon .= '<span class="newMarker">NEW </span>';
	if($thread['closed'])
		$NewIcon .= '<i class="icon-lock"></i> ';

	if($thread['sticky'] == 0 && $haveStickies == 1 && $dostickies)
	{
		$haveStickies = 2;
		if($mobileLayout)
			$forumList .= "<tr class=\"header1\"><th colspan=\"".($showforum?'8':'7')."\" style=\"height: 6px;\"></th></tr>";
		else
			$forumList .= "<tr class=\"header1\"><th colspan=\"7\" style=\"height: 8px;\"></th></tr>";
	}
	if($thread['sticky'] && $haveStickies == 0) $haveStickies = 1;

	$numpages = floor($thread['replies'] / 20);
	$pl = "";
	if($numpages >= 1)
		$threadlink .= '-- '.actionLinkTag('latest &raquo;', "thread", $thread['id'], "from=".($numpages * 20));

	$lastLink = "";
	if($thread['lastpostid'])
		$lastLink = " ".actionLinkTag("&raquo;", "post", $thread['lastpostid']);
	
	$forumList .= '<tr class="cell'.$cellClass.'"><td>'.$NewIcon.$threadlink.'<br><small>by '.UserLink($starter).$forumcell.' -- '.Plural($thread['replies'], 'reply').', '.Plural($numpages+1, 'page').'</small></td><td class="smallFonts center">'.formatdate($thread['lastpostdate']).'<br>by '.UserLink($last).' '.$lastLink.'</td></tr>';

	return $forumList;
}

function gfxnumber($num)
{
	return $num;
	// 0123456789/NA-
	
	$sign = '';
	if ($num < 0)
	{
		$sign = '<span class="gfxnumber" style="background-position:-104px 0px;"></span>';
		$num = -$num;
	}
	
	$out = '';
	while ($num > 0)
	{
		$out = '<span class="gfxnumber" style="background-position:-'.(8*($num%10)).'px 0px;"></span>'.$out;
		$num = floor($num / 10);
	}
	
	return '<span style="white-space:nowrap;">'.$sign.$out.'</span>';
}

function makeLinks($links)
{
	return $links;
}

function makeCrumbs($path='', $links='')
{
	global $mobileLayout, $layout_crumbs, $layout_actionlinks;
	
	if($path && count($path) != 0)
	{
		$pathPrefix = array(BOARD_NAME => actionLink(MAIN_PAGE));
		$path = $pathPrefix + $path;
		
		$first = true;
	
		foreach($path as $text=>$link)
		{
			if(is_array($link))
			{
				$dalink = $text;
				$tags = $link[1];
				$text = $link[0];
				$link = $dalink;
			}
			else
				$tags = "";

			$link = str_replace("&","&amp;",$link);
			
			if(!$first)
				$layout_crumbs .= " &raquo; ";
			$first = false;
			
			if(!$tags)
				$layout_crumbs .= "<a href=\"".$link."\">".$text."</a>";
			elseif(TAGS_DIRECTION === 'Left')
				$layout_crumbs .= $tags." <a href=\"".$link."\">".$text."</a>";
			else
				$layout_crumbs .= "<a href=\"".$link."\">".$text."</a> ".$tags;
		}
	}

	if($links)
	{
		$type = 'pipemenu';
		
		foreach($links as $link)
		{
			$layout_actionlinks .= '<li>'.$link.'</li>';
		}

		$layout_actionlinks = '<div style="float:right;"><ul class="'.$type.'">'.$layout_actionlinks.'</ul></div>';
	}
}

function mfl_forumBlock($fora, $catid, $selID, $indent)
{
	$ret = '';
	
	foreach ($fora[$catid] as $forum)
	{
		$ret .=
'				<option value="'.$forum['id'].'"'.($forum['id'] == $selID ? ' selected="selected"':'').'>'
	.str_repeat('&nbsp; &nbsp; ', $indent).htmlspecialchars($forum['title'])
	.'</option>
';
		if (!empty($fora[-$forum['id']]))
			$ret .= mfl_forumBlock($fora, -$forum['id'], $selID, $indent+1);
	}
	
	return $ret;
}

function makeForumList($fieldname, $selectedID)
{
	global $loguserid, $loguser;

	$pl = $loguser['powerlevel'];
	if($pl < 0) $pl = 0;
	
	$rCats = Query("SELECT id, name FROM {categories} ORDER BY corder, id");
	$cats = array();
	while ($cat = Fetch($rCats))
		$cats[$cat['id']] = $cat;

	$rFora = Query("	SELECT
							f.id, f.title, f.catid
						FROM
							{forums} f
						WHERE ".forumAccessControlSQL().(($pl < 1) ? " AND f.hidden=0" : '')."
						ORDER BY f.forder, f.id");
						
	$fora = array();
	while($forum = Fetch($rFora))
		$fora[$forum['catid']][] = $forum;

	$theList = '';
	foreach ($cats as $cid=>$cat)
	{
		if (empty($fora[$cid]))
			continue;
			
		$cname = $cat['name'];
			
		$theList .= 
'			<optgroup label="'.htmlspecialchars($cname).'">
'.mfl_forumBlock($fora, $cid, $selectedID, 0).
'			</optgroup>
';
	}

	return "<select id=\"$fieldname\" name=\"$fieldname\">$theList</select>";
}


function doLastPosts($compact, $limit)
{
	global $mobileLayout, $loguser;
	if($mobileLayout)
		$compact = true;

	$rPosts = Query("SELECT
						p.id, p.date,
						u.(_userfields),
						t.title AS ttit, t.id AS tid,
						f.title AS ftit, f.id AS fid
					FROM {posts} p
						LEFT JOIN {users} u on u.id = p.user
						LEFT JOIN {threads} t on t.id = p.thread
						LEFT JOIN {forums} f on t.forum = f.id
					WHERE ".forumAccessControlSql()."
					ORDER BY date DESC LIMIT 0, {0u}",$limit);

	while($post = Fetch($rPosts))
	{
		$thread = array();
		$thread["title"] = $post["ttit"];
		$thread["id"] = $post["tid"];

		$c = ($c+1) % 2;
		if($compact)
		{
			$theList .= format(
			"
				<tr class=\"cell{5}\">
					<td>
						{3} &raquo; {4}
						<br>{2}, {1} 
						<span style=\"float:right\">&raquo; {6}</span>
					</td>
				</tr>
			", $post['id'], formatdate($post['date']), UserLink(getDataPrefix($post, "u_")), 
				actionLinkTag($post["ftit"], "forum", $post["fid"], "", $post["ftit"]), makeThreadLink($thread), $c, 
				actionLinkTag($post['id'], "post", $post['id']));
		}
		else
		{
			$theList .= format(
			"
				<tr class=\"cell{5}\">
					<td>
						{3}
					</td>
					<td>
						{4}
					</td>
					<td>
						{2}
					</td>
					<td>
						{1}
					</td>
					<td>
						&raquo; {6}
					</td>
				</tr>
			", $post['id'], formatdate($post['date']), UserLink(getDataPrefix($post, "u_")), 
				actionLinkTag($post["ftit"], "forum", $post["fid"], "", $post["ftit"]), makeThreadLink($thread), $c,
				actionLinkTag($post['id'], "post", $post['id']));
		}
	}

	if($theList == "")
		$theList =
	"
		<tr class=\"cell1\">
			<td colspan=\"5\" style=\"text-align: center\">
				Nothing has been posted...
			</td>
		</tr>
	";

	if($compact)
		write(
		"
		<table class=\"margin outline\">
			<tr class=\"header0\">
				<th colspan=\"5\">Latest posts</th>
			</tr>
			{0}
		</table>
		", $theList);
	else
		write(
		"
		<table class=\"margin outline\">
			<tr class=\"header0\">
				<th colspan=\"5\">Latest posts</th>
			</tr>
			<tr class=\"header1\">
				<th>Forum</th>
				<th>Thread</th>
				<th>User</th>
				<th>Date</th>
				<th></th>
			</tr>
			{0}
		</table>
		", $theList);
}

function newPostForm($lepost)
{
	global $mobileLayout;
	
	$form = '
		<form name="postform" action="'.$lepost['action'].'" method="post">
			<table class="outline margin">
				<tr class="header1">
					<th colspan=2>'.$lepost['heading'].'</th>
				</tr>
				'.$lepost['top'].'
				<tr class="cell0">
					<td colspan=2>
						<textarea id="text" name="text" rows="10" style="width:98%;">'.$lepost['prefill'].'</textarea>
					</td>
				</tr>
				<tr class="cell2">
					<td colspan=2>
						<input type="submit" name="actionpost" value="Post">
						<input type="submit" name="actionpreview" value="Preview">
						<select size="1" name="mood">
							'.$lepost['moodoptions'].'
						</select>
						<label>
							<input type="checkbox" name="nopl" '.$lepost['nopl'].'>&nbsp;Disable post layout
						</label>
						<label>
							<input type="checkbox" name="nosm" '.$lepost['nosm'].'>&nbsp;Disable smilies
						</label>
						<input type="hidden" name="id" value="'.$lepost['id'].'">
						'.$lepost['mod'].'
					</td>
				</tr>
			</table>
		</form>
	';
	
	if($mobileLayout)
		echo $form;
	else
		echo '
			<table style="width:100%;">
				<tr>
					<td class="penis" style="vertical-align:top;">
						'.$form.'
					</td>
					<td class="penis" style="width:25%;vertical-align: top;">
						<table class="outline margin">
							<tr class="header0"><th>Cheatsheet</th></tr>
							<tr class="cell0 left"><td>
								<h4>Presentation</h4>
								[b]&hellip;[/b] &mdash; <strong>bold</strong> <br>
								[i]&hellip;[/i] &mdash; <em>italic</em> <br>
								[u]&hellip;[/u] &mdash; <span class="underline">underline</span> <br>
								[s]&hellip;[/s] &mdash; <del>strikethrough</del><br>
								[code]&hellip;[/code] &mdash; monospaced <br>
								[spoiler]&hellip;[/spoiler] &mdash; click to expand <br>
								[spoiler=&hellip;]&hellip;[/spoiler] <br>
								<br>
								<h4>Links</h4>
								[img]http://&hellip;[/img]<br>
								[url=http://&hellip;]&hellip;[/url] <br>
								>>123; &mdash; link to post ID <br>
								[user=123] &mdash; link to user ID <br>
								<br>
								<h4>Quotations</h4>
								[quote]&hellip;[/quote]<br>
								[quote=&hellip;]&hellip;[/quote]<br>
								[quote="&hellip;" id="&hellip;"]&hellip;[/quote] <br>
								<br>
								Most plain HTML also allowed.
							</td></tr>
						</table>
					</td>
				</tr>
			</table>';
			
	if(!$lepost['nofocus']) echo '<script type="text/javascript">document.postform.text.focus();</script>';
}
