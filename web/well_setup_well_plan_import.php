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
   <LINK href="gva_tab0.css" rel="stylesheet" type="text/css">
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
			<tr><td>Well Plan CSV: </td><td><input type='file' name='userfile'></td></tr>
			
			<tr><td></td><td><button>import</button></td></tr>
			</table>
			</FORM></td>
		</tr>
	  </TABLE>
</TD>
</TR>
</TABLE>


