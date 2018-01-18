var faultupdown = function(val, ontimeout=false){
	var selected = data[index_of_selected]
	var oldval = parseFloat(parseFloat(document.getElementById('sectfault').value).toFixed(2))
	var newval = (oldval+val)
	document.getElementById('sectfault').value = Number(newval).toFixed(2)
	if(!ontimeout){
		updateFault(document.getElementById('sectfault'))
		sendWellLogFieldUpdate("fault", newval, "wld_"+selected.tableid)
	} else {
		lastscrollEvent = setTimeout(function(){
			updateFault(document.getElementById('sectfault'))
			var selected = data[index_of_selected]
			sendWellLogFieldUpdate("fault", document.getElementById('sectfault').value, "wld_"+selected.tableid)
		}, 250)
	} 
}

var updateFault = function(infield){
	var newfault = parseFloat(infield.value)
	var selected = data[index_of_selected]
	var newy = []
	var oldfault = selected.fault
	var addval = newfault-oldfault
	for(var i = 0; i < selected.y.length; i++){
		newy.push(selected.y[i]-addval)
	}
	selected.y = newy
	selected.fault = newfault
	Plotly.restyle('well_log_plot' ,{y: [selected.y]},[index_of_selected])
	cascadeFaultUpdate(addval)

}


var cascadeFaultUpdate = function(addfault){
	var startindex = index_of_selected+1
	var updateIndexes = []
	var newys = []
	for(var i = startindex; i <= last_index; i++){
		var selected = data[i]
		var newy = []
		for(var j = 0; j < selected.y.length; j++){
			newy.push(selected.y[j]-addfault)
		}
		selected.y = newy
		updateIndexes.push(i);
		newys.push(newy)
	}	
	
	Plotly.restyle('well_log_plot' ,{y: newys}, updateIndexes)
}

