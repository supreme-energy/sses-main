 <?php
include ("../api_header.php");
$id = $_REQUEST['id'];
$query = "delete from addforms where id='$id'";
echo $query;
$db->DoQuery($query);
$query = "delete from addformsdata where infoid= '$id'";
echo $query;
$db->DoQuery($query);
echo json_encode(array("status"=>"success", "message"=>"formation deleted"));
?>