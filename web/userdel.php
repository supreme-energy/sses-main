<?php /*
	Written by: C. Bergman
	Copyright: 2012, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
include_once("sses_include.php");
require_once("dbio.class.php");

$currtab= $_POST['currtab'];
$userid = $_POST['userid'];
$username = $_POST['seluserdel'];

$entity_id = $_SESSION['entity_id'];

$cnt=0;

$db=new dbio("sgta_index");
$db->OpenDb();

$db->DoQuery("SELECT * FROM users;");
while($db->FetchRow()) {
    $cnt++;
}

$db->DoQuery("BEGIN TRANSACTION");		
$strQry = "DELETE FROM users WHERE email='" .$username."' AND entity_id = " .entity_id. ";";
$db->DoQuery($strQry);
$db->DoQuery("COMMIT");

$db->DoQuery("SELECT * FROM users;");
while($db->FetchRow()) {
    $cnt--;
}
$db->CloseDb();

if($cnt == 1) {
   	$_SESSION['err_login_msg'] = "User Deleted.  " .$strQry;
    header("Location: gva_admin.php?login_err=99&currtab=$currtab");
    exit();
} else {
	$_SESSION['err_login_msg'] = "DELETE Failed. " .$strQry;
    header("Location: gva_admin.php?login_err=1&currtab=$currtab");
    exit();
}
?>
