<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?php
$hookload="---------";
$dbgamma="---------";
$rop="-----------";
$wob="-----------";
$flowin="-----------";
$flowout="-----------";
$pumppressure="-----------";
$gas="-----------";
$bitdepth = $holedepth = $blockheight = "-----------";
$pump1 = $pump2 = $pump3 = "-----------";
$rottorque = $rotspeed = "-----------";

$db->DoQuery("SELECT * FROM tfhistogram ORDER BY id DESC LIMIT 4 OFFSET 0");
$num=$db->FetchNumRows($result); 
$tfarray=array();
for($i=0;$i<$num;$i++) {
	$db->FetchRow();
	$tfarray[] = $db->FetchField("value");
	if($i==0) {
		$timedate=$db->FetchField("timedate");
		$currdate = mktime(date("H"),date("i"),date("s"),date("n"),date("j"),date("Y"));
		$dtc=strtotime($timedate);
		$lasttf=$currdate-$dtc;
		$ltfh=sprintf("%d", $lasttf/3600);
		$ltfm=sprintf("%d", ($lasttf-($ltfh*3600))/60);
		$ltfs=sprintf("%d", ($lasttf-($ltfm*60)-($ltfh*3600)) );
		$lasttf=sprintf("%02d:%02d:%02d", $ltfh,$ltfm,$ltfs);
	}
}

$currdate = mktime(date("H"),date("i"),date("s"),date("n"),date("j"),date("Y"));
$expiration=$currdate-300;
$cdid=array();
$cddesc=array();
$cdwitsid=array();
$cdvalue=array();
$cdlasttime=array();
$cdtimedate=array();
$cdalarmhi=array();
$cdalarmlo=array();
$cddp=array();

$db->DoQuery("SELECT * FROM idtable ORDER BY witsid");
$num=$db->FetchNumRows(); 
for($i=0;$i<$num;$i++) {
	$db->FetchRow();
	$witsid=$db->FetchField("witsid");
	$value=$db->FetchField("lastvalue");
	$lasttime=$db->FetchField("lasttime");
	$timedate=$db->FetchField("timedate");

	$cdid[]=$db->FetchField("id");
	$cddesc[]=$db->FetchField("description");
	$cdalarmhi[]=$db->FetchField("alarmhi");
	$cdalarmlo[]=$db->FetchField("alarmlo");
	$cddp[]=$db->FetchField("dp");

	$cdwitsid[]=$witsid;
	$cdvalue[]=$value;
	$cdlasttime[]=$lasttime;
	$cdtimedate[]=$timedate;

	$dtc=strtotime($timedate);
	if(strcasecmp($witsid, "0108") == 0) {
		$bitdepth=sprintf("%.2f", $value);
		$surveydepth=sprintf("%.2f", $bitdepth-$surveyoffset);
	}
	if(strcasecmp($witsid, "0110") == 0)
		$holedepth=sprintf("%.2f", $value);
	if($dtc > $expiration) {
		if(strcasecmp($witsid, "0112") == 0) 
			$blockheight=sprintf("%.2f", $value);
		if(strcasecmp($witsid, "0113") == 0)
			$rop=sprintf("%.2f", $value);
		if(strcasecmp($witsid, "0115") == 0)
			$hookload=sprintf("%.1f", $value);
		if(strcasecmp($witsid, "0117") == 0)
			$wob=sprintf("%.1f",$value);
		if(strcasecmp($witsid, "0119") == 0)
			$rottorque=sprintf("%.1f", $value);
		if(strcasecmp($witsid, "0120") == 0)
			$rotspeed=sprintf("%.1f", $value);
		if(strcasecmp($witsid, "0121") == 0)
			$pumppressure=sprintf("%.1f", $value);
		if(strcasecmp($witsid, "0123") == 0)
			$pump1=sprintf("%.0f", $value);
		if(strcasecmp($witsid, "0124") == 0)
			$pump2=sprintf("%.0f", $value);
		if(strcasecmp($witsid, "0125") == 0)
			$pump3=sprintf("%.0f", $value);
		if(strcasecmp($witsid, "0128") == 0)
			$flowout=sprintf("%.1f", $value);
		if(strcasecmp($witsid, "0130") == 0)
			$flowin=sprintf("%.1f", $value);
		if(strcasecmp($witsid, "0140") == 0)
			$gas=sprintf("%.1f", $value);
		if(strcasecmp($witsid, "0823") == 0)
			$dbgamma=sprintf("%.0f", $value);
		if(strcasecmp($witsid, "0824") == 0)
			$dbgamma=sprintf("%.0f", $value);
	}
}
?>
