<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
require_once("dbio.class.php");
$badshit = array("'", "%%");
$currtab=$_POST['currtab'];
$seldbname=$_POST['seldbname'];
$witsml_id = $_POST['witsml_id'];
$endpoint=$_POST['witsml_endpoint'];
$witsml_uname=$_POST['witsml_username'];
$witsml_pass=$_POST['witsml_password'];
$active=$_POST['witsml_active']?'true':'false';
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("BEGIN TRANSACTION;");
if($witsml_id>0){
	$query="update witsml_details set endpoint='$endpoint',username='$witsml_uname',password='$witsml_pass',send_data=$active where id='$witsml_id';";
}else{
	$query = "insert into witsml_details (endpoint,username,password,send_data) values ('$endpoint','$witsml_uname','$witsml_pass',$active);";
}
$db->DoQuery($query);
//echo $query;
$result=$db->DoQuery("COMMIT;");

$db->CloseDb();
header("Location: gva_tab1.php?seldbname=$seldbname&currtab=$currtab");
exit();
?>
