<?php
require_once("dbio.class.php");
$seldbname=$_REQUEST['seldbname'];
$field=$_REQUEST['field'];
if($field =='vsland' || $field=='vsldip'||$field=='vslon'){
	$value=$_REQUEST['value'];
	$db=new dbio($seldbname);
	$db->OpenDb();
	$query = "update wellinfo set $field=$value";
	$db->DoQuery($query);
}
?>
