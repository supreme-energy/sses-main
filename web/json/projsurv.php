<?php
	require_once("../dbio.class.php");
	require_once("../classes/Survey.class.php");
	$call_back = $_REQUEST['callback'];
	header('Content-type: application/json');
	$db_name = $_REQUEST['seldbname'];
    $db=new dbio("$db_name"); 
	$db->OpenDb();
	$db->DoQuery("SELECT * FROM projections ORDER BY md desc;");
	$resp_array = array();
	while($row = $db->FetchRow()) {
		$row['proj']=true;
		array_push($resp_array,$row);
	}
	$survey_loader = new Survey($_REQUEST);
	$surveys=$survey_loader->get_surveys(false);
	$bitproj = $survey_loader->get_bitProjection($surveys);
	array_unshift($surveys,$bitproj);
	$final_res = array_merge($resp_array,$surveys);
	$resp =  json_encode($final_res);
	echo "$call_back($resp)";
?>