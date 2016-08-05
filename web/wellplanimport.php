<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
require_once("dbio.class.php");
$seldbname=$_GET['seldbname'];
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("SELECT * FROM wellinfo LIMIT 1;");
$num=$db->FetchNumRows();
$rigid="";
if ($num > 0) {
	$db->FetchRow();
	$rigid=$db->FetchField("rigid");
	$wellname=$db->FetchField("wellname");
} 
$db->CloseDb();
?>
<HEAD>
<link rel="stylesheet" type="text/css" href="wellplanimport.css" />
<title><?echo "$seldbname-Import Wellplan";?></title>
</HEAD>

<html>
<body class='normal'>
<table class='group'
<tr>
<td>
	<center><big><big>Import Wellplan Surveys From CSV File</big></big><center>
	<A href=gva_tab2.php?seldbname=<?echo $seldbname;?>>Back To Wellplan</A>
</td>
</tr>
<tr>
<td>
	<br>
	<form action="wellplanupload.php" method="post" enctype="multipart/form-data">
	<b>File to import from:</b>
	<br>
	<input type="hidden" name="MAX_FILE_SIZE" value="999999999">
	<input type="hidden" name="seldbname" value="<?echo $seldbname;?>">
	<input name="userfile" type="file" size="85"><br>
</td>
</tr>
<tr>
<td style='text-align: right'>
	<input type="submit" value="Import">
	</form>
</td>
</tr>
<tr>
<td>
	<center><small><small>&#169; 2010-2011 Supreme Source Energy Services, Inc.</small></small></center>
</td>
</tr>
</table>
</body>
</html>
