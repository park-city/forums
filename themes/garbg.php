<?php
$themename["garbg"] = "GarBG";
$themedesc["garbg"] = "Theme by FirePhoenix";
$themeprev["garbg"] = "
.safe.garbg { 
	background: black url(/img/garbg.png);
	color: #fff;
	border-collapse: collapse;
}
.safe.garbg td, .safe.garbg th {
	background: rgba(0, 22, 42, 0.85);
	border: 1px solid #000212;
}
.safe.garbg .cell2 {
	background: rgba(0, 38, 76, 0.85);
}
.safe.garbg th {
	background: rgba(0, 49, 100, 0.85);
	color: #eee;
}
";
$themecode["garbg"] = "
@import url('/css/common-dark.css');
@import url('/css/borders.css');

body { background: black url(/img/garbg.png); }

.header0:not(.safe), .header1:not(.safe)
{
	border-color: #000212;
}

.header0 th:not(.safe)
{
	background: rgba(0, 49, 100, 0.85);
	color: #EEEEEE;
}

.header1 th:not(.safe)
{
	background: rgba(0, 13, 34, 0.85);
}

.cell2:not(.safe)
{
	background: rgba(0, 38, 76, 0.85);
}

.cell1:not(.safe), table.post td.side:not(.safe), table.post td.userlink:not(.safe), table.post td.meta:not(.safe)
{
	background: rgba(0, 28, 56, 0.85);
}

.cell0:not(.safe), table.post td.post:not(.safe)
{
	background: rgba(0, 22, 42, 0.85);
}

.errort
{
	background: rgba(0, 49, 100, 0.85);
	border-color: #000212;
}

.errorc
{
	background: rgba(0, 28, 56, 0.85);
	border-color: #000212;
}


td, th:not(.safe)
{
	border-color: #000212;
}


table.post td.post:not(.safe)
{
	border-color: #000212;
}

button, input[type='submit']
{
	border: 1px solid #000212;
	background: rgba(0, 49, 100, 0.85);
	color: #EEEEEE;
}

input[type='text'], input[type='password'], input[type='file'], input[type='email'], select, textarea
{
	background: rgba(0, 13, 34, 0.90);
	border: 1px solid #000212;
	color: white;
}

input[type='checkbox'], input[type='radio']
{
	background: rgba(0, 13, 34, 0.90);
	border: 1px solid #000212;
	color: #B7E6FF;
}

input[type='radio']
{
	border-radius: 8px;
}

input[type='text']:focus, input[type='password']:focus, input[type='file']:focus, input[type='email']:focus, select:focus, textarea:focus
{
	border-color: rgba(0, 49, 100, 0.85);
}

div#tabs button.selected
{
	border-bottom: 1px solid rgba(0, 49, 100, 0.85);
	background: rgba(0, 49, 100, 0.85);
}

a:link,a:visited { color: #B8DEFE;}
";
?>