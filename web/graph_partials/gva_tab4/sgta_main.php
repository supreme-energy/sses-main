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
	
	$yrange_start = $graph_obj->cur_depth_max + ($zoom*2);
	$yrange_end = $graph_obj->cur_depth_min - ($zoom*2);
	$xrange_start = 0;
	$xrange_end =  $scaleright;
	$query = "select * from splotlist WHERE ptype='SGTA' AND mtype='DEPTH'";
	$db->DoQuery($query);
	if($db->FetchRow()){
		$yrange_start = $db->FetchField('mintvd');
		$yrange_end   = $db->FetchField('maxtvd');
		$xrange_start = $db->FetchField('minvs');
		$xrange_end   = $db->FetchField('maxvs');
	}
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
			range: [<?php echo $xrange_start ?>,<?php echo $xrange_end ?>],
			rangemode: 'nonnegative',
			nticks: 50
		  },
		  yaxis: {
			autorange: false,
            range: [ <?php echo $yrange_start?>, <?php echo $yrange_end  ?>],
			nticks: 50
	      },
	      yaxis2:{
			autorange: false,
			range: [<?php echo $yrange_start?>, <?php echo $yrange_end  ?>],
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
	console.log('triggered')
	new_xrange = graphDiv.layout.xaxis.range
	new_yrange = graphDiv.layout.yaxis.range
	sendSgtaPositionUpdate('mintvd',new_yrange[0])
	sendSgtaPositionUpdate('maxtvd',new_yrange[1])
	sendSgtaPositionUpdate('minvs',new_xrange[0])
	sendSgtaPositionUpdate('maxvs',new_xrange[1])
}
graphDiv.on('plotly_hover', function(eventData) {
	  var xaxis = eventData.points[0].xaxis,
	      yaxis = eventData.points[0].yaxis;
	  
	  eventData.points.forEach(function(p) {
	    //console.log('pixel position', xaxis.l2p(p.x), yaxis.l2p(p.y))
	  });
	})
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