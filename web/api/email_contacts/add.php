<?php 
include ("../api_header.php");
$json_body = file_get_contents('php://input');
$obj = json_decode($json_body);
$query = "insert into emaillist (email, name, phone, cat) values ('$obj->email', '$obj->name', '$obj->phone', '$obj->cat')";
$db->DoQuery($query);
$response = array("status"=>"success", "message" => "operation successful");
echo json_encode($response);
?>