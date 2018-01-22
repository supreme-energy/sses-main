var dipupdown  = function(val, ontimeout=false){
	var selected = data[index_of_selected]
	oldval = parseFloat(document.getElementById('sectdip_parent').value)	
	newval = (oldval+val).toPrecision(3)
	if(newval < -89.9){
		newval = -89.9
	} 
	if(newval > 89.9){
		newval = 89.9
	}
	document.getElementById('sectdip_parent').value = newval
	if(!ontimeout){
		updateDip(document.getElementById('sectdip_parent').value, index_of_selected)
		sendWellLogFieldUpdate("dip", newval, "wld_"+selected.tableid)
	} else {
		lastscrollEvent = setTimeout(function(){
			var selected = data[index_of_selected]
			updateDip(document.getElementById('sectdip_parent').value, index_of_selected)
			sendWellLogFieldUpdate("dip", document.getElementById('sectdip_parent').value, "wld_"+selected.tableid)
		}, 250)
	}
}

var calcDepth = function(tvd,dip,vs,lastvs,fault,lasttvd,lastdepth){
	return tvd-(-Math.tan(dip/57.29578)*Math.abs(vs-lastvs))-fault-(lasttvd-lastdepth);
}

var updateDip = function(dip, index_to_update, cascade_down=false){
	var newdip = parseFloat(dip)
	var selected = data[index_to_update]
	var lastvs = selected.vs[0]
	var lasttvd = selected.tvd[0]
	var lastmd  = selected.md[0]
	var lastdepth = selected.y[0]
	if(index_to_update != first_index){
	  var previous = data[index_to_update - 1]
	  lastvs = previous.vs[previous.vs.length-1]
	  lasttvd = previous.tvd[previous.tvd.length-1]
	  lastmd  = previous.md[previous.md.length-1]
	  lastdepth = previous.y[previous.y.length-1]
	}

	newdepths=[]
	for(var i = 0; i < selected.tvd.length; i++){
		vs = selected.vs[i]
		tvd = selected.tvd[i]
		md = selected.md[i]
		depth = calcDepth(tvd,newdip,vs,lastvs,selected.fault,lasttvd,lastdepth)
		newdepths.push(depth)
	}
	selected.y = newdepths
	selected.dip = newdip
	Plotly.restyle('well_log_plot' ,{y: [selected.y]},[index_to_update])	
	cascadeDipUpdate(index_to_update, cascade_down, newdip)

}



var cascadeDipUpdate = function(index_to_update, cascade_down, newdip){
	var startindex = index_to_update+1
	var updateIndexes = []
	var newys = []
	for(var i = startindex; i <= last_index; i++){
		var selected = data[i]
		var previous = data[i - 1]
		lastvs = previous.vs[previous.vs.length-1]
		lasttvd = previous.tvd[previous.tvd.length-1]
		lastmd  = previous.md[previous.md.length-1]
		lastdepth = previous.y[previous.y.length-1]
		var newdepths = []
		for(var j = 0; j < selected.tvd.length; j++){
			vs = selected.vs[j]
			tvd = selected.tvd[j]
			md = selected.md[j]
			var dip = selected.dip
			if(cascade_down){
				dip = newdip
			}
			depth = calcDepth(tvd,dip,vs,lastvs,selected.fault,lasttvd,lastdepth)
			newdepths.push(depth)
		}
		selected.y = newdepths
		updateIndexes.push(i);
		newys.push(newdepths)
	}
	Plotly.restyle('well_log_plot' ,{y: newys}, updateIndexes)
}

