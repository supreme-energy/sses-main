<?php 
include ("../api_header.php");
$json_body = file_get_contents('php://input');
$obj = json_decode($json_body);
$id = $obj->id;

foreach($obj as $key => $value){
    if($key=='id'){
        continue;
    }
    $query = "update emaillist set $key = '$value' where id = $id";   
    $db->DoQuery($query);
}

$response = array("status"=>"success", "message" => "operation successful");
echo json_encode($response);
?>