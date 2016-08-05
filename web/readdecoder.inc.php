<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?php
$currdate = mktime(date("H"),date("i"),date("s"),date("n"),date("j"),date("Y"));
$fileto=$currdate-300;
$pumpstatus = -1;
$pumpoffalarm = 0;
$pumponalarm = 0;
$puti="----------";
$pdti="----------";
$avpa="----------";
$avcf="----------";
$avpaqf="----------";
$avqf="----------";
$pdcd="----------";
$inc="----------";
$azm="----------";
$dip="----------";
$temp="----------";
$htot="----------";
$gtot="----------";
$gamma="----------";
$BatV="----------";
$tfdigital=$gtf=$mtf=$ppres=0;
$DipE=$MagE=$GraE=$OTemp=0;
if ($tooltype == 2 || $tooltype == 3) 
	$db->DoQuery("SELECT * FROM decodertable ORDER BY witsid ASC");
else
	$db->DoQuery("SELECT * FROM decodertable ORDER BY param ASC");
$num=$db->FetchNumRows(); 
$svystatus="none";
$tfmode="G";
$tfmodestring="Gravity";
$inct=0;
$decparam=array();
$decvalue=array();
$dectime=array();
$decwits=array();
for($i=0;$i<$num;$i++) {
	$db->FetchRow();
	$param=$db->FetchField("param");
	$witsid=$db->FetchField("witsid");
	$value=$db->FetchField("value");
	$timedate=$db->FetchField("timedate");
	if ($tooltype == 2 || $tooltype == 3) {
		$t = $param;
		$param = $witsid;
		$witsid = $t;
	}

	// xxt specific
	switch($param) {
	case "POnTi":
	case "POffTi":
		$h=sprintf("%d", $value/3600);
		$m=sprintf("%d", ($value-($h*3600))/60);
		$s=sprintf("%d", ($value-($m*60)-($h*3600)) );
		$value=sprintf("%02d%02d%02d", $h,$m,$s);
	}

	$decparam[]=$param;
	$decvalue[]=$value;
	$dectime[]=$timedate;
	$decwits[]=$witsid;

	switch($param) {
	case "POnTi":
	case "PUTi":
	case "6413":
		$puti="$value[0]$value[1]:$value[2]$value[3]:$value[4]$value[5]";
		break;
	case "POffTi":
	case "PDTi":
	case "6412":
		$pdti="$value[0]$value[1]:$value[2]$value[3]:$value[4]$value[5]";
		break;
	case "AvCF":
	case "AvQF":
	case "6410":
		$avcf=$value; break;
	case "AvPA":
	case "6411":
		$avpa=$value; break;
	case "IncT":
	case "0722":
		$inct=$value; break;
	case "SvyS":
	case "RxSR":
	case "6426":
		if($value==0) $svystatus="Steering Mode";
		else if($value==1) $svystatus="Receiving Survey...";
		else if($value==2) $svystatus="Idle";
		else $svystatus="Syncing...";
		break;
	case "Inc":
	case "0713":
		$inc=$value; break;
	case "TAzm":
	case "0715":
		$azm=$value; break;
	case "Grav":
	case "0726":
		$gtot=$value; break;
	case "MagF":
	case "0725":
		$htot=$value; break;
	case "Temp":
	case "0836":
		$temp=$value; break;
	case "DipA":
	case "0722":
		$dip=$value; break;
	case "Gama":
	case "0824":
	case "GamaR":
	case "0823":
		$gamma=sprintf("%d", $value); break;
	case "gTFA":
	case "0717":
		$gtf=$value; break;
	case "mTFA":
	case "0716":
		$mtf=$value; break;
	case "PmpP": 
	case "0121":
		$ppress=sprintf("%.1f", $value); break;
	case "DipE":
	case "0925":
		$DipE=$value; break;
	case "MagE":
	case "0926":
		$MagE=$value; break;
	case "GraE":
	case "0927":
		$GraE=$value; break;
	case "OTemp":
	case "0924":
		$OTemp=$value; break;
	case "BatV":
	case "0921":
		$BatV=$value; break;
	case "Pumps":
		if (strpos($value,"Off")!=FALSE) $value=0;
		else $value=1;
	case "PmpS":
		$pumpstatus = $value;
		$dtc=strtotime($timedate);
		if($dtc <= $fileto)
			$pumpstatus = 0;
		break;
	}
}
if($inct!=0 && strstr($inc, "--")==FALSE) {
	if($inc<=$inct) {
		$tfmode="M";
		$tfmodestring="Magnetic";
	}
	else {
		$tfmode="G";
		$tfmodestring="Gravity";
	}
}
?>
