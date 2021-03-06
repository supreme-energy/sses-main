<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Supreme Source Energy Services, Inc.
//	All rights reserved.
//	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
$json_only = strpos($_SERVER['CONTENT_TYPE'],'application/json') !== false;
$seldbname=$_POST['seldbname'];
$ret=$_POST['ret'];
$method=$_POST['meth'];
$autoposdec = 0+$_POST['autoposdec'];
$bprjtpos = $_POST['bprjpostcl'];
$data=$_POST['data'];
$project=$_POST['project'];
$bitoffset=$_POST['bitoffset'];
$md=$_POST['md'];
$inc=$_POST['inc'];
$azm=$_POST['azm'];
$tvd=$_POST['tvd'];
$vs=$_POST['vs'];
$ca=$_POST['ca'];
$cd=$_POST['cd'];
$tpos=$_POST['tpos'];
$tot=$_POST['tot'];
$bot=$_POST['bot'];
$dip=$_POST['dip'];
$fault=$_POST['fault'];
// $fault=0;
$pmd=$_POST['pmd'];
$pinc=$_POST['pinc'];
$pazm=$_POST['pazm'];
$ptvd=$_POST['ptvd'];
$pca=$_POST['pca'];
$pcd=$_POST['pcd'];
$currid=$_POST['currid'];
$newid=$_POST['newid'];
// echo "passed currid=$currid, newid=$newid";

$dmd=$md-$pmd;
$dinc=$inc-$pinc;
$dazm=$azm-$pazm;
$dtvd=$tvd-$ptvd;
$dcd=$cd-$pcd;
$dca=$ca-$pca;

require_once("dbio.class.php");

$db=new dbio($seldbname);
$db->OpenDb();
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


exec ("./sses_af -d $seldbname");
if($json_only){
    include ("readsurveys.inc.php");
    echo json_encode($srvys_joined);
} else {
    $db->CloseDb();
    if($newid==""){
    
    ?>
    	<HTML>
    	<HEAD>
    	<LINK rel='stylesheet' type='text/css' href='projws.css'/>
    	<TITLE>Project <?echo $project?></TITLE>
    	</HEAD>
    	<BODY onload='closeupanddie()'>
    	<SCRIPT language="javascript">
    	function closeupanddie()
    	{
    		if(window.opener && !window.opener.closed) {
    			// window.opener.location.href="<?php echo $ret ?>?seldbname=<?echo $seldbname?>";
    			// window.opener.location.load();
    			window.opener.location.reload();
    		}
    		//window.close();
    	}
    	</SCRIPT>
    	</BODY>
    	</HTML>
    <?} else {
    	$currid=$newid;
    
    	include("projws.php");
    }
}