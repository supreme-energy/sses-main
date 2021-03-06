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
$tot_thickness=0;
$bot_thickness=0;
$totid =null;
$botid = null;
while($db->FetchRow()){
	
	if(trim($db->FetchField('label'))=='TOT'){
		$tot_thickness = $db->FetchField('thickness');
	    $totid = $db->FetchField('id');
	}
	if(trim($db->FetchField('label'))=='BOT'){
	    $bot_thickness = $db->FetchField('thickness');
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
	$tot = $db->FetchField("tot") + $tot_thickness;
	$bot = $db->FetchField("tot") + $bot_thickness;
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
	$svy_tot[]=sprintf("%.2f", $tot);
	$svy_bot[]=sprintf("%.2f",$bot);
	$svy_dip[]=sprintf("%.2f", $db->FetchField("dip"));
	$svy_fault[]=sprintf("%.2f", $db->FetchField("fault"));
	$plan=sprintf("%d", $db->FetchField("plan"));
	$svy_plan[]=$plan;
	$svy_isghost[]=$db->FetchField("isghost");
	if($plan<=0) $svy_cnt++;
	$srvys_joined[]= array(
			'id' => $svyid,
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
			'dip'=>sprintf("%.2f", $db->FetchField("dip")),
			'fault'=>sprintf("%.2f", $db->FetchField("fault")),
			'plan'=> $plan,
			'ghost'=>$db->FetchField("isghost"),
			'unixtime_src'=> $db->FetchField("srcts"),
			'created_at' => $db->FetchField("created_at") 
	);
}
$db2->CloseDb();
?>
