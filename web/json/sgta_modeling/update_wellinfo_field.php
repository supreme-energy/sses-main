<?php 
	$field = $_REQUEST['field'];
	$value = $_REQUEST['value'];
	if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '');
	$db=new dbio($seldbname);
	$db->OpenDb();
	$query = "update wellinfo set $field = '$value';";
	echo $query;
	$db->DoQuery($query);
	$db->CloseDb();
	echo 'Done';
?>