<?php

$ajaxPage = true;

$theme = $_GET['id'];

$themeFile = "themes/$theme/style.css";
if(!file_exists($themeFile))
	$themeFile = "themes/$theme/style.php";

$result = array(
	"css" => $themeFile,
);

echo json_encode($result);

/*
function checkForImage(&$image, $external, $file)
{
	global $dataDir, $dataUrl;

	if($image) return;

	if($external)
	{
		if(file_exists($dataDir.$file))
			$image = $dataUrl.$file;
	}
	else
	{
		if(file_exists($file))
			$image = resourceLink($file);
	}
}
*/
