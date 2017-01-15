<?php
	require_once("dbio.class.php");
	require_once("classes/Reports.php");
	$report_loader = new Reports($_REQUEST);
	$job_list = $report_loader->get_job_list();
	if(!isset($_REQUEST['ja'])){
		$_REQUEST['ja']=$job_list[0]['id'];
	}
	$last_report_str = "load_latest_report.php?ja=".$_REQUEST['ja'];
?>
<table id='main' class='tabcontainer' style='width: 1040px'>
<tr><td><span>Job:<select onchange="window.location='index.php?cmd=report_list&ja='+this.value">
	<?foreach($job_list as $job){?>
		<option value = '<?echo $job['id']?>' <? echo ($_REQUEST['ja']==$job['id']? 'selected':'') ?>><?echo $job['job_name']?></option>
	<?}?>
</select></span></td><td align='right'><button onclick="window.location='<?echo $last_report_str?>'">Enter Report Mode</button><span onclick="window.location='index.php?cmd=logout'"><button>logout</button></span></td></tr>
<tr><td style='width:990px;padding-top:10px;' colspan=2>
	<?php include "reports_list.php";?>
</td></tr>
<tr>
<td colspan='16'>
	<br><center><small>&#169; 2010-2011 Supreme Source Energy Services, Inc.</small></center>
</td>
</tr>
</table>