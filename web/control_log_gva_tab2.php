<script type="text/javascript" src="https://cdn.plot.ly/plotly-latest.min.js"></script>
<?
	$x_plot = Array();
	$y_plot = Array();
    $db->DoQuery("select * from $tablename where md > $startmd and md <= $endmd order by md");
    $max_y = 0;
	while($db->FetchRow()){
		array_push($x_plot, $db->FetchField("md")*-1);
		array_push($y_plot, $db->FetchField("value"));
		if($db->FetchField("value") > $max_y){
			$max_y = $db->FetchField("value");
		}
	}
	$db->DoQuery("select * FROM controllogs LIMIT 1;");
	$db->FetchRow();
	$tcl_x   = $db->FetchField("tot")*-1;
	$tcl_y   = $db->FetchField("bot")*-1;
?>
<script>
var trace2 = {
		y: [<?php echo $tcl_x?>,<?php echo $tcl_x?>],
		x: [<?php echo $tcl_y?>,<?php echo $max_y * 2?> ],
		type: 'scatter',
		name: 'TCL',
};
var trace1 = {
  y: <?php echo '[' . implode(',', $x_plot) . ']'?>,
  x: <?php echo '[' . implode(',', $y_plot) . ']'?>,
  type: 'scatter',
  showlegend: false
};



var layout = {
		  height: 950,
		  margin: {
			l: 25,
			r: 25,
			t: 25,
			b: 25
		  },
		  xaxis: {
			autorange: false,
			range: [0,<?php echo $max_y+30?>]
		  }
		};
var data = [trace1,trace2];
</script>
<div id='control_log_plot' '></div>
<script>
Plotly.newPlot('control_log_plot', data, layout);
</script>