<?php 
include ("../../api_header.php");
$with_data = $_REQUEST ['data'] == '1';
$query="select * from deleted_survey_group order by id desc";
$db->DoQuery($query);
$deleted_groups = array();
while($row = $db->FetchRow()){
    $group_id = $row['id'];
    array_push($deleted_groups,array("id" => $group_id, "created" => $row['created']));
    if($with_data){
        include ("./cleaned.php");
        $result ['data'] = $data;
    }
}
echo json_encode($deleted_groups);
?>