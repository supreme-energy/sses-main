<?php
require_once("dbio.class.php");
$ret=$_POST['ret'];
$sgtadmod =(int)$_POST['sgtadmod'];
$seldbname=$_POST['seldbname'];
$ptype=$_POST['ptype']; if($ptype=="")	$ptype="LAT";
$mtype=$_POST['mtype']; if($mtype=="")	$mtype="INC";
$inputa=$_POST['inputa'];
$inputb=$_POST['inputb'];
$mintvd=$_POST['mintvd'];
$maxtvd=$_POST['maxtvd'];
$minvs=$_POST['minvs'];
$maxvs=$_POST['maxvs'];
if($inputa=="")	$inputa=60.0;
if($inputb=="")	$inputb=300;
if($mintvd=="")	$mintvd=0;
if($maxtvd=="")	$maxtvd=99999.0;
if($minvs=="")	$minvs=0;
if($minvs=="")	$maxvs=99999.0;

$db=new dbio($seldbname);
$db->OpenDb();
include "readappinfo.inc.php";
if($sgtadmod && $sgtadmod!=$dmod){
	$db->doQuery("UPDATE appinfo set dmod='$sgtadmod';");
}
if($lastptype!=$ptype || $lastmtype!=$mtype) {
	$db->DoQuery("UPDATE appinfo SET lastptype='$ptype';");
	
	$db->DoQuery("UPDATE appinfo SET lastmtype='$mtype';");
	$db->DoQuery("SELECT * FROM splotlist WHERE ptype='$ptype' AND mtype='$mtype';");
	if($ptype=="VS") { $inputb/=100; }
	if($db->FetchRow()) {
		$inputa=$db->FetchField("inputa");
		$inputb=$db->FetchField("inputb");
		$mintvd=$db->FetchField("mintvd");
		$maxtvd=$db->FetchField("maxtvd");
		$minvs=$db->FetchField("minvs");
		$maxvs=$db->FetchField("maxvs");
	} else {
		$db->DoQuery("INSERT INTO splotlist (ptype,mtype,inputa,inputb,mintvd,maxtvd,minvs,maxvs) VALUES ('$ptype','$mtype','$inputa','$inputb','$mintvd','$maxtvd','$minvs','$maxvs');");
	}
}
else {
	$db->DoQuery("SELECT * FROM splotlist WHERE ptype='$ptype' AND mtype='$mtype';");
	if(!isset($inputa) || $inputa=="") $inputa=60.0;
	if(!isset($inputb) || $inputb=="") $inputb=300;
	if(!isset($mintvd) || $mintvd=="") $mintvd=99999.0;
	if(!isset($maxtvd) || $maxtvd=="") $maxtvd=-99999.0;
	if(!isset($minvs) || $minvs=="") $minvs=99999.0;
	if(!isset($maxvs) || $maxvs=="") $maxvs=-99999.0;
	if($ptype=="VS") { $inputb/=100; }
	if($db->FetchRow()) {
		$id=$db->FetchField("id");
		$db->DoQuery("UPDATE splotlist SET inputa='$inputa' WHERE id=$id;");
		$db->DoQuery("UPDATE splotlist SET inputb='$inputb' WHERE id=$id;");
		$db->DoQuery("UPDATE splotlist SET mintvd='$mintvd' WHERE id=$id;");
		$db->DoQuery("UPDATE splotlist SET maxtvd='$maxtvd' WHERE id=$id;");
		$db->DoQuery("UPDATE splotlist SET minvs='$minvs' WHERE id=$id;");
		$db->DoQuery("UPDATE splotlist SET maxvs='$maxvs' WHERE id=$id;");
	}
	else
		$db->DoQuery("INSERT INTO splotlist (ptype,mtype,inputa,inputb,mintvd,maxtvd,minvs,maxvs) VALUES ('$ptype','$mtype','$inputa','$inputb','$mintvd','$maxtvd','$minvs','$maxvs');");
}


$db->CloseDb();
if(strlen($ret)) include "$ret";
else include "splotconfig.php";
?>
