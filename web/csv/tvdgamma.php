<?php
	require_once('../classes/WellLog.class.php');
	$dbname = $_REQUEST['seldbname'];
	$filename = $dbname."_tvdgamma.csv";
	header('Content-type: text/csv');
	header("Content-disposition: attachment;filename=$filename");
	$welllog = new WellLog($_REQUEST);
	$sdepth = isset($_REQUEST['sdepth'])?$_REQUEST['sdepth']:0;
	$edepth = isset($_REQUEST['edepth'])?$_REQUEST['edepth']:999999;
	$incr   = isset($_REQUEST['incr'])?$_REQUEST['incr']:null;
	$result = $welllog->get_tvdtogamma($sdepth,$edepth,$incr);
	echo("tvd,gamma\n");
	foreach($result as $r){
		echo $r['tvd'].",".$r['gamma']."\n";
	}
?>