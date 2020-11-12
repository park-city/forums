<?php
// meta
$themename["night"] = "Night";
$themedesc["night"] = "Theme by Kaj";

// theme css
$themecode["night"] = "@import url('/css/common-dark.css');

body { background: rgb(4,4,23) url(/img/bg.png); }

th:not(.safe),
td:not(.safe),
button,input[type='submit']
{
	border-top: 1px solid rgba(125, 123, 193, 0.15);
	border-left: 1px solid rgba(125, 123, 193, 0.15);
	border-right: 1px solid #000;
	border-bottom: 1px solid #000;
	background: hsla(240, 50%, 13%, 0.60);
}

td.side.userlink:not(.safe) { border-right: 0 none; }
td.meta:not(.safe) { border-left: 0 none; }

.cell2:not(.safe),
.cell2 td:not(.safe),
td.side:not(.safe)
{
	background: hsla(240, 50%, 13%, 0.80);
}

th:not(.safe) { padding: 5px; }

th:not(.safe),
td.side.userlink:not(.safe),
td.meta:not(.safe),
button,input[type='submit']
{
	background: linear-gradient(to bottom, rgba(47,47,96,0.75) 0%,rgba(37,37,86,0.75) 100%);
}

a:link,a:visited,a:active { color: #bebafe; }
a:hover	{ color: #fff;}
";

$themeprev["night"] = "
.safe.night {
	background: rgb(4,4,23) url(/img/bg.png);
	color: #fff;
	border-collapse: separate;
}

.safe.night th,
.safe.night td
{
	border-top: 1px solid rgba(125, 123, 193, 0.15);
	border-left: 1px solid rgba(125, 123, 193, 0.15);
	border-right: 1px solid #000;
	border-bottom: 1px solid #000;
	background: hsla(240, 50%, 13%, 0.60);
}

.safe.night .cell2
{
	background: hsla(240, 50%, 13%, 0.80);
}

.safe.night th {
	background: linear-gradient(to bottom, rgba(47,47,96,0.75) 0%,rgba(37,37,86,0.75) 100%);
}
";

?>