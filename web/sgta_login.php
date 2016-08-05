<?php /*
	Written by: Mark Carrier
	Copyright: 2011, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
require_once("dbio.class.php");
require_once("tabs.php");
require_once("login.class.php");
include_once("sses_include.php");

$log = new LogMeIn();
$log->encrypt = true;

if($_REQUEST['action'] == "login") {
    if($log->login($_REQUEST['username'], $_REQUEST['password'])) {
        $dbs=new dbio("sgta_index");
        $dbs->OpenDb();

        $username = $_REQUEST['username'];
    	$strQry = "SELECT users.entity_id, dbinfo.entity_id, dbinfo.lastid, dbindex.id, dbindex.dbname AS dbname";
    	$strQry = $strQry . " FROM users, dbinfo, dbindex";
    	$strQry = $strQry . " WHERE users.email = '$username' AND users.entity_id = dbinfo.entity_id";
    	$strQry = $strQry . " AND dbinfo.lastid = dbindex.id;";
    	$dbs->DoQuery($strQry);
	    
	    if($dbs->FetchRow()) {
	        $seldbname=$dbs->FetchField("dbname");
        }

        $dbs->CloseDb();
        $URI = "Location: gva_tab1.php?username=" . $username . "&seldbname=" . $seldbname;	
    }else{
        $log->logout();	
        $_SESSION['err_login_msg'] = "Invalid Login";
        $URI = "Location: index.php?login_err=1";
    }
    header($URI);
}

