<?php
	require_once("../dbio.class.php");
	$call_back = $_REQUEST['callback'];
	header('Content-type: application/json');
	$db_name = $_REQUEST['seldbname'];
	$id = $_REQUEST['id'];
	$reportv = $_REQUEST['rv'];
    $db=new dbio("$db_name"); 
	$db->OpenDb();
	$db->DoQuery("select * from emaillist where id=$id");
	$db->FetchRow();
	$addtolist=0;
	if($reportv=="las"){
		$addtolist =  $db->FetchField("las_file");
		$db->DoQuery("update emaillist set las_file=0 where id=$id");
	}else if($reportv=="r1"){
		$addtolist =  $db->FetchField("report_1");
		$db->DoQuery("update emaillist set report_1=0 where id=$id");
	}else if($reportv=="r2"){
		$addtolist =  $db->FetchField("report_2");
		$db->DoQuery("update emaillist set report_2=0 where id=$id");
	}
	if($addtolist==1){
		$rval = "'removed'";
	} else {
		$rval = "'already_not_set'";
	}
	echo "$call_back($rval)";
?>
