<?php 
	$field = $_REQUEST['field'];
	$value = $_REQUEST['value'];
	$tablename = $_REQUEST['id'];
	if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '');
	$db=new dbio($seldbname);
	$db->OpenDb();
	$query = "update welllogs set $field = '$value' where tablename='$tablename';";
	echo $query;
	$db->DoQuery($query);
	$db->CloseDb();
	echo 'Done';
	exec ("./sses_gva -d $seldbname -s 0");
?>