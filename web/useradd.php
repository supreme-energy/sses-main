<?php
/*
 * Created on May 11, 2012
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
?>
<?
include_once("sses_include.php");
require_once("dbio.class.php");

$newlogin=$_POST['newlogin'];
$newpassword1=$_POST['newpassword1'];
$newpassword2=$_POST['newpassword2'];
$newplevel=$_POST['selplevel'];
$currtab=$_POST['currtab'];

$cnt = 0;

require_once("dbio.class.php");

$entity_id = $_SESSION['entity_id'];

if($newlogin=="") {
	    $_SESSION['err_login_msg'] = "New User Login Email is blank. Please enter New User Login Email.";       
        header("Location: gva_admin.php?currtab=$currtab");
        exit();
} else {
	if(!validateLoginEmail($newlogin)) {
	    $_SESSION['err_login_msg'] = "New User Login Email is invalid.";
        header("Location: gva_admin.php?login_err=1&currtab=$currtab");
        exit();
    }	
}
if($newpassword1=="") {
	    $_SESSION['err_login_msg'] = "Password is blank. Please enter Password.";
        header("Location: gva_admin.php?login_err=2&currtab=$currtab");
        exit();
}
if($newpassword2=="") {
	    $_SESSION['err_login_msg'] = "Re-Typed Password is blank. Please re-type Password.";
        header("Location: gva_admin.php?login_err=4&currtab=$currtab");
        exit();
}
  
if($newpassword1 != $newpassword2) {
	    $_SESSION['err_login_msg'] = "Password Entries do not match. Please re-enter Passwords.";
        header("Location: gva_admin.php?login_err=5&currtab=$currtab");
        exit();
}
if($newplevel=="") {
	    $_SESSION['err_login_msg'] = "Privilege Level not selected. Please select Privilege Level.";
        header("Location: gva_admin.php?login_err=6&currtab=$currtab");
        exit();
}

$db=new dbio("sgta_index");
$db->OpenDb();

$db->DoQuery("SELECT email FROM users;");
while($db->FetchRow()) {
	$userlogin=$db->FetchField("email");
	if ($newlogin == $userlogin) {
		$_SESSION['err_login_msg'] = "User Login Email already exists. Please enter new User Login Email";
        header("Location: gva_admin.php?login_err=7&currtab=$currtab");
        exit();
	}
	$cnt--;
}

if($newpassword1 == $newpassword2) {
    $newpassword=md5($newpassword1); 
    $strQry = "INSERT INTO users (email, password, entity_id, plevel) VALUES ";
    $strQry = $strQry . "('" .$newlogin. "','" .$newpassword1. "'," .$entity_id. ",'" .$newplevel. "');";
  	$db->DoQuery($strQry); 
  	$db->DoQuery("COMMIT;");
  	$cnt2=0;
    $db->DoQuery("SELECT email FROM users ORDER BY id;");
    while($db->FetchRow()) {
	    $cnt++;
	}
	if ($cnt == 1) {
		$_SESSION['err_login_msg'] = "INSERT Successful.  " .$strQry;;
        header("Location: gva_admin.php?login_err=99&currtab=$currtab");
        exit();
	} else {
		$_SESSION['err_login_msg'] = "INSERT failed.  " .$strQry;
        header("Location: gva_admin.php?login_err=8&currtab=$currtab");
        exit();
    }
} else {
	$_SESSION['err_login_msg'] = "Password Entries do not match." .$newpassword1. " " .$newpassword2;
    header("Location: gva_admin.php?login_err=9&currtab=$currtab");
    exit();
}
$db->CloseDb();
header("Location: gva_admin.php?login_err=0&currtab=$currtab");
exit();
?>
