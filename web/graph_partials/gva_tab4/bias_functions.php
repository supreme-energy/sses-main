var biasupdown = function(val){
	var selected = data[index_of_selected]
	var oldval = parseFloat(document.getElementById('scalebias').value)
	var newval =  (oldval+val).toPrecision(2)
	document.getElementById('scalebias').value = newval
	updatePlotBias(newval)
	sendWellLogFieldUpdate("scalebias", newval, "wld_"+selected.tableid)
}

var updatePlotBias = function(value){
	var newbias  = parseFloat(value)
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

var allBiasUpDown = function(val){
	oldplotbias = parseFloat(document.getElementById('plotbias').value)
	var newval =  (oldplotbias+val).toPrecision(2)
	document.getElementById('plotbias').value = newval
	sendAppInfoFieldUpdate('bias', newval)
	updateAllPlotBias(newval)
}
var updateAllPlotBias = function(value){
	var startindex = first_index
	var updateIndexes = []
	var newbias  = parseFloat(value)
	var newxs = []
	
	for(var i = startindex; i <= last_index; i++){
		var selected = data[i]
		var newx = []
		var addval = newbias - oldplotbias
		for(var j = 0; j < selected.x.length; j++){
			newx.push(selected.x[j]+addval)
		}
		selected.x = newx
		updateIndexes.push(i);
		newxs.push(newx)
	}	
	
	Plotly.restyle('well_log_plot' ,{x: newxs}, updateIndexes)
}

