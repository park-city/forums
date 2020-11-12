<?php

$themename["abxd20"] = "ABXD 2.0";
$themedesc["abxd20"] = "Theme by Kawa";
$themeprev["abxd20"] = "
.safe.abxd20 {
	background: #000 url(/img/abxd20.png) repeat-x; color: #fff;
	box-shadow: 0px 0px 4px rgb(192, 192, 255);
	border-radius: 8px;
}
.safe.abxd20 td, .safe.abxd20 th { border: 1px solid rgba(0, 0, 0, 0.5); }
.safe.abxd20 td { background: rgba(160, 160, 255, 0.025); }
.safe.abxd20 .cell2 { background: rgba(160, 160, 255, 0.075); }
.safe.abxd20 th {
	background: rgba(160, 160, 255, 0.200);
	color: white;
	text-shadow: 1px 1px 2px black;
	border-top-left-radius: 8px;
	border-top-right-radius: 8px;
}
.safe.abxd20 tr:last-child td:first-child { border-bottom-left-radius: 8px; }
.safe.abxd20 tr:last-child td:last-child { border-bottom-right-radius: 8px; }
";
$themecode["abxd20"] = "@import url('/css/common-dark.css');
@import url('/css/roundcorners.css');

body { background: #000 url(/img/abxd20.png) repeat-x; }

#header,#header *,#footer,#footer *,#mobile-footer,#mobile-footer *,#crumbs,#crumbs * {
	background: none !important;
	box-shadow: none;
	border: 0px none;
}

.outline:not(.safe){ box-shadow: 0px 0px 4px rgb(192, 192, 255); }

.cell0:not(.safe) { background: rgba(160, 160, 255, 0.025); }
.cell1:not(.safe) { background: rgba(160, 160, 255, 0.050); }
.cell2:not(.safe) { background: rgba(160, 160, 255, 0.075); }

th:not(.safe) {
	background: rgba(160, 160, 255, 0.200);
	color: white;
	text-shadow: 1px 1px 2px black;
}

td:not(.safe), th:not(.safe) {
	border: 1px solid rgba(0, 0, 0, 0.5);
}

table.post:not(.safe)
{
	width: 100%;
	background: rgba(160, 160, 255, 0.000);
	border: 1px solid rgba(160, 160, 255, 0.500);
}

table.post td.side:not(.safe)
{
	width: 15%;
	vertical-align: top;
	background: rgba(160, 160, 255, 0.100);
	border: 0px none;
}

table.post td.post:not(.safe)
{
	background: rgba(160, 160, 255, 0.075);
	border: 1px solid #000;
	border-bottom: 0px none;
	border-right: 0px none;
	vertical-align: top;
	height: 100px;
}

table.post td.post td:not(.safe)
{
	border: 0px none;
}

table.post td.meta:not(.safe)
{
	font-size: 0.80em; /* 8pt; */
	height: 10px;
	margin: 0px;
	border: 0px none;
	background: rgba(160, 160, 255, 0.100);
}

table.post td.links:not(.safe)
{
	margin: 0px;
	border: 0px none;
}

table.outline, table.message, .errorc, table.post:not(.safe) {
	border-radius: 8px; }

h3
{
	border: 0px none;
}

td.post code.Code, .geshi
{
	background: rgba(0, 0, 0, 0.75);
}

button.expander
{
	color: white;
	border: 1px solid rgba(160, 160, 255, 0.175);
}

button, input[type='submit']
{
	border: 1px solid rgba(160, 160, 255, 0.175);
	background: rgba(160, 160, 255, 0.200);
	border-radius: 8px;
	color: white;
}

input[type='text'], input[type='password'], input[type='file'], input[type='email'], select, textarea
{
	border: 1px solid rgba(160, 160, 255, 0.175);
	background: rgba(0, 0, 0, 0.500);
	color: white;
}

input[type='checkbox'], input[type='radio']
{
	border: 1px solid rgba(160, 160, 255, 0.175);
	background: rgba(0, 0, 0, 0.500);
	color: white;
	border-radius: 2px;
}

input[type='radio']
{
	border-radius: 8px;
}
";

?>