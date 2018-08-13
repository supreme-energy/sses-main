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
$seldbname = (isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '');
if($seldbname == '') include('dberror.php');
$db=new dbio($seldbname);
$db->OpenDb();

$db->DoQuery("SELECT * FROM wellinfo;");
if($db->FetchRow()) {
	$colortot=$db->FetchField("colortot");
}

$db->DoQuery("SELECT * FROM controllogs ORDER BY tablename");
if ($db->FetchRow()) {
	$tablename=$db->FetchField("tablename");
	$startmd=$db->FetchField("startmd");
	$endmd=$db->FetchField("endmd");
	$cltot=$db->FetchField("tot");
	$clbot=$db->FetchField("bot");
	$cldip=$db->FetchField("dip");
	$stregdipazm=$db->FetchField("azm");
}
else {
	// create an entry in the controllogs table
	$db->DoQuery("INSERT INTO controllogs (tablename) VALUES ('xxxxxx');");
	$db->DoQuery("SELECT * FROM controllogs WHERE tablename='xxxxxx';");
	$id="";
	if($db->FetchRow()){
		$id = $db->FetchField("id");
		// create table which contains imported data
		if($id!="") {
			$tablename="cld_$id";
			$query="CREATE TABLE \"$tablename\" (id serial not null, md float, tvd float, vs float, value float, hide smallint not null default 0);";
			$db->DoQuery($query);
			$query="UPDATE controllogs SET tablename='$tablename' WHERE id='$id';";
			$db->DoQuery($query);
		}
		else die("<pre>Id for new table entry not found!\n</pre>");
	}
}
?>
   <LINK href="gva_tab0.css?x=<?=time();?>" rel="stylesheet" type="text/css">
   <TABLE class='tabcontainer'>
   <TR>
       <TD>
           <TABLE style='margin: 2 0; padding: 0 0;' class='container'>
	           <TD> 
		       <img src='digital_tools_logo.png'  align='left'>
		       <H2 style='line-height: 2.0; font-style: italic; color: #040;' align='center'>Digital Oil Tools</H2>
		       <H1 style='line-height: 1.0;' align='center'>Control Log Import and Config</H1>
		       <p align="center">Version <?echo $version; ?> (<?echo $_SERVER['SERVER_NAME']?>:<?echo $_SERVER['SERVER_PORT']?> - <?echo $hostname?>)</p>
		   </TD>
	   </TABLE>
	   <TABLE class='container'>
		<tr>
			<td>
			<FORM method='post' action='well_setup_control_log_import_save.php' enctype="multipart/form-data">
			<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
			Ref Well Name: <input type='text' name='ref_well_name'><br>
			TCL: <input type='text' name='tcl_depth'><br>
			TCL Line Color:  <input type="text" readonly="true" size='7' id="colortot" name="colortot" 
				value="<?echo "#$colortot"?>" 
				style="vertical-align:bottom;background-color:#<?echo "$colortot";?>;color:white;"
				onclick=''/><br>
			Control Log LAS File: <input id='userfile' type='file' name='userfile'><br> 
			<div id='md_header'>MD Header: <select><option>Select an import file first</option></select></div>
			<div id='gr_header'>Gamma Header:<select><option>Select an import file first</option></select></div>
			<br>		
			<button>import</button>
			</FORM></td>
		</tr>
		<tr><td colspan='2' style='align: right'>
		<a href ='well_setup_marker_bed_import.php?seldbname=<?php echo $seldbname?>'>Next</a>
		</td></tr>
	  </TABLE>
</TD>
</TR>

</TABLE>
<script>

function parseLasFile(filecontents){
	var split1 = filecontents.split('~Curve Information Section')
	var split2 = split1[1].split("~ASCII Log Data Section")
	
	var header_data = split2[0]
	var header_rows = header_data.split("\n")
	var options = ""
	for(var i = 0; i < header_rows.length; i++){
		var row = header_rows[i];
		if(row!=''){
			header_name = row.split(" ")[0]
			options+="<option value='"+i+"'>"+header_name+"</option>"
		}
	}
	document.getElementById("md_header").innerHTML = "MD Header: <select name='md_header_idx'>"+options+"</select>"
	document.getElementById("gr_header").innerHTML = "Gamma Header: <select name='gr_header_idx'>"+options+"</select>"
}

function readLasFile(evt) {
	var xhr = new XMLHttpRequest();	
	xhr.open('POST', '/sses/upload_raw_file.php', true);
	var file = this.files[0];	
	var fd = new FormData()
	fd.append("userfile", file);
    xhr.onreadystatechange = function () {
  	  	  if(xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
    	    parseLasFile(xhr.responseText)
    	  }
    	};
	xhr.send(fd);
  }

document.getElementById('userfile').addEventListener('change', readLasFile, false);
</script>

