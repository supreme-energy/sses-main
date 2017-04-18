<?php
require_once("dbio.class.php");
function RemoveOuijaCutoffProjection(){
	global $seldbname;
	$db2=new dbio($seldbname);
	$db2->OpenDb();
	$query = "delete from projections where ptype='rot';";
	$db2->DoQuery($query);
	$db2->CloseDb();
}
if("$seldbname"=="")	$seldbname=$_GET['seldbname']; 
if(isset($_REQUEST['removerot'])){
	if($_REQUEST['removerot']=='t'){
		RemoveOuijaCutoffProjection();
	}
}

$ouija_db = new dbio($seldbname);
$ouija_db->OpenDb();

$ouija_db->DoQuery("select count(*) as cnt from projections where ptype='rot'");

$ouija_db->FetchRow();

$rot_cnt = $ouija_db->FetchField("cnt");


$ouija_db->DoQuery("select count(*) as cnt from projections where ptype='sld'");
$ouija_db->FetchRow();
$sld_cnt = $ouija_db->FetchField("cnt");

if($rot_cnt > $sld_cnt){
	RemoveOuijaCutoffProjection();
}
$ouija_db->CloseDb();
?>
