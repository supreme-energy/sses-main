<?php
	$call_back = $_REQUEST['callback'];
	require_once('../classes/Index.class.php');
	$obj = new Index();
	header('Content-type: application/json');
	$resp = $obj->get_jobs();
	echo "$call_back($resp)";
?>