<?php /*
	Written by: Richard R Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
//header("Location: gva_tab1.php");
include_once("sses_include.php");
require_once("dbio.class.php");
require_once("version.php");


$seldbname=$_REQUEST['seldbname'];
require_once("graph_partials/shared/uploaded_file_list.php");
$db=new dbio('sgta_index');
$db->OpenDb();
$db->DoQuery("select * from dbindex where dbname='$seldbname';");
$db->FetchRow();
$wellname = $db->FetchField("realname");
$db->CloseDb();

$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("SELECT * FROM wellinfo;");

if($db->FetchRow()) {
	$colorwp =$db->FetchField("colorwp");
}

if($colorwp == ''){
	$colorwp = '3dfd0d';
}
?>
<style>
table.importContentTable {
    border-width: 1px;
    border-spacing: 2px;
    border-style: outset;
    border-color: gray;
    border-collapse: separate;
    background-color: white;
}

table.importContentTable td {
    border-width: 1px;
    padding: 1px;
    border-style: inset;
    border-color: gray;
    background-color: white;
    -moz-border-radius: ;
}

table.importContentTable td:hover{
    background-color: green
}
</style>
   <LINK href="gva_tab0.css?x=<?=time();?>" rel="stylesheet" type="text/css">
   <TABLE class='tabcontainer'>
   <TR>
       <TD>
           <TABLE style='margin: 2 0; padding: 0 0;' class='container'>
	           <TD> 
		       <img src='digital_tools_logo.png' align='left'>
		       <H2 style='line-height: 2.0; font-style: italic; color: #040;' align='center'>Digital Oil Tools</H2>
		       <H1 style='line-height: 1.0;' align='center'>Well Plan Import and Config</H1>
		       <p align="center">Version <?echo $version; ?> (<?echo $_SERVER['SERVER_NAME']?>:<?echo $_SERVER['SERVER_PORT']?> - <?echo $hostname?>)</p>
		   </TD>
	   </TABLE>
	   <TABLE class='container' >
		<tr>
			<td>
			<table style='float:left'>
			     <tr><td>Previously loaded files:</td></tr>
			     <tr><td><select id='previous_loaded_files' onchange="readExistingFile()"><option>--------------------------------</option>
			     	<?php 
			     	foreach($import_files as $fname){
			     		echo "<option>".$fname."</option>";
			     	}
			     	?>
			     </select></td></tr>
			</table>
			<FORM method='post' action='well_setup_well_plan_import_save.php' enctype="multipart/form-data">
			<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
			<table style='float:right'>
			<tr><td>Well Plan Line Color:</td><td> <input type="text" readonly="true" size='7' id="colorrawwp" name="colorrawwp" 
				value="<?echo "#$colorwp"?>" 
				style="vertical-align:bottom;background-color:#<?echo "$colorwp";?>;color:white;"
				onclick=''/> </td></tr>
			<tr><td>Well Plan CSV/LAS: </td><td><input id='userfile' type='file' name='userfile'></td></tr>
			
			<tr><td></td><td><button>import</button></td></tr>
			</table>
			</FORM></td>
		</tr>
		<tr><td><div id='import_content_display'></div></td></tr>
	  </TABLE>
</TD>
</TR>
</TABLE>
<script language="javascript">

<?php include "graph_partials/shared/assignment_definition_js_vars.php"?>

var filetype = ''
var wellplan_filename = ''

function readExistingFile(){
  var e = document.getElementById("previous_loaded_files");
  var filename = e.options[e.selectedIndex].text;
  if(filename == '--------------------------------') return;
  document.getElementById('import_content_display').innerHTML = ''
  wellplan_filename = filename
  filetype = wellplan_filename.split('.').pop()
  var xhr = new XMLHttpRequest();	
  xhr.open('POST', '/sses/read_raw_file.php', true);
  var fd = new FormData()
  fd.append('seldbname', '<?echo $seldbname;?>');
  fd.append('filename', filename);
  
  xhr.onreadystatechange = function () {
	if(xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
      parseAndDisplayFile(xhr.responseText)
    }  
  };
  xhr.send(fd);
}

function readLasFile(evt) {
	var xhr = new XMLHttpRequest();	
	xhr.open('POST', '/sses/upload_raw_file.php', true);
	var file = this.files[0];
	wellplan_filename = file.name
	filetype = wellplan_filename.split('.').pop()
	var fd = new FormData()
	fd.append("userfile", file);
	fd.append("seldbname", '<?echo $seldbname;?>');
    xhr.onreadystatechange = function () {
  	  	  if(xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
    	    parseAndDisplayFile(xhr.responseText)
    	  }
    	};
	xhr.send(fd);
  }

document.getElementById('userfile').addEventListener('change', readLasFile, false);

function filetype_check(){
	switch (filetype.toLowerCase()){
		case 'csv':
		case 'las':			
			return true
		default:
			alert(filetype+" is not an accepted format")
			return false	
	}
}


function generateIneractiveDataTable(file_content){
	if(filetype.toLowerCase() == 'csv'){
		generateFromCSV(file_content)
	} else {
		generateFromLAS(file_content)
	}	
}


function generateFromCSV(file_content){
	var csv_content = parseCSV(file_content)
	var table = createTable(csv_content)
	document.getElementById('import_content_display').appendChild(table)
}

function generateFromLAS(file_content){
    var las_content = parseLAS(file_content)
	var table = createTable(las_content)
	document.getElementById('import_content_display').appendChild(table)
}

function parseAndDisplayFile(response){
	if(filetype_check()){
		window.open('/sses/well_setup_import_assignment.php?seldbname=<?echo $seldbname;?>','sses_variableAssignmentWindow','height=220px,width=400px');
		generateIneractiveDataTable(response)
		var currentSelectedIdx = localStorage.getItem("currentVariableAssignmentIdx")
		var currentSelectedVar = sharedVars.selectable_definitions[currentSelectedIdx]
		var result = JSON.parse(localStorage.getItem(currentSelectedVar.display_name))
	    highlightFromStorage(result)
	}
}

var currentSelectedCells = []

var tableSelectionType = 'cell'
var dehighlightCurrent = function(){
	for(var i = 0 ; i < currentSelectedCells.length;i++){
		try{
		  var previousSelection = currentSelectedCells[i]
		  var prevElement = document.getElementById(previousSelection.cell_id)
		  deHighlightColumn(previousSelection.cell_id)
		  prevElement.style.backgroundColor = 'white'
		  prevElement.highlighted = false
		} catch(e){}
		
	}
}
var highlightFromStorage = function(inval){
	dehighlightCurrent()
	if (Array.isArray(inval)){
	   currentSelectedCells = inval
	} else {
	   currentSelectedCells = []
	}

	for(var i = 0 ; i < currentSelectedCells.length; i ++ ){
		try{
		  var currentSel = currentSelectedCells[i]
		  if(currentSel.filename != wellplan_filename){
			  continue
		  }
		  var el = document.getElementById(currentSel.cell_id)
		  el.style.backgroundColor='green'
		  el.highlighted = true
        
		  if(currentSel.field_type=='column'){
			  highlightColumn(currentSel.cell_id)
		  }
		} catch(e){}
	}
}

var highlightColumn = function(start_cell){
    var split_vals = start_cell.split("_")
	var row_pos = parseInt(split_vals[1])
	var hasmore_data = true
	var next_id =''
	while(hasmore_data){
		row_pos += 1
		next_id = split_vals[0]+"_"+row_pos+"_"+split_vals[2]+"_"+split_vals[3]
		try{
            var el = document.getElementById(next_id)
			if(el.innerHTML == ''){
				hasmore_data = false
			} else {
				el.style.backgroundColor='green'
			}
		}catch(e){
			hasmore_data = false
		}
    }
}

var deHighlightColumn = function(start_cell){
	var split_vals = start_cell.split("_")
	var row_pos = parseInt(split_vals[1])
	var hasmore_data = true
	var next_id =''
	while(hasmore_data){
		row_pos += 1;
		next_id = split_vals[0]+"_"+row_pos+"_"+split_vals[2]+"_"+split_vals[3];
		try{
            var el = document.getElementById(next_id)
			if(el.style.backgroundColor == 'green'){
			   el.style.backgroundColor='white'
			} else {
			   hasmore_data = false
			}
		}catch(e){
			hasmore_data = false
		}
	}
}

var onCellClick = function (e){
	var currentSelectedIdx = localStorage.getItem("currentVariableAssignmentIdx")
	var currentSelectedVar = sharedVars.selectable_definitions[currentSelectedIdx]
	var selection = {
				   filename: wellplan_filename,
		           cell_id: this.id,
				   cell_value: this.innerHTML,
				   field_type: currentSelectedVar.field_type
			   }	
	if (e.ctrlKey) {
	   currentSelectedCells.push(selection)  
	} else{
       dehighlightCurrent()
	   currentSelectedCells = [selection]
	   if (currentSelectedVar.field_type == 'column'){
           highlightColumn(this.id)
	   }
	}
	localStorage.setItem(currentSelectedVar.display_name,  JSON.stringify(currentSelectedCells))
	this.style.backgroundColor='green'
}

function createTable(tableData) {
	  var table = document.createElement('table');
	  table.className  = 'importContentTable';
	  var tableBody = document.createElement('tbody');
      var row_idx = 0
	  var max_columns = 1
	  tableData.forEach(function(rowData) {
	   if(rowData.length > max_columns){
		   max_columns = rowData.length
	   }
	  })  
	  tableData.forEach(function(rowData) {
	    var row = document.createElement('tr');
		row.id='row_'+row_idx
        var column_idx = 0
	    rowData.forEach(function(cellData) {
	      var cell = document.createElement('td');
		  cell.id = 'row_'+row_idx+'_col_'+column_idx
	      cell.onclick = onCellClick
            
		  column_idx++
		  if(column_idx < max_columns && column_idx == rowData.length){
			  var colspan = max_columns - column_idx 
			  cell.colSpan = colspan
		  }
		  cell.appendChild(document.createTextNode(cellData));
	      row.appendChild(cell);
		  

	    });
        row_idx++
	    tableBody.appendChild(row);
	  });

	  table.appendChild(tableBody);
	  return table
	}

function parseLAS(str){
    var lines = str.split("\n")
	var arr = [];
	for(c = 0; c < lines.length; c++){
		arr[c] =  lines[c].split(/[\s]+/);
	}
	return arr
}

function parseCSV(str) {
    var arr = [];
    var quote = false;  // true means we're inside a quoted field

    // iterate over each character, keep track of current row and column (of the returned array)
    for (var row = col = c = 0; c < str.length; c++) {
        var cc = str[c], nc = str[c+1];        // current character, next character
        arr[row] = arr[row] || [];             // create a new row if necessary
        arr[row][col] = arr[row][col] || '';   // create a new column (start with empty string) if necessary

        // If the current character is a quotation mark, and we're inside a
        // quoted field, and the next character is also a quotation mark,
        // add a quotation mark to the current column and skip the next character
        if (cc == '"' && quote && nc == '"') { arr[row][col] += cc; ++c; continue; }  

        // If it's just one quotation mark, begin/end quoted field
        if (cc == '"') { quote = !quote; continue; }

        // If it's a comma and we're not in a quoted field, move on to the next column
        if (cc == ',' && !quote) { ++col; continue; }

        // If it's a newline (CRLF) and we're not in a quoted field, skip the next character
        // and move on to the next row and move to column 0 of that new row
        if (cc == '\r' && nc == '\n' && !quote) { ++row; col = 0; ++c; continue; }

        // If it's a newline (LF or CR) and we're not in a quoted field,
        // move on to the next row and move to column 0 of that new row
        if (cc == '\n' && !quote) { ++row; col = 0; continue; }
        if (cc == '\r' && !quote) { ++row; col = 0; continue; }

        // Otherwise, append the current character to the current column
        arr[row][col] += cc;
    }
    return arr;
}
window.addEventListener("storage", function(e){
	var currentSelectedVar = sharedVars.selectable_definitions[e.newValue]
	var result = JSON.parse(localStorage.getItem(currentSelectedVar.display_name))
	highlightFromStorage(result)
}, false)
</script>

