<?
	require_once("dbio.class.php");	
	$report_loader = new Reports($_REQUEST);
	$report_list = $report_loader->report_list();
?>
<table id='report_table' width='100%' class='tablesorter'>
<thead>
<tr>
<th class='surveys'>DATE</th>
<th class='surveys'>TIME</th>
<th class='surveys'>REPORT TYPE</th>
<th class='surveys'>REPORT FILE</th>
<th class='surveys'>APPROVED</th>
<th class='surveys'>DELETE</th>
<td></td>
</tr>
</thead>
<tbody>
<? foreach ($report_list as $report){
	$link="<a href='report_download.php?seldbname=".$_REQUEST['seldbname']."&id=".$report['id']."' target='_blank'>View Report</a>";
	
	?>
	<tr>
		<td><? echo date('m/d/Y', strtotime($report['created']))?></td>
		<td><? echo date('h:i a', strtotime($report['created']))?></td>
		<td><? echo $report['report_type']?></td>
		<td><? echo $link?></td>
		<td><? echo $report['approved']? 'approved':'<button onclick="window.location=\'report_managment.php?seldbname='.$_REQUEST['seldbname'].'&id='.$report['id'].'&cmd=approve\'">Approve</button'?></td>
		<td><button
		onclick="r = confirm('Are you sure you wish to delete this report?');if(r==true){window.location='<?echo "report_managment.php?seldbname=".$_REQUEST['seldbname'].'&id='.$report['id'].'&cmd=delete'?>'}"
		>Delete</button></td>
	</tr>
<?}?>
</tbody>
</table>