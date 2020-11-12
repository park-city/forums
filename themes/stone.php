<?php
$themename["stone"] = "Stone";
$themedesc["stone"] = "Theme by Kaj";
$themecode["stone"] = "@import url('/css/common-dark.css');

body { background: #0d0d0d; color: #eee; }
	
a:link, a:visited { color: #6DBDF2; }
a:active, a:hover { color: #fff; }

td:not(.safe),
th:not(.safe) {
	border-top: 1px solid #383838;
	border-left: 1px solid #383838;
	border-right: 1px solid #000; 
	border-bottom: 1px solid #000;
	background: #232323;
}

td.side.userlink:not(.safe) { border-right: 0 none; }
td.meta:not(.safe) { border-left: 0 none; }

.cell2:not(.safe),
.cell2 td:not(.safe),
td.side:not(.safe) {
	background: #1e1e1e;
}

th:not(.safe),
td.meta:not(.safe),
td.side.userlink:not(.safe) {
	background: linear-gradient(#1e1e1e, #141414);
}
";

$themeprev["stone"] = "
.safe.stone td, .safe.stone th {
	border-top: 1px solid #383838;
	border-left: 1px solid #383838;
	border-right: 1px solid #000; 
	border-bottom: 1px solid #000;
	background: #232323;
	color: #eee;
}

.safe.stone .cell2 { background: #1e1e1e; }
.safe.stone th { background: linear-gradient(#1e1e1e, #141414); }

";

?>