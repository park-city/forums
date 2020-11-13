<?php

if (!function_exists(srgb)) {
	function srgb($r, $g, $b, $f = 1.0) {
		$r*=1.45;
		$g*=1.45;
		$b*=1.45;
		return array('r' => $f * $r, 'g' => $f * $g, 'b' => $f * $b);
	}

	function fadesch($c, $n, $pct) {
		$pct2 = 1 - $pct;
		$ret = floor($c[$n]['r'] * $pct2 + $c[$n + 1]['r'] * $pct) * 65536 + 
				floor($c[$n]['g'] * $pct2 + $c[$n + 1]['g'] * $pct) * 256 + 
				floor($c[$n]['b'] * $pct2 + $c[$n + 1]['b'] * $pct);
		return $ret;
	}

	function rgbacol($hex, $a) {
		list($r, $g, $b) = sscanf($hex, '%02X%02X%02X');
		$a = $a / 127;
		return "rgba($r,$g,$b,$a)";
	}

	function sqr($x) {
		return $x;
	}
}

$curtime = getdate(time());
$whores = $curtime['hours'] + ((int)$loguser['timezone']/3600);
$minty = $curtime['minutes'] + floor(abs($loguser['timezone']/60)%60);
$min = $whores * 60 + $minty;

$tbg1[1] = srgb(10, 10, 33);
$tbg2[1] = srgb(7, 7, 22);
$tbg3[1] = srgb(4, 4, 11);
$thb[1] = srgb(23, 23, 80);
$tbd[1] = srgb(60, 65, 166, 0.7);
$lnk[1] = srgb(90, 100, 200);
$cbg[1] = srgb(17, 17, 57);
$tbg1[2] = srgb(48, 6, 82);
$tbg2[2] = srgb(34, 5, 50);
$tbg3[2] = srgb(20, 4, 18);
$thb[2] = srgb(70, 30, 110);
$tbd[2] = srgb(118, 66, 165, 0.7);
$lnk[2] = srgb(140, 100, 200);
$cbg[2] = srgb(59, 18, 96);
$tbg1[3] = srgb(0, 10, 86);
$tbg2[3] = srgb(0, 5, 48);
$tbg3[3] = srgb(0, 0, 12);
$thb[3] = srgb(0, 80, 160);
$tbd[3] = srgb(0, 112, 192, 0.7);
$lnk[3] = srgb(0, 140, 255);
$cbg[3] = srgb(0, 40, 128);
$tbg1[4] = srgb(50, 10, 9);
$tbg2[4] = srgb(31, 7, 7);
$tbg3[4] = srgb(12, 4, 4);
$thb[4] = srgb(96, 24, 13);
$tbd[4] = srgb(190, 106, 32, 0.7);
$lnk[4] = srgb(255, 150, 40);
$cbg[4] = srgb(73, 17, 11);
$tbg1[5] = $tbg1[1];
$tbg2[5] = $tbg2[1];
$tbg3[5] = $tbg3[1]; //I believe there was a typo here.
$thb[5] = $thb[1];
$tbd[5] = $tbd[1];
$lnk[5] = $lnk[1];
$cbg[5] = $cbg[1];
$n = floor($min / 360) + 1;
$pct = ($min - floor($min / 360) * 360) / 360;
$pct2 = 1 - $pct;
$tblbg1 = fadesch($tbg1, $n, $pct);
$tblbg2 = fadesch($tbg2, $n, $pct);
$tblbg3 = fadesch($tbg3, $n, $pct);
$tblhb = fadesch($thb, $n, $pct);
$tblbd = fadesch($tbd, $n, $pct);
$catbg = fadesch($cbg, $n, $pct);

//return ($x<128)?(128*pow($x/128.0,4.0)):(128*(1.0-pow(1.0-($x/128.0),4.0))); }
$scr1 = floor(192 + sqr($tbd[$n]['r'] * $pct2 + $tbd[$n + 1]['r'] * $pct) * 0.25) * 65536 + floor(192 + sqr($tbd[$n]['g'] * $pct2 + $tbd[$n + 1]['g'] * $pct) * 0.25) * 256 + floor(192 + sqr($tbd[$n]['b'] * $pct2 + $tbd[$n + 1]['b'] * $pct) * 0.25);
$scr2 = floor(128 + sqr($tbd[$n]['r'] * $pct2 + $tbd[$n + 1]['r'] * $pct) * 0.50) * 65536 + floor(128 + sqr($tbd[$n]['g'] * $pct2 + $tbd[$n + 1]['g'] * $pct) * 0.50) * 256 + floor(128 + sqr($tbd[$n]['b'] * $pct2 + $tbd[$n + 1]['b'] * $pct) * 0.50);
$scr3 = floor(64 + sqr($tbd[$n]['r'] * $pct2 + $tbd[$n + 1]['r'] * $pct) * 0.75) * 65536 + floor(64 + sqr($tbd[$n]['g'] * $pct2 + $tbd[$n + 1]['g'] * $pct) * 0.75) * 256 + floor(64 + sqr($tbd[$n]['b'] * $pct2 + $tbd[$n + 1]['b'] * $pct) * 0.75);
$tablebg1 = substr(dechex($tblbg1 + 16777216), -6);
$tablebg2 = substr(dechex($tblbg2 + 16777216), -6);
$tablebg3 = substr(dechex($tblbg3 + 16777216), -6);
$tableheadbg = substr(dechex($tblhb + 16777216), -6);
$tableborder = substr(dechex($tblbd + 16777216), -6);
$categorybg = substr(dechex($catbg + 16777216), -6);
$sc1 = substr(dechex($scr1 + 16777216), -6);
$sc2 = substr(dechex($scr2 + 16777216), -6);
$sc3 = substr(dechex($scr3 + 16777216), -6);

?>