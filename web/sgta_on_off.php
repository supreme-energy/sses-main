<?php
/*
 * Created on Nov 14, 2012
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once("dbio.class.php");
 $dbname=$_REQUEST['seldbname'];
 $value=$_REQUEST['value'];
 $query = "update appinfo set sgta_off=$value";
 if($query){
 	 $db=new dbio($dbname);
	 $db->OpenDb();
	 $db->DoQuery($query);
	 $db->CloseDb();
 }
 header( 'Location: '."gva_tab3.php?seldbname=$dbname" ) ; 
?>
