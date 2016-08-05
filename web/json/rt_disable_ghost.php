<?php
/*
 * Created on Jan 16, 2016
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once("../dbio.class.php");
$call_back = $_REQUEST['callback'];
header('Content-type: application/json');
$db_name = $_REQUEST['seldbname'];

$sql ="update wellinfo set rt_stream_ghost=0 ";

$db=new dbio("$db_name"); 
$db->OpenDb();
$db->DoQuery($sql);
$sql = "select dip,fault from welllogs where isghost=1";
//echo $sql."<br>";
$db->DoQuery($sql);
$dip_fault = $db->FetchRow();
//print_r($dip_fault);
$sql = "update appinfo set ghost_dip=".$dip_fault["dip"].",ghost_fault=".$dip_fault['fault'];
//echo $sql."<br>";
$db->DoQuery($sql);
$sql="delete from surveys where isghost=1";
$db->DoQuery($sql);
$sql = "select * from welllogs where isghost=1";
$db->DoQuery($sql);
$db2 = new dbio("$db_name");
$db2->OpenDb();
while($db->FetchRow()){
	$tablename = $db->FetchField("tablename");
	$sql2 = " DROP TABLE IF EXISTS\"$tablename\"";
	$db2->DoQuery($sql2);
}
$sql = "delete from welllogs where isghost=1";
$db->DoQuery($sql);
$db2->CloseDb();
$db->CloseDb();

echo "{\"result\":\"DONE\"}";
?>
