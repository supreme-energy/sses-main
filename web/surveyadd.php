<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Digital Oil Tools

$seldbname=$_REQUEST['seldbname'];
$sortdir=$_REQUEST['sortdir'];
$md=$_REQUEST['md']; if($md=="") $md=0;
$inc=$_REQUEST['inc']; if($inc=="") $inc=0;
$azm=$_REQUEST['azm']; if($azm=="") $azm=0;
$ret=(isset($_REQUEST['ret']) ? $ret = $_REQUEST['ret'] : 'gva_tab3.php');
$debug = (isset($_REQUEST['debug']) ? true : true);
$json_resp = (isset($_REQUEST['json']) ? true: false );
$runcalcs  = (isset($_REQUEST['nocalc']) ? false: true);
require_once("dbio.class.php");
require_once("classes/WitsmlData.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
include("readwellinfo.inc.php");
$db->DoQuery("select count(*) as cnt from surveys where md=$md and inc=$inc and azm=$azm");
$db->FetchRow();
echo "select count(*) as cnt from suerveys where md=$md and inc=$inc and azm=$azm";
if($db->FetchField("cnt") == 0){
	$db->DoQuery("select  * from surveys where plan=0 order by md desc limit 1");
	$frow = $db->FetchRow();
	$db->DoQuery("INSERT INTO surveys (md,inc,azm) VALUES ($md,$inc,$azm)");
	$db->DoQuery("UPDATE wellinfo SET pamethod='-1';");
	$db->DoQuery("delete from projections where ptype='rot' or ptype='sld'");
	if($autorc_type=='welldata'){
		exec("./sses_gva -d $seldbname");
		exec("./sses_cc -d $seldbname");
		exec("./sses_cc -d $seldbname -p");
		exec("./sses_af -d $seldbname");
		require_once('classes/PolarisConnection.class.php');
		$witsml = new PolarisConnection($_REQUEST);
		if($frow['md']>0 && $frow['md']<$md ){
			$smd=$frow['md']+1;
		} else{
			$smd=0;
		}
		$emd=$md;
		$witsml->prepare_las_data($smd,$emd);
	}
	
	if(runcalcs){
		exec("./sses_gva -d $seldbname");
		exec("./sses_cc -d $seldbname");
		exec("./sses_cc -d $seldbname -p");
		exec("./sses_af -d $seldbname");
	}
	$db->CloseDb();
}
if(!$debug && !$json_resp){
	header("Location: {$ret}?seldbname=$seldbname&sortdir=$sortdir");
	exit();
}
?>