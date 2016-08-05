<?php
	require_once("../dbio.class.php");
	$call_back = $_REQUEST['callback'];
	header('Content-type: application/json');
	$db_name = $_REQUEST['seldbname'];
    $db=new dbio("$db_name"); 
	$db->OpenDb();
	$db->DoQuery("SELECT * FROM projections ORDER BY md desc;");
	$resp_array = array();
	while($row = $db->FetchRow()) {
		array_push($resp_array,$row);
	}
	$resp =  json_encode($resp_array);
	echo "$call_back($resp)";
?>