<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
require_once("dbio.class.php");
$seldbname=$_GET['seldbname'];
$entity_id=$_SESSION['entity_id'];

$dbids=array();
$dbnames=array();
$realnames=array();
$db=new dbio("sgta_index");
$db->OpenDb();
//commented till multi-user is ready for deploy.
//$strQry= "SELECT * FROM dbindex WHERE entity_id=" .$entity_id. " ORDER BY id;";
//$db->DoQuery($strQry);
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
<LINK rel='stylesheet' type='text/css' href='waitdlg.css'/>
<TITLE>Database Manager</TITLE>
</HEAD>
<body>
<?include("waitdlg.html");?>
<table class='container'>
<tr>
<td>
	<table>
	<tr>
	<TD>
		<INPUT type='submit' value='Exit' onclick="document.location='gva_tab1.php?seldbname=<?echo $seldbname;?>'">
	</TD>
	<td>
	<FORM method='post' action='dbsaveas.php'>
	<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
	<INPUT type='hidden' name='dbname' value='sgta_template'>
	<INPUT type='hidden' name='newname' value='New Database'>
	<INPUT type='submit' value='New Database' onclick="return ray.ajax()">
	</FORM>
	</td>
	<td>
	<FORM method='post' action='dbrestorebackup.php'>
	<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
	<INPUT type='submit' value='Create New Database From Backup' onclick="return ray.ajax()">
	</FORM>
	</td>
	</tr>
	</table>

	<TABLE class='index'>
	<TR> 
	<TH>DB Name</TH>
	<TH>Description</TH>
	</TR>
	<? for($i=0; $i<$numdbs; $i++) { ?>
	<FORM id='dbform' name='dbform' method='post'>
	<TR class="<?if($i%2==0) echo 'row1'; else echo 'row2';?>">
	<INPUT type='hidden' name='id' value='<?echo $dbids[$i];?>'>
	<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
	<INPUT type='hidden' name='dbname' value='<?echo $dbnames[$i];?>'>
	<INPUT type='hidden' name='realname' value='<?echo $realnames[$i];?>'>
	<INPUT type='hidden' name='newname' value='<?echo "$realnames[$i]";?>'>
	<TD class='index'><?echo $dbnames[$i];?></TD>
	<TD class='index'><?echo $realnames[$i];?></TD>
	<TD class='index'><INPUT type='submit' value='Rename' onclick="OnRename(this.form)"></TD>
	<TD class='index'><INPUT type='submit' value='Save As' onclick="OnSaveAs(this.form)"></TD>
	<TD class='index'><INPUT type='submit' value='Backup' onclick="OnBackup(this.form)"></TD>
	<TD class='index'><INPUT type='submit' value='Restore' onclick="OnRestore(this.form)"></TD>
	<TD class='index'><INPUT type='submit' value='Delete' onclick="OnDelete(this.form)"></TD>
	</TR>
	</FORM>
	<?}?>
	</TABLE>
	<center><small>&#169; 2010-2011 Digital Oil Tools</small></center>
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
function OnDelete(rowform)
{
	rowform.action="";
	var r=confirm("Delete this database?");
	if (r==true)
 	{
		t = 'dbdelete.php';
		t = encodeURI (t);
		rowform.action = t;
		rowform.submit();
		return ray.ajax();
 	}
	else rowform.action="";
}
function OnRename(rowform){
	rowform.action="";
	var name=prompt("New name:", rowform.newname.value);
	if (name!=null && name!="") {
		rowform.newname.value = name;
		t='dbrename.php';
		t=encodeURI (t);
		rowform.action=t;
		rowform.submit();
		return ray.ajax();
 	}
	else rowform.action="";
}
function OnSaveAs(rowform){
	rowform.action="";
	var trythis=rowform.realname.value + "(copy)";
	var name=prompt("Name to save database as:", trythis);
	if (name!=null && name!="") {
		rowform.newname.value = name;
		t='dbsaveas.php';
		t=encodeURI (t);
		rowform.action=t;
		rowform.submit();
		return ray.ajax();
 	}
	else rowform.action="";
}
function OnBackup(rowform){
	rowform.action="";
	var r=confirm("Backup database now?");
	if (r==true)
 	{
		t = 'dbbackup.php';
		t = encodeURI (t);
		rowform.action = t;
		rowform.submit();
 	}
	else rowform.action="";
}
function OnRestore(rowform){
	rowform.action="";
	var r=confirm("Restore this database from backup?\n(This will overwrite all changes)");
	if (r==true)
 	{
		t = 'dbrestore.php';
		t = encodeURI (t);
		rowform.action = t;
		rowform.submit();
 	}
	else rowform.action="";
}
//-->
</SCRIPT>
