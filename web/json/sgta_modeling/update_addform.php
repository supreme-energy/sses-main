<?php 
	$id    = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
	$label = $_REQUEST['label'];
	$thickness = $_REQUEST['thickness'];
	if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '');
	$db=new dbio($seldbname);
	$db->OpenDb();
	$query = "select * from addforms where id=".pg_escape_string($id)." or label='".pg_escape_string($label)."'";
	$db->DoQuery($query);
	$row = $db->FetchRow();
	if($row){
		$query = "update addforms set label='".pg_escape_string($label)."' , thickness='".pg_escape_string($thickness)."' where id=".$row['id'];
	} else {
		$query = "insert into addforms (label,thickness) values ('".pg_escape_string($label)."','".pg_escape_string($thickness)."')";
	}
	$db->DoQuery($query);
	$db->CloseDb();
	echo 'Done';
?>