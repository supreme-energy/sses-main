<?php 
include ("../../api_header.php");
$with_data = $_REQUEST ['data'] == '1';
$query="select * from deleted_survey_group order by id desc";
$db->DoQuery($query);
$deleted_groups = array();
while($row = $db->FetchRow()){
    $this_group = array("id" => $group_id, "created" => $row['created']);
    $group_id = $row['id'];
    if($with_data){
        include ("./cleaned.php");
        $this_group ['data'] = $data;
    }
    array_push($deleted_groups, $this_group);
}
echo json_encode($deleted_groups);
?>