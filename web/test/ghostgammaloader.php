<?php
/*
 * Created on Jan 16, 2016
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once("../dbio.class.php");

$db_name = $argv[1];
$handle = fopen("../test/wld_655_for_357.csv","r");
$headers = fgetcsv($handle);
$db=new dbio("$db_name"); 
$db->OpenDb();
$exit_onbreak=0;
while(1){
	$sql = "select rt_stream_test from wellinfo";
	echo "running $sql\n";
	$db->DoQuery($sql);	
	$row = $db->FetchRow();
	if($row["rt_stream_test"]==0){
		break;
	} else {
		echo "starting csv loop\n";
		while($els = fgetcsv($handle)){
			$sql = "select rt_stream_status,rt_stream_ghost,rt_stream_ld from wellinfo";
			echo "running $sql\n";
			$db->DoQuery($sql);	
			$row = $db->FetchRow();
			if($row["rt_stream_status"]==0){break;$exit_onbreak=1;}
			$sql = "insert into ghost_data (md,tvd,vs,value,hide,depth) values" .
					"(".$els[1].",".$els[2].",".$els[3].",".$els[4].",".$els[5].",".$els[6].")";
			echo "running $sql\n";
			$db->DoQuery($sql);
			sleep(11);
			$sql = "select rt_stream_status,rt_stream_ghost,rt_stream_ld from wellinfo";
			echo "running $sql\n";
			$db->DoQuery($sql);	
			$row = $db->FetchRow();
			if($row["rt_stream_status"]==0){break;$exit_onbreak=1;}
			$exit_onbreak=0;
		}
	}
	if($exit_onbreak==0){
		break;
	}
 	sleep(11);
}
?>
