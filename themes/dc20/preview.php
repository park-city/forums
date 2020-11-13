<?php
header("Content-Type: text/css");

include('dc20.php');

$css = ".safe.dc20 {
	color: #acd;
	background: #$tablebg2 url(".$imgname.".png);
	border: #".$tableborder." 1px solid;
	border-collapse: separate;
}

.safe.dc20 td, .safe.dc20 th {
	border-left: 1px solid #000;
	border-top:  1px solid #000;
}

.safe.dc20 td:not(.cell2), .safe.dc20 th {
	border-right: 1px solid #000;
}

.safe.dc20 tr:last-child td {
	border-bottom: 1px solid #000;
}

.safe.dc20 th {
	background: ".rgbacol($tableheadbg, 60).";
	color: #ddd;
}

.safe.dc20 td { background: ".rgbacol($tablebg2, 60)."; }
.safe.dc20 .cell2 { background: ".rgbacol($tablebg1, 60)."; }";

print $css;

?>