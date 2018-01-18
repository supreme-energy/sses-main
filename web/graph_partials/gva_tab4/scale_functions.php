var factorupdown = function(val){
  var selected = data[index_of_selected]
  var oldval = parseFloat(document.getElementById('scalefactor').value)
  var newval =  (oldval+val).toPrecision(2)
  document.getElementById('scalefactor').value = newval
  updateScaleFactor(document.getElementById('scalefactor'))
  sendWellLogFieldUpdate("scalefactor", newval, "wld_"+selected.tableid)
}


var updateScaleFactor = function(infield){
	var newfactor  = parseFloat(infield.value)
	var selected = data[index_of_selected]
	var newx = []
	var oldfactor = parseFloat(selected.factor)
	var bias = parseFloat(selected.bias)
	for(var i = 0; i < selected.x.length; i++){
		var oldval = selected.x[i]
		var newval = (oldval - bias)/oldfactor
		newval *= newfactor
		newval += bias
		newx.push(newval) 
	}
	selected.x = newx
	selected.factor = newfactor
	Plotly.restyle('well_log_plot' ,{x: [selected.x]},[index_of_selected])

}