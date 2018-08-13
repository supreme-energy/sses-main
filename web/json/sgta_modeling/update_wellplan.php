<?php 
	
	$plan_file = $_REQUEST['plan_file'];
	$plan_start_row = $_REQUEST['plan_row'];
	$plan_start_col = $_REQUEST['plan_col']
	$wellplan_name = $_REQUEST['plan_name'];
	
	$required_fields = ['MD', 'INC', 'AZM']
	
	if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '');
	$db=new dbio($seldbname);
	$db->OpenDb();
	$query = "update wellinfo set $field = '$value';";
	echo $query;
	$db->DoQuery($query);
	$db->CloseDb();
	echo 'Done';
?>