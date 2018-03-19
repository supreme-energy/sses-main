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
?>
   <script>
		var marker_bed_cnt = 2;
		var addMarkerBed = function(){
			marker_bed_cnt++
			var table = document.getElementById('add_marker_table')
			var insertAt = table.rows.length-1
			console.log(insertAt)
			var row = table.insertRow(insertAt)
			var cell1 = row.insertCell(0);
			var cell2 = row.insertCell(1);
			var cell3 = row.insertCell(2);

			cell1.innerHTML = "<input type='text' name='input_"+marker_bed_cnt+"' value=''>"
			cell2.innerHTML = "<input type='text' name='value_"+marker_bed_cnt+"' value=''>"
			cell3.innerHTML = "<button onclick='return deleteMarkerBed("+insertAt+")'>delete</button>"
			return false;
		}
		var deleteMarkerBed = function(row_to_remove){
			var table = document.getElementById('add_marker_table')
			table.deleteRow(row_to_remove)
			return false;
		}
   </script>
   <LINK href="gva_tab0.css?x=<?=time();?>" rel="stylesheet" type="text/css">
   <TABLE class='tabcontainer'>
   <TR>
       <TD>
           <TABLE style='margin: 2 0; padding: 0 0;' class='container'>
	           <TD> 
		       <img src='digital_tools_logo.png'  align='left'>
		       <H2 style='line-height: 2.0; font-style: italic; color: #040;' align='center'>Digital Oil Tools</H2>
		       <H1 style='line-height: 1.0;' align='center'>Subsurface Geological Tracking Analysis</H1>
		       <p align="center">Version <?echo $version; ?> (<?echo $_SERVER['SERVER_NAME']?>:<?echo $_SERVER['SERVER_PORT']?> - <?echo $hostname?>)</p>
		   </TD>
	   </TABLE>
	   <TABLE class='container'>
		<tr>
			<td>
			<FORM method='post' action='well_setup_marker_bed_import_save.php'>
			<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
			<table id='add_marker_table'>
				<tr><td>Label</td><td>Distance From TCL</td><td></td>
				<tr><td><input type='text' name='input_1' value='TOT'></td><td><input type='text' name='value_1' value=''></td><td><button onclick='return deleteMarkerBed(1)'>delete</button></td></tr>
				<tr><td><input type='text' name='input_2' value='BOT'></td><td><input type='text' name='value_2' value=''></td><td><button onclick='return deleteMarkerBed(2)'>delete</button></td></tr>
				<tr id='add_marker_row'><td colspan='3' style='text-align:right'><button onclick="return addMarkerBed()">Add Marker Bed</button>
			</table>
			<button>Next</button>
			</FORM></td>
		</tr>
	  </TABLE>
</TD>
</TR>
</TABLE>


