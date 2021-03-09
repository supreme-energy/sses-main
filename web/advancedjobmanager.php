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
$entity_id=$_SESSION['entity_id'];
$updateparent = isset($_GET['reloadparent']);

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
		<button onclick="window.close()">Close</button>
	</TD>
	<td>
	<FORM method='post' action='advancedjobsaveas.php'>
	<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
	<INPUT type='hidden' name='dbname' value='sgta_template'>
	<INPUT type='hidden' name='newname' value='New Database'>
	<INPUT type='submit' value='New Database' onclick="return ray.ajax()">
	</FORM>
	</td>
	<td>
	<FORM method='post' action='advancedrestorefrombackup.php'>
	<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
	<INPUT type='submit' value='Create New Job From Backup' onclick="return ray.ajax()">
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
	<TD class='index'><INPUT type='submit' value='Save As' onclick="OnSaveAs(this.form)"></TD>
	<TD class='index'><INPUT type='submit' value='Backup' onclick="OnBackup(this.form)"></TD>
	<TD class='index'><INPUT type='submit' value='Restore' onclick="OnRestore(this.form)"></TD>
	<TD class='index'><INPUT type='submit' value='Delete' onclick="OnDelete(this.form)"></TD>
	</TR>
	</FORM>
	<?}?>
	</TABLE>
	<center><small>&#169; <?php echo date("Y");?> Supreme Source Energy Services, Inc.</small></center>
</td>
</tr>
</table>
<div id="load" style="display:none;">Submitting... Please wait</div>
</body>
</html>
<SCRIPT language="javascript">
<!--
<?php if($updateparent){ ?>
  var targetWindow = window.opener;
  targetWindow.postMessage('reloadWellList');
<?php } ?>
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
		t = 'advancedjobdelete.php';
		t = encodeURI (t);
		rowform.action = t;
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
		t='advancedjobsaveas.php';
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
		t = 'advancedjobbackup.php';
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
		t = 'advancedrestore.php';
		t = encodeURI (t);
		rowform.action = t;
		rowform.submit();
 	}
	else rowform.action="";
}
//-->
</SCRIPT>
