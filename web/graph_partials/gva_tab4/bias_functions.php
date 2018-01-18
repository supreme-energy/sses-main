var biasupdown = function(val){
	var selected = data[index_of_selected]
	var oldval = parseFloat(document.getElementById('scalebias').value)
	var newval =  (oldval+val).toPrecision(2)
	document.getElementById('scalebias').value = newval
	updatePlotBias(document.getElementById('scalebias'))
	sendWellLogFieldUpdate("scalebias", newval, "wld_"+selected.tableid)
}

var updatePlotBias = function(infield){
	var newbias  = parseFloat(infield.value)
	var selected = data[index_of_selected]
	var newx = []
	var oldbias = selected.bias
	var addval = newbias - oldbias
	 
	for(var i = 0; i < selected.x.length; i++){
		newx.push(selected.x[i]+addval)
	}
	selected.x = newx
	selected.bias = newbias
	Plotly.restyle('well_log_plot' ,{x: [selected.x]},[index_of_selected])
}

