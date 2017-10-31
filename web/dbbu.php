<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
require_once("dbio.class.php");
$servername=$_GET['servername'];
$dbids=array();
$dbnames=array();
$realnames=array();
$db=new dbio("sgta_index");
$db->OpenDb();
$db->DoQuery("SELECT * FROM dbindex ORDER BY id;");
$numdbs=0;
while($db->FetchRow()) {
	$dbids[]=$db->FetchField("id");
	$dbnames[]=$db->FetchField("dbname");
	$realnames[]=$db->FetchField("realname");
	$numdbs++;
} 
$db->CloseDb();
?>
<html>
<HEAD>
<LINK rel='stylesheet' type='text/css' href='dbindex.css'/>
<TITLE>Database Manager</TITLE>
<?include("waitdlg.html");?>
</HEAD>
<body>
<table class='container'>
<tr>
<td>
	<TABLE class='index'>
	<TR> 
	<TH>DB Name</TH>
	<TH>Description</TH>
	<TH><?echo $servername?></TH>
	</TR>
	<? for($i=0; $i<$numdbs; $i++) { ?>
	<FORM id='dbform' name='dbform' method='post'>
	<TR class="<?if($i%2==0) echo 'row1'; else echo 'row2';?>">
	<INPUT type='hidden' name='id' value='<?echo $dbids[$i];?>'>
	<INPUT type='hidden' name='dbname' value='<?echo $dbnames[$i];?>'>
	<INPUT type='hidden' name='realname' value='<?echo $realnames[$i];?>'>
	<INPUT type='hidden' name='newname' value='<?echo "$realnames[$i]";?>'>
	<TD class='index'><?echo $dbnames[$i];?></TD>
	<TD class='index'><?echo $realnames[$i];?></TD>
	<TD class='index'><INPUT type='submit' value='Backup' onclick="OnBackup(this.form)"></TD>
	</TR>
	</FORM>
	<?}?>
	</TABLE>
</td>
</tr>
</table>
<div id="load" style="display:none;">Submitting... Please wait</div>
</body>
</html>
<SCRIPT language="javascript">
<!--
var ray={
ajax:function(st) { this.show('load'); },
show:function(el) { this.getID(el).style.display=''; },
getID:function(el) { return document.getElementById(el); }
}
function OnBackup(rowform){
	rowform.action="";
	var r=confirm("Backup database now?");
	if (r==true)
 	{
		t = 'dbbud.php';
		t = encodeURI (t);
		rowform.action = t;
		rowform.submit();
 	}
	else rowform.action="";
}
//-->
</SCRIPT>
