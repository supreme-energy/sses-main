<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
require_once("dbio.class.php");
$seldbname=$_POST['seldbname'];
$dbname=$_POST['dbname'];
$newname=$_POST['newname'];
$entity_id=$_SESSION['entity_id'];
$db=new dbio("sgta_index");
$db->OpenDb();
$db->DoQuery("INSERT INTO dbindex (dbname) VALUES ('xxx');");
$db->DoQuery("SELECT id,dbname FROM dbindex WHERE dbname='xxx';");
if($db->FetchRow()) $id = $db->FetchField("id");
if($id!="") {
	$newdbname="sgta_$id";
	$query="CREATE DATABASE $newdbname TEMPLATE $dbname;";
	$result=$db->DoQuery($query);
	if($result!=FALSE) {
		$query="UPDATE dbindex SET dbname='$newdbname',realname='$newname' WHERE id='$id';";
		$db->DoQuery($query);
	}
	else	die("<pre>Failed to create new database\n</pre>");
}
else die("<pre>Failed to update dbindex!\n</pre>");
$db->CloseDb();
header("Location: dbindex.php?seldbname=$seldbname");
exit();
?>
