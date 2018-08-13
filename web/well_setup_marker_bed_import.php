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
$db=new dbio($seldbname);
$db->OpenDb();
$query = "select * from addforms";
$result = $db->DoQuery($query);
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

			cell1.innerHTML = "<input id='label_row_"+insertAt+"' type='text' name='input_"+marker_bed_cnt+"' value=''>"
			cell2.innerHTML = "<input id='row_' type='text' onchange=\"saveMarkerBed(this, document.getElementById('label_row_"+insertAt+"').value)\" name='value_"+marker_bed_cnt+"' value=''>"
			cell3.innerHTML = "<button onclick='return deleteMarkerBed("+insertAt+")'>delete</button>"
			return false;
		}
		var deleteMarkerBed = function(row_to_remove){
			var table = document.getElementById('add_marker_table')
			table.deleteRow(row_to_remove)
			return false;
		}

		var saveMarkerBed = function(obj,label){
			var id = obj.id.split("_")[1];
			if(obj.value){
			  sendAddformUpdate(id,label,obj.value);
			}
		}
		
	<?php include("graph_partials/shared/update_addforms_js.php")?>	
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
			
			<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
			<table id='add_marker_table'>
				<tr><td>Label</td><td>Distance From TCL</td><td></td>
				<?php
					$tot_val = '';
					$tot_id  = '';
					$bot_val = '';
					$bot_id  = '';
					$add_rows = Array();
					$row_count = 3;
					while($row = $db->FetchRow()){
						if($row['label'] == 'TOT'){
							$tot_id  = $row['id'];
							$tot_val = $row['thickness'];
							$row_count--;
						}else if($row['label'] == 'BOT'){
							$bot_id = $row['id'];
							$bot_val = $row['thickness'];
							$row_count--;
						}else{
								array_push($add_rows, "<tr><td><input onchange=\"saveMarkerBed(document.getElementById('row_".$row['id']."'),this.value)\" id='label_row_".$row_count."' type='text' value='".$row['label']."'></td><td><input id='row_".$row['id']."' onchange=\"saveMarkerBed(this,document.getElementById('label_row_".$row_count."').value)\" type='text' value='".$row['thickness']."'></td><td><button onclick='return deleteMarkerBed(".$row_count.")'>delete</button></tr>");
						}
						$row_count++;
					}
				?>
				<tr><td><input type='text' name='input_1' disabled value='TOT'></td><td><input id='row_<?php echo $tot_id?>'  onchange="saveMarkerBed(this, 'TOT')" type='text' name='value_1' value='<?php echo $tot_val ?>'></td><td></td></tr>
				<tr><td><input type='text' name='input_2' disabled value='BOT'></td><td><input id='row_<?php echo $bot_id?>' onchange="saveMarkerBed(this, 'BOT')" type='text' name='value_2' value='<?php echo $bot_val ?>'></td><td></td></tr>
				<?php echo implode($add_rows,"")?>
				<tr id='add_marker_row'><td colspan='3' style='text-align:right'><button onclick="return addMarkerBed()">Add Marker Bed</button>
			</table>
			
			</td>
		</tr>
		<tr><td><a href ='well_setup_tie_in.php?seldbname=<?php echo $seldbname?>'>next</a></td><td></td></tr>
		<tr><td><a href ='well_setup_well_plan_import.php?seldbname=<?php echo $seldbname?>'>previous</a></td><td></td></tr>
	  </TABLE>
</TD>
</TR>
</TABLE>


