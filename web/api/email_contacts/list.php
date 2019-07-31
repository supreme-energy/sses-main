<?php 
include("../api_header.php");
$query = "select * from emaillist order by cat;";
$db->DoQuery($query);
$results = Array();
while($db->FetchRow()){
    array_push($results, 
        Array(
            "id"=> $db->FetchField("id"),
            "email" => $db->FetchField("email"),
            "name" => $db->FetchField("name"),
            "phone" => $db->FetchField("phone"),
            "cat" => $db->FetchField("cat")
            ));
}
echo json_encdoe($results);
?>