var OnLasImport = function(rowform)
{
	window.open('/sses/welllogfilesel.php?seldbname=<?php echo $seldbname ?>','targetWindow',
                                   'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=700,height=350');
}

var sendWellLogFieldUpdate = function(field,value,id){
	var xhr = new XMLHttpRequest();
	xhr.open('GET', '/sses/json.php?path=json/sgta_modeling/update_welllog_field.php&seldbname=<?php echo $seldbname ?>&field='+field+'&value='+value+'&id='+id);
	xhr.send();
}
var sendAppInfoFieldUpdate = function(field,value){
	var xhr = new XMLHttpRequest();
	xhr.open('GET', '/sses/json.php?path=json/sgta_modeling/update_appinfo_field.php&seldbname=<?php echo $seldbname ?>&field='+field+'&value='+value);
	xhr.send();
}

var sendSgtaPositionUpdate = function(field,value){
	var xhr = new XMLHttpRequest();
	xhr.open('GET', '/sses/json.php?path=json/sgta_modeling/update_sgta_position_field.php&seldbname=<?php echo $seldbname ?>&field='+field+'&value='+value);
	xhr.send();
}



var updateFormations = function(){
	var selected = data[index_of_selected]
	var updateIndices = [0]
	var newys = [[selected.tcl, selected.tcl]]
	for(var i = 0; i < formationThickness.length; i++){
		var newval = selected.tcl+formationThickness[i]
		newys.push([newval,newval])
		updateIndices.push(i+1)
	}
	Plotly.restyle('well_log_plot' ,{y: newys},updateIndices)
}

var updateDataModelingValues = function(){
	var selected = data[index_of_selected]
	document.getElementById('realname_tablename').innerHTML = selected.filename+"( wld_"+selected.tableid+")"
	document.getElementById('sectfault').value = selected.fault
	document.getElementById('sectdip_parent').value = selected.dip
	document.getElementById('scalebias').value = selected.bias
	document.getElementById('scalefactor').value = selected.factor
}
var updateDisplayedValues = function(){
	var selected = data[index_of_selected]
	var fields_to_update = ['md_start_disp', 'md_end_disp', 'tvd_start_disp', 'tvd_end_disp', 'vs_start_disp', 'vs_end_disp']
	for(var i = 0 ; i < fields_to_update.length; i++){
		var field = fields_to_update[i]
		var split_field = field.split('_');
		var new_value = valueBylookupFieldType(selected[split_field[0]],split_field[1])
		document.getElementById(field).innerHTML = new_value
	}
}


