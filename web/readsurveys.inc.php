<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Supreme Source Energy Services, Inc.
//	All rights reserved.
//	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.

$svy_cnt=0;
$svy_total=0;
$svy_id=array();
$svy_plan=array();
$svy_md=array();
$svy_inc=array();
$svy_azm=array();
$svy_tvd=array();
$svy_vs=array();
$svy_ns=array();
$svy_ew=array();
$svy_ca=array();
$svy_cd=array();
$svy_dl=array();
$svy_cl=array();
$svy_tcl=array();
$svy_tot=array();
$svy_bot=array();
$svy_dip=array();
$svy_fault=array();
$svy_isghost=array();
$db2=new dbio($seldbname);
$db2->OpenDb();
$db->DoQuery("select * from addforms");
$totid =null;
$botid = null;
while($db->FetchRow()){
	
	if(trim($db->FetchField('label'))=='TOT'){
		$totid = $db->FetchField('id');
	}
	if(trim($db->FetchField('label'))=='BOT'){
		$botid = $db->FetchField('id');
	}
}
$db->DoQuery("SELECT * FROM surveys ORDER BY plan $surveysort,md $surveysort;");
$svy_total=$db->FetchNumRows(); 
for ($i=0; $i<$svy_total; $i++) {
	$db->FetchRow();
	
	$svy_id[]=$db->FetchField("id");
	$svyid=$db->FetchField("id");
	$md = $db->FetchField("md");
	$tot='NF';
	$bot='NF';
	$plan=sprintf("%d", $db->FetchField("plan"));
	if($totid){
		if($plan){
			$query = "select tot from addformsdata where md=$md and infoid=$totid;";
		}else{
			$query = "select tot from addformsdata where svyid=$svyid and infoid=$totid;";
		} 
		$db2->DoQuery($query);
		$db2->FetchRow();
		$tot =sprintf("%.2f", $db2->FetchField("tot"));
	}
	if($botid){
		if($plan){
			$query = "select tot from addformsdata where md=$md and infoid=$botid;";
		}else{
			$query = "select tot from addformsdata where svyid=$svyid and infoid=$botid;";
		}
		$db2->DoQuery($query);
		$db2->FetchRow();
		$bot =sprintf("%.2f", $db2->FetchField("tot"));
	}

	$svy_md[]=sprintf("%.2f",$md);
	$svy_inc[]=sprintf("%.2f", $db->FetchField("inc"));
	$svy_azm[]=sprintf("%.2f", $db->FetchField("azm"));
	$svy_tvd[]=$db->FetchField("tvd");
	$svy_vs[]=sprintf("%.2f", $db->FetchField("vs"));
	$svy_ns[]=sprintf("%.2f", $db->FetchField("ns"));
	$svy_ew[]=sprintf("%.2f", $db->FetchField("ew"));
	$svy_ca[]=sprintf("%.2f", $db->FetchField("ca"));
	$svy_cd[]=sprintf("%.2f", $db->FetchField("cd"));
	$svy_dl[]=sprintf("%.2f", $db->FetchField("dl"));
	$svy_cl[]=sprintf("%.2f", $db->FetchField("cl"));
	$svy_tcl[]=$db->FetchField("tot");
	$svy_tot[]=$tot;
	$svy_bot[]=$bot;
	$svy_dip[]=sprintf("%.2f", $db->FetchField("dip"));
	$svy_fault[]=sprintf("%.2f", $db->FetchField("fault"));
	$plan=sprintf("%d", $db->FetchField("plan"));
	$svy_plan[]=$plan;
	$svy_isghost[]=$db->FetchField("isghost");
	if($plan<=0) $svy_cnt++;
	$srvys_joined[]= array(
			'id' => $id,
			'md' => sprintf("%.2f",$md) ,
			'inc' =>sprintf("%.2f", $db->FetchField("inc")),
			'azm' =>sprintf("%.2f", $db->FetchField("azm")),
			'tvd' =>$db->FetchField("tvd"),
			'vs' =>sprintf("%.2f", $db->FetchField("vs")),
			'ns' =>sprintf("%.2f", $db->FetchField("ns")),
			'ew' =>sprintf("%.2f", $db->FetchField("ew")),
			'ca' =>sprintf("%.2f", $db->FetchField("ca")),
			'cd' =>sprintf("%.2f", $db->FetchField("cd")),
			'dl' =>sprintf("%.2f", $db->FetchField("dl")),
			'cl' =>sprintf("%.2f", $db->FetchField("cl")),
			'tcl'=>$db->FetchField("tot"),
			'tot'=>$tot,
			'bot'=>$bot,
			'dip'=>$db->FetchField("tot"),
			'fault'=>sprintf("%.2f", $db->FetchField("fault")),
			'plan'=> $plan,
			'ghost'=>$db->FetchField("isghost")
	);
}
$db2->CloseDb();
?>
