var faultupdown = function(val, ontimeout=false){
	var selected = data[index_of_selected]
	var oldval = parseFloat(parseFloat(document.getElementById('sectfault').value).toFixed(2))
	var newval = (oldval+val)
	document.getElementById('sectfault').value = Number(newval).toFixed(2)
	if(!ontimeout){
		updateFault(document.getElementById('sectfault').value, index_of_selected)
		sendWellLogFieldUpdate("fault", newval, "wld_"+selected.tableid)
	} else {
		lastscrollEvent = setTimeout(function(){
			updateFault(document.getElementById('sectfault').value, index_of_selected)
			var selected = data[index_of_selected]
			sendWellLogFieldUpdate("fault", document.getElementById('sectfault').value, "wld_"+selected.tableid)
		}, 250)
	} 
}

var updateFault = function(fault, index_to_update){
	var newfault = parseFloat(fault)
	var selected = data[index_to_update]
	var newy = []
	var oldfault = selected.fault
	var addval = newfault-oldfault
	for(var i = 0; i < selected.y.length; i++){
		newy.push(selected.y[i]-addval)
	}
	selected.y = newy
	selected.fault = newfault
	Plotly.restyle('well_log_plot' ,{y: [selected.y]},[index_to_update])
	cascadeFaultUpdate(addval,index_to_update)

}


var cascadeFaultUpdate = function(addfault, si){
	var startindex = si+1
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

