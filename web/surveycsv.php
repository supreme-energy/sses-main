<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?php
require_once("dbio.class.php");
$seldbname=$_GET['seldbname'];
$db=new dbio($seldbname);
$db->OpenDb();
$surveysort="DESC";
include 'readwellinfo.inc.php';
include 'readsurveys.inc.php';
$db->CloseDb();
shell_exec('rm ./tmp/surveys.csv');
$file=fopen("./tmp/surveys.csv", "w");

if($file)
{
	$headers ="md,inc,azm,tvd,vs,ns,ew,ca,cd,dl,cl,northing,easting,tcl,pos-tcl,tot,bot,dip,fault";
	$addform_col_id_ord = array();
	
	$db3=new dbio($seldbname);
	$db3->OpenDb();
	$db2=new dbio($seldbname);
	$db2->OpenDb();
	$db2->DoQuery("select * from addforms;");
	$totid =null;
	$botid = null;
	while($db2->FetchRow()){
		
		if(trim($db2->FetchField('label'))=='TOT'){
			$totid = $db2->FetchField('id');
		}
		if(trim($db2->FetchField('label'))=='BOT'){
			$botid = $db2->FetchField('id');
		}
		if(trim($db2->FetchField('label'))!='BOT' && trim($db2->FetchField('label'))!='TOT'){
			$headers.=",".$db2->FetchField('label');
			array_push($addform_col_id_ord,$db2->FetchField('id'));
			
		}
	}
	fprintf($file, "$headers\n");
	$db2->DoQuery("SELECT * FROM projections ORDER BY md $surveysort;");
	$numprojs=$db2->FetchNumRows(); 
	for ($i=0; $i<$numprojs; $i++) {
		$db2->FetchRow();
		$id=$db2->FetchField("id");
		$md =  $db2->FetchField("md");
		$tot='NF';
		$bot='NF';
		if($totid){
			$query = "select tot from addformsdata where projid=$id and infoid=$totid;"; 
			$db3->DoQuery($query);
			$db3->FetchRow();
			$tot =sprintf("%.2f", $db3->FetchField("tot"));
		}
		if($botid){
			$query = "select tot from addformsdata where projid=$id and infoid=$botid;";
			$db3->DoQuery($query);
			$db3->FetchRow();
			$bot =sprintf("%.2f", $db3->FetchField("tot"));
		}
		
		$meth=sprintf("%.2f", $db2->FetchField("method"));
		$md=sprintf("%.2f", $db2->FetchField("md"));
		$inc=sprintf("%.2f", $db2->FetchField("inc"));
		$azm=sprintf("%.2f", $db2->FetchField("azm"));
		$tvd=sprintf("%.2f", $db2->FetchField("tvd"));
		$ns=sprintf("%.2f", $db2->FetchField("ns"));
		$ew=sprintf("%.2f", $db2->FetchField("ew"));
		$vs=sprintf("%.2f", $db2->FetchField("vs"));
		$ca=sprintf("%.2f", $db2->FetchField("ca"));
		$cd=sprintf("%.2f", $db2->FetchField("cd"));
		$dl=sprintf("%.2f", $db2->FetchField("dl"));
		$cl=sprintf("%.2f", $db2->FetchField("cl"));
		$tcl = sprintf("%.2f", $db2->FetchField("tot"));
		$dip=sprintf("%.2f", $db2->FetchField("dip"));
		$fault=sprintf("%.2f", $db2->FetchField("fault"));
		$hide=sprintf("%d", $db2->FetchField("hide"));
		$tf = $db2->FetchField("tf");
		if(!$tf){
			$tf='-';
		}
		$pnum=$i+1;
		if($surveysort=="DESC") 	$pnum = $numprojs-$i;
		
		$northing=sprintf("%.0f", $survey_northing+$ns);
		$easting=sprintf("%.0f", $survey_easting+$ew);
		
		
		$disppa = strtoupper($db2->FetchField("ptype")).$pnum;
		$lastmd=$md;
		$postcl=$tcl-$tvd;
		$line = "$md,$inc,$azm,$tvd,$vs,$ns,$ew,$ca,$cd,$dl,$cl,$northing,$easting,$tcl,$postcl,$tot,$bot,$dip,$fault";
		foreach($addform_col_id_ord as $addform_id){
			$query = "select tot from addformsdata where projid=$id and infoid=$addform_id;";
			$db3->DoQuery($query);
			$db3->FetchRow();
			$val =sprintf("%.2f", $db3->FetchField("tot"));
			$line.=",".$val;
		}
		fprintf($file,"%s\n",$line);
	}
	$db2->CloseDb();
	
	for($i=0; $i < $svy_total; $i++) {
		$mdraw = $svy_md[$i];
		$id = $svy_id[$i];
		$md=sprintf("%.2f", $svy_md[$i]);
		$inc=sprintf("%.2f", $svy_inc[$i]);
		$azm=sprintf("%.2f", $svy_azm[$i]);
		$tvd=$svy_tvd[$i];
		$ns=sprintf("%.2f", $svy_ns[$i]);
		$ew=sprintf("%.2f", $svy_ew[$i]);
		$vs=sprintf("%.2f", $svy_vs[$i]);
		$ca=sprintf("%.2f", $svy_ca[$i]);
		$cd=sprintf("%.2f", $svy_cd[$i]);
		$dl=sprintf("%.2f", $svy_dl[$i]);
		$cl=sprintf("%.2f", $svy_cl[$i]);
		$northing= sprintf("%.0f", $survey_northing+$ns);;
		$easting = sprintf("%.0f", $survey_easting+$ew);;
		$tcl = $svy_tcl[$i];
		$tot=$svy_tot[$i];
		$bot=$svy_bot[$i];
		$dip=sprintf("%.2f", $svy_dip[$i]);
		$fault=sprintf("%.2f", $svy_fault[$i]);
		$plan=sprintf("%d", $svy_plan[$i]);
		$postcl =  $tcl-$tvd;
		$line="$md,$inc,$azm,$tvd,$vs,$ns,$ew,$ca,$cd,$dl,$cl,$northing,$easting,$tcl,$postcl,$tot,$bot,$dip,$fault";
		foreach($addform_col_id_ord as $addform_id){
			
			if($svy_plan[$i]){
				$query = "select tot from addformsdata where md=$mdraw and infoid=$addform_id;";
			}else{
				$query = "select tot from addformsdata where svyid=$id and infoid=$addform_id;";
			}
			$db3->DoQuery($query);
			$db3->FetchRow();
			$val =sprintf("%.2f", $db3->FetchField("tot"));
			$line.=",".$val;
		}
		fprintf($file, "%s\n", $line);
	} 
	$db3->CloseDb();
	fclose($file);
}
$db->CloseDb();

$file="./tmp/surveys.csv";
header("Content-type: application/force-download");
header("Content-Transfer-Encoding: Binary");
header("Content-length: ".filesize($file));
header("Content-disposition: attachment; filename=\"".basename($file)."\"");
readfile("$file");
?>
