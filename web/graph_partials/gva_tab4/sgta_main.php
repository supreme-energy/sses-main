<script type="text/javascript" src="https://cdn.plot.ly/plotly-latest.min.js"></script>
<?
	include_once("classes/graphing/SgtaModelingTab4.class.php");
	
    $db->DoQuery("select * from controllogs limit 1;");
    $db->FetchRow();
    $tot_val = $db->FetchField('tot');
    $bot_val = $db->FetchField('bot');
	$max_x = $scaleright;
	$db->DoQuery("select * from controllogs limit 1");
	$db->FetchRow();
	$controltot = $db->FetchField("tot");
	$controlbot = $db->FetchField("bot");
	
	
	$graph_obj = new SgtaModelingTab4($_REQUEST,$tableid, $plotbias,$secttot, $scaleright, true);

?>
<script>
var layout = {
		  height: 850,
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
			nticks: 50
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
			side: 'right'
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

viewallds = <?php echo $viewallds ?>

var shadowTraces = []
var data = [tcl]

var index = 0
var index_of_selected = index

var isShadowOn = <?php echo ($viewdspcnt>=1) ? 'true' : 'false' ?>

var formationThickness = <?php echo '[' . implode(',', $graph_obj->formation_thickness) . ']' ?>

<?php foreach($graph_obj->addformplots as $log){?>
index+=1
data.push(<?php $log->to_js()?>)
<?php }?>
var first_index = index+1
<?php 
foreach($graph_obj->wellogplots as $log){?>	
  index+=1
  var obj = <?php $log->to_js()?>
  
  data.push(obj)
  if(obj.current_sel){
   index_of_selected = index	
  }
<?php }?>

var last_index = index
var control_log_index = index+1

var shadow_start_index = control_log_index+1
var shadow_end_index = shadow_start_index

data.push(<?php echo $graph_obj->control_log->to_js()?>)

</script>
<div id='well_log_plot' style="<?php if($viewrotds>=1){ ?>transform: rotate(-90deg);<?php }?>"></div>
<script>
//,{scrollZoom: true}
Plotly.newPlot('well_log_plot', data, layout,{scrollZoom: true});
var graphDiv = document.getElementById('well_log_plot')

var storeLayout = function(){
	console.log('triggered');
}
graphDiv.on('plotly_relayout', storeLayout);



<?php include 'scroll_dip_fault_zoom_controls.php' ?>
<?php include 'update_functions.php' ?>
<?php include 'data_set_selection_function.php' ?>
<?php include 'visibility_functions.php' ?>
<?php include 'shadow_functions.php' ?>
<?php include 'dip_functions.php' ?>
<?php include 'fault_functions.php' ?>
<?php include 'bias_functions.php' ?>
<?php include 'scale_functions.php' ?>

document.addEventListener("DOMContentLoaded", function(event) { 
	if(<?php echo ($viewallds == 1 ? 'true' : 'false')?>){
		viewAll()
	} else if(<?php echo ($viewallds == 0 ? 'true' : 'false') ?>){
		viewOnlySelected()
	} else {
		viewPreviousXMD()
	}
	initializeShadow()
})
</script>