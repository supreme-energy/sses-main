<?php
	require_once("../../dbio.class.php");
	$call_back = $_REQUEST['callback'];
	header('Content-type: application/json');
	$db_name = $_REQUEST['seldbname'];
	$well_log_table = $_REQUEST['wld'];
	$pmd = isset($_REQUEST['md'])?$_REQUEST['md']:null;
	$inc_opts = $_REQUEST['inc_opts'];
	$shadow   = $_REQUEST['shadow'];
	if($inc_opts=='all'){
		
	}else if($inc_opts=='prev'){
	
	}else if($inc_opts=='prnxt'){
	
	}else{
		
	}
    $db=new dbio("$db_name"); 
	$db->OpenDb();
	
	$resp_array = array();
	$db->DoQuery("select * from welllogs where tablename='$well_log_table'");
	$wellloginfo = $db->FetchRow();
	$db->DoQuery("Select count(*) as cnt from $well_log_table where hide=0");
	$row = $db->FetchRow();
	$cnt = $row['cnt'];
	$db->DoQuery("SELECT * FROM $well_log_table WHERE hide=0 ORDER BY id ASC");
		
	while($row = $db->FetchRow()) {		
		array_push($resp_array,array('x'=>$row['value'],'y'=>null,'x2'=>null,'y2'=>null,'y3'=>null,'y4'=>$row['depth']*-1,'y5'=>null,'y6'=>null,'l'=>$cnt));		
	}
	$db->DoQuery("SELECT * FROM cld_12 WHERE hide=0 and md >5680 and md < 5790 ORDER BY id ASC");
	while($row=$db->FetchRow()){
		array_push($resp_array,array('x'=>$row['value'],'y'=>$row['md']*-1,'x2'=>null,'y2'=>null,'y3'=>null,'y4'=>null,'y5'=>null,'y6'=>null,'l'=>null));
	}
	if($pmd){
		$endmdp =$wellloginfo['startmd'];
		$endmd=$wellloginfo['startmd']-$pmd;
	}else{
		$endmd = 0;
		$endmdp=999999;
	}
	$db->DoQuery("SELECT * from welllogs where hide=0 and startmd > $endmd and endmd< $endmdp and startdepth > 5680 and enddepth <5790 and tablename!='$well_log_table' order by id");
	$db2=new dbio("$db_name"); 
	$db2->OpenDb();
	while($row=$db->FetchRow()){
		$ntable = $row['tablename'];
		$query = "SELECT * FROM $ntable WHERE hide=0  ORDER BY id ASC";
		$db2->DoQuery($query);
		while($row2=$db2->FetchRow()){
			array_push($resp_array,array('x'=>$row2['value'],'y'=>null,'x2'=>null,'y2'=>$row2['depth']*-1,'y3'=>null,'y4'=>null,'y5'=>null,'y6'=>null,'l'=>null));
		}		
	}
	$resp =  json_encode($resp_array);
	echo "$call_back($resp)";
?>