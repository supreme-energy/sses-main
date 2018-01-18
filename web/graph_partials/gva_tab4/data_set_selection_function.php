var valueBylookupFieldType = function(inarr, ltype){
	if(ltype=='end'){
		return inarr[inarr.length - 1]
	} else {
		return inarr[0]
	}
}

var goToDataSetAt    = function(){
	var md = parseInt(document.getElementById("gotodatasetat").value)
	var didmove = false
	var starting_index = index_of_selected
	for(i = first_index; i <= last_index; i++){
		var current = data[i]
		if(current.md[0] <= md && current.md[current.md.length-1] > md){
			new_index = i
			didmove = true
		} 
	}
	if(!didmove){
		if(data[first_index].md[0] >= md){
			new_index = first_index
			didmove = true
		} else if(data[last_index].md[data[last_index].md.length-1] <= md){
			new_index = last_index
			didmove = true
		}		
	}

	if(didmove && starting_index != new_index){
		var move_to = Math.abs(starting_index - new_index)
		if(starting_index > new_index){
				move_to = move_to*-1
		}
		moveDataSetBy(move_to)
		reRangeOnCurrent()
	}
}

var moveDataSetBy = function(val){	
	visibility_of_last = true
	if(viewallds == 0){
		visibility_of_last = false
	}
	Plotly.restyle('well_log_plot',{
        visible: visibility_of_last,
		line: {
		    color: '#00008B'
	      }
		}, index_of_selected)
	Plotly.restyle('well_log_plot',{
		visible: true,
		line: {
		    color: '#ff0000'
	      }
		}, index_of_selected+val)	
	index_of_selected +=val	
	if(viewallds > 1){
		viewPreviousXMD()
	}
	updateDisplayedValues()
	updateDataModelingValues()
	updateFormations()
	sendAppInfoFieldUpdate('tablename',data[index_of_selected].name)
}

var nextDataSet = function(){
	if(index_of_selected+1 > last_index){
		return
	}
	moveDataSetBy(1)
	//reRangeOnCurrent()
}
var prevDataSet = function(){
	if(index_of_selected-1 < first_index){
		return
	}
	moveDataSetBy(-1)
	//reRangeOnCurrent()
}

var firstDataSet = function(){
	moveDataSetBy(first_index - index_of_selected )
	reRangeOnCurrent()
}

var lastDataSet = function(){
	moveDataSetBy(last_index - index_of_selected)
	reRangeOnCurrent()
}


