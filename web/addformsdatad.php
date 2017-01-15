<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
$seldbname=$_POST['seldbname'];
$scrolltop=$_POST['scrolltop'];
if($scrolltop=="")	$scrolltop=0;
$infoid=$_POST['infoid'];
$id=$_POST['id'];
$md=$_POST['md'];
$thicknessin=$_POST['thickness'];
$thickness=sprintf("%f", $thicknessin);
require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
if(strlen($id)>0)
	$db->DoQuery("UPDATE addformsdata SET thickness='$thickness' WHERE md>=$md AND infoid=$infoid");
$db->CloseDb();
header("Location: gva_tab7.php?seldbname=$seldbname&infoid=$infoid&scrolltop=$scrolltop");
exit();
?>
