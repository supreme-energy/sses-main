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
require_once("tabs.php");
require_once("login.class.php");
require_once("version.php");
?>
   <LINK href="gva_tab0.css" rel="stylesheet" type="text/css">
   <TABLE class='tabcontainer'>
   <TR>
       <TD>
           <TABLE style='margin: 2 0; padding: 0 0;' class='container'>
	           <TD> 
		       <img src='digital_tools_logo.png' width='76' height='74' align='left'>
		       <H2 style='line-height: 2.0; font-style: italic; color: #040;' align='center'>Digital Oil Tools</H2>
		       <H1 style='line-height: 1.0;' align='center'>Control Log Import and Config</H1>
		       <p align="center">Version <?echo $version; ?> (<?echo $_SERVER['SERVER_NAME']?>:<?echo $_SERVER['SERVER_PORT']?> - <?echo $hostname?>)</p>
		   </TD>
	   </TABLE>
	   <TABLE class='container'>
		<tr>
			<td>
			<FORM method='post' action='well_setup_control_log_import_save.php'>
			<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
			<INPUT type='hidden' name='dbname' value='sgta_template'>
			<INPUT type='hidden' name='newname' value='New Database'>
			Ref Well Name: <br>
			TCL: <br>
			Control Log Line Color: <br>
			Control Log LAS File: <input type='file' name='userfile'> 
			<button>import</button>
			</FORM></td>
		</tr>
	  </TABLE>
</TD>
</TR>
</TABLE>


