<?php
$themename["holo"] = "Holo";
$themedesc["holo"] = "Theme by Nadia";
$themeprev["holo"] = "
.safe.holo {
	background: #111;
	border-radius: 2px;
	border: 1px solid #36383B;
	box-shadow: 0px 0px 0px 3px rgba(0, 0, 0, 0.25);
	color: #fff;
}
.safe.holo th {
	color: #ccc;
	border-bottom: 1px solid #36383b;
}
";
$themecode["holo"] = "
@import url('/css/common-dark.css');

body {
	background: linear-gradient(#000, #272D33);
	background-attachment: fixed;
}

*::selection {
	background: #33B5E5;
	color: #000;
}

:not(.safe) a:link,
:not(.safe) a:visited,
:not(.safe) a:active { color: #33B5E5; }
:not(.safe) a:hover { color: #fff; }

:not(.safe) td.meta .pipemenu li a,
.mainMenu li a {
	color: #fff !important;
	text-transform: uppercase;
}

.mainMenu li:hover {
	background: #36383B;
}

/* general tables */

table:not(.safe) {
	background: #111;
	border-radius: 2px;
	border: 1px solid #36383B;
	box-shadow: 0px 0px 0px 3px rgba(0, 0, 0, 0.25);
}

th:not(.safe) {
	color: #ccc;
	padding-top: 4px;
	padding-bottom: 4px;
}

th:not(.safe), .mobile-postheader:not(.deleted) {
	border-bottom: 1px solid #36383B;
}

#header {
	border-bottom: 2px solid #33B5E5;
}

#header * {
	box-shadow: none;
}

/* posts */

td.side:not(.safe), td.post:not(.safe) {
	background: #131313;
}

td.userlink:not(.safe), td.meta:not(.safe) {
	background: #131313;
	border-bottom: 2px solid #33B5E5;
}

/* input */

input, textarea, select {
	color: #fff;
	background: transparent;
	border-bottom: 1px solid #313131;
	padding: 4px;
}

input:focus, textarea:focus, select:focus {
	border-bottom: 1px solid #33B5E5;
	outline: none;
}

button,
input[type='submit']
{
	background: #5D5D5D;
	border: 1px solid #646464;
	color: #FFF;
}

button:hover,
input[type='submit']:hover {
	background: #33B5E5;
}

div#tabs button {
	text-transform: uppercase;
	border-radius: 0px;
	margin: 0px;
	background: none;
	border: 0px none;
	color: #FFF;
	height: 32px;
	padding-left: 12px;
	padding-right: 12px;
	margin-bottom: 0px;
}

div#tabs button.selected {
	border-bottom: 2px solid #33B5E5;
	margin-bottom: 0px;
	outline: none;
}

div#tabs button:active {
	background: #33B5E5;
}
";

?>