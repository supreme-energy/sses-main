<?
	require_once("dbio.class.php");
	require_once("classes/Reports.php");
	if(!$reports_loader){
		$report_loader = new Reports($_REQUEST);
	}
	
	$data = $report_loader->report_list($_REQUEST);
?>
<table id='anno_table' width='100%' class='tablesorter'>
<thead>
<tr>
<th class='surveys'>DATE</th>
<th class='surveys'>TIME</th>
<th class='surveys'>REPORT TYPE</th>
<td></td>
</tr>
</thead>
<tbody>
	<script>
		var current_count = <?=count($data->results)?>;
		var outstanding_request = false;
		var check_for_new_report = function(){
			
			if(!outstanding_request){
				outstanding_request=true;
			$.ajax({
				url: "report_count.php?cmd=report_count&ja=<?=$_REQUEST['ja']?>",
				cache:false,
				complete:function(){
					outstanding_request=false;
				},
				success:function(data){
					r = eval('('+data+')');
					if(r.count != current_count){
						$( "#error_popup" ).show()
						$("#error_message_area").html('A new report is ready. <a href="">Click here when you are ready to update the report list</a>');
						clearInterval();
					}
				}
			})
			}
		}
		setInterval('check_for_new_report()', 3000);
	</script>
<? foreach($data->results as $r){
	$link="<a href='report_download.php?ja=".$_REQUEST['ja']."&id=".$r->id."' target='_blank'>Download PDF Report</a>";
	$link.="&nbsp;<button onclick=\"window.location='index.php?cmd=report_view&ja=".$_REQUEST['ja']."&id=".$r->id."'\">View in Report Mode</a>";
	?>
	<tr>
	<td><? echo date('m/d/Y', strtotime($r->created))?></td>
	<td><? echo date('h:i a', strtotime($r->created))?></td>
	<td><?echo $r->report_type?></td>
	<td><?echo $link?></td>
	</tr>
<?}?>
</tbody>
</table>
