<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Supreme Source Energy Services, Inc.
//	All rights reserved.
//	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once("dbio.class.php");
require_once("classes/Survey.class.php");

$survey_loader = new Survey($_REQUEST);
$surveys=$survey_loader->get_surveys('rows');
$bitproj = $survey_loader->get_bitProjection($surveys);
$projs   = $survey_loader->get_projs('rows');
$seldbname=$_REQUEST['seldbname'];
$ret=$_REQUEST['ret'];

function FetchSurveys()
{
	global $seldbname, $project, $totalsvys, $svynum, $pbmethod, $svysel, $currid;
	global $svymd, $svyinc, $svyazm, $svytvd, $svyvs, $svyns, $svyew, $svyca, $svymeth, $svydata,$svytf;
	global $svytot, $svybot, $svycd, $svydl, $svycl, $svycnt, $svyplan, $svytype, $svyid, $svytpos, $lasttot;
	global $svyftot,$prevbot,$prevtot;
	global $svydip, $svyfault, $projdip;
	$db3 = new dbio($seldbname);
	$db3->OpenDb();
	$db2=new dbio($seldbname);
	$db2->OpenDb();
	$db2->DoQuery("SELECT * FROM surveys ORDER BY md ASC;");
	$totalsvys=$db2->FetchNumRows(); 
	$db3->DoQuery("select * from addforms;");
	$totid =null;
	$botid = null;
	while($db3->FetchRow()){
		
		if(trim($db3->FetchField('label'))=='TOT'){
			$totid = $db3->FetchField('id');
		}
		if(trim($db3->FetchField('label'))=='BOT'){
			$botid = $db3->FetchField('id');
		}
	}
	for ($i=0; $i<$totalsvys; $i++) {
		$db2->FetchRow();
		if($i<$totalsvys-3)	continue;
		$plan=sprintf("%d", $db2->FetchField("plan"));
		$md = $db2->FetchField("md");
		$id= $db2->FetchField("id");
		
		if($totid){
			if($plan){
				$query = "select tot from addformsdata where md=$md and infoid=$totid;";
			}else{
				$query = "select tot from addformsdata where svyid=$id and infoid=$totid;";
			} 
			$db3->DoQuery($query);
			$db3->FetchRow();
			$tot =sprintf("%.2f", $db2->FetchField("tot"));
		}
		if($botid){
			if($plan){
				$query = "select tot from addformsdata where md=$md and infoid=$botid;";
			}else{
				$query = "select tot from addformsdata where svyid=$id and infoid=$botid;";
			}
			$db3->DoQuery($query);
			$db3->FetchRow();
			$bot =sprintf("%.2f", $db2->FetchField("tot"));
		}

		 // fetch the last 2 + BPrj
		$svyid[]=$db2->FetchField("id");
		$md=sprintf("%.02f", $db2->FetchField("md"));
		$svyinc[]=sprintf("%.02f", $db2->FetchField("inc"));
		$svyazm[]=sprintf("%.02f", $db2->FetchField("azm"));
		$svyns[]=sprintf("%.02f", $db2->FetchField("ns"));
		$svyew[]=sprintf("%.02f", $db2->FetchField("ew"));
		$svyvs[]=sprintf("%.02f", $db2->FetchField("vs"));
		$svyca[]=sprintf("%.02f", $db2->FetchField("ca"));
		$svycd[]=sprintf("%.02f", $db2->FetchField("cd"));
		$svydl[]=sprintf("%.02f", $db2->FetchField("dl"));
		$svycl[]=sprintf("%.02f", $db2->FetchField("cl"));
		$svyfault[]=sprintf("%.02f", $db2->FetchField("fault"));
		$svyhide[]=sprintf("%d", $db2->FetchField("hide"));		
		$tvd=sprintf("%.02f", $db2->FetchField("tvd"));
		
		$svytpos[]=sprintf("%.02f", $tot-$tvd);
		$svymd[]=$md;
		$svytvd[]=$tvd;
		$svytot[]=sprintf("%d", $db2->FetchField("tot"));
		$svyftot[]=$tot;
		$svybot[]=$bot;
		$svytf[]='-';
		if($plan<=0) {
			$svydip[]=sprintf("%.02f", $db2->FetchField("dip"));
			$svytype[]="svy";
			$svynum[]=$i;
			$svymeth[]="-1";
		}
		else {
			$svydip[]=sprintf("%.2f", $projdip);
			$svytype[]="proj";
			$svynum[]="BPrj";
			$svymeth[]=$pbmethod;
			if($project=="bit")	$svysel=$svycnt;
		}
		$svyplan[]=$plan;
		$svydata[]="0,0,0";
		$svycnt++;
		$lasttot=$tot;
		$prevtot=$tot;
		$prevbot=$bot;
	}
	$db2->CloseDb();
}

function FetchProjections() {
	global $seldbname, $svycnt;
	global $svymd, $svyinc, $svyazm, $svytvd, $svyvs, $svyns, $svyew, $svyca, $svymeth, $svydata, $svytpos,$svytf;
	global $svytot, $svybot, $svycd, $svydl, $svycl, $svyid, $svytype, $svyplan, $svynum, $lasttot,$svyftot;
	global $svydip, $svyfault,$prevbot,$prevtot;
	$db3 = new dbio($seldbname);
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
	}
	$db2->DoQuery("SELECT * FROM projections ORDER BY md ASC;");
	$num=$db2->FetchNumRows();
	for ($i=0; $i<$num; $i++) {
		$db2->FetchRow();
		$id = $db2->FetchField('id');
		if($id){
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
		}}else{
			$tot=$prevtot;
			$bot=$prevbot;
		}
		$ptype = strtoupper($db2->FetchField("ptype"));
		$svyid[]=$db2->FetchField("id");
		$svymd[]=sprintf("%.02f", $db2->FetchField("md"));
		$svyinc[]=sprintf("%.02f", $db2->FetchField("inc"));
		$svyazm[]=sprintf("%.02f", $db2->FetchField("azm"));
		$svyns[]=sprintf("%.02f", $db2->FetchField("ns"));
		$svyew[]=sprintf("%.02f", $db2->FetchField("ew"));
		$svyca[]=sprintf("%.02f", $db2->FetchField("ca"));
		$svycd[]=sprintf("%.02f", $db2->FetchField("cd"));
		$svydl[]=sprintf("%.02f", $db2->FetchField("dl"));
		$svycl[]=sprintf("%.02f", $db2->FetchField("cl"));
		
		$tf = $db2->FetchField("tf");
		if($tf){
			$svytf[]=$tf;	
		} else {
			$svytf[]='-';
		}		
		$dip=sprintf("%.02f", $db2->FetchField("dip"));
		$fault=sprintf("%.02f", $db2->FetchField("fault"));
		$svyhide[]=sprintf("%d", $db2->FetchField("hide"));
		if($ptype=='ROT'){
			$method=$svymeth[]=3;
		}else{
			$method=$svymeth[]=sprintf("%d", $db2->FetchField("method"));
		}
		$data=sprintf("%s", $db2->FetchField("data"));
		$tvd=sprintf("%.02f", $db2->FetchField("tvd"));
		$vs=sprintf("%.02f", $db2->FetchField("vs"));
		$tpos=sprintf("%.02f", $tot-$tvd);
		// set inputs if required
		if($method==6 || $method==7 || $method==8) {
			$line=trim($data);
			$line=preg_replace( '/\s+/', ',', $line );
			$data=explode(",", $line);
			$total=$data[0]+$data[1]+$data[2];
			if($total>.01 || $total<-.01) {
				if($method==8) {
					if($data[0]!="")	$vs=$data[0];
					if($data[1]!="")	$tpos=$data[1];
					if($data[2]!="")	$dip=$data[2];
					if($data[3]!="")	$fault=$data[3];
				}
				else if($method==6) {
					$tvd=$data[0];
					$vs=$data[1];
					$tpos=$tot-$tvd;
				} else {
					$tot=$data[0];
					$vs=$data[1];
					$tpos=$data[2];
				}
			}
		}
		
		$svydata[]=$data;
		$svytvd[]=$tvd;
		$svyvs[]=$vs;
		$svytot[]=sprintf("%.02f", $db2->FetchField("tot"));
		$svyftot[]=$tot;
		$svybot[]=$bot;
		$svytpos[]=$tpos;
		$svydip[]=$dip;
		$svyfault[]=$fault;
		$svytype[]="proj";
		$svyplan[]=2;
		$lasttot=$tot;

		$n=$i+1;
		$svynum[]=$ptype."$n";
		$svycnt++;
	}
	$db2->CloseDb();
}

function AddOuijaCutoffProjection($bitproj) {
	global $seldbname;
	$db2=new dbio($seldbname);
	$db2->OpenDb();
	$query = "select * from surveys order by md desc limit 2";
	$db2->DoQuery($query);
	$bitprojection = $db2->FetchRow();
	$lastsurvey    = $db2->FetchRow();
	$query = "select * from projections order by md asc limit 1";
	$db2->DoQuery($query);
	$pa1  = $db2->FetchRow();
	$cutoffmd = $bitprojection['md']+30;
	$cutoffinc = ((($pa1['inc']-$bitprojection['inc'])/($pa1['md']-$bitprojection['md']))*30)+$bitprojection['inc'];
	$cutoffazm = ((($pa1['azm']-$bitprojection['azm'])/($pa1['md']-$bitprojection['md']))*30)+$bitprojection['azm'];
	$dip = $bitprojection['dip'];
	$query ="insert into projections (md,inc,azm,ptype,dip,method) values ($cutoffmd,$cutoffinc,$cutoffazm,'rot',$dip,3);";
	$db2->DoQuery($query);
	exec("./sses_cc -d $seldbname");	
	exec("./sses_cc -d $seldbname -p");
	exec("./sses_gva -d $seldbname");
	exec ("./sses_af -d $seldbname");
	$db2->CloseDb();
}

function AddProjection() {
	global $seldbname, $svycnt, $svysel;
	global $svymd, $svyinc, $svyazm, $svytvd, $svyvs, $svyns, $svyew, $svyca, $svymeth, $svydata, $svytpos,$svytf;
	global $svytot, $svybot, $svycd, $svydl, $svycl, $svyid, $svytype, $svyplan, $svynum, $lasttot,$svyftot;
	global $svydip, $svyfault;
	$i=$svycnt-1;
	$md=$svymd[$i];
	$inc=$svyinc[$i];
	$azm=$svyazm[$i];
	$tvd=$svytvd[$i];
	$ns=$svyns[$i];
	$ew=$svyew[$i];
	$vs=$svyvs[$i];
	$ca=$svyca[$i];
	$cd=$svycd[$i];
	$dl=$svydl[$i];
	$cl=$svycl[$i];
	$ftot=$svyftot[$i];
	$tot=$svytot[$i];
	$bot=$svybot[$i];
	$dip=$svydip[$i];
	$fault=$svyfault[$i];
	$hide= (isset($svyhide[$i]) ? $svyhide[$i] : '');
	$plan=$svyplan[$i];
	$svyid[]="";
	$svytf[]="-";
	$svymd[]=$md;
	$svyinc[]=$inc;
	$svyazm[]=$azm;
	$svytvd[]=$tvd;
	$svyns[]=$ns;
	$svyew[]=$ew;
	$svyvs[]=$vs;
	$svyca[]=$ca;
	$svycd[]=$cd;
	$svydl[]=$dl;
	$svycl[]=$cl;
	$lasttot=$svytot[]=$tot;
	$svyftot[]=$ftot;
	$svybot[]=$bot;
	$svydip[]=$dip;
	$svyfault[]=$fault;
	$svyhide[]=$hide;
	$svymeth[]=3;
	$svydata[]= (isset($data) ? $data : '');
	$svytype[]="proj";
	$svyplan[]=2;
	$svynum[]="SLDn";
	$svytpos[]=sprintf("%.02f", $tot-$tvd);
	$svysel=$svycnt;
	$svycnt++;
	
}

function DisplaySurveys() {
	global $seldbname, $project, $svyplan, $svytype, $svycnt;
	global $svymd, $svyinc, $svyazm, $svytvd, $svyvs, $svyns, $svyew, $svyca, $svymeth, $svydata,$svytf;
	global $svytot, $svybot, $svycd, $svydl, $svycl, $svyid, $svynum, $svytpos,$svyftot;
	global $svydip, $svyfault;
	for ($i=0; $i<$svycnt; $i++) {
		if($svyplan[$i]>0) { $cls="proj"; } else { $cls="svy"; }
		$snum=$svynum[$i];
		$clickstr="";
		echo "<TR id='row_$snum'><TD class='$cls'>";
		if($svyplan[$i]>0 && $snum!="BPrj") {
			echo "<INPUT CLASS='edit' TYPE='submit' id='btnid$i' name='btnid$i' VALUE='$snum' \
			onclick='projws(this.form, {$svyid[$i]})' \
			onmouseover='showline($i)' \
			onmouseout='noshowline()' disabled>";
			$clickstr=" onmouseover='showline($i)' onmouseout='noshowline()'";
		} else {
			echo "<INPUT class='$cls' id='svytype$i' type='hidden' value='{$svytype[$i]}'>$snum";
		}
		
		echo "</TD>";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svymd$i' type='text' size='6' value='{$svymd[$i]}' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svyinc$i' type='text' size='4' value='{$svyinc[$i]}' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svyazm$i' type='text' size='4' value='{$svyazm[$i]}' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svytvd$i' type='text' size='6' value='{$svytvd[$i]}' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svyvs$i' type='text' size='6' value='{$svyvs[$i]}' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svyns$i' type='text' size='6' value='{$svyns[$i]}' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svyew$i' type='text' size='6' value='{$svyew[$i]}' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svycd$i' type='text' size='6' value='{$svycd[$i]}' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svyca$i' type='text' size='4' value='{$svyca[$i]}' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svydl$i' type='text' size='3' value='" .
		(strpos($snum,'SLD') === false ? $svydl[$i] : '-') . "' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svycl$i' type='text' size='3' value='{$svycl[$i]}' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svytf$i' type='text' size='6' value='{$svytf[$i]}' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svytot$i' type='text' size='6' value='{$svytot[$i]}' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svytpos$i' type='text' size='4' value='{$svytpos[$i]}' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svyftot$i' type='text' size='3' value='{$svyftot[$i]}' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svybot$i' type='text' size='3' value='{$svybot[$i]}' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svydip$i' type='text' size='3' value='{$svydip[$i]}' disabled></td>\n";
		echo "<td class='$cls'>\n";
		echo "<INPUT class='$cls' $clickstr readonly id='svyfault$i' type='text' size='3' value='{$svyfault[$i]}' disabled></td>\n";
		echo "</TR>\n";
		echo "<input id='svyrowid$i' type='hidden' value='row_$snum'>\n";
		echo "<INPUT id='svyid$i' type='hidden' value='{$svyid[$i]}'>\n";
		echo "<INPUT id='svymeth$i' type='hidden' value='{$svymeth[$i]}'>\n";
		echo "<INPUT id='svydata$i' type='hidden' value='{$svydata[$i]}'>\n";
	}
}
if(isset($projs[count($projs)-1])) $opa1=$projs[count($projs)-1];
if(isset($projs[count($projs)-2])) $opa2=$projs[count($projs)-2];
//AddOuijaCutoffProjection($bitproj);
$project=$_POST['project'];
$propazm=$_POST['propazm'];
if(!isset($currid) or $currid == '')	$currid=(isset($_POST['currid']) ? $_POST['currid'] : '');
// $newid=$currid;

$svysel=0;
$svycnt=0;
$totalsvys=0;
$lasttot=0;
$svynum=array();
$svytype=array();
$svyid=array();
$svyplan=array();
$svymd=array();
$svyinc=array();
$svyazm=array();
$svytvd=array();
$svyvs=array();
$svyns=array();
$svyew=array();
$svyca=array();
$svycd=array();
$svydl=array();
$svyftot=array();
$svytot=array();
$svybot=array();
$svydip=array();
$svyfault=array();
$svymeth=array();
$svydata=array();
$svytf = array();

$prevbot=0;
$prevtot=0;
$db=new dbio($seldbname);
$db->OpenDb();
include("readwellinfo.inc.php");
$db->CloseDb();

FetchSurveys();

if($currid!="") {
	for($i=0;$i<$svycnt;$i++) {
		if($currid==$svyid[$i] && $svytype[$i]=='proj') {
			$svysel=$i;
			$method=$svymeth[$svysel];
			break;
		}
	}
} else {
	if($svysel<=0) {
		if($project=="ahead") {
			$method=$svymeth[$svycnt-1];
			AddProjection();
		}
		else {
			$method=$pbmethod;
			AddProjection();
			$svynum[$svycnt-1]="BPrj";
			$svymd[$svycnt-1]+=$bitoffset;
		}
	} else  $method=$pbmethod;
}
if($project!="bit") FetchProjections();

?>
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE>Project <?echo $project?></TITLE>
<LINK rel='stylesheet' type='text/css' href='projws.css'/>
<STYLE>
#layer1 {
	position: fixed;
	visibility: hidden;
	background-color: #eee;
	border: 1px solid #000;
	padding: 0 2;
}
#layer1 input {
	border: none;
	background-color: transparent;
	color: blue;
	padding: 0 0;
	margin: 0;
}
#close {
	float: left;
}
</STYLE>

<SCRIPT language="javascript" src="mathproc.js" ></SCRIPT>
<SCRIPT language="javascript">
var skiprot=false;
var RC=180.0*100.0/Math.PI;
var R2D=180.0/Math.PI;
var save=new Array();
var D2R=Math.PI/180.0;
var patodel=new Array()
function degrees(a) {
	var b=a*R2D;
	// if(b<0.0)	b=180.0+b;
	if(b<0.0)	b+=360.0;
	return b;
}
function sdegrees(a) {
	var b=a*R2D;
	return b;
}
function radians(a) {
	if(a>180.0) return ((a-360.0)*D2R);
	return (a*D2R);
}
function init()
{
	var sel=parseFloat(document.getElementById("svysel").value);
	save[0]=document.getElementById("svymd"+sel).value;
	save[1]=document.getElementById("svyinc"+sel).value;
	save[2]=document.getElementById("svyazm"+sel).value;
	save[3]=document.getElementById("svytvd"+sel).value;
	save[4]=document.getElementById("svyvs"+sel).value;
	save[5]=document.getElementById("svyns"+sel).value;
	save[6]=document.getElementById("svyew"+sel).value;
	save[7]=document.getElementById("svycd"+sel).value;
	save[8]=document.getElementById("svyca"+sel).value;
	save[9]=document.getElementById("svydl"+sel).value;
	save[10]=document.getElementById("svytf"+sel).value;
	save[11]=document.getElementById("svytpos"+sel).value;
	save[12]=document.getElementById("svytot"+sel).value;
	save[13]=document.getElementById("svydip"+sel).value;
	save[14]=document.getElementById("svyfault"+sel).value;
	changeInputs();
	var svycnt=parseInt(document.getElementById("svycnt").value);
	var skiprot=false;
	var w=window.outerWidth;
	var h=320;
	if(svycnt>6) {
		var c=svycnt-6;
		h+=c*20;
	}
	update_calculator();
	//calculate();
}
function reload_init()
{
	if(window.opener && !window.opener.closed) {
		window.opener.location.reload();
	}
	init();
}
function restore() {
	var sel=parseFloat(document.getElementById("svysel").value);
	document.getElementById("svymd"+sel).value=parseFloat(save[0]).toFixed(2);
	document.getElementById("svyinc"+sel).value=parseFloat(save[1]).toFixed(2);
	document.getElementById("svyazm"+sel).value=parseFloat(save[2]).toFixed(2);
	document.getElementById("svytvd"+sel).value=parseFloat(save[3]).toFixed(2);
	document.getElementById("svyvs"+sel).value=parseFloat(save[4]).toFixed(2);
	document.getElementById("svyns"+sel).value=parseFloat(save[5]).toFixed(2);
	document.getElementById("svyew"+sel).value=parseFloat(save[6]).toFixed(2);
	document.getElementById("svycd"+sel).value=parseFloat(save[7]).toFixed(2);
	document.getElementById("svyca"+sel).value=parseFloat(save[8]).toFixed(2);
	document.getElementById("svydl"+sel).value=parseFloat(save[9]).toFixed(2);
	document.getElementById("svytpos"+sel).value=parseFloat(save[10]).toFixed(2);
	document.getElementById("svytot"+sel).value=parseFloat(save[11]).toFixed(2);
	document.getElementById("svydip"+sel).value=parseFloat(save[13]).toFixed(2);
	var method=document.getElementById("svymeth"+sel).value;
	if(method!=8)
		document.getElementById("svyfault"+sel).value=parseFloat("0.0").toFixed(2);
	else
		document.getElementById("svyfault"+sel).value=parseFloat(save[14]).toFixed(2);
}
function clrInput(obj) {
	obj.setAttribute('readonly', '');
	obj.setAttribute('class', 'proj');
	obj.removeAttribute('onchange');
	obj.removeAttribute('onKeyPress');
	obj.removeAttribute('onclick');
}
function setInput(obj, func) {
	obj.removeAttribute('readonly');
	obj.setAttribute('class', 'rw');
	obj.setAttribute('onchange', func);
	obj.setAttribute('onKeyPress', 'return disableEnterKey(event)');
}
function changeInputs() {
	var project=document.getElementById("project").value;
	var sel=document.getElementById("svysel").value;
	var method=document.getElementById("method").value;
	document.getElementById("svymeth"+sel).value=method;
	if(project!="bit") document.getElementById("btnid"+sel).focus();
	var emd=document.getElementById("svymd"+sel);
	var einc=document.getElementById("svyinc"+sel);
	var eazm=document.getElementById("svyazm"+sel);
	var etvd=document.getElementById("svytvd"+sel);
	var evs=document.getElementById("svyvs"+sel);
	var ens=document.getElementById("svyns"+sel);
	var eew=document.getElementById("svyew"+sel);
	var ecd=document.getElementById("svycd"+sel);
	var eca=document.getElementById("svyca"+sel);
	var edl=document.getElementById("svydl"+sel);
	var ecl=document.getElementById("svycl"+sel);
	var epos=document.getElementById("svytpos"+sel);
	var etot=document.getElementById("svytot"+sel);
	var edip=document.getElementById("svydip"+sel);
	var efault=document.getElementById("svyfault"+sel);

	clrInput(emd);
	clrInput(einc);
	clrInput(eazm);
	clrInput(etvd);
	clrInput(evs);
	clrInput(ens);
	clrInput(eew);
	clrInput(ecd);
	clrInput(eca);
	clrInput(edl);
	clrInput(ecl);
	clrInput(epos);
	clrInput(etot);
	
	clrInput(edip);
	clrInput(efault);
	//document.getElementById("btnaccept").removeAttribute('disabled');

	if(method==8) { // DIP/FAULT/POS/VS
		setInput(evs, 'calculate()');
		setInput(epos, 'calculate()');
		setInput(edip, 'calculate()');
		setInput(efault, 'calculate()');
	} else if(method==7) { // TOT/POS/VS
		setInput(evs, 'calculate()');
		setInput(epos, 'calculate()');
		setInput(etot, 'calculate()');
	} else if(method==6) { // TVD/VS
		setInput(etvd, 'calculate()');
		setInput(evs, 'calculate()');
		setInput(edip, 'calculate()');
		setInput(efault, 'calculate()');
	} else if(method==5) { // solve for inc
		setInput(edip, 'calculate()');
		setInput(efault, 'calculate()');
		setInput(emd, 'calculate()');
		setInput(eazm, 'calculate()');
		setInput(etvd, 'calculate()');
	} else if(method==4) { // solve for md
		setInput(edip, 'calculate()');
		setInput(efault, 'calculate()');
		setInput(einc, 'calculate()');
		setInput(eazm, 'calculate()');
		setInput(etvd, 'calculate()');
	} else if(method==3) { // md/inc/az
		setInput(edip, 'calculate()');
		if(project=='ahead') setInput(efault, 'calculate()');
		setInput(emd, 'calculate()');
		setInput(einc, 'calculate()');
		setInput(eazm, 'calculate()');
	} else if(method==1 || method==2) { // baker inc and az projections
		setInput(etvd, 'calculate()');
		setInput(ecd, 'calculate()');
		setInput(eca, 'calculate()');
	} else if(method==0) { // last dogleg
		setInput(emd, 'calculate()');
		setInput(edip, 'calculate()');
		if(project=='ahead') setInput(efault, 'calculate()');
	}
	else document.getElementById("btnaccept").setAttribute('disabled', 'true');
}
function changemethod()
{
	changeInputs();
	restore();
	calculate();
}
function cc(pa, svysel)
{
	var ca=0.0;
	var cd=0.0;
	var radius=0.0;
	var i=0;
	var svycnt=parseInt(document.getElementById("svycnt").value);
	// for(i=0; i<svycnt; i++) {
	for(i=svysel; i<svysel+1; i++) {
		var pi=i-1;
		var pmd=parseFloat(document.getElementById("svymd"+pi).value);
		var pinc=parseFloat(document.getElementById("svyinc"+pi).value);
		var pazm=parseFloat(document.getElementById("svyazm"+pi).value);
		var ptvd=parseFloat(document.getElementById("svytvd"+pi).value);
		var pns=parseFloat(document.getElementById("svyns"+pi).value);
		var pew=parseFloat(document.getElementById("svyew"+pi).value);

		var md=parseFloat(document.getElementById("svymd"+i).value);
		var inc=parseFloat(document.getElementById("svyinc"+i).value);
		var azm=parseFloat(document.getElementById("svyazm"+i).value);
		var tvd=parseFloat(document.getElementById("svytvd"+i).value);
		var vs=parseFloat(document.getElementById("svyvs"+i).value);
		var dl=parseFloat(document.getElementById("svydl"+i).value);
		var cl=md-pmd;


		if(inc>180.0) inc-=360.0;
		if(pinc>180.0) pinc-=360.0;
		if(azm>360.0) azm-=360.0;
		if(pazm>360.0) pazm-=360.0;
		if(inc<0.0 || inc>180.0)	return;
		if(pinc<0.0 || pinc>180.0)	return;
		inc*=D2R;
		azm*=D2R;
		pinc*=D2R;
		pazm*=D2R;
		dl=Math.acos( (Math.cos(pinc) * Math.cos(inc)) + (Math.sin(pinc) * Math.sin(inc) * Math.cos(azm-pazm)));
		if (dl!=0.0) radius=(2.0/dl) * Math.tan(dl/2.0);
		else radius=1.0;
		tvd=ptvd+((cl/2.0)*(Math.cos(pinc)+Math.cos(inc))*radius);
		var ns=pns +( (cl/2.0)* ((Math.sin(pinc) * Math.cos(pazm)) + (Math.sin(inc) * Math.cos(azm))) * radius);
		var ew=pew + ( (cl/2.0)* ((Math.sin(pinc) * Math.sin(pazm)) + (Math.sin(inc) * Math.sin(azm))) * radius);
		if (ns!=0) ca=Math.atan2(ew,ns);
		else ca=(Math.PI*Math.PI);
		if (ca!=0.0) cd=Math.abs(ew/Math.sin(ca));
		else cd=ns;
		vs=cd * Math.cos(ca-pa);
		dl=((dl*100)/cl)*R2D;
		inc=inc*R2D;
		azm=azm*R2D;
		ca=ca*R2D;
		if(ca<0.0)	ca+=360.0;
		document.getElementById("svytvd"+i).value=tvd.toFixed(2);
		document.getElementById("svyvs"+i).value=vs.toFixed(2);
		document.getElementById("svyns"+i).value=ns.toFixed(2);
		document.getElementById("svyew"+i).value=ew.toFixed(2);
		document.getElementById("svyca"+i).value=ca.toFixed(2);
		document.getElementById("svycd"+i).value=cd.toFixed(2);
		document.getElementById("svydl"+i).value=dl.toFixed(2);
		document.getElementById("svycl"+i).value=cl.toFixed(2);
	}
}

function projtia(pa, svysel) {
	var psvysel=svysel-1;
	var pmd=parseFloat(document.getElementById("svymd"+psvysel).value);
	
	var ptvd=parseFloat(document.getElementById("svytvd"+psvysel).value);
	
	var pinc=parseFloat(document.getElementById("svyinc"+psvysel).value);
	var pazm=parseFloat(document.getElementById("svyazm"+psvysel).value);
	var tvd=parseFloat(document.getElementById("svytvd"+svysel).value);
	var inc=parseFloat(document.getElementById("svyinc"+svysel).value);
	var azm=parseFloat(document.getElementById("svyazm"+svysel).value);
	inc=radians(inc);
	pinc=radians(pinc);
	azm=radians(azm);
	pazm=radians(pazm);
	var dtvd=tvd-ptvd;
	var md = pmd+(dtvd*(inc-pinc)) / (Math.sin(inc)-Math.sin(pinc));
	if(!isNaN(md)){
		document.getElementById("svymd"+svysel).value=md.toFixed(2);
	}
	//cc(pa, svysel);
}

function projtma(pa, svysel) {
	var psvysel=svysel-1;
	var ptvd=parseFloat(document.getElementById("svytvd"+psvysel).value);
	var pmd=parseFloat(document.getElementById("svymd"+psvysel).value);
	var pazm=parseFloat(document.getElementById("svyazm"+psvysel).value);
	var pinc=parseFloat(document.getElementById("svyinc"+psvysel).value);
	var tvd=parseFloat(document.getElementById("svytvd"+svysel).value);
	var md=parseFloat(document.getElementById("svymd"+svysel).value);
	var azm=parseFloat(document.getElementById("svyazm"+svysel).value);
	var dtvd=tvd-ptvd;
	var dmd=md-pmd;
	if(Math.abs(dtvd)>Math.abs(dmd)) {
		//alert('Delta TVD is greater than the delta MD');
		return;
	}
	azm=radians(azm);
	pazm=radians(pazm);
	var a=0;
	var incr=.01;
	var limit=180.0;
	for(inc=0; inc<=180; inc+=incr) {
		if(inc>=pinc-incr&&inc<=pinc+incr)	continue;
		var dl=Math.acos(
			(Math.cos(radians(pinc)) * Math.cos(radians(inc))) +
			(Math.sin(radians(pinc)) * Math.sin(radians(inc)) * Math.cos(azm-pazm))
			);
		var radius=0;
		if (dl!=0.0) radius=(2.0/dl) * Math.tan(dl/2.0);
		a=ptvd+((dmd/2.0)*(Math.cos(radians(pinc))+Math.cos(radians(inc)))*radius);
		// document.writeln("<pre>Angle:"+inc+" TVD:"+a+"</pre>");
		if(a<=tvd)	break;
	}
	if(inc<=180.0) {
		document.getElementById("svyinc"+svysel).value=inc.toFixed(2);
		cc(pa, svysel);
	}
	
}

function projtva(pa, svysel) {
	var i=0;
	for(i=svysel; i<svysel+1; i++) {
		var pi=i-1;
		var pmd=parseFloat(document.getElementById("svymd"+pi).value);
		var pinc=radians(parseFloat(document.getElementById("svyinc"+pi).value));
		var pazm=radians(parseFloat(document.getElementById("svyazm"+pi).value));
		var ptvd=parseFloat(document.getElementById("svytvd"+pi).value);
		var pvs=parseFloat(document.getElementById("svyvs"+pi).value);
		var pns=parseFloat(document.getElementById("svyns"+pi).value);
		var pew=parseFloat(document.getElementById("svyew"+pi).value);
		var ptot=parseFloat(document.getElementById("svytot"+pi).value);
		

		var md=parseFloat(document.getElementById("svymd"+i).value);
		var azm=radians(parseFloat(document.getElementById("svyazm"+i).value));
		var tvd=parseFloat(document.getElementById("svytvd"+i).value);
		var vs=parseFloat(document.getElementById("svyvs"+i).value);
		var tot=parseFloat(document.getElementById("svytot"+i).value);
		var pos=parseFloat(document.getElementById("svytpos"+i).value);
		var dip=parseFloat(document.getElementById("svydip"+i).value);
		var fault=parseFloat(document.getElementById("svyfault"+i).value);
		var method=parseInt(document.getElementById("svymeth"+i).value);
		if(method==8 || method==7 || method == 6) {
			if(method==8) {
				var tot=ptot+(-Math.tan(dip/57.29578)*Math.abs(vs-pvs));
				
				tot+=fault; 
				tvd=tot-pos;
				document.getElementById("tot").value=tot.toFixed(2);
				
				document.getElementById("tvd").value=tvd.toFixed(2);
				document.getElementById("svytvd"+i).value=tvd.toFixed(2);
				document.getElementById("svytot"+i).value=tot.toFixed(2);
				
				document.getElementById("svydip"+i).value=dip.toFixed(2);
				document.getElementById("svyfault"+i).value=fault.toFixed(2);
			}
			if(method==7) {
				tvd=tot-pos;
				
				if(vs-pvs==0.0) var dip=0.0;
				else {
					var tdiff=tot-ptot;
					var vdiff=vs-pvs;
					var a=tdiff/vdiff;
					var dip=-degrees( Math.atan(a) );
					if(dip < (-180))	dip+=360.0;
				}
				document.getElementById("tot").value=tot.toFixed(2);
				
				document.getElementById("tvd").value=tvd.toFixed(2);
				document.getElementById("dip").value=dip.toFixed(2);
				document.getElementById("tpos").value=pos.toFixed(2);
				document.getElementById("svytvd"+i).value=tvd.toFixed(2);
				document.getElementById("svydip"+i).value=dip.toFixed(2);
				
				document.getElementById("svytpos"+i).value=pos.toFixed(2);
			}

			var inc=0; var ns=0; var ew=0; var ca=0; var cd=0; var dl=0; var cl=0;
			var newinc=0; var newvs=0; var newmd=0; var newtvd=0; var radius=1.0;
			var dtvd=tvd-ptvd; var dvs=vs-pvs;
			if(dtvd!=0.0 || dvs!=0.0) {
				var incr=.01;
				var r=Math.sqrt((dtvd*dtvd)+(dvs*dvs));
				newmd=pmd+r;
				newinc=0.0;
				do {
					cl=newmd-pmd;
					if(newinc>=degrees(pinc)-incr && newinc<=degrees(pinc)+incr) {
						if(newvs<vs && newtvd>tvd) { newmd+=incr; newinc+=incr; }
						else if(newvs>vs && newtvd<=tvd) { newinc-=incr; }
						else if(newvs>vs && newtvd<tvd) { newinc-=incr; newmd-=incr; }
						continue;
					}
					inc=radians(newinc);
					dl=Math.acos( (Math.cos(pinc) * Math.cos(inc)) + (Math.sin(pinc) * Math.sin(inc) * Math.cos(azm-pazm)));
					if (dl!=0.0) radius=(2.0/dl) * Math.tan(dl/2.0); else radius=1.0;
					newtvd=ptvd+((cl/2.0)*(Math.cos(pinc)+Math.cos(inc))*radius);
					ns=pns +( (cl/2.0)* ((Math.sin(pinc) * Math.cos(pazm)) + (Math.sin(inc) * Math.cos(azm))) * radius);
					ew=pew + ( (cl/2.0)* ((Math.sin(pinc) * Math.sin(pazm)) + (Math.sin(inc) * Math.sin(azm))) * radius);
					if (ns!=0) ca=Math.atan2(ew,ns); else ca=( Math.PI * .5 * (ew/fabs(ew)) );
					if (ca!=0.0) cd=Math.abs(ew/Math.sin(ca)); else cd=ns;
					newvs=cd * Math.cos(ca-pa);
					if(Math.abs(newvs-vs)<2.0 && Math.abs(newtvd-tvd)<2.0)	incr=.001;
					if(newvs<vs && newtvd>tvd) { newmd+=incr; newinc+=incr; }
					else if(newvs<vs && newtvd<=tvd) { newmd+=incr; }
					else if(newvs>vs && newtvd<=tvd) { newinc-=incr; }
					else if(newvs>=vs && newtvd>tvd) { newmd-=incr; }
					else if(newvs>vs && newtvd<tvd) { newinc-=incr; newmd-=incr; }
				} while ( (newtvd>tvd+incr || newvs<vs-incr) && newinc>0 && newinc<=180);
				
				ca*=R2D; if(ca<0.0)	ca=180.0+ca;
				dl=((dl*100)/cl)*R2D;
				newinc=degrees(pinc+((inc-pinc)/2.0));
				document.getElementById("svymd"+i).value=newmd.toFixed(2);
				document.getElementById("svyinc"+i).value=newinc.toFixed(2);
				document.getElementById("svyns"+i).value=ns.toFixed(2);
				document.getElementById("svyew"+i).value=ew.toFixed(2);
				document.getElementById("svycd"+i).value=cd.toFixed(2);
				document.getElementById("svyca"+i).value=ca.toFixed(2);
				document.getElementById("svydl"+i).value=dl.toFixed(2);
				document.getElementById("svycl"+i).value=cl.toFixed(2);
				// save inputs
				if(i==svysel) {
					document.getElementById("tvd").value=tvd.toFixed(2);
					document.getElementById("vs").value=vs.toFixed(2);
				}
			}
		}
	}
}

function doParseInputs()
{
	var svysel=document.getElementById("svysel").value;
	var method=document.getElementById("method").value;
	x = new MathProcessor;
	if(method==5) {	// Solve for inc
		try{ y=x.parse(document.getElementById("svytvd"+svysel).value);
			document.getElementById("svytvd"+svysel).value=y.toFixed(2); }
		catch(e){ alert(e); return; }
		try{ y=x.parse(document.getElementById("svymd"+svysel).value);
			document.getElementById("svymd"+svysel).value=y.toFixed(2); }
		catch(e){ alert(e); return; }
		try{ y=x.parse(document.getElementById("svyazm"+svysel).value);
			document.getElementById("svyazm"+svysel).value=y.toFixed(2); }
		catch(e){ alert(e); return; }
		}
	else if(method==4) { 	// Solve for MD
		try{ y=x.parse(document.getElementById("svytvd"+svysel).value);
			document.getElementById("svytvd"+svysel).value=y.toFixed(2); }
		catch(e){ alert(e); return; }
		try{ y=x.parse(document.getElementById("svyinc"+svysel).value);
			document.getElementById("svyinc"+svysel).value=y.toFixed(2); }
		catch(e){ alert(e); return; }
		try{ y=x.parse(document.getElementById("svyazm"+svysel).value);
			document.getElementById("svyazm"+svysel).value=y.toFixed(2); }
		catch(e){ alert(e); return; }
		}
	else if(method==3) { 	// MD/INC/AZ
		try{ y=x.parse(document.getElementById("svymd"+svysel).value);
			document.getElementById("svymd"+svysel).value=y.toFixed(2); }
		catch(e){ alert(e); return; }
		try{ y=x.parse(document.getElementById("svyinc"+svysel).value);
			document.getElementById("svyinc"+svysel).value=y.toFixed(2); }
		catch(e){ alert(e); return; }
		try{ y=x.parse(document.getElementById("svyazm"+svysel).value);
			document.getElementById("svyazm"+svysel).value=y.toFixed(2); }
		catch(e){ alert(e); return; }
		}
	else if(method==1) { 	// baker inc
		}
	else if(method==2) { 	// baker az
		}
	else if(method==0) { 	// last DL
		try{ y=x.parse(document.getElementById("svymd"+svysel).value);
			document.getElementById("svymd"+svysel).value=y.toFixed(2); }
		catch(e){ alert(e); return; }
	}
}
function calculate(skiprot)
{
	skiprot = skiprot?skiprot:false;
	doParseInputs();
	var project=document.getElementById("project").value;
	var svycnt=parseInt(document.getElementById("svycnt").value);
	var svysel=parseInt(document.getElementById("svysel").value);
	var pa=parseFloat(document.getElementById("propazm").value);
	if(pa>180)	pa-=360; pa*=D2R;
	for (var i=2; i<svycnt; i=i+1) {
		rowid = document.getElementById("svyrowid"+i).value
		if(rowid.indexOf("ROT")>=0){
			if(skiprot){
				
				el = document.getElementById(rowid)
				el.setAttribute("style",'display:none');
				continue;
			}else{
				el = document.getElementById(rowid)
				el.setAttribute("style",'');
			}
		}
		var pi=i-1;
		var meth=parseInt(document.getElementById("svymeth"+i).value);
		if(meth==8) {	// Input DIP/FAULT/POS/VS
			projtva(pa, i); }
		else if(meth==7) {	// Input TOT/POS/VS
			projtva(pa, i); }
		else if(meth==6) {	// Input TVD/VS
			projtva(pa, i); }
		else if(meth==5) {	// Solve for inc
			projtma(pa, i); }
		else if(meth==4) { 	// Solve for MD
			projtia(pa, i); }
		else if(meth==3) { 	// Input MD/INC/AZ
			cc(pa, i); }
		else if(meth==0) { 	// last DL
			if(i>1) {
				var svymd=parseFloat(document.getElementById("svymd"+i).value);
				var svyinc=parseFloat(document.getElementById("svyinc"+i).value);
				var svyazm=parseFloat(document.getElementById("svyazm"+i).value);
				var psvymd=parseFloat(document.getElementById("svymd"+pi).value);
				var psvyinc=parseFloat(document.getElementById("svyinc"+pi).value);
				var psvyazm=parseFloat(document.getElementById("svyazm"+pi).value);
				var dmd=svymd-psvymd;
				if(dmd>0.0) { // fetch the previous dl
					var md1=parseFloat(document.getElementById("svymd"+(pi-1)).value);
					var inc1=parseFloat(document.getElementById("svyinc"+(pi-1)).value);
					var azm1=parseFloat(document.getElementById("svyazm"+(pi-1)).value);
					var cl=psvymd-md1;
					var dinc=(psvyinc-inc1)/cl;
					var dazm=(psvyazm-azm1)/cl;
					var inc=psvyinc+(dinc*dmd);
					var azm=psvyazm+(dazm*dmd);
					document.getElementById("svyinc"+i).value=inc.toFixed(2);
					document.getElementById("svyazm"+i).value=azm.toFixed(2);
					if(document.getElementById("project").value!='ahead')
						document.getElementById("bitoffset").value=dmd;
					cc(pa, i);
				}
			}
		}
		else if(meth==1) { 	/* baker inc (not used) */ }
		else if(meth==2) { 	/* baker az (not used) */ }

		// set position or calculate dip
		var vs=parseFloat(document.getElementById("svyvs"+i).value);
		var tvd=parseFloat(document.getElementById("svytvd"+i).value);
		var dip=parseFloat(document.getElementById("svydip"+i).value);
		var fault=parseFloat(document.getElementById("svyfault"+i).value);
		var pvs=parseFloat(document.getElementById("svyvs"+pi).value);
		var ptvd=parseFloat(document.getElementById("svytvd"+pi).value);
		var ptot=parseFloat(document.getElementById("svytot"+pi).value);
		
		if(meth!=7 && meth!=8) {	// calculate TOT/POS
			var tot=ptot+(-Math.tan(dip/57.29578)*Math.abs(vs-pvs));
			
			tot+=fault; 
			var pos=tot-tvd;
			// if(project=='bit' && i==svycnt-1)
				// alert(vs +"," +pvs +"," +tot +"," +tvd +"," +pos);
			document.getElementById("svytot"+i).value=tot.toFixed(2);
			
			document.getElementById("svytpos"+i).value=pos.toFixed(2);
		}
		/* done in projtva: */
		/*
		if(meth==7) {	// calculate the dip
			var tot=parseFloat(document.getElementById("svytot"+i).value);
			if(vs-pvs==0.0) dip=0.0;
			else dip=-degrees( Math.atan( (tot-ptot) / (vs-pvs) ) );
			if(dip < (-180))	dip+=360.0;
			document.getElementById("svydip"+i).value=dip.toFixed(2);
		}
		*/
	}
}

function onaccept(form)
{
	patodelstr = patodel.join(',')
	document.getElementById('motor_yield_hidden').value = document.getElementById('motor_yield').value
	document.getElementById("pavsdel").value=patodelstr
	var svysel=document.getElementById("svysel").value;
	var psvysel=svysel-1;
	var pmd=parseFloat(document.getElementById("svymd"+psvysel).value);
	var md=parseFloat(document.getElementById("svymd"+svysel).value);
	if(md<pmd) {
		//alert("Measured depth less than last survey depth\nProjections will be re-ordered...");
		// return false;
	}
	var inc=parseFloat(document.getElementById("svyinc"+svysel).value);
	if(inc>180.0||inc<0) {
		//alert("Inclination out of range");
		return false;
	}
	var azm=parseFloat(document.getElementById("svyazm"+svysel).value);
	if(azm>360.0||azm<0) {
		//alert("Azimuth out of range");
		return false;
	}

	var meth=parseInt(document.getElementById("svymeth"+svysel).value);
	document.getElementById("skiprot").value=skiprot;
	// document.getElementById("tpos").value=document.getElementById("tpos").value;
	document.getElementById("meth").value=document.getElementById("method").value;
	document.getElementById("currid").value=document.getElementById("svyid"+svysel).value;
	document.getElementById("md").value=document.getElementById("svymd"+svysel).value;
	document.getElementById("inc").value=document.getElementById("svyinc"+svysel).value;
	document.getElementById("azm").value=document.getElementById("svyazm"+svysel).value;
	document.getElementById("tvd").value=document.getElementById("svytvd"+svysel).value;
	document.getElementById("vs").value=document.getElementById("svyvs"+svysel).value;
	document.getElementById("ca").value=document.getElementById("svyca"+svysel).value;
	document.getElementById("cd").value=document.getElementById("svycd"+svysel).value;
	document.getElementById("dip").value=document.getElementById("svydip"+svysel).value;
	document.getElementById("fault").value=document.getElementById("svyfault"+svysel).value;
	document.getElementById("tot").value=document.getElementById("svytot"+svysel).value;
	document.getElementById("tpos").value=document.getElementById("svytpos"+svysel).value;
	document.getElementById("pmd").value=document.getElementById("svymd"+psvysel).value;
	document.getElementById("pinc").value=document.getElementById("svyinc"+psvysel).value;
	document.getElementById("pazm").value=document.getElementById("svyazm"+psvysel).value;
	document.getElementById("ptvd").value=document.getElementById("svytvd"+psvysel).value;
	document.getElementById("pca").value=document.getElementById("svyca"+psvysel).value;
	document.getElementById("pcd").value=document.getElementById("svycd"+psvysel).value;
	console.log( document.getElementById("svytf3").value);
	document.getElementById("tf").value = document.getElementById("svytf3").value;
	rowform=form;
	console.log(rowform);
	t = 'oujiawsd.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return true;
}
function disableEnterKey(e)
{
     var key;
     if(window.event)
          key = window.event.keyCode;     //IE
     else
          key = e.which;     //firefox

     if(key == 13) {
		  calculate();
          return false;
	 }
     else
          return true;
}
function projws(rowform, npid)
{
	rowform.newid.value=npid;
	onaccept();
}
function onAddProjection() {
	var svysel=document.getElementById("svysel").value;
	var psvysel=svysel-1;
	var curr=parseFloat(document.getElementById("svymd"+svysel).value);
	var prev=parseFloat(document.getElementById("svymd"+psvysel).value);
	var newmd=curr-((curr-prev)/2.0);
	depth=prompt("MD for new projection: ", newmd.toFixed(2));
	if(depth!=null && depth!="") {
		newmd=parseFloat(depth);
		rowform=document.getElementById("oujiawsd");
		rowform.currid.value="";
		rowform.meth.value='3';
		rowform.md.value=newmd.toFixed(2);
		rowform.inc.value=document.getElementById("svyinc"+svysel).value;
		rowform.azm.value=document.getElementById("svyazm"+svysel).value;
		rowform.tvd.value=document.getElementById("svytvd"+svysel).value;
		rowform.vs.value=document.getElementById("svyvs"+svysel).value;
		rowform.ca.value=document.getElementById("svyca"+svysel).value;
		rowform.cd.value=document.getElementById("svycd"+svysel).value;
		rowform.dip.value=document.getElementById("svydip"+svysel).value;
		rowform.fault.value=document.getElementById("svyfault"+svysel).value;
		rowform.tot.value=document.getElementById("svytot"+svysel).value;
		rowform.pmd.value=document.getElementById("svymd"+psvysel).value;
		rowform.pinc.value=document.getElementById("svyinc"+psvysel).value;
		rowform.pazm.value=document.getElementById("svyazm"+psvysel).value;
		rowform.ptvd.value=document.getElementById("svytvd"+psvysel).value;
		rowform.pca.value=document.getElementById("svyca"+psvysel).value;
		rowform.pcd.value=document.getElementById("svycd"+psvysel).value;
		t = 'oujiawsd.php';
		t = encodeURI (t);
		rowform.action = t;
		rowform.submit();
		return true;
	}
	return false;
}
function closeupanddie(form)
{
	rowform=form;
	var seldbname=document.getElementById('seldbname').value
	window.close();
//	if(window.opener && !window.opener.closed) {
//		window.opener.location.reload();
//		window.opener.location='gva_tab3.php?removerot=t&seldbname='+seldbname;
//	}
}
function noshowline() {
	var svycnt=document.getElementById("svycnt").value;
	var i;
	for(i=2;i<svycnt;i++) {
		var emd=document.getElementById("svymd"+i);
		var einc=document.getElementById("svyinc"+i);
		var eazm=document.getElementById("svyazm"+i);
		var etvd=document.getElementById("svytvd"+i);
		var evs=document.getElementById("svyvs"+i);
		var ens=document.getElementById("svyns"+i);
		var eew=document.getElementById("svyew"+i);
		var ecd=document.getElementById("svycd"+i);
		var eca=document.getElementById("svyca"+i);
		var edl=document.getElementById("svydl"+i);
		var ecl=document.getElementById("svycl"+i);
		var etpos=document.getElementById("svytpos"+i);
		var etot=document.getElementById("svytot"+i);
		var edip=document.getElementById("svydip"+i);
		var efault=document.getElementById("svyfault"+i);
		if(emd.getAttribute('readonly')=="") emd.setAttribute('class', 'proj');
		if(einc.getAttribute('readonly')=="") einc.setAttribute('class', 'proj');
		if(eazm.getAttribute('readonly')=="") eazm.setAttribute('class', 'proj');
		if(etvd.getAttribute('readonly')=="") etvd.setAttribute('class', 'proj');
		if(evs.getAttribute('readonly')=="") evs.setAttribute('class', 'proj');
		if(ens.getAttribute('readonly')=="") ens.setAttribute('class', 'proj');
		if(eew.getAttribute('readonly')=="") eew.setAttribute('class', 'proj');
		if(ecd.getAttribute('readonly')=="") ecd.setAttribute('class', 'proj');
		if(eca.getAttribute('readonly')=="") eca.setAttribute('class', 'proj');
		if(edl.getAttribute('readonly')=="") edl.setAttribute('class', 'proj');
		if(ecl.getAttribute('readonly')=="") ecl.setAttribute('class', 'proj');
		if(etpos.getAttribute('readonly')=="") etpos.setAttribute('class', 'proj');
		if(etot.getAttribute('readonly')=="") etot.setAttribute('class', 'proj');
		
		if(edip.getAttribute('readonly')=="") edip.setAttribute('class', 'proj');
		if(efault.getAttribute('readonly')=="") efault.setAttribute('class', 'proj');
	}
}
function showline(i) {
	noshowline();
	var emd=document.getElementById("svymd"+i);
	var einc=document.getElementById("svyinc"+i);
	var eazm=document.getElementById("svyazm"+i);
	var etvd=document.getElementById("svytvd"+i);
	var evs=document.getElementById("svyvs"+i);
	var ens=document.getElementById("svyns"+i);
	var eew=document.getElementById("svyew"+i);
	var ecd=document.getElementById("svycd"+i);
	var eca=document.getElementById("svyca"+i);
	var edl=document.getElementById("svydl"+i);
	var ecl=document.getElementById("svycl"+i);
	var etpos=document.getElementById("svytpos"+i);
	var etot=document.getElementById("svytot"+i);
	var edip=document.getElementById("svydip"+i);
	var efault=document.getElementById("svyfault"+i);
	if(emd.getAttribute('readonly')=="") emd.setAttribute('class', 'svy');
	if(einc.getAttribute('readonly')=="") einc.setAttribute('class', 'svy');
	if(eazm.getAttribute('readonly')=="") eazm.setAttribute('class', 'svy');
	if(etvd.getAttribute('readonly')=="") etvd.setAttribute('class', 'svy');
	if(evs.getAttribute('readonly')=="") evs.setAttribute('class', 'svy');
	if(ens.getAttribute('readonly')=="") ens.setAttribute('class', 'svy');
	if(eew.getAttribute('readonly')=="") eew.setAttribute('class', 'svy');
	if(ecd.getAttribute('readonly')=="") ecd.setAttribute('class', 'svy');
	if(eca.getAttribute('readonly')=="") eca.setAttribute('class', 'svy');
	if(edl.getAttribute('readonly')=="") edl.setAttribute('class', 'svy');
	if(ecl.getAttribute('readonly')=="") ecl.setAttribute('class', 'svy');
	if(etpos.getAttribute('readonly')=="") etpos.setAttribute('class', 'svy');
	if(etot.getAttribute('readonly')=="") etot.setAttribute('class', 'svy');
	
	if(edip.getAttribute('readonly')=="") edip.setAttribute('class', 'svy');
	if(efault.getAttribute('readonly')=="") efault.setAttribute('class', 'svy');
}
function showMethod(event, i) {
	var emeth=parseInt(document.getElementById("svymeth"+i).value);
	var msg=document.getElementById("layer1text");
	if(emeth==0) msg.value="Last dogleg";
	else if(emeth==1) msg.value="Solve for INC";
	else if(emeth==2) msg.value="Solve for AZM";
	else if(emeth==3) msg.value="Input MD/INC/AZM";
	else if(emeth==4) msg.value="Solve for MD";
	else if(emeth==5) msg.value="Solve for INC";
	else if(emeth==6) msg.value="Input TVD/VS";
	else if(emeth==7) msg.value="Input TOT/POS/VS";
	else if(emeth==8) msg.value="Input DIP/FAULT/POS/VS";
	else msg.value="Unknown method";

	x=event.clientX-10;
	y=event.clientY-10;
	setVisible('layer1', x, y);
}
var update_calculator=function(){
	pa1checked=document.getElementById('pa1radiosel').checked
	pa2checked=document.getElementById('pa2radiosel').checked
	sldindex=0;
	rotindex=0;
	pa2idx=0;
	pa3idx=0;
	for(i=0;i<<?php echo count($svyid)?>;i++){
			rowid = document.getElementById('svyrowid'+i).value
			if(rowid.indexOf('ROT')>-1){
				if(rotindex==0){
					rotindex=i
				}
			}
			if(rowid.indexOf('SLD')>-1){
				if(sldindex==0){
					sldindex=i
				}
			}
			if(rowid.indexOf('PA2')>-1 || rowid.indexOf('PA1')>-1){
				pa2idx=i
			}
			if(rowid.indexOf('PA3')>-1){
				pa3idx=i
			}
		}
	if(pa1checked){
		pa1inc = <?echo isset($opa1['inc']) ? $opa1['inc'] : "''" ?>;
		pa1azm = <?echo isset($opa1['azm']) ? $opa1['azm'] : "''" ?>;
		pa1md = <?echo isset($opa1['md']) ? $opa1['md'] : "''" ?>;
		bprjinc = <?echo $bitproj['inc']?>;
		bprjazm = <?echo $bitproj['azm']?>;
		bprjmd  = parseFloat(document.getElementById('svymd2').value);
		cutoffinc = (((pa1inc-bprjinc)/(pa1md-bprjmd))*30)+bprjinc;
		cutoffazm = (((pa1azm-bprjazm)/(pa1md - bprjmd))*30)+bprjazm;
		document.getElementById('proj_inc').value=cutoffinc.toFixed(2)
		document.getElementById('proj_azm').value=cutoffazm.toFixed(2)
	}
	if(pa2checked){
		pa2inc = <?echo isset($opa2['inc']) ? $opa2['inc'] : "''" ?>;
		pa2azm = <?echo isset($opa2['azm']) ? $opa2['azm'] : "''" ?>;
		bprjinc = <?echo $bitproj['inc']?>;
		bprjazm = <?echo $bitproj['azm']?>;
		sldinc = ((pa2inc - bprjinc)/2)+bprjinc
		sldazm = ((pa2azm - bprjazm)/2)+bprjazm
		document.getElementById('proj_inc').value=sldinc.toFixed(2)
		document.getElementById('proj_azm').value=sldazm.toFixed(2)
	}
}
var caculate_slide=function(){
		sldindex=0
		rotindex=0
		for(i=0;i<<?php echo count($svyid)?>;i++){
			rowid = document.getElementById('svyrowid'+i).value
			if(rowid.indexOf('ROT')>-1){
				if(rotindex==0){
					rotindex=i
				}
			}
			if(rowid.indexOf('SLD')>-1){
				if(sldindex==0){
					sldindex=i
				}
			}
		}

		pinc = parseFloat(document.getElementById('present_inc').value)
		pazm = parseFloat(document.getElementById('present_azm').value)
		tinc = parseFloat(document.getElementById('proj_inc').value)
		if(isNaN(tinc)){
			alert('Slide INC must be a numeric value');
			return;
		}
		document.getElementById('svyinc4').value=tinc
		document.getElementById('tinc').value=tinc

		tazm = parseFloat(document.getElementById('proj_azm').value)
		if(isNaN(tazm)){
			alert('Slide AZ must be a numeric value');
			return;
		}
		
		document.getElementById('svyazm4').value=tazm
		document.getElementById('propazm').value=tazm
		document.getElementById('tazm').value=tazm

		my   = parseFloat(document.getElementById('motor_yield').value)
		if(isNaN(my)){
			alert('Motor Yield must be a numeric value');
			return;
		}
		rlfval = 0;
		if((tazm - pazm) > 180){
			rlfval=(tazm-pazm)-360
		} else {
			if((tazm - pazm)<-180){
				rlfval = (tazm-pazm)+360
			} else{
				rlfval = (tazm-pazm)
			}
		}
		rl   = (rlfval>0) ?'R':'L';
		pi   = Math.PI
		v1   = Math.sin(pi/180*pinc)*Math.sin(pi/180*tinc)+Math.cos(pi/180*pinc)*Math.cos(pi/180*tinc)
		v3a  = Math.abs(pazm-tazm)
		v2   = Math.cos(pi/180*v3a)
		v3   =  tinc - pinc
		v4   = v2*v1
		v5   =Math.acos(v4)*180/pi
		v6   = v3/v5
		v7   = Math.acos(v6)*180/pi
		tf_n    = v7
		slide_n = v5/my*100
		document.getElementById('tfn').value=tf_n;
		document.getElementById('sliden').value=slide_n;
		document.getElementById('tfrl').value = rl;
		if(slide_n!='Infinity'){
			newmd = parseFloat(document.getElementById('svymd'+(sldindex-1)).value)+slide_n
			rotmd = parseFloat(document.getElementById('svymd'+rotindex).value)
			if(newmd>rotmd){
				skiprot=true;
			} else {
				skiprot=false;
			}
			document.getElementById('svymd'+sldindex).value=newmd.toFixed(2);
			document.getElementById('svyinc'+sldindex).value=tinc
			document.getElementById('svyazm'+sldindex).value=tazm
			document.getElementById('svytf'+sldindex).value=tf_n.toFixed(2)+rl;
			calculate(skiprot);
			
		}
		sldvs = parseFloat(document.getElementById('svyvs'+sldindex).value)
		rotvs = parseFloat(document.getElementById('svyvs'+rotindex).value)
		vscnt = sldindex+1
		while(true){
			if(vscnt==rotindex){
				vscnt++;
				continue;
			}
			if(document.getElementById('svyvs'+vscnt)){
				val = parseFloat(document.getElementById('svyvs'+vscnt).value)
				if(val <sldvs || val < rotvs){
					console.log('found val for deletion');
					curdel = document.getElementById('svyid'+vscnt).value
					console.log(curdel)
					if(patodel.indexOf(curdel)==-1){
						patodel.push(curdel)
					}
					
				}else{
					curdel = document.getElementById('svyid'+vscnt).value
					if(patodel.indexOf(curdel)!=-1){
						patodel.splice(patodel.indexOf(curdel),1)
					}
				}
				console.log(patodel)
			} else {
				break
			}
			vscnt++
		}
		
		document.getElementById('btnaccept').disabled=false;
	}
</SCRIPT>
<script type="text/javascript" src="popupDiv.js"></script>
</HEAD>

<?
if(!isset($reload) or $reload=='') echo "<BODY onload='init()'>";
else echo "<BODY onload='reload_init()'>";
?>
<div id="layer1">
<a href="javascript:setVisible('layer1',0,0)" style="text-decoration: none"><strong>X</strong></a>
<input readonly type='text' id='layer1text' value='xx' style='text-align: left; border: none; background-color: transparent;'>
</div>

<table class='tabcontainer' style='width: 990px;'>
<tr>
<td colspan='17' align='right' style='padding:8px 20px'>
	
	<input type='hidden' id='method' value='3' name='method'>
	<table class='tabcontainer' style='width=200px'>
	<TR><TD colspan='2'><H2>Ouija</H2></TD></TR>
	
	<FORM id='oform' name='oform' method='post'>
	
	<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
	<INPUT type='hidden' name='username' value='<?echo isset($username) ? $username : '' ?>'>
	<INPUT type='hidden' name='ret' value='<?php echo $ret ?>'>
	<TR><td></td><td>BPrj</td><td>Slide</td><td></td></tr>
	<input type='hidden' id='bpmd' value='<?echo $bitproj['md']?>'>
	<tr><td>INC</td><td><input id='present_inc' type='text' value='<?echo $bitproj['inc']?>' disabled></td><td><input id='proj_inc' type='text' value='<?echo $svyinc[4]?>' ></td><td></td></tr>
	<tr><td>AZ</td><td><input id='present_azm' type='text' value='<?echo $bitproj['azm']?>' disabled></td><td><input id='proj_azm' type='text' value='<?echo $svyazm[4]?>' ></td><td></td></tr>
	<tr><td>Motor Yield</td><td><input id='motor_yield' type='text' value="<?echo $wi_motoryield?>"></td><td></td><td><input type='radio' name='calctype' value='pa1' id='pa1radiosel' checked onclick="update_calculator()"> PA1 prj. (curve & Lat.)</td></tr>
	<tr><td>TF Needed</td><td><input id='tfn' value='' disabled></td><td style='text-align:left' align='left'><input id='tfrl' value='' size='3' disabled></td><td><input type='radio' name='calctype' value='pa2' id='pa2radiosel' onclick="update_calculator()"> PA2 prj. (Lat.)</td></tr>
	<tr><td>Slide</td><td><input id='sliden' value='' disabled></td><td></td><td></td></tr>
	<tr><td colspan='4'><button type="button" onclick="caculate_slide()">Calculate</button></tr>
	</table>
	
</td>
</tr>
<tr>
<th class='surveys'>SVY</th>
<th class='surveys'>MD</th>
<th class='surveys'>INC</th>
<th class='surveys'>AZM</th>
<th class='surveys'>TVD</th>
<th class='surveys'>VS</th>
<th class='surveys'>NS</th>
<th class='surveys'>EW</th>
<th class='surveys'>CD</th>
<th class='surveys'>CA</th>
<th class='surveys'>DL</th>
<th class='surveys'>CL</th>
<th class='rot'>TF</th>
<th class='rot'>TCL</th>
<th class='rot'>Pos-TCL</th>
<th class='rot'>TOT</th>
<th class='rot'>BOT</th>
<th class='rot'>Dip</th>
<th class='rot'>Fault</th>
</tr>

<FORM name='oujiawsd' id='oujiawsd' method='post' action='oujiawsd.php'>
<tr>
<?php DisplaySurveys(); ?>
<input id='motor_yield_hidden' type='hidden' name='motoryield' value=''>
<input id='pavsdel' type='hidden' name='pavsdel' value=''>
<input id='tinc' type='hidden' name='tinc' value=''>
<input id='tazm' type='hidden' name='tazm' value=''>
<INPUT id='svycnt' type='hidden' name='svycnt' value='<?echo $svycnt?>'>
<INPUT id='svysel' type='hidden' name='svysel' value='<?echo $svysel?>'>
<INPUT id='currid' type='hidden' name='currid' value='<?echo $currid?>'>
<INPUT id='skiprot' type='hidden' name='skiprot' value='false'>
<INPUT id='newid' type='hidden' name='newid' value=''>
<INPUT id='seldbname' type='hidden' name='seldbname' value='<?echo $seldbname?>'>
<INPUT id='ret' type='hidden' name='ret' value='<?echo $ret?>'>
<INPUT id='project' type='hidden' name='project' value='<?echo $project?>'>
<INPUT id='bitoffset' type='hidden' name='bitoffset' value='<?echo $bitoffset?>'>
<INPUT id='propazm' type='hidden' name='propazm' value='<?echo $propazm?>'>
<INPUT id='meth' type='hidden' name='meth' value='<?echo $method?>'>
<INPUT id='data' type='hidden' name='data' value='<?echo $svydata[$svysel]?>'>
<INPUT id='md' type='hidden' name='md' value='<?echo $svymd[$svysel]?>'>
<INPUT id='inc' type='hidden' name='inc' value='<?echo $svyinc[$svysel]?>'>
<INPUT id='azm' type='hidden' name='azm' value='<?echo $svyazm[$svysel]?>'>
<INPUT id='tvd' type='hidden' name='tvd' value='<?echo $svytvd[$svysel]?>'>
<INPUT id='vs' type='hidden' name='vs' value='<?echo $svyvs[$svysel]?>'>
<INPUT id='ca' type='hidden' name='ca' value='<?echo $svyca[$svysel]?>'>
<INPUT id='cd' type='hidden' name='cd' value='<?echo $svycd[$svysel]?>'>
<INPUT id='tpos' type='hidden' name='tpos' value='<?echo $svytpos[$svysel]?>'>
<INPUT id='tot' type='hidden' name='tot' value='<?echo $svytot[$svysel]?>'>
<INPUT id='dip' type='hidden' name='dip' value='<?echo $svydip[$svysel]?>'>
<INPUT id='fault' type='hidden' name='fault' value='<?echo $svyfault[$svysel]?>'>
<INPUT id='pmd' type='hidden' name='pmd' value='<?echo $svymd[$svysel-1]?>'>
<INPUT id='pinc' type='hidden' name='pinc' value='<?echo $svyinc[$svysel-1]?>'>
<INPUT id='pazm' type='hidden' name='pazm' value='<?echo $svyazm[$svysel-1]?>'>
<INPUT id='ptvd' type='hidden' name='ptvd' value='<?echo $svytvd[$svysel-1]?>'>
<INPUT id='pca' type='hidden' name='pca' value='<?echo $svyca[$svysel-1]?>'>
<INPUT id='pcd' type='hidden' name='pcd' value='<?echo $svycd[$svysel-1]?>'>
<INPUT id='tf' type='hidden' name='tf' value=''>

<td colspan='16' align='center'>
	<INPUT type='submit' value='Cancel' onclick='closeupanddie(this.form)'>
	<INPUT type='submit' id='btnaccept' value='Save and Close' onclick='onaccept(this.form);' disabled>
</td>
</tr>
</FORM>
<tr>
<td colspan='16'>
	<br><center><small>&#169; 2010-2011 Supreme Source Energy Services, Inc.</small></center>
</td>
</tr>
</table>
</BODY>
</HTML>
