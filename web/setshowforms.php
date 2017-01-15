<?
require_once("dbio.class.php");
$seldbname=$_REQUEST['seldbname'];
$who_to_set = $_REQUEST['wts'];
$value_to_set = $_REQUEST['vts'];
$who_to_set = ($who_to_set==0?'sgta_show_formations':'wb_show_formations');
$query = "update wellinfo set $who_to_set = $value_to_set";
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery($query);
?>