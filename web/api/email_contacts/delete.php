<?php 

include ("../api_header.php");
$id = $_REQUEST['id'];
$query = "delete from emaillist where id = $id";
$db->DoQuery($query);
$response = array("status"=>"success", "message" => "operation successful");
echo json_encode($response);

?>
