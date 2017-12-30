<?php 
	include_once("classes/graphing/AdditionalDataPlot.class.php");
	$graph_obj = new AdditionalDataPlot($_REQUEST)
?>
<script>
var gamma_layout = <?php echo $graph_obj->get_gamma_layout($minvs,$maxvs,$yscale)?>;

var layout_ad ={
		  height: <?php echo $graph_obj->get_layout_height() ?>,
		  width: 1048,
		  margin: {
			l: 60,
			r: 25,
			t: 5,
			b: 5
		  },
		  xaxis: {
			autorange: false,
			range: [<?php echo $minvs ?>, <?php echo $maxvs ?>],
			nticks: 50,
			rangemode: 'nonnegative',
			fixedrange: true,
			showticklabels: false
		  }
		};
var slide_data = []
var data_ad = []
//slides
<?php foreach($graph_obj->slides as $s){?>
slide_data.push(<?php $s->to_js()?>);
<?php } ?>
//gamma data
<?php foreach($graph_obj->gamma_plot as $ad){?>
data_ad.push(<?php $ad->to_js()?>);
<?php }?>


</script>
<div id='gamma_log' style="margin-top:0px;height:<?php echo $graph_obj->get_layout_height() ?>px;width:1148px;"></div>
<script>
Plotly.newPlot('gamma_log', slide_data.concat(data_ad), gamma_layout);
var addPlotChangeGamma = function(){
	var graphDiv = document.getElementById('gamma_log')
	new_yrange = graphDiv.layout.yaxis.range
	document.getElementById('gamma_scale').value = new_yrange[1]
	for(var i = 0, len = add_data_plotids.length; i < len; i++){
		  if(add_data_plotids[i]=='gamma_log'){
		  	continue
		  } 
		  Plotly.relayout(add_data_plotids[i],{'yaxis.range': new_yrange})
		}
}
document.getElementById('gamma_log').on('plotly_relayout', addPlotChangeGamma);
var add_data_plotids = ['gamma_log']
</script>
<?php 
$spindex = 0;
foreach($graph_obj->single_plots as $sp){?>
<script>
var layout_add_<?php echo $spindex ?> = <?php echo $graph_obj->get_single_plot_layout($minvs,$maxvs,$yscale,$spindex)?>;
var data_ad_<?php echo $spindex?> = [<?php $sp->to_js()?>]
add_data_plotids.push('single_plot_<?php echo $spindex?>')
</script>
<div id='single_plot_<?php echo $spindex?>' style="margin-top:0px;height:<?php echo $graph_obj->get_layout_height() ?>px;width:1148px;"></div>
<script>
Plotly.newPlot('single_plot_<?php echo $spindex?>', data_ad_<?php echo $spindex?>.concat(slide_data), layout_add_<?php echo $spindex ?>);

</script>
<?php $spindex+=1;}?>

<script>
var addDataScaleUpdate = function(){
	new_yrange = [0, document.getElementById('gamma_scale').value]
	Plotly.relayout('gamma_log',{'yaxis.range': new_yrange})
}
</script>