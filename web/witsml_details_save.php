<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
require_once("dbio.class.php");

$seldbname=$_REQUEST['seldbname'];
$wellid=$_REQUEST['welluid'];
$wellboreid=$_REQUEST['wellboreuid'];
$logid= $_REQUEST['loguid'];


$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("select count(*) as cnt from witsml_details");
$row = $db->FetchRow();
if($row['cnt']>0){
$db->DoQuery("BEGIN TRANSACTION;");
$query="update witsml_details set wellid='$wellid',boreid='$wellboreid',logid='$logid'";
$db->DoQuery($query);
$result=$db->DoQuery("COMMIT;");
}else {
	$query = "insert into witsml_details (wellid,boreid,logid) values ('$wellid','$wellboreid','$logid')";
	$db->DoQuery($query);
}

$db->CloseDb();

?>
