<?php
header("Content-Type: text/css");

include('dc20.php');

$css = "@import url('../../css/common-dark.css');
@import url('../../css/borders.css');

body {
	color: #e0e0e0;
	background: #$tablebg2 url('img/bg.png');
}

a:link,a:visited,a:active {color:#".$sc2."}
a:hover {color:#".$sc1."}

table:not(.safe) {
	color: #acd;
}

.outline:not(.safe), table.post:not(.safe) {
	border: #".$tableborder." 1px solid;
	border-collapse: separate;
}

.header0 th:not(.safe) {
	background: ".rgbacol($tableheadbg, 60).";
	color: #ddd;
}

.header1 th:not(.safe) {
	background: ".rgbacol($categorybg, 60).";
	color: #eee;
}

td:not(.safe) { background: ".rgbacol($tablebg2, 60)."; }
.cell2:not(.safe),.meta:not(.safe),.side:not(.safe) { background: ".rgbacol($tablebg1, 60)."; }

textarea,input,select,button{
	border:    #".$sc2." ridge 2px;
	background:#000;
	color:     #fff;
}";

print $css;

?>