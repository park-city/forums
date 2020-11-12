<?php

$themename["cheese"] = "Acmlm's Board";
$themedesc["cheese"] = "Theme by Acmlm";
$themeprev["cheese"] = "
.safe.cheese {
	background: #05012E url('/img/cheese.png');
	border-collapse: collapse;
	color: #fff;
}
.safe.cheese td,.safe.cheese th {
	background: #05012E;
	border: 1px solid #1F6FA5;
}
.safe.cheese .cell2 { background: #080151; }
.safe.cheese th { background: #0F4687; }
";
$themecode["cheese"] = "
@import url('/css/common-dark.css');

body {
	background: #05012E url('/img/cheese.png');
}

table:not(.safe) {
	border-collapse: collapse;
}

td:not(.safe),
th:not(.safe)
{
	border: 1px solid #1F6FA5;
}

.header0 th:not(.safe) { background: #0B246C; }
.header1 th:not(.safe) { background: #0F4687; }

.cell1:not(.safe),
td.post:not(.safe)
{
	background: #05012E;
}

.cell0:not(.safe),
.cell2:not(.safe),
td.side:not(.safe),
td.userlink:not(.safe),
td.meta:not(.safe)
{
	background: #080151;
}

td.side:not(.userlink):not(.safe) { border-top: 0 none; }
td.meta:not(.safe) { border-left: 0 none; }
td.userlink:not(.safe) { border-right: 0 none; border-bottom: 0 none; }

button, input[type='submit']
{
	border: 1px solid #1F6FA5;
	background: #05012E;
	color: #FFF;
}

input[type='text'], input[type='password'], input[type='file'], input[type='email'], select, textarea
{
	background: #05012E;
	border: 1px solid #1F6FA5;
	color: white;
}

input[type='checkbox'], input[type='radio']
{
	background: #05012E;
	border: 1px solid #1F6FA5;
	color: white;
}

div#tabs button.selected {
	background: #0B246C;
}
";

?>