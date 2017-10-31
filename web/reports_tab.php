<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Digital Oil Tools

require_once 'sses_include.php';

require_once("dbio.class.php");
include_once('gva_tab5_funct.php');
include_once('classes/Reports.class.php');

$seldbname=$_REQUEST['seldbname'];
$db=new dbio($seldbname);
$db->OpenDb();
include("readappinfo.inc.php");
?>
<HTML>
<HEAD>
<TITLE>Reports</TITLE>
<link rel="stylesheet" type="text/css" href="gva_tab5.css" />
<LINK rel='stylesheet' type='text/css' href='projws.css'/>
<LINK rel='stylesheet' type='text/css' href='themes/blue/style.css'/>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />
  <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
  <script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
  <link rel='stylesheet' type='text/css' href='jquery.timepicker.css'/>
<script type="text/javascript" src='jquery.timepicker.js'></script>
<script type="text/javascript" src='js/jquery.tablesorter.js'></script>
</HEAD>
<BODY>
<?
$maintab=7;
include "apptabs.inc.php";
?>
<table class='tabcontainer'>
<input type='hidden' id='seldbn' value='<?echo $seldbname;?>'>
<tr>
<td>
	<table class='buttons'>
	<tr>
	<td>
		<H2 style='margin: 0; line-height: 1.0; padding: 0 0 0 0;'><?echo $wellname;?></H2>
	</td>
	<td>
		<input type=button name=choice onClick="window.open('splotconfig.php?seldbname=<?echo $seldbname?>&title=Survey%20Plot','popuppage','width=450,height=220,left=250');" value="Plot Surveys">
	</td>
	<td>
		<input type=button name=choice onClick="window.open('annotations.php?seldbname=<?echo $seldbname?>','annotations','width=1050,height=600,left=250');" value="Annotations">
	</td>
	</tr>
	</table>
</td>
</tr>
<tr><td style='width:990px;padding-top:50px;'>
	<?php include "report_list.php";?>
</td></tr>
<tr>
</table>
</BODY>
</HTML>
