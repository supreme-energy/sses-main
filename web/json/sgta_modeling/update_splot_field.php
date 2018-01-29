<?php 
	$field = $_REQUEST['field'];
	$value = $_REQUEST['value'];
	if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '');
	$db=new dbio($seldbname);
	$db->OpenDb();
	$query = "update splotlist set $field = '$value' WHERE ptype='LAT' AND mtype='TVD';";
	echo $query;
	$db->DoQuery("update splotlist set $field = '$value' WHERE ptype='LAT' AND mtype='TVD';");
	$db->CloseDb();
	echo 'Done';
?>