<?php
	$call_back = $_REQUEST['callback'];
	require_once('../classes/WellInfo.class.php');
	$obj = new WellInfo($_REQUEST);
	header('Content-type: application/json');
	$resp = $obj->to_json();
	echo "$call_back($resp)";
?>