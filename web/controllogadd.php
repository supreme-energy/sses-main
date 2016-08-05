<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?php
require_once("dbio.class.php");
$seldbname=$_GET['seldbname'];
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("INSERT INTO controllogs (tablename) VALUES ('xxxxxx');");
$db->DoQuery("SELECT id,tablename FROM controllogs WHERE tablename='xxxxxx';");
if($db->FetchRow()) {
	$id = $db->FetchField("id");
	$tn = $db->FetchField("tablename");
}
if($id!="") {
	$query="CREATE TABLE 'cld_$id' (id serial not null, md float, tvd float, vs float, value float);";
	$db->DoQuery($query);
	$query="UPDATE controllogs SET tablename='cld_$id' WHERE id='$id';";
	$db->DoQuery($query);
}
$db->CloseDb();
?>

