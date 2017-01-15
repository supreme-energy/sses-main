<?php
	require_once("../dbio.class.php");
	$call_back = $_REQUEST['callback'];
	header('Content-type: application/json');
	$db_name = $_REQUEST['seldbname'];
    $db=new dbio("$db_name"); 
	$db->OpenDb();
	$db->DoQuery("SELECT * FROM emaillist ORDER BY cat;");
	$resp_array = array();
	while($db->FetchRow()) {
		array_push($resp_array,array('id'=>$db->FetchField("id"),
		'email'=>$db->FetchField("email"),'name'=>$db->FetchField("name"),
		'personnel'=>$db->FetchField("cat"),
		'phone'=>$db->FetchField("phone")));
	}
	$resp =  json_encode($resp_array);
	echo "$call_back($resp)";
?>

