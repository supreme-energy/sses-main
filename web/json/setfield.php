<?php
/*
 * Created on Sep 18, 2015
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once("../dbio.class.php");
	$call_back = $_REQUEST['callback'];
	header('Content-type: application/json');
	$db_name = $_REQUEST['seldbname'];
	$id = $_REQUEST['id'];
	$reportv = $_REQUEST['rv'];
    $db=new dbio("$db_name"); 
	$db->OpenDb();
	$addtolist=$id;
	if($id==0 || $id==1){
		if($reportv=="las"){
			$db->DoQuery("update appinfo set email_attach_las=$id");
		}else if($reportv=="r1"){
			$db->DoQuery("update appinfo set email_attach_r1=$id");
		}else if($reportv=="r2"){
			$db->DoQuery("update appinfo set email_attach_r2=$id");
		}
	}
?>
