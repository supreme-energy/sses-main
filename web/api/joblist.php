<?php 
require_once("../dbio.class.php");
if(!isset($seldbname) or $seldbname == '')	$seldbname = (isset($_GET['seldbname']) ? $_GET['seldbname'] : ''
$dbids=array();
$dbnames=array();
$realnames=array();
$db=new dbio("sgta_index");
$db->OpenDb();
$db->DoQuery("SELECT * FROM dbindex ORDER BY id DESC;");
$response = array();
while($db->FetchRow()) {
	$id=$db->FetchField("id");
	$dbids=$id;
	$dbn=$db->FetchField("dbname");
	$dbreal=$db->FetchField("realname");
	$response->push({"sgta_index_id" => $id, "jobname" => $dbn, "realjobname" => $dbreal})	
}
echo json_encode($response);	
?>