var setVisibility = function(index, visible){
	Plotly.restyle('well_log_plot',{
		 visible: visible
		}, index)
}

var viewAll = function(vis,noupdate = false){
	indexes = []
	for(i = first_index; i <= last_index; i++){
		indexes.push(i)
	}
	setVisibility(indexes, vis)
	if(noupdate==false){
	  viewallds = 1
	  sendAppInfoFieldUpdate('viewallds',viewallds)
	}
}

var viewOnlySelected = function(){
	viewAll(false)
	setVisibility(index_of_selected, true)
	viewallds = 0
	sendAppInfoFieldUpdate('viewallds',viewallds)
}

var viewPreviousXMD  = function(){
	viewallds = parseInt(document.getElementById("viewallprevval").value)
	viewAll(false, true)
	setVisibility(index_of_selected, true)
	var indexes = []
	selected = data[index_of_selected]
	for(i = index_of_selected-1; i >= first_index; i--){
		var current = data[i]
		if((selected.md[0] - viewallds) <= current.md[current.md.length-1]){
			indexes.push(i);
		}
	}
	if(indexes.length > 0 ){
	  setVisibility(indexes,true)
	}
	sendAppInfoFieldUpdate('viewallds',viewallds)
}

var reRangeOnCurrent = function(){
	var current = data[index_of_selected]
	var new_yrange = [current.y[current.y.length-1]+(<?php echo $zoom ?>*2),current.y[0]-(<?php echo $zoom ?>*2)]
	Plotly.relayout('well_log_plot',{'yaxis.range': new_yrange})
}