<?php
	require_once("../dbio.class.php");
	$seldbname = $_REQUEST['seldbname'];
	$tn = $_REQUEST['tn'];
	$filename = $seldbname.$tn."_rawimportexport.csv";
	header('Content-type: text/csv');
	header("Content-disposition: attachment;filename=$filename");
	$db=new dbio($seldbname);
	$db->OpenDb();
	$query = "select raw_import_data from welllogs where tablename='$tn'";
	//echo $query;
	$db->DoQuery($query);
	if($db->FetchRow()){
		echo $db->FetchField('raw_import_data');
	} else {
		echo 'error';
	}
	$db->CloseDb();
?>