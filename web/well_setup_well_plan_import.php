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
			<FORM method='post' action='well_setup_well_plan_import_save.php' enctype="multipart/form-data">
			<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
			<table style='float:right'>
			<tr><td>Well Name: </td><td><input type='text' name='wellname' value='<?php echo $wellname; ?>'></td> </tr>
			<tr><td>Well Plan Line Color:</td><td> <input type="text" readonly="true" size='7' id="colorrawwp" name="colorrawwp" 
				value="<?echo "#$colorwp"?>" 
				style="vertical-align:bottom;background-color:#<?echo "$colorwp";?>;color:white;"
				onclick=''/> </td></tr>
			<tr><td>Well Plan CSV: </td><td><input id='userfile' type='file' name='userfile'></td></tr>
			
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
function readLasFile(evt) {
	var xhr = new XMLHttpRequest();	
	xhr.open('POST', '/sses/upload_raw_file.php', true);
	var file = this.files[0];
	filetype = file.name.split('.').pop()
	var fd = new FormData()
	fd.append("userfile", file);
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
	console.log(file_content)
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
		var previousSelection = currentSelectedCells[i]
		var prevElement = document.getElementById(previousSelection.cell_id)
		prevElement.style.backgroundColor = 'white'
		
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
		var currentSel = currentSelectedCells[i]
		var el = document.getElementById(currentSel.cell_id)
		el.style.backgroundColor='green'
	}
}
var onCellClick = function (e){
	var selection = {
				   filename: 'well_plan',
		           cell_id: this.id,
				   cell_value: this.innerHTML
			   }	
	if (e.ctrlKey) {
	   currentSelectedCells.push(selection)  
	} else{
       dehighlightCurrent()
	   currentSelectedCells = [selection]
	}
	var currentSelectedIdx = localStorage.getItem("currentVariableAssignmentIdx")
	var currentSelectedVar = sharedVars.selectable_definitions[currentSelectedIdx]
	localStorage.setItem(currentSelectedVar.display_name,  JSON.stringify(currentSelectedCells))
	this.style.backgroundColor='green'
}

var onCellMouseOver = function(evnt){
	   this.style.backgroundColor = 'green'
}

var onCellMouseOut = function(evnt){
    var hasId = false
	for(var i = 0 ; i < currentSelectedCells.length;i++){
	       var previousSelection = currentSelectedCells[i]
	       if(previousSelection.cell_id == this.id){
            hasId=true
			break;
		   }
	}
	if(!hasId){
		this.style.backgroundColor = 'white'
	}
}
function createTable(tableData) {
	  var table = document.createElement('table');
	  table.className  = 'importContentTable';
	  var tableBody = document.createElement('tbody');
      var row_idx = 0
	    
	  tableData.forEach(function(rowData) {
	    var row = document.createElement('tr');
		row.id='row_'+row_idx
        var column_idx = 0
	    rowData.forEach(function(cellData) {
	      var cell = document.createElement('td');
		  cell.id = 'row_'+row_idx+'_col_'+column_idx
	      cell.onclick = onCellClick
		  cell.onmouseover = onCellMouseOver
		  cell.onmouseout  = onCellMouseOut
		  cell.appendChild(document.createTextNode(cellData));
	      row.appendChild(cell);
		  column_idx++
	    });
        row_idx++
	    tableBody.appendChild(row);
	  });

	  table.appendChild(tableBody);
	  return table
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

