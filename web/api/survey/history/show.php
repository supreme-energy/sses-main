<?php 
include ("../../api_header.php");
$group_id = $_REQUEST['id'];
$query="select * from deleted_survey_group where id = $group_id";
$db->DoQuery($query);
while($row = $db->FetchRow()){
    $this_group = array("id" => $group_id, "created" => $row['created']);    
    include ("./cleaned.php");
    $this_group ['data'] = $data;
}
echo json_encode($this_group);
?>