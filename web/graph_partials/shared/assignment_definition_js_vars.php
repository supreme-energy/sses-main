<?php
 include_once 'load_assigned_vars_js.php';
 include_once 'update_wellinfo.php';
?>

  Array.prototype.eachSlice = function (size) {
    var arr = []
    if (this !== undefined) {
      for (var i = 0, l = this.length; i < l; i += size) {
        arr.push(this.slice(i, i + size))
      }
    }
    return arr
  }

  Array.prototype.contains = function (elem) {
    if (this === undefined) {
      return true
    }
    return (this.indexOf(elem) > -1)
  }

  Array.prototype.intersect = function (array) {
    // this is naive--could use some optimization
    var result = []
    if (this !== undefined) {
      for (var i = 0; i < this.length; i++) {
        if (array.contains(this[i]) && !result.contains(this[i])) { result.push(this[i]) }
      }
    }
    return result
  }

  Array.prototype.union = function (a) {
    if (this !== undefined) {
      var r = this.slice(0)
      a.forEach(function (i) { if (r.indexOf(i) < 0) r.push(i) })
      return r
    } else {
      return []
    }
  }
var fuzzyMatch = function (value1, value2) {
      var n1 = nameBigram(value1)
      var n2 = nameBigram(value2)
      var intersect = n1.intersect(n2)
      var union = n1.union(n2)
      var similarity_factor = intersect.length / union.length
      return similarity_factor >= 0.75
}
var nameBigram = function (n) {
      var n1 = []
      n.split('').eachSlice(2).forEach(function (el) {
        n1.push(el.join().toLowerCase())
      })
      n.slice(1).split('').eachSlice(2).forEach(function (el) {
        n1.push(el.join().toLowerCase())
      })
      return n1.filter(function (value, index, self) { return self.indexOf(value) === index })
    }
    
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
	selected.filename = filename
}

function definitionDataObj (display_name, current_value = '', table = '', field_name = '', field_type='normal', filetype='not_specified'){
	this.display_name = display_name
	this.db_table = table
	this.db_field_name = field_name
	this.value = current_value
	this.filename = ''
	this.column = -1
	this.row = -1
	this.locked = localStorage.getItem(display_name+"_lock", 0)
	this.field_type = field_type
	this.filetype = filetype
	var storedData = JSON.parse(localStorage.getItem(display_name))
	this.dbStore = function(){
		console.log(this)
		if( this.field_type == 'normal' || this.field_type == 'not_specified'){
			if(this.db_table == 'wellinfo'){
				let xhr = new XMLHttpRequest();
				xhr.open('GET', '/sses/json.php?path=json/sgta_modeling/update_wellinfo_field.php&seldbname=<?php echo $seldbname ?>&field='+this.db_field_name+'&value='+this.value);
				xhr.send();
			} else {
				throw "Table type db storage not defined"
			}
		}else {
			if(this.db_table == 'wellplan' ||
			   this.db_table == 'controllog' ||
			   this.db_table == 'welllog' ){
				let xhr = new XMLHttpRequest();
				let params = "path=json/sgta_modeling/update_data_column.php&seldbname=<?php echo $seldbname ?>&filename="+this.filename+"&field="+this.db_field_name+"&col_start="+this.column+"&row_start="+this.row+"&table="+this.db_table
				xhr.open('POST', '/sses/json.php');
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhr.send(params);
			} 
			if(this.db_table=='wellog'){
			
			}	
		}
		
	}
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
    new definitionDataObj('Control Log MD','','controllog','md', 'column'),
    new definitionDataObj('Control Log Gamma','','controllog','value', 'column'),
    new definitionDataObj('Control Log VS','','controllog','vs', 'column'),
    new definitionDataObj('Control Log TVD','','controllog','tvd', 'column'),
    new definitionDataObj('Well Log MD','','welllog','md', 'column'),
    new definitionDataObj('Well Log Gamma','','welllog','value', 'column'),
    new definitionDataObj('Well Log VS','','welllog','vs', 'column'),
    new definitionDataObj('Well Log TVD','','welllog','tvd', 'column')
   ]
}
