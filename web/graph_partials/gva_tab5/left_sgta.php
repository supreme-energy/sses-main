<?
	include_once("classes/graphing/SgtaModelingTab4.class.php");
	
	$db->DoQuery("SELECT * FROM welllogs WHERE tablename='$lasttablename';");
	if($db->FetchRow()) {
		$tablename=$db->FetchField("tablename");
		$tableid=$db->FetchField("id");
		$secttot=$db->FetchField('tot');
	}
	$graph_obj = new SgtaModelingTab4($_REQUEST,$tableid, $plotbias,$secttot, $scaleright, true);

?>
<script>
var layout_ls = {
		  height: 700,
		  wdith: 300,
		  margin: {
			l: 60,
			r: 25,
			t: 25,
			b: 25
		  },
		  xaxis: {
			autorange: false,
			range: [0,<?php echo $scaleright?>],
			rangemode: 'nonnegative',
			nticks: 25,
			showticklabels: false
		  },
		  yaxis: {
			autorange: false,
            range: [ <?php echo ($graph_obj->cur_depth_max + ($zoom*2))?>, <?php echo ($graph_obj->cur_depth_min - ($zoom*2)) ?>],
			nticks: 50
	      },
	      yaxis2:{
			autorange: false,
			range: [<?php echo ($graph_obj->cur_tvd_max + ($zoom*2))?>, <?php echo ($graph_obj->cur_tvd_min - ($zoom*2)) ?>],
			overlaying: 'y',
			side: 'right',
			showticklabels: false
		  }
		};
var tcl = {
		x: [0, <?php echo $scaleright?>],
		y: [<?php echo $graph_obj->cur_tcl ?>, <?php echo  $graph_obj->cur_tcl ?>],
		yaxis: 'y2',
		name: 'TCL',
		showlegend: false,
		line: {
			color: 'black'
			}
	}	


var data_ls = [tcl]
<?php foreach($graph_obj->addformplots as $log){?>
data_ls.push(<?php $log->to_js()?>)
<?php }?>
<?php 
if($viewallds >= 1){
foreach($graph_obj->wellogplots as $log){?>	
	<?php 
	$startmd = $graph_obj->current_dataset->min_md;
	echo '<!---'
	echo $graph_obj->current_dataset->to_js();
	echo '-->'
	if ($viewallds==1 || ($log->min_md > ($startmd-$viewallds))){?>
	data_ls.push(<?php $log->to_js()?>)
    <?php }?>
<?php }
}?>
data_ls.push(<?php echo $graph_obj->current_dataset->to_js()?>)
data_ls.push(<?php echo $graph_obj->control_log->to_js()?>)


</script>
<div id='well_log_plot' style="width:300px; height:700px"></div>
<script>
Plotly.newPlot('well_log_plot', data_ls, layout_ls);
</script>