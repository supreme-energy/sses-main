<?php
/*
 * Created on Jan 16, 2016
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once("../dbio.class.php");
$call_back = $_REQUEST['callback'];
header('Content-type: application/json');
$db_name = $_REQUEST['seldbname'];

$sql ="update wellinfo set rt_stream_status=1 ";
$db=new dbio("$db_name"); 
$db->OpenDb();
$db->DoQuery($sql);
$db->CloseDb();

echo "{\"result\":\"DONE\"}";
?>
