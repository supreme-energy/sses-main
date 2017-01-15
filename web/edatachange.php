<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
$seldbname=$_POST['seldbname'];
$seledata=$_POST['seledata'];
$tablename=$_POST['tablename'];
$colnum=$_POST['colnum'];
$label=$_POST['label'];
$scalelo=$_POST['scalelo'];
$scalehi=$_POST['scalehi'];
$logscale=$_POST['logscale']; if(strlen($logscale)<1)	$logscale=0;
$color=$_POST['color'];
$enabled = (isset($_POST['enabled'])&&$_POST['enabled']=='1')?1:0;

$badshit=array("#");
$color=str_replace($badshit, "", $color);
// echo "edatachange...";
require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
if(strlen($seledata)>0) {
	// echo "done";
	$db->DoQuery("UPDATE edatalogs SET enabled=$enabled,colnum=$colnum,tablename='$tablename',label='$label',scalelo=$scalelo,scalehi=$scalehi,logscale=$logscale,color='$color' WHERE id=$seledata");
}
$db->CloseDb();
header("Location: gva_tab6.php?seldbname=$seldbname&seledata=$seledata");
exit();
?>
