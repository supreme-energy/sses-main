<?php
	require_once("../../dbio.class.php");
	$call_back = $_REQUEST['callback'];
	header('Content-type: application/json');
	$db_name = $_REQUEST['seldbname'];
	$contrl_log_table = $_REQUEST['cld'];
	$offset = (int)$_REQUEST['offset'];
	
	$stard  = isset($_REQUEST['smd'])?" and md >= '".$_REQUEST['smd']."' ":'';
	$endd   = isset($_REQUEST['emd'])?" and md >= '".$_REQUEST['emd']."' ":'';
	
    $db=new dbio("$db_name"); 
	$db->OpenDb();
	
	$resp_array = array();

	if($offset <=0){
		$db->DoQuery("Select * from controllogs WHERE tablename='$contrl_log_table'");
		while($row = $db->FetchRow()){
			$bot = $row['bot']*-1;
			$tot = $row['tot']*-1;
			array_push($resp_array,array('x2'=>-1000,'y2'=>0,'y3'=>0,'x'=>null,'y'=>null));
			array_push($resp_array,array('x2'=>-1000,'y2'=>$tot,'y3'=>$bot,'x'=>null,'y'=>null));
			array_push($resp_array,array('x2'=>500,'y2'=>$tot,'y3'=>$bot,'x'=>null,'y'=>null));
			array_push($resp_array,array('x2'=>1000,'y2'=>$tot,'y3'=>$bot,'x'=>null,'y'=>null));
		}
	}
	$db->DoQuery("Select count(*) as cnt from $contrl_log_table where hide=0$stard$endd;");
	$row = $db->FetchRow();
	$cnt = $row['cnt'];
	$db->DoQuery("SELECT * FROM $contrl_log_table WHERE hide=0$stard$endd ORDER BY id ASC;");
	while($row = $db->FetchRow()) {		
		array_push($resp_array,array('x'=>$row['value'],'y'=>$row['md']*-1,'x2'=>null,'y2'=>null,'y3'=>null,'l'=>$cnt));		
	}
	$resp =  json_encode($resp_array);
	echo "$call_back($resp)";
?>