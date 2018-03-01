<?php 
require_once("dbio.class.php");
$seldbname=$_REQUEST['seldbname'];
$id = $_REQUEST['id'];
$db=new dbio($seldbname,true);
$db->OpenDb();
$query = "select * from welllogs where id = $id";
$db->DoQuery($query);
$row = $db->FetchRow();
if($row){
	$query = "drop table where tablename=".$row['tablename'];
	$db->DoQuery($query);
	$query = "delete from welllogs where id = $id";
	$db->DoQuery($query);
}
?>