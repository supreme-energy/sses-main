<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
include_once("sses_include.php");
require_once("dbio.class.php");

$currtab=$_POST['currtab'];
$userid=$_POST['userid'];
$selusermod=$_POST['selusermod'];
$modlogin=$_POST['newemaillogin'];
$modpassword1=$_POST['newpassword1'];
$modpassword2=$_POST['newpassword2'];
$modplevel=$_POST['selplevel'];

$entity_id = $_SESSION['entity_id'];

$isThereChange = false;

if($modlogin!="") {
//	$strQry = "SELECT email FROM users WHERE email = '" .$selusermod. "' AND entity_id = " .entity_id. ";";
//	$db->DoQuery($strQry);
/	if($db->FetchRow()) {
//		$_SESSION['err_login_msg'] = "Modified User Login Email already exists. Please re-enter.";
//      header("Location: gva_admin.php?login_err=1&currtab=$currtab");
//        exit();
//	}
	if(!validateLoginEmail($modlogin)) {
	    $_SESSION['err_login_msg'] = "Modified User Login Email is invalid. Please re-enter.";
        header("Location: gva_admin.php?login_err=2&currtab=$currtab");
        exit();
    }
    $isThereChange = true;
}

if($modpassword1 !="") {
    $isThereChange = true;
}
if($modpassword1 !="" && $modpassword2=="") {
	    $_SESSION['err_login_msg'] = "Re-Typed Modified Password is blank. Please enter.";
        header("Location: gva_admin.php?login_err=3&currtab=$currtab");
        exit();
}
if($modpassword1 !="" && $modpassword1 != $modpassword2) {
	    $_SESSION['err_login_msg'] = "Modified Password Entries do not match. Please re-enter.";
        header("Location: gva_admin.php?login_err=4&currtab=$currtab");
        exit();
}
if($modplevel!="No_Change") {
	    $isThereChange = true;
}
if($modplevel=="") {
	    $_SESSION['err_login_msg'] = "Modified Privilege Level not selected. Please select Modified Privilege Level.";
        header("Location: gva_admin.php?login_err=5&currtab=$currtab");
        exit();
}

if($isThereChange == false) {
	$_SESSION['err_login_msg'] = "No changes entered. No updates made/" .$strQry;
    header("Location: gva_admin.php?login_err=9&currtab=$currtab");
    exit();
} else {
    $db=new dbio("sgta_index");
	$db->OpenDb();
	$db->DoQuery("BEGIN TRANSACTION;");

	if($modpassword1 !="") {
		$modpassword = md5($modpassword1);
		$strQry = "UPDATE users SET password='" .$modpassword. "' WHERE email='" .$selusermod. "' AND entity_id = " .entity_id. ";";
		$db->DoQuery($strQry);
	} 

	if($modplevel != "No_Change") {
		$strQry = "UPDATE users SET plevel='" .$modplevel. "' WHERE email='" .$selusermod. "' AND entity_id = " .entity_id. ";";
		$db->DoQuery($strQry);
	}

	if($modlogin !="") {
		$strQry = "UPDATE users SET email='" .$modlogin. "' WHERE email='" .$selusermod. "' AND entity_id = " .entity_id. ";";
		$db->DoQuery($strQry);
	}
	$result=$db->DoQuery("COMMIT;");

	//*  Verify updates.  *//
	if($modlogin !="") {
		$strQry = "SELECT email FROM users WHERE email='" .$modlogin. "' AND entity_id = " .entity_id. ";";
		$db->DoQuery($strQry);
		if(!$db->FetchRow()) {
	   		$_SESSION['err_login_msg'] = "User Modification LOGIN EMAIL FAILED. " .$strQry;
        	header("Location: gva_admin.php?login_err=6&currtab=$currtab");
        	exit();
   	 	} 
	}

	if($modpassword1 !="") {
		$strQry = "SELECT password FROM users WHERE email='" .$modlogin. "' AND entity_id = " .entity_id. ";";
		$db->DoQuery($strQry);
		while($db->FetchRow()) {
			$password = $db->FetchField("password");
	   		if($password != $modpassword) {
	    		$_SESSION['err_login_msg'] = "User Modification PASSWORD FAILED. " .$strQry;
       	 		header("Location: gva_admin.php?login_err=7&currtab=$currtab");
        		exit();
	    	}
    	} 
	}
	
	if($modplevel != "No_Change") {
		$strQry = "SELECT plevel FROM users WHERE email='" .$modlogin. "' AND entity_id = " .entity_id. ";";
		$db->DoQuery($strQry);
		while($db->FetchRow()) {
	  		$plevel = $db->FetchField("plevel");
			if($plevel != $modplevel) {
				$_SESSION['err_login_msg'] = "User Modification PLEVEL FAILED. " .$strQry;
       	 		header("Location: gva_admin.php?login_err=8&currtab=$currtab");
        		exit();
			}
		}
	}
	$_SESSION['err_login_msg'] = "User Modified! " .$selmoduser. "  " .$modlogin;
	header("Location: gva_admin.php?login_err=99&currtab=$currtab");
	exit();
}
?>
