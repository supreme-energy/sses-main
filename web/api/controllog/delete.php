<?php 
include("../api_header.php");
$id = $_REQUEST['id'];
$db->DoQuery("SELECT * FROM controllogs where id=$id;");
$db->FetchRow();
$id = $db->FetchField("id");
$tn = $db->FetchField("tablename");
$db->DoQuery("DROP TABLE \"$tn\";");
$db->DoQuery("DELETE FROM controllogs WHERE id='$id';");
echo json_encode(array('status'=>'success', 'message'=>"control log $tn deleted "));
?>