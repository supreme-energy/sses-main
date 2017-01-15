<?php
	$call_back = $_REQUEST['callback'];
	require_once('../classes/ControlLog.class.php');
	$obj = new ControlLog($_REQUEST);
	header('Content-type: application/json');
	$resp = $obj->to_json();
	echo "$call_back($resp)";
?>