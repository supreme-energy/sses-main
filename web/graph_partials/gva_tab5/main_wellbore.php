<?php 
	include_once("classes/graphing/WellBorePlot.class.php");
	$graph_obj = new WellBorePlot($_REQUEST);
?>
<script>
<?php include 'graph_partials/shared/update_splot_field.php' ?>
var layout_mw ={
		  height: <?php echo $graph_obj->get_layout_height() ?>,
		  margin: {
			l: 60,
			r: 25,
			t: 25,
			b: 25
		  },
		  xaxis: {
			autorange: false,
			range: [<?php echo $minvs ?>, <?php echo $maxvs ?>],
			nticks: 50
		  },
		  yaxis: {
			autorange: false,
			nticks: 50,
            range: [<?php echo $maxtvd ?>, <?php echo $mintvd ?> ]
	      }
		};

var annotations_holding = {}
<?php foreach($graph_obj->annotations as $annotation ){?>

	annotations_holding['<?php echo $annotation[1]?>']=<?php echo $annotation[0] ?>;
	
<?php }?>
var data_mw = []
var last_interacted_plot = '';
var mw_index = 0;
<?php foreach($graph_obj->formations as $formation){?>
data_mw.push(<?php $formation->to_js()?>)
mw_index+=1
<?php }?>

data_mw.push(<?php echo $graph_obj->tcl->to_js()?>)
var mw_tcl_idx = mw_index
mw_index+=1
data_mw.push(<?php echo $graph_obj->proj_tcl->to_js()?>)
mw_index+=1
data_mw.push(<?php echo $graph_obj->wellplan->to_js()?>)
mw_index+=1

data_mw.push(<?php echo $graph_obj->surveys_top->to_js()?>)
mw_index+=1
data_mw.push(<?php echo $graph_obj->surveys_bot->to_js()?>)
mw_index+=1

data_mw.push(<?php echo $graph_obj->surveys->to_js()?>)
var mw_surveys_idx = mw_index
mw_index+=1

data_mw.push(<?php echo $graph_obj->final_survey->to_js()?>)
mw_index+=1
data_mw.push(<?php echo $graph_obj->bit->to_js()?>)
mw_index+=1
data_mw.push(<?php echo $graph_obj->projections->to_js()?>)
mw_index+=1
</script>
<div id='main_wellbore' style="height:<?php echo $graph_obj->get_layout_height() ?>px;width:1148px;"></div>
<script>
Plotly.newPlot('main_wellbore', data_mw, layout_mw,{scrollZoom: true});
var nowellplotchange = false;

var wellboreFieldUpdates = function(new_xrange, new_yrange){
	document.getElementById('minvs').value =new_xrange[0]
	document.getElementById('maxvs').value = new_xrange[1]
	document.getElementById('maxtvd').value = new_yrange[0]
	document.getElementById('mintvd').value = new_yrange[1]
	
	sendSplotFieldUpdate('minvs',new_xrange[0])
	sendSplotFieldUpdate('maxvs',new_xrange[1])
	sendSplotFieldUpdate('maxtvd',new_yrange[0])
	sendSplotFieldUpdate('mintvd',new_yrange[1])
}

var wellborePlotUpdate = function(){
	new_xrange = [document.getElementById('minvs').value, document.getElementById('maxvs').value]
	new_yrange = [document.getElementById('maxtvd').value, document.getElementById('mintvd').value]
	
	Plotly.relayout('main_wellbore',
	   {'xaxis.range': new_xrange,
		'yaxis.range': new_yrange		
		})
}

var wellborePlotChange = function(){
	if(nowellplotchange==false){
		var graphDiv = document.getElementById('main_wellbore')
		new_xrange = graphDiv.layout.xaxis.range
		new_yrange = graphDiv.layout.yaxis.range
		console.log(new_yrange)
		wellboreFieldUpdates(new_xrange,new_yrange)

		for(var i = 0, len = add_data_plotids.length; i < len; i++){
		  Plotly.relayout(add_data_plotids[i],{'xaxis.range': new_xrange})
		}
	} else {
		nowellplotchange=false
	}
}
var graphDiv = document.getElementById('main_wellbore')
graphDiv.on('plotly_relayout', wellborePlotChange);
graphDiv.on('plotly_hover', function(eventData) {
	  var xaxis = eventData.points[0].xaxis,
	      yaxis = eventData.points[0].yaxis;
	  
	  eventData.points.forEach(function(p) {
		
		annotation = annotations_holding[''+p.x+"_"+p.y]
		if(annotation){
		nowellplotchange = true	
		Plotly.relayout('main_wellbore', {
			'annotations': [annotation]
	        });
		}
	  });
	})

</script>