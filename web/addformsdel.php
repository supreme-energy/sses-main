<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
$seldbname=$_POST['seldbname'];
$infoid=$_POST['infoid'];
require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
if(strlen($infoid)>0)
	$db->DoQuery("DELETE FROM addforms WHERE id=$infoid;");
$db->CloseDb();
header("Location: gva_tab7.php?seldbname=$seldbname");
exit();
?>
