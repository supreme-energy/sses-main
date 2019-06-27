<?php 
include("api_header.php");
include("update_functions");
echo json_encode(array('last_updated'=> $db->FetchField('last_updated')));
?>