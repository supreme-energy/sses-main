<?php
/*
 * Created on Apr 18, 2012
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once("dbio.class.php");
$callback  = isset($_REQUEST['callback'])?$_REQUEST['callback']:'';
$seldbname='sgta_78';
$query = "select * from surveys order by md";
$db=new dbio($seldbname);
$db->OpenDb();
$db2=new dbio($seldbname);
$db2->OpenDb();
$db->DoQuery($query);
$data = array();
while($db->FetchRow()) {		
	$vs =  (float)($db->FetchField("vs") );
	$tvd = (float)($db->FetchField("tvd") );
	$set = array('x'=>$vs,'y'=>$tvd*-1);
	array_push($data,$set);
	
}
$query = "select * from projections where vs < 3500 order by md";
$db->DoQuery($query);
while($db->FetchRow()) {		
	$vs =  (float)($db->FetchField("vs") );
	$tvd = (float)($db->FetchField("tvd") );
	$set = array('x'=>$vs,'y2'=>$tvd*-1);
	array_push($data,$set);	
}
$query = "select * from addforms";
$db->DoQuery($query);
$fmcnt = 3;
while($db->FetchRow()){
	$fid = $db->FetchField('id');
	$query2 =  "SELECT * FROM addformsdata WHERE infoid=$fid and vs<3500 ORDER BY md";
	$db2->DoQuery($query2);
	while($db2->FetchRow()){
		$vs = (float)($db2->FetchField("vs") );
		$tot = (float)($db2->FetchField("tot") );
		$set = array('x'=>$vs, "y$fmcnt"=>$tot*-1);
		array_push($data,$set);
	}
	$fmcnt++;
}
$json = json_encode($data);
header('Content-type: application/json');
echo "$callback($json)";
?>
