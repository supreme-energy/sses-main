<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
$db->DoQuery("SELECT * FROM appinfo LIMIT 1;");
$gr_import_mnemonic="GR";
if ($db->FetchRow()) {
	$id=$db->FetchField("id");
	$scaleright=$db->FetchField("scaleright");
	$zoom=$db->FetchField("zoom");
	$plotscale=$db->FetchField("scale");
	$plotbias=$db->FetchField("bias");
	$viewrotds=$db->FetchField("viewrotds");
	$viewallds=$db->FetchField("viewallds");
	$surveysort=$db->FetchField("surveysort");
	$lastdataset=$db->FetchField("dataset");
	$lasttablename=$db->FetchField("tablename");
	$lastptype=$db->FetchField("lastptype");
	$lastmtype=$db->FetchField("lastmtype");
	$sgtastart=$db->FetchField("sgtastart");
	$sgtaend=$db->FetchField("sgtaend");
	$sgtacutin=$db->FetchField("sgtacutin");
	$sgtacutoff=$db->FetchField("sgtacutoff");
	$uselogscale=$db->FetchField("uselogscale");
	$showxy=$db->FetchField("showxy");
	$viewdspcnt=$db->FetchField("viewdspcnt");
	$dataavg=$db->FetchField("dataavg");
	$dscache_dip=$db->FetchField("dscache_dip");
	$dscache_bias=$db->FetchField("dscache_bias");
	$dscache_scale=$db->FetchField("dscache_scale");
	$dscache_freeze=$db->FetchField("dscache_freeze");
	$dscache_md=$db->FetchField("dscache_md");
	$dscache_plotstart=$db->FetchField("dscache_plotstart");
	$dscache_plotend=$db->FetchField("dscache_plotend");
	$dscache_fault=$db->FetchField("dscache_fault");
	$dsholdfault  =$db->FetchField("dsholdfault");
	$dmod = $db->FetchField("dmod");
	$sgta_off = $db->FetchField("sgta_off");
	$labeling_start = $db->FetchField("labeling_start");
	$label_every = $db->FetchField("label_every");
	$label_dmd = $db->FetchField("label_dmd");
	$label_dvs = $db->FetchField("label_dvs");
	$label_orient = $db->FetchField("label_orient");
	$label_dreport = $db->FetchField("label_dreport");
	$label_dwebplt = $db->FetchField("label_dwebplot");
	$gr_import_mnemonic = $db->FetchField("auto_gr_mnemonic");
	$import_alarm_enabled = $db->FetchField("import_alarm_enabled");
	$import_alarm = $db->FetchField("import_alarm");
	$email_attach_las = $db->FetchField("email_attach_las");
	$email_attach_r1 = $db->FetchField("email_attach_r1");
	$email_attach_r2 = $db->FetchField("email_attach_r2");
}

if(isset($sortdir) && strlen($sortdir)>0) {
	$db->DoQuery("UPDATE appinfo SET surveysort='$sortdir';");
	$surveysort=$sortdir;
}
if(strlen($surveysort)<=0) $surveysort="DESC";
if($surveysort=="DESC") $revsortdir="ASC";
else $revsortdir="DESC";

if(isset($noshowxy) && strlen($noshowxy)>0) {
	$db->DoQuery("UPDATE appinfo SET showxy='$noshowxy';");
	$showxy=$noshowxy;
}
if(isset($noshowxy) && "$showxy"=="1")	$noshowxy=0;
else	$noshowxy=1;

$appinfo_joined = array(
		"scaleright" => $scaleright,
		"zoom" => $zoom,
		"scale" => $plotscale,
		"bias" => $plotbias,
		"viewrotds" => $viewrotds,
		"viewallds" => $viewallds,
		"surveysort" => $surveysort,
		"dataset" => $lastdataset,
		"tablename" => $lasttablename,
		"lastptype" => $lastptype,
		"lastmtype" => $lastmtype,
		"sgtastart" => $sgtastart,	
		"sgtaend" => $sgtaend,
		"sgtacutin" => $sgtacutin,
		"sgtacutoff" => $sgtacutoff,
		"uselogscale" => $uselogscale,
		"showxy"      => $showxy,
		"viewdspcnt"  => $viewdspcnt,
		"dataavg"     => $dataavg,
		"dscache_dip" => $dscache_dip,
		"dscache_bias"=> $dscache_bias,
		"dscache_scale" => $dscache_scale,
		"dscache_freeze"=> $dscache_freeze,
		"dscache_md"    => $dscache_md,
		"dscache_plotstart" => $dscache_plotstart,
		"dscache_plotend" => $dscache_plotend,
		"dscache_fault"   => $dscache_fault,
		"dsholdfault"     => $dsholdfault,
		"dmod"            => $dmod,
		"sgta_off"        => $sgta_off,
		"labeling_start"  => $labeling_start,
		"label_every"     => $label_every,
		"label_dmd"       => $label_dmd,
		"label_dvs"       => $label_dvs,
		"label_orient"    => $label_orient,
		"label_dreport"   => $label_dreport,
		"label_dwebplot"  => $label_dwebplt,
		"auto_gr_mnemonic"=> $gr_import_mnemonic,
		"import_alarm_enabled" => $import_alarm_enabled,
		"import_alarm"         => $import_alarm,
		"email_attach_las" => $email_attach_las,
		"email_attach_r1"  => $email_attach_r1,
		"email_attach_r2"  => $email_attach_r2 );
?>
