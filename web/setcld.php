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
$dip=$_POST['dip'];
$tot=$_POST['tot'];
$bot=$_POST['bot'];
$azm = $_POST['dazm'];
$seldbname=$_POST['seldbname'];
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("BEGIN TRANSACTION;");
$db->DoQuery("UPDATE controllogs SET dip='$dip' WHERE tablename='$tablename';");
$db->DoQuery("UPDATE controllogs SET tot='$tot' WHERE tablename='$tablename';");
$db->DoQuery("UPDATE controllogs SET bot='$bot' WHERE tablename='$tablename';");
$db->DoQuery("UPDATE controllogs SET azm='$azm' WHERE tablename='$tablename';");
$db->DoQuery("COMMIT;");
$db->CloseDb();
include($ret);
?>

