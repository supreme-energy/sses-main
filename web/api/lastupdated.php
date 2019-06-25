<?php 
include("api_header.php");
$query ="select greatest(max(t1.updated_at),max(t2.updated_at), max(t3.updated_at), max(t4.updated_at)) as last_updated from surveys as t1, projections as t2, appinfo as t3, wellinfo as t4;";
$db->DoQuery($query);
$db->FetchRow();
echo json_encode(array('last_updated'=> $db->FetchField('last_updated')));
?>