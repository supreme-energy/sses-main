<?php 
	$field = $_REQUEST['field'];
	$value = $_REQUEST['value'];
	$col_idx = $_REQUEST['col_idx'];
	if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '');
	$db=new dbio($seldbname);
	$db->OpenDb();
	$query = "select count(*) as cnt from import_config where field_name='$field'";
	$db->DoQuery($query);
	$cnt_row = $db->FetchRow();
	if($cnt_row['cnt']>0){
		$query = "update import_config set field_value = '$value', field_column_index='$col_idx' where field_name='$field';";
	} else {
		$query = "insert into import_config (field_name,field_value,field_column_index) values ('$field','$value','$col_idx')";
	}
	$db->DoQuery($query);
	$db->CloseDb();
	echo 'Done';
?>