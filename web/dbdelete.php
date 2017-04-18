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
$dbname=$_POST['dbname'];
$db=new dbio("sgta_index");
$db->OpenDb();
$db->DoQuery("select * from server_info");
$row = $db->FetchRow();
if($row['on_lan']){
	$report_addr= $row['reports_lan'];
 	$my_addr = $row['lan_addr'];
} else {
	$report_addr= $row['reports_wan'];
 	$my_addr = $row['wan_addr'];
}
$call_to = "http://$report_addr/report_back_end/index.php?cmd=cu_all&dbserver=$my_addr&dbname=$seldbname";
$process = curl_init($call_to);
curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($process, CURLOPT_HEADER, 1);
curl_setopt($process, CURLOPT_USERPWD, $up);
curl_setopt($process, CURLOPT_TIMEOUT, 30);
curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
$return = curl_exec($process);
curl_close($process);
$db->DoQuery("DROP DATABASE IF EXISTS \"$dbname\";");
$db->DoQuery("DELETE FROM dbindex WHERE id=$id;");
$db->CloseDb();
if($dbname==$seldbname)	$seldbname="";
header("Location: dbindex.php?seldbname=$seldbname");
exit();
?>
