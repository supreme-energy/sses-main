<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
require_once("dbio.class.php");
$badshit = array("'", "%%");
$currtab=$_REQUEST['currtab'];
$seldbname=$_REQUEST['seldbname'];
$connectiontype = $_REQUEST['connection_type'];
$tablename=$_REQUEST['connection_dbname'];
$addr=$_REQUEST['connection_addr'];
$uname=$_REQUEST['connection_uname'];
$pass=$_REQUEST['connection_pass'];
$aisd = $_POST['acsd'];
$grmnemonic= (isset($_POST['gr_import_mnemonic'])&& $_POST['gr_import_mnemonic']!='')?$_POST['gr_import_mnemonic']:'GR';
$enable_alarm = (isset($_POST['importalarmenable'])&&$_POST['importalarmenable']!='')?$_POST['importalarmenable']:0;
$alarm = (isset($_POST['importalarm'])&&$_POST['importalarm']!='')?$_POST['importalarm']:'';
if(!$aisd || $aisd <0){
	$aisd = 0;
}

$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("select count(*) as cnt from rigminder_connection");
$row = $db->FetchRow();
if($row['cnt']>0){
$db->DoQuery("BEGIN TRANSACTION;");
$query="update rigminder_connection set host='$addr',username='$uname',password='$pass',dbname='$tablename',aisd='$aisd',connection_type='$connectiontype';";
$db->DoQuery($query);
$result=$db->DoQuery("COMMIT;");
}else {
	$query = "insert into rigminder_connection (host,username,password,dbname,aisd,connection_type) values ('$addr','$uname','$pass','$tablename','$aisd','$connectiontype')";
	$db->DoQuery($query);
}
$query= "update appinfo set auto_gr_mnemonic='$grmnemonic',import_alarm_enabled=$enable_alarm,import_alarm='$alarm'";
$db->DoQuery($query);

$db->CloseDb();
header("Location: gva_tab1.php?seldbname=$seldbname&currtab=$currtab");
exit();
?>
