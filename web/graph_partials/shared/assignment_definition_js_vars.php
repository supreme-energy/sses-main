<?php
 include_once 'load_assigned_vars_js.php';
?>
var buildDataDefinationFromLocalStorage = function(selected, inval){
    if(inval == null){
        return
    }
    var values = []
    var columns = []
    var rows = []
    var filename=''
	var field_type = 'normal'
    for(var i = 0 ; i < inval.length; i ++){
        var selObj = inval[i]
        var splitrows_cols = selObj.cell_id.split("_")
		field_type = selObj.field_type
        rows.push(splitrows_cols[1])
        columns.push(splitrows_cols[3])
        values.push(selObj.cell_value)
        filename = selObj.filename
    }
    selected.value = values.join(" ")
    selected.column = columns.join("&")
    selected.row = rows.join("&")
	selected.field_type = field_type
}

function definitionDataObj (display_name, current_value = '', table = '', field_name = '', field_type='normal'){
	this.display_name = display_name
	this.db_table = table
	this.db_field_name = field_name
	this.value = current_value
	this.filename = ''
	this.column = -1
	this.row = -1
	this.locked = 0
	this.field_type = field_type
	var storedData = JSON.parse(localStorage.getItem(display_name))
	buildDataDefinationFromLocalStorage(this, storedData)
}

var sharedVars = {
 selectable_definitions: [
 	new definitionDataObj('Operator', '<?=$opname?>', 'wellinfo', 'operatorname'),
 	new definitionDataObj('Well Name', '<?=$wellname?>', 'wellinfo', 'wellborename'),
 	new definitionDataObj('Rig Id', '<?=$rigid?>', 'wellinfo', 'rigid'),
 	new definitionDataObj('Job Number', '<?=$jobnumber?>', 'wellinfo', 'jobnumber'),
 	new definitionDataObj('API or UWI', '<?=$wellid?>', 'wellinfo', 'wellid'),
 	new definitionDataObj('Directional', '<?=$dirname?>', 'wellinfo', 'directionalname'),
 	new definitionDataObj('Field', '<?=$field?>', 'wellinfo', 'field'),
 	new definitionDataObj('Location', '<?=$location?>', 'wellinfo', 'location'),
 	new definitionDataObj('State or Province', '<?=$stateprov?>', 'wellinfo', 'stateprov'),
 	new definitionDataObj('County', '<?=$county?>', 'wellinfo', 'county'),
 	new definitionDataObj('Country', '<?=$country?>', 'wellinfo', 'country'),
 	new definitionDataObj('Starte Date', '<?=$startdate?>', 'wellinfo', 'startdate'),
 	new definitionDataObj('End Date', '<?=$enddate?>', 'wellinfo', 'enddate'),
 	new definitionDataObj('Easting(X) Survey', '<?=$survey_easting?>', 'wellinfo', 'survey_easting'),
 	new definitionDataObj('Northing(Y) Survey', '<?=$survey_northing?>', 'wellinfo', 'survey_northing'),
 	new definitionDataObj('Ground Elevation', '<?=$elev_ground?>', 'wellinfo', 'elev_ground'),
 	new definitionDataObj('RKB Elevation', '<?=$elev_rkb?>', 'wellinfo', 'elev_rkb'),
 	new definitionDataObj('Easting(X) Landing Point', '<?=$landing_easting?>', 'wellinfo', 'landing_easting'),
 	new definitionDataObj('Northing(Y) Landing Point', '<?=$landing_northing?>', 'wellinfo', 'landing_northing'),
 	new definitionDataObj('Easting(X) PBHL', '<?=$pbhl_easting?>', 'wellinfo', 'pbhl_easting'),
 	new definitionDataObj('Northing(Y) PBHL', '<?=$pbhl_northing?>', 'wellinfo', 'pbhl_northing'),
 	new definitionDataObj('Correction', '<?=$correction?>', 'wellinfo', 'correction'),
 	new definitionDataObj('Coordinate System', '<?=$coordsys?>', 'wellinfo', 'coordsys'),
	new definitionDataObj('Well Plan MD', '', 'wellplan', 'md', 'column'),
	new definitionDataObj('Well Plan INC', '', 'wellplan', 'inc', 'column'),
    new definitionDataObj('Well Plan AZM', '', 'wellplan', 'azm', 'column'),
   ]
}
