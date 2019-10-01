<?
require_once("../../dbio.class.php");
require_once("../formation/initialization/create_functions.php");
$name=$_REQUEST['name'];
$copy_from= isset($_REQUEST['copy']) ? $_REQUEST['copy'] : 'sgta_template';

$db=new dbio("sgta_index");
$db->OpenDb();
$db->DoQuery("INSERT INTO dbindex (dbname) VALUES ('xxx');");
$db->DoQuery("SELECT id,dbname FROM dbindex WHERE dbname='xxx';");

if($db->FetchRow()) $id = $db->FetchField("id");
if($id!="") {
	$newdbname="sgta_$id";
	$query="CREATE DATABASE $newdbname TEMPLATE $copy_from;";
	$result=$db->DoQuery($query);
	if($result!=FALSE) {
		$query="UPDATE dbindex SET dbname='$newdbname',realname='$name' WHERE id='$id';";
		$db->DoQuery($query);
	}
	else {
	    echo json_encode(array("status"=>"error", "message"=>"Failed to create new database"));
	    exit();
	}
	
}
else {
    echo json_encode(array("status"=>"error", "message"=>"Failed to update dbindex!"));		   
    exit();
}
$db->CloseDb();
$db = new dbio("$newdbname");
$db->OpenDb();
$db->DoQuery("update wellinfo set created_at = now(), update_at=now()");
$db->DoQuery("update appinfo set created_at = now(), update_at=now()");
header("Location: ./show.php?seldbname=$newdbname");
exit();
?>
