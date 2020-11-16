<?php
if(!defined('DINNER')) die();
$ajaxPage = true;

$blah = FetchResult("select views from {misc}");
echo number_format($blah);

