<?php

AssertForbidden("optimize");

if($loguser['powerlevel'] < 3)
	Kill('Access denied.');

$title = 'Optimize tables';
$crumbo = array('Admin' => actionLink('admin'), $title => actionLink('optimize'));
$layout_crumbs = MakeCrumbs($crumbo);

$rStats = Query("show table status");
while($stat = Fetch($rStats))
	$tables[$stat['Name']] = $stat;

$tablelist = "";
$total = 0;
foreach($tables as $table)
{
	$cellClass = ($cellClass+1) % 2;
	$overhead = $table['Data_free'];
	$total += $overhead;
	$status = __("OK");
	if($overhead > 0)
	{
		Query("OPTIMIZE TABLE `{".$table['Name']."}`");
		$status = "<strong>Optimized!</strong>";
	}

	$tablelist .= format(
"
	<tr class=\"cell{0}\">
		<td class=\"cell2\">{1}</td>
		<td>
			{2}
		</td>
		<td>
			{3}
		</td>
		<td>
			{4}
		</td>
	</tr>
",	$cellClass, $table['Name'], $table['Rows'], $overhead, $status);
}

write(
"
<table class=\"outline margin\">
	<tr class=\"header1\">
		<th>
			Table
		</th>
		<th>
			Rows
		</th>
		<th>
			Overhead
		</th>
		<th>
			Final status
		</th>
	</tr>
	{0}
	<tr class=\"header0\">
		<th colspan=\"7\" style=\"font-size: 130%;\">
			Excess trimmed: {1} bytes
		</th>
	</tr>
</table>

", $tablelist, $total);

?>
