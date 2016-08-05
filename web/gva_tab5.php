<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Supreme Source Energy Services, Inc.
//	All rights reserved.
//	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'sses_include.php';
require_once 'dbio.class.php';

include('gva_tab5_funct.php');

$seldbname=$_REQUEST['seldbname'];
$db=new dbio($seldbname);
$db->OpenDb();
include("readappinfo.inc.php");
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="gva_tab5.css" />
<title><?echo "$dbrealname";?>-GVA Wellbore<?echo " ($seldbname)";?></title>
</head>
<body>
<?
$maintab=4;
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
		<FORM ID="avgdipform" NAME="avgdipform" METHOD="get" style='margin: 0;'>
		<input type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<input type='hidden' name='dip' value='0'>
		<INPUT type='hidden' name='ret' value='gva_tab5.php'>
		<INPUT TYPE="submit" VALUE="Average Dip" ONCLICK="return OnAvgDip('<?echo $seldbname;?>')">
		</FORM>
	</td>
	<td>
		<input type=button name=choice onClick="window.open('annotations.php?seldbname=<?echo $seldbname?>','annotations','width=1050,height=600,left=250');" value="Annotations">
	</td>
	</tr>
	</table>
</td>
<?if("$mintvd"!=""){?>
<form action='splotconfigd.php' method='post'>
<td>
	<table class='buttons'>
	<td> SGTA DMod:<input size'6' type='text' name='sgtadmod' value='<?echo $dmod?>' onchange="OnSubmit(this.form)"></td>
	</table>
</td>
<td>
	<table class='buttons'>
	<INPUT id='seldbname' type='hidden' name='seldbname' value='<?echo $seldbname?>'>
	<INPUT id='ret' type='hidden' name='ret' value='gva_tab5.php'>
	<INPUT id='ptype' type='hidden' name='ptype' value='LAT'>
	<INPUT id='mtype' type='hidden' name='mtype' value='TVD'>
	<INPUT id='inputa' type='hidden' name='inputa' value='<?echo $inputa?>'>
	<td> Min TVD: <input size='6' type='text' name='mintvd' value='<?echo $mintvd?>' onchange="OnSubmit(this.form)"> </td>
	<td> Max TVD: <input size='6' type='text' name='maxtvd' value='<?echo $maxtvd?>' onchange="OnSubmit(this.form)"> </td>
	</table>
</td>
<td>
	<table class='buttons'>
	<INPUT id='seldbname' type='hidden' name='seldbname' value='<?echo $seldbname?>'>
	<INPUT id='ret' type='hidden' name='ret' value='gva_tab5.php'>
	<INPUT id='ptype' type='hidden' name='ptype' value='LAT'>
	<INPUT id='mtype' type='hidden' name='mtype' value='TVD'>
	<INPUT id='inputa' type='hidden' name='inputa' value='<?echo $inputa?>'>
	<td> Min VS: <input size='6' type='text' name='minvs' value='<?echo $minvs?>' onchange="OnSubmit(this.form)"> </td>
	<td> Max VS: <input size='6' type='text' name='maxvs' value='<?echo $maxvs?>' onchange="OnSubmit(this.form)"> </td>
	</table>
</td>
<td>
	<table class='buttons'>
	<INPUT id='seldbname' type='hidden' name='seldbname' value='<?echo $seldbname?>'>
	<INPUT id='ret' type='hidden' name='ret' value='gva_tab5.php'>
	<INPUT id='ptype' type='hidden' name='ptype' value='LAT'>
	<INPUT id='mtype' type='hidden' name='mtype' value='TVD'>
	<INPUT id='inputa' type='hidden' name='inputa' value='<?echo $inputa?>'>
	<td> <? echo $uselogscale?'Resistivity':'Gamma'?> Scale: <input size='2' type='text' name='inputb' value='<?echo $yscale?>' onchange="OnSubmit(this.form)"> </td>
	</table>
</td>
</form>
<?}?>
</tr>
<tr>
<td colspan='5' style='padding: 0 0;' class='container'>
	<table style='padding: 0; border-spacing: 0; margin: 0 0;'>
	<tr>
	
	<td style='padding: 0; background-color: white;' rowspan='2' >
	<IMG style='display:<?php if($sgta_off){echo 'none';}else{echo 'inline';}?>' SRC='<?echo $fn5;?>'>
	</td>
	
	<td style='padding: 0; vertical-align: top;'>
	<IMG SRC='<?echo $fn;?>'>
	<br>
	<IMG SRC='<?echo $fn4;?>'>
	</td>
<!--	<td style='padding: 0 4; vertical-align: top;'>
	<IMG SRC='<?echo $fn2;?>'>
	</td> -->
	</tr>
	</table>
	<center><small>&#169; 2010-2011 Supreme Source Energy Services, Inc.</small></center>
</td>
<!--
<td style='padding: 0 0;' class='container'>
	<IMG SRC='<?echo $fn2;?>'>
</td>
-->
</tr>
<?/*
<tr>
<td colspan='4'>
	<IMG SRC='<?echo $fn3;?>'>
</td>
</tr>
*/?>
</table>
<script language="javascript">
function OnSubmit(rowform) {
	t = 'splotconfigd.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return true;
}
function OnAvgDip(dbname)
{
	var phpcall="projavgdip.php?seldbname=" + dbname;
	var newwindow=window.open(phpcall, 'Average Dip Calulator',
		'height=220,width=300,left=500,top=60,scrollbars=no,location=no,resizable=no');
	if (window.focus) {newwindow.focus()}
	return false;
}
</script>
</body>
</html>
