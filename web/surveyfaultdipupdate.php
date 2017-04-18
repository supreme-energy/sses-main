<?php
// Created on Nov 12, 2012
//
// To change the template for this generated file go to
// Window - Preferences - PHPeclipse - PHP - Code Templates

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once("dbio.class.php");

if(isset($_REQUEST['ret']) and $_REQUEST['ret'] != '') $ret = $_REQUEST['ret'];
else $ret = 'gva_tab3.php';

$dbname=$_REQUEST['seldbname'];
$action=$_REQUEST['action'];
$value=$_REQUEST['value'];
if(!$value){
 	$value=0;
}
$id = $_REQUEST['id'];
if($action=='dip'){
 		$query = "update surveys set dip=$value where id=$id";
}else if($action=='fault'){
 		$query = "update surveys set fault=$value where id=$id"; 	
}else{
 	$query='';
}
echo "<p>query=$query</p>";
if($query){
 	 $db=new dbio($dbname);
	 $db->OpenDb();
	 $db->DoQuery($query);
	 $db->CloseDb();
}
//exec("./sses_gva -d $dbname");
header( "Location:{$ret}?seldbname={$dbname}" ) ; 
?>
