<?php

$hugeInt = "bigint(20) NOT NULL DEFAULT '0'";
$genericInt = "int(11) NOT NULL DEFAULT '0'";
$smallerInt = "int(8) NOT NULL DEFAULT '0'";
$bool = "tinyint(1) NOT NULL DEFAULT '0'";
$notNull = " NOT NULL DEFAULT ''";
$text = "text DEFAULT ''"; //NOT NULL breaks in certain versions/settings.
$postText = "mediumtext DEFAULT ''";
$var128 = "varchar(128)".$notNull;
$var256 = "varchar(256)".$notNull;
$var1024 = "varchar(1024)".$notNull;
$AI = "int(11) NOT NULL AUTO_INCREMENT";
$keyID = "primary key (`id`)";

$tables = array
(
	//Weird column names: An entry means that "blockee" has blocked the layout of "user"
	"blockedlayouts" => array
	(
		"fields" => array
		(
			"user" => $genericInt,
			"blockee" => $genericInt,
		),
		"special" => "key `mainkey` (`blockee`, `user`)"
	),
	"categories" => array
	(
		"fields" => array
		(
			"id" => $AI,
			"name" => $var256,
			"corder" => $smallerInt,
		),
		"special" => $keyID
	),
	"forummods" => array
	(
		"fields" => array
		(
			"forum" => $genericInt,
			"user" => $genericInt,			
		),
		"special" => "key `mainkey` (`forum`, `user`)"
	),
	"forums" => array
	(
		"fields" => array
		(
			"id" => $AI,
			"title" => $var256,
			"description" => $text,
			"catid" => $smallerInt,
			"minpower" => $smallerInt, //
			"minpowerthread" => $smallerInt, //
			"minpowerreply" => $smallerInt, //
			"numthreads" => $genericInt,
			"numposts" => $genericInt,
			"lastpostdate" => $genericInt,
			"lastpostuser" => $genericInt,
			"lastpostid" => $genericInt,
			"hidden" => $bool,
			"forder" => $smallerInt,
			"l" => $genericInt,
			"r" => $genericInt,
			"redirect" => $var256,
			"offtopic" => $bool,
		),
		"special" => $keyID.", key `catid` (`catid`), key `l` (`l`), key `r` (`r`)"
	),
	"guests" => array
	(
		"fields" => array
		(
			"id" => $AI,
			"ip" => "varchar(64)".$notNull,
			"date" => $genericInt,
			"lasturl" => "varchar(256)".$notNull,
			"lastforum" => $genericInt,
			"useragent" => "varchar(256)".$notNull,			
			"bot" => $bool,
		),
		"special" => $keyID.", key `ip` (`ip`), key `bot` (`bot`)"
	),
	"ignoredforums" => array
	(
		"fields" => array
		(
			"uid" => $genericInt,
			"fid" => $genericInt,			
		),
		"special" => "key `mainkey` (`uid`, `fid`)"
	),
	"ip2c" => array
	(
		"fields" => array
		(
			"ip_from" => "bigint(12) NOT NULL DEFAULT '0'",
			"ip_to" => "bigint(12) NOT NULL DEFAULT '0'",
			"cc" => "varchar(2) DEFAULT ''",			
		),
		"special" => "key `ip_from` (`ip_from`)"
	),
	"ipbans" => array
	(
		"fields" => array
		(
			"ip" => "varchar(45)".$notNull,
			"reason" => $var128,			
			"date" => $genericInt,			
			"whitelisted" => $bool,
		),
		"special" => "unique key `ip` (`ip`), key `date` (`date`)"
	),
	"misc" => array
	(
		"fields" => array
		(
			"version" => $genericInt,
			"views" => $genericInt,
			"hotcount" => $genericInt,			
			"maxusers" => $genericInt,
			"maxusersdate" => $genericInt,
			"maxuserstext" => $text,
			"maxpostsday" => $genericInt,
			"maxpostsdaydate" => $genericInt,
			"maxpostshour" => $genericInt,
			"maxpostshourdate" => $genericInt,
			"milestone" => $text,
		),
	),
	"moodavatars" => array
	(
		"fields" => array
		(
			"id" => $AI,
			"uid" => $genericInt,			
			"mid" => $genericInt,			
			"name" => $var256,
		),
		"special" => $keyID. ", key `mainkey` (`uid`, `mid`)"
	),
	"passmatches" => array
	(
		"fields" => array
		(
			"date" => $genericInt,
			"ip" => "varchar(50)".$notNull,
			"user" => $genericInt,
			"matches" => "varchar(200)".$notNull,
		),
	),
	"permissions" => array
	(
		"fields" => array
		(
			"applyto" => "tinyint(4) NOT NULL DEFAULT '0'",
			"id" => $genericInt,
			"perm" => "varchar(32)".$notNull,
			"arg" => $genericInt,
			"value" => "tinyint(4) NOT NULL DEFAULT '0'",
		),
		"special" => "primary KEY (`applyto`,`id`,`perm`,`arg`), KEY `perm` (`perm`,`arg`), KEY `applyto` (`applyto`,`id`), KEY `applyto_2` (`applyto`,`id`,`perm`)"
	),
	"posts" => array
	(
		"fields" => array
		(
			"id" => $AI,
			"thread" => $genericInt,
			"user" => $genericInt,
			"date" => $genericInt,
			"ip" => "varchar(45)".$notNull,
			"num" => $genericInt,
			"deleted" => $bool,
			"deletedby" => $genericInt,
			"reason" => "varchar(300)".$notNull,
			"options" => "tinyint(4) NOT NULL DEFAULT '0'",
			"mood" => $genericInt,
			"currentrevision" => $genericInt,
		),
		"special" => $keyID.", key `thread` (`thread`), key `date` (`date`), key `user` (`user`), key `ip` (`ip`), key `id` (`id`, `currentrevision`), key `deletedby` (`deletedby`)"
	),
	"posts_text" => array
	(
		"fields" => array
		(
			"pid" => $genericInt,
			"text" => $postText,
			"revision" => $genericInt,
			"user" => $genericInt,
			"date" => $genericInt,
		),
		"special" => "fulltext key `text` (`text`), key `pidrevision` (`pid`, `revision`), key `user` (`user`)"
	),
	"proxybans" => array
	(
		"fields" => array
		(
			"id" => $AI,			
			"ip" => "varchar(45)".$notNull,
		),
		"special" => $keyID.", unique key `ip` (`ip`)"
	),
	"queryerrors" => array
	(
		"fields" => array
		(
			"id" => $AI,		
			"user" => $genericInt,	
			"ip" => "varchar(50)".$notNull,
			"time" => $genericInt,	
			"query" => $text,
			"get" => $text,
			"post" => $text,
			"cookie" => $text,
			"error" => $text
		),
		"special" => $keyID
	),
	"log" => array
	(
		"fields" => array
		(
			"user" => $genericInt,
			"date" => $genericInt,
			"type" => "varchar(16)".$notNull,
			"user2" => $genericInt,
			"thread" => $genericInt,
			"post" => $genericInt,
			"forum" => $genericInt,
			"forum2" => $genericInt,
			"pm" => $genericInt,
			"text" => "varchar(1024)".$notNull,
			"ip" => "varchar(50)".$notNull,
		),
	),
	"secondarygroups" => array
	(
		"fields" => array
		(
			"userid" => $genericInt,
			"groupid" => $genericInt,
		),
		"special" => "primary KEY (`userid`,`groupid`)"
	),
	"sessions" => array
	(
		"fields" => array
		(
			"id" => $var256,
			"user" => $genericInt,
			"expiration" => $genericInt,
			"autoexpire" => $bool,
			"iplock" => $bool,
			"iplockaddr" => $var128,
			"lastip" => $var128,
			"lasturl" => $var128,
			"lasttime" => $genericInt,
		),
		"special" => $keyID.", key `user` (`user`), key `expiration` (`expiration`)"
	),
	"smilies" => array
	(
		"fields" => array
		(
			"id" => $AI,
			"code" => "varchar(32)".$notNull,
			"image" => "varchar(32)".$notNull,
		),
		"special" => $keyID
	),
	"threads" => array
	(
		"fields" => array
		(
			"id" => $AI,
			"forum" => $genericInt,
			"user" => $genericInt,
			"date" => $genericInt,
			"firstpostid" => $genericInt,
			"views" => $genericInt,
			"title" => $var128,
			"icon" => "varchar(200)".$notNull,
			"replies" => $genericInt,
			"lastpostdate" => $genericInt,
			"lastposter" => $genericInt,
			"lastpostid" => $genericInt,
			"closed" => $bool,
			"sticky" => $bool,
		),
		"special" => $keyID.", key `forum` (`forum`), key `user` (`user`), key `sticky` (`sticky`), key `lastpostdate` (`lastpostdate`), key `date` (`date`), fulltext key `title` (`title`)"
	),
	"threadsread" => array
	(
		"fields" => array
		(
			"id" => $genericInt,
			"thread" => $genericInt,
			"date" => $genericInt,
		),
		"special" => "primary key (`id`, `thread`)"
	),
	"usergroups" => array
	(
		"fields" => array
		(
			"id" => $genericInt,
			"name" => "varchar(32)".$notNull,
			"title" => $var256, 
			"rank" => $genericInt,
			"type" => "tinyint(4) NOT NULL DEFAULT '0'",
			"display" => "tinyint(4) NOT NULL DEFAULT '0'",
		),
		"special" => $keyID
	),
	"users" => array
	(
		"fields" => array
		(
			"id" => $AI,
			"name" => "varchar(32)".$notNull,
			"displayname" => "varchar(32)".$notNull,
			"password" => $var256,
			"pss" => "varchar(16)".$notNull,
			"primarygroup" => $genericInt,
			"flags" => "smallint(6) NOT NULL DEFAULT '0'",
			"posts" => $genericInt,
			"regdate" => $genericInt,
			"minipic" => $var128,
			"picture" => $var128,
			"title" => $var256,
			"postheader" => $text,
			"signature" => $text,
			"bio" => $text,
			"css" => $text,
			"color" => $var128,
			"hascolor" => $bool,
			"rankset" => $var128,
			"lastknownbrowser" => $text,
			"birthday" => $genericInt,
			"email" => "varchar(60)".$notNull,
			"lastposttime" => $genericInt,
			"lastactivity" => $genericInt,
			"lastip" => "varchar(50)".$notNull,
			"lasturl" => $var128,
			"lastforum" => $genericInt,
			"timezone" => "float NOT NULL DEFAULT '0'",
			"theme" => "varchar(64)".$notNull,
			"signsep" => $bool,
			"dateformat" => "varchar(20) NOT NULL DEFAULT 'm-d-y'",
			"timeformat" => "varchar(20) NOT NULL DEFAULT 'h:i a'",
			"blocklayouts" => $bool,
			"globalblock" => $bool,
			"fulllayout" => $bool,
			"showemail" => $bool,
			"newcomments" => $bool,
			"tempbantime" => $hugeInt,
			"tempbanpl" => $smallerInt,
			"forbiddens" => $var1024,
			"pluginsettings" => $text,
			"lostkey" => $var128,
			"lostkeytimer" => $genericInt,
			"loggedin" => $bool,
		),
		"special" => $keyID.", key `posts` (`posts`), key `name` (`name`), key `lastforum` (`lastforum`), key `lastposttime` (`lastposttime`), key `lastactivity` (`lastactivity`)"
	),
	'wiki_pages' => array
	(
		'fields' => array
		(
			'id' => $var128,
			'revision' => $genericInt,
			'flags' => $genericInt,
		),
		'special' => $keyID
	),
	'wiki_pages_text' => array
	(
		'fields' => array
		(
			'id' => $var128,
			'revision' => $genericInt,
			'date' => $genericInt,
			'user' => $genericInt,
			'text' => $text,
		),
		'special' => 'UNIQUE KEY `wpt` (`id`,`revision`)'
	)
);

?>