<?php 
	$field = $_REQUEST['field'];
	$value = $_REQUEST['value'];
	if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '');
	$db=new dbio($seldbname);
	$db->OpenDb();
	$query = "update appinfo set $field = '$value';";
	echo $query;
	$db->DoQuery("update appinfo set $field = '$value';");
	$db->CloseDb();
	echo 'Done';
?>