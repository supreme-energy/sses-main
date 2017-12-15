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
	
	
	$graph_obj = new SgtaModelingTab4($_REQUEST,$tableid, $plotbias,$secttot, $scaleright);
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
			rangemode: 'nonnegative'
		  },
		  yaxis: {
			autorange: false,
            range: [ <?php echo $plotend ?>, <?php echo $plotstart ?>],
            tickangle: -45,
			ticklen: 10,
			tickmode: 'linear',
			tick0: 0,
			dtick: <?php echo $zoom ?>
	      },
	      yaxis2:{
			autorange: false,
			range: [<?php echo $endtvd + 10 ?>,<?php echo $starttvd-10 ?>],
			overlaying: 'y',
			side: 'right'
		  }
		};
var tcl = {
		x: [0, <?php echo $scaleright?>],
		y: [<?php echo $secttot ?>, <?php echo $secttot ?>],
		axis: 'y2',
		name: 'TCL',
		line: {
			color: 'black'
			}
}
var cl_tot = {
		x: [0, <?php echo $scaleright?>],
		y: [<?php echo $controltot?>,<?php echo $controltot?>],
		yaxis: 'y2',
		name: 'Control TOT',
		line: {
			color: 'red'
			}
}
var cl_bot = {
		x: [0, <?php echo $scaleright*2?>],
		y: [<?php echo $controlbot?>,<?php echo $controlbot?>],
		yaxis: 'y2',
		name: 'Control BOT',
		line: {
			color: 'red'
			}
}

var data = [tcl]
<?php foreach($graph_obj->addformplots as $log){?>
data.push(<?php $log->to_js()?>)
<?php }?>
<?php foreach($graph_obj->wellogplots as $log){?>
  data.push(<?php $log->to_js()?>)
<?php }?>
data.push(<?php echo $graph_obj->current_dataset->to_js()?>)
data.push(<?php echo $graph_obj->control_log->to_js()?>)


</script>
<div id='well_log_plot' '></div>
<script>
Plotly.newPlot('well_log_plot', data, layout);
</script>