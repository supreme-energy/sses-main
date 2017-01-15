<?php
/*
 * Created on Jan 16, 2016
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("../dbio.class.php");
	$call_back = $_REQUEST['callback'];
	header('Content-type: application/json');
	$db_name = $_REQUEST['seldbname'];
	$current_depth = $_REQUEST['depth'];
	//if(!$current_depth){$current_depth=0;}
	$sql="select * from ghost_data";
	$db=new dbio("$db_name"); 
	$db->OpenDb();
	$db->DoQuery($sql);
	$resp_array = array();
	while($row = $db->FetchRow()) {
		array_push($resp_array,$row);
	}
	$resp =  json_encode($resp_array);
	echo "{$resp}";
?>
