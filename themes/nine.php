<?php

$themename["nine"] = "1996";
$themedesc["nine"] = "Theme by Ian Bradley";
$themecode["nine"] = "
@import url('/css/common-dark.css');

body {
	background: #000 url(/img/ninetysix.gif);
	font-family: 'Times New Roman', serif;
	font-size: 90%;
}

a:link,a:visited,a:active { color: #00ffff; }
a:hover { color: #428bca; }

.outline:not(.safe),.outline td:not(.safe),.outline th:not(.safe) { border: 1px solid #888; border-collapse: collapse; }

td:not(.safe),th:not(.safe) { padding: 4px 8px 4px 8px; }
";

$themeprev["nine"] = "
.safe.nine {
	background: #000 url(/img/ninetysix.gif);
	border-collapse: collapse;
	color: #fff;
}

.safe.nine td, .safe.nine th {
	border: 1px solid #888;
}

";

?>