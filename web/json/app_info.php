<?php
	$call_back = $_REQUEST['callback'];
	require_once('../classes/AppInfo.class.php');
	$obj= new AppInfo($_REQUEST);
	header('Content-type: application/json');
	$resp = $obj->to_json();
	echo "$call_back($resp)";
?>