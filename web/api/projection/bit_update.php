<?php
include("../api_header.php");
$body = file_get_contents('php://input');
$json_body = json_decode($body);
$method= $json_body->meth;
$autoposdec = $json_body->autoposdec;
$bprjtpos = $json_body->bprjpostcl;
$data=$json_body->data;
$project=$json_body->project;
$bitoffset=$json_body->bitoffset;
$md=$json_body->md;
$inc=$json_body->inc;
$azm=$json_body->azm;
$tvd=$json_body->tvd;
$vs=$json_body->vs;
$ca=$json_body->ca;
$cd=$json_body->cd;
$tpos=$json_body->tpos;
$tot=$json_body->tot;
$bot=$json_body->bot;
$dip=$json_body->dip;
$fault=$json_body->fault;
// $fault=0;
$pmd=$json_body->pmd;
$pinc=$json_body->pinc;
$pazm=$json_body->pazm;
$ptvd=$json_body->ptvd;
$pca=$json_body->pca;
$pcd=$json_body->pcd;
$currid=$json_body->currid;
$newid=$json_body->newid;
// echo "passed currid=$currid, newid=$newid";

$dmd=$md-$pmd;
$dinc=$inc-$pinc;
$dazm=$azm-$pazm;
$dtvd=$tvd-$ptvd;
$dcd=$cd-$pcd;
$dca=$ca-$pca;

$db->DoQuery("UPDATE wellinfo SET autoposdec=$autoposdec");
if($project!='ahead') {
	$plan = 1;
	$db->DoQuery("UPDATE wellinfo SET pbmethod=$method");
	$db->DoQuery("UPDATE wellinfo SET projdip='$dip';");
	$db->DoQuery("UPDATE wellinfo SET bitoffset='$bitoffset';");
	if($method==0) $db->DoQuery("UPDATE wellinfo SET pbdata='$dmd,0,0';");
	else if($method==1) $db->DoQuery("UPDATE wellinfo SET pbdata='$dtvd,$dcd,$dca';");
	else if($method==2) $db->DoQuery("UPDATE wellinfo SET pbdata='$dtvd,$dcd,$dca';");
	else if($method>=3 && $method<=5) $db->DoQuery("UPDATE wellinfo SET pbdata='$dmd,$dinc,$dazm';");
	else $db->DoQuery("UPDATE wellinfo SET pbdata='0,0,0';");
} else {
	if($currid!="") {
		if($method==0) $data="$dmd,0,0";
		else if($method>=3 && $method<=5) $data="$dmd,$dinc,$dazm";
		else if($method==6) $data="$tvd,$vs,$tpos";
		else if($method==7) $data="$tot,$vs,$tpos";
		else if($method==8) $data="$vs,$tpos,$dip,$fault";
		else $data="0,0,0";
		/*
		$db->DoQuery("UPDATE projections SET method='$method' WHERE id=$currid;");
		$db->DoQuery("UPDATE projections SET data='$data' WHERE id=$currid;");
		$db->DoQuery("UPDATE projections SET md='$md' WHERE id=$currid;");
		$db->DoQuery("UPDATE projections SET inc='$inc' WHERE id=$currid;");
		$db->DoQuery("UPDATE projections SET azm='$azm' WHERE id=$currid;");
		$db->DoQuery("UPDATE projections SET dip='$dip' WHERE id=$currid;");
		$db->DoQuery("UPDATE projections SET fault='$fault' WHERE id=$currid;");
		$db->DoQuery("UPDATE projections SET tot='$tot' WHERE id=$currid;");
		$db->DoQuery("UPDATE projections SET bot='$bot' WHERE id=$currid;");
		$db->DoQuery("UPDATE projections SET tvd='$tvd' WHERE id=$currid;");
		$db->DoQuery("UPDATE projections SET vs='$vs' WHERE id=$currid;");
		*/
		$db->DoQuery("UPDATE projections SET method='$method',data='$data',
md='$md',inc='$inc',azm='$azm',dip='$dip',fault='$fault',tot='$tot',bot='$bot',tvd='$tvd',vs='$vs' WHERE id=$currid;");
	} else {
		if($method==0) $data="$dmd,0,0";
		else if($method>=3 && $method<=5) $data="$dmd,$dinc,$dazm";
		else if($method==6) $data="$tvd,$vs,$tpos";
		else if($method==7) $data="$tot,$vs,$tpos";
		else if($method==8) $data="$vs,$tpos,$dip,$fault";
		else $data="0,0,0";
		$db->DoQuery("INSERT INTO projections (method, data, md, inc, azm, dip, fault, tvd, vs, tot, bot) 
		VALUES ('$method','$data','$md','$inc','$azm','$dip','$fault','$tvd','$vs','$tot','$bot');");
		$db->DoQuery("SELECT id FROM projections WHERE md=$md;");
		if($db->FetchRow()) $newid=$db->FetchField("id");
	}
}


exec("../../sses_gva -d $seldbname");
exec("../../sses_cc -d $seldbname");
exec("../../sses_cc -d $seldbname -p");
exec("../../sses_af -d $seldbname");
include ("../../readsurveys.inc.php");
echo json_encode($srvys_joined);
?>