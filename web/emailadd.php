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
$id=$_POST['id'];
$currtab=$_POST['currtab'];
$emailname=$_POST['emailname'];
$emailaddr=$_POST['emailaddr'];
$db=new dbio($seldbname);
$db->OpenDb();
// $db->DoQuery("INSERT INTO emaillist (name,email) VALUES ('$emailname','$emailaddr');");
$db->DoQuery("INSERT INTO emaillist (name) VALUES ('$emailname');");
$db->CloseDb();
header("Location: gva_tab1.php?seldbname=$seldbname&currtab=$currtab");
exit();
?>
