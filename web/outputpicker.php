<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
$seldbname=$_GET['seldbname'];
$title=$_GET['title'];
$program=$_GET['program'];
$filename=$_GET['filename'];
$plotstart=$_GET['plotstart'];
$plotend=$_GET['plotend'];
$wlid=$_GET['wlid'];
$showxy=$_GET['showxy'];
?>
<html>
<head>
<style>
body {
	font-family: Arial, Helvetica, sans-serif;
  color: black;
  /*background-color: #2C4C69; */
  background-color: #b0b080;
}
input.button {
	-moz-border-radius: 5px 5px 5px 5px;
	border: thin solid black;
	padding: 2 4;
	margin: 0 1;
  background-color: rgb(230,230,190);
}
input.button:hover { background-color: #70b080; }
input.button:active { background-color: rgb(128,128,128); }
</style>
</head>
<body>
<div id=EchoTopic>
<center>
<h2><?echo $title?></h2>
<input type='hidden' name='seldbname' id='seldbname' value='<?echo $seldbname;?>'>
<input type='hidden' name='program' id='program' value='<?echo $program;?>'>
<input type='hidden' name='filename' id='filename' value='<?echo $filename;?>'>
<input type='hidden' name='plotstart' id='plotstart' value='<?echo $plotstart;?>'>
<input type='hidden' name='plotend' id='plotend' value='<?echo $plotend;?>'>
<input type='hidden' name='wlid' id='wlid' value='<?echo $wlid;?>'>
<input type='hidden' name='showxy' id='showxy' value='<?echo $showxy;?>'>
<form name=formview method='get'>
<input type='button' class='button' value='View Report' onclick='ViewReport(this.form);'>
</form>
<!--
<form name=formprint method='get'>
<input type='button' class='button' value='Print Report' onclick='PrintReport(this.form);'>
</form>
-->
<form name=formemail method='get'>
<input type='button' class='button' value='Email Report' onclick='EmailReport(this.form);'>
</form>
</center>
</div><!-- Default Insight Tag -->
</body>
<script language="JavaScript">
function ViewReport(rowform)
{
	var phpcall=
		document.getElementById("program").value +
		"?seldbname=" + document.getElementById("seldbname").value +
		"&plotstart=" + document.getElementById("plotstart").value +
		"&plotend=" + document.getElementById("plotend").value +
		"&wlid=" + document.getElementById("wlid").value +
		"&showxy=" + document.getElementById("showxy").value;
	newwindow=window.open(phpcall, "_blank", "width=1050,height=650,left=10,top=0,status=0,scrollbars=yes");
	// newwindow=window.opener.window.open(phpcall, "_self");
	if (window.focus) {newwindow.focus()}
	window.close();
}
function PrintReport(rowform)
{
	ViewReport(rowform);
}
function EmailReport(rowform)
{
	var phpcall= "emailmsg.php" +
		"?seldbname=" + document.getElementById("seldbname").value +
		"&program=" + document.getElementById("program").value +
		"&plotstart=" + document.getElementById("plotstart").value +
		"&plotend=" + document.getElementById("plotend").value +
		"&filename=" + document.getElementById("filename").value +
		"&wlid=" + document.getElementById("wlid").value +
		"&showxy=" + document.getElementById("showxy").value;
	newwindow=window.open(phpcall, "_blank", "width=850,height=550,left=10,top=0,status=0");
	// newwindow=window.opener.window.open(phpcall);
	if (window.focus) {newwindow.focus()}
	window.close();
}
function sendValue(s){
	var selvalue = s.options[s.selectedIndex].value;
	window.opener.document.popupform.choice.value = selvalue;
	if(selvalue=="View")	alert("View Survey Report");
	if(selvalue=="Print")	alert("Print Survey Report");
	if(selvalue=="Email")	alert("Email Survey Report");
	// OnSurveyPDF();
	window.close();
}
</script>
</html>
