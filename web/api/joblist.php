<?php 
require_once("../dbio.class.php");

$db=new dbio("sgta_index");
$db->OpenDb();
$db->DoQuery("SELECT * FROM dbindex ORDER BY id DESC;");
$response = array();
while($db->FetchRow()) {
	$id=$db->FetchField("id");
	$dbn=$db->FetchField("dbname");
	$dbreal=$db->FetchField("realname");
	$response->push({"sgta_index_id" => $id, "jobname" => $dbn, "realjobname" => $dbreal})	
}
echo json_encode($response);	
?>