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
$db=new dbio("sgta_index");
$db->OpenDb();
$query="UPDATE dbindex SET realname='$newname' WHERE dbname='$dbname';";
$db->DoQuery($query);
$db->CloseDb();
header("Location: dbindex.php?seldbname=$seldbname");
exit();
?>
