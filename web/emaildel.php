<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
require_once("dbio.class.php");
$seldbname=$_POST['seldbname'];
$id=$_POST['id'];
$currtab=$_POST['currtab'];
$emailid=$_POST['emailid'];
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("DELETE FROM emaillist WHERE id=$emailid;");
$db->CloseDb();
header("Location: gva_tab1.php?seldbname=$seldbname&currtab=$currtab");
exit();
?>
