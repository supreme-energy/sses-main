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
$zoom=$_POST['zoom'];
$seldbname=$_POST['seldbname'];
$sgtastart=$_POST['sgtastart'];
$sgtaend=$_POST['sgtaend'];
$sgtacutin=$_POST['sgtacutin'];
$sgtacutoff=$_POST['sgtacutoff'];
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("SELECT id FROM appinfo LIMIT 1;");
if ($db->FetchRow()) {
	$id=$db->FetchField("id");
	$db->DoQuery("UPDATE appinfo SET zoom='$zoom' WHERE id='$id';");
}
$db->CloseDb();
include($ret);
?>

