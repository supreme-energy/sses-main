<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?php
require_once("dbio.class.php");
$ret=$_POST['ret'];
$tablename=$_POST['tablename'];
$scrolltop=$_POST['scrolltop'];
$scrollleft=$_POST['scrollleft'];
$dscnt=$_POST['dscnt'];
$dsnum=$_POST['dsnum'];
$hide=$_POST['hide'];
$seldbname=$_POST['seldbname'];
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("SELECT id FROM welllogs WHERE tablename='$tablename';");
if ($db->FetchRow()) {
	$id=$db->FetchField("id");
	$db->DoQuery("UPDATE welllogs SET hide='$hide' WHERE id='$id';");
}
$db->CloseDb();
include($ret);
?>

