<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
require_once("dbio.class.php");
$seldbname=$_POST['seldbname'];
$sortdir=$_POST['sortdir'];
$sids=$_POST['sids'];
$deccl = $_REQUEST['deccl'];
$deccl_m = $_REQUEST['deccl_m'];
$returnto = (isset($_POST['returnto']) and $_POST['returnto'] != '') ? $_POST['returnto'] : 'gva_tab3.php';
$data=explode(",", $sids);
$num=count($data);
if($num>0) {
	$db=new dbio($seldbname);
	
	$db->OpenDb();
	require_once('readwellinfo.inc.php');
	if($deccl=='t'){
		$db2 = new dbio($seldbname);
		$db2->OpenDb();
		$query = "select pterm_method from wellinfo";
		$db2->DoQuery($query);
		$db2->FetchRow();
		$pterm_method = $db2->FetchField('pterm_method');
		if($pterm_method=='bp'){
			$query = "select * from projections order by md desc";
			$db->DoQuery($query);
			$cnt = 0;
			while($db->FetchRow()){
			
				if($cnt==0){
					$cnt++;
					continue;
				}else{
					$cnt++;
					$vs = $db->FetchField('vs')-$deccl_m;
					$id = $db->FetchField('id');
					$datal = $db->FetchField('data');
					$data_ar = explode(',',$datal);
					if(count($data_ar)>3){
						$data_ar[0]=$vs;
					}else {
						$data_ar[1]=$vs;
					}
					$datal = implode(',',$data_ar);
					$query = "update projections set vs = $vs , data='$datal' where id=$id";
					$db2->DoQuery($query);
				}
				
			}
		}
		$db2->CloseDb();
	}
	$db->DoQuery("BEGIN TRANSACTION");
	$mdstart=0;
	$mdend = 0;
	$deletionranges = array();
	echo 1;
	for($i=0;$i<$num;$i++) {
		// echo "<pre>id:$data[$i]\n</pre>";
		if(substr($data[$i], 0, 1)=='p') {
			$tmp=substr($data[$i], 1);
			// echo "projection id: $tmp";
			$db->DoQuery("DELETE FROM projections WHERE id='$tmp'");
		}
		else {
			// echo "survey id: $data[$i]";
			$db->DoQuery("select * from surveys where id='$data[$i]'");
			$db->FetchRow();
			$mdcurrent = $db->FetchField('md');
			if($mdcurrent > $mdend){
				$mdend = $mdcurrent;
			}
			if($mdstart==0){
				$db->DoQuery("select * from surveys where md < $mdcurrent order by md desc limit 1;");
				if($db->FetchRow()){
					$mdstart = $db->FetchField('md');
				}
			}
			if($mdend < $mdstart){
				$mdswitch = $mdstart;
				$mdstart = $mdend;
				$mdend = $mdswitch;
			}
			array_push($deletionranges,array($mdstart,$mdend));
			$db->DoQuery("DELETE FROM surveys WHERE id='$data[$i]'");
			$mdstart=0;
			$mdend=0;
		}
		
	}
	if($autorc_host){
		$db2= new dbio($seldbname);
		$db2->OpenDb();
		$db->DoQuery("select * from edatalogs;");
		foreach($deletionranges as $dr){
			$mdstart = $dr[0];
			$mdend = $dr[1];
			while($db->FetchRow()){
				$tn = $db->FetchField('tablename');
				$db2->DoQuery("delete from \"$tn\" where md > $mdstart and md<=$mdend");
			}
		}
		$db2->CloseDb();
	}
	$db->DoQuery("COMMIT");
	$db->CloseDb();
	
	exec ("./sses_cc -d $seldbname");
	exec("./sses_gva -d $seldbname");
	exec ("./sses_cc -d $seldbname -p");
}
header("Location: {$returnto}?seldbname={$seldbname}");
exit();
// include "gva_tab3.php";
?>
