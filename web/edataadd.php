<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
$seldbname=$_POST['seldbname'];
$colnum=$_POST['colnum'];
$tablename=$_POST['tablename'];
$label=$_POST['label'];
$scalelo=$_POST['scalelo'];
$scalehi=$_POST['scalehi'];
$logscale=$_POST['logscale']; if(strlen($logscale)<1)	$logscale=0;
$color=$_POST['color'];
$badshit=array("#");
$color=str_replace($badshit, "", $color);
require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();

$db->DoQuery("SELECT colnum FROM edatalogs ORDER BY colnum DESC LIMIT 1;");
$id=-1;
$colnum=0;
if($db->FetchRow()) {
	$colnum = $db->FetchField("colnum");
	$colnum++;
}

if($tablename=="")	$tablename="new table";
if($scalelo=="")	$scalelo=0.0;
if($scalehi=="")	$scalehi=300.0;
if($logscale=="")	$logscale=0;

$label="Data $colnum";
$db->DoQuery("INSERT INTO edatalogs (colnum,tablename,label,scalelo,scalehi,logscale,color) VALUES ('$colnum','$tablename','$label','$scalelo','$scalehi','$logscale','$color')");

$db->DoQuery("SELECT id FROM edatalogs ORDER BY colnum DESC LIMIT 1;");
if($db->FetchRow()) $id = $db->FetchField("id");

if($id>=0) {
	$db->DoQuery("UPDATE edatalogs SET enabled=0;");
	$db->DoQuery("UPDATE edatalogs SET enabled=1 WHERE id=$id;");

	$tablename="edl_$id";
	$query="CREATE TABLE \"$tablename\" (id serial not null primary key, md float, tvd float, vs float, value float);";
	$db->DoQuery($query);
	$query="UPDATE edatalogs SET tablename='$tablename' WHERE id='$id';";
	$db->DoQuery($query);
}
$db->CloseDb();
header("Location: gva_tab6.php?seldbname=$seldbname&seledata=$id");
exit();
?>
