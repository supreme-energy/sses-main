<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Digital Oil Tools

function FetchSurveys() {
	global $seldbname, $project, $totalsvys, $svynum, $pbmethod, $svysel, $currid,$projpostcl;
	global $svymd, $svyinc, $svyazm, $svytvd, $svyvs, $svyns, $svyew, $svyca, $svymeth, $svydata;
	global $svytot, $svybot, $svycd, $svydl, $svycl, $svycnt, $svyplan, $svytype, $svyid, $svytpos, $lasttot,$svyftot;
	global $svydip, $svyfault, $projdip,$prevtot,$prevbot,$svytf;
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
		if($i<$totalsvys-3)	continue; // fetch the last 2 + BPrj
		$id =$db2->FetchField("id");
		$svyid[]=$id;
		$md=sprintf("%.02f", $db2->FetchField("md"));
		$plan=sprintf("%d", $db2->FetchField("plan"));
		if($totid){
			if($plan){
				$query = "select tot from addformsdata where md=$md and infoid=$totid;";
			}else{
				$query = "select tot from addformsdata where svyid=$id and infoid=$totid;";
			} 
			$db3->DoQuery($query);
			$db3->FetchRow();
			$ftot =sprintf("%.2f", $db2->FetchField("tot"));
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
		$svytf[]='-';
		$tvd=sprintf("%.02f", $db2->FetchField("tvd"));
		$tot=sprintf("%.02f", $db2->FetchField("tot"));
		
		$svytpos[]=sprintf("%.02f", $tot-$tvd);
		$svymd[]=$md;
		$svytvd[]=$tvd;
		$svytot[]=$tot;
		$svyftot[]=$ftot;
		$svybot[]=$bot;
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
			$projpostcl= $tot-$tvd;
			if($project=="bit")	$svysel=$svycnt;
		}
		$svyplan[]=$plan;
		$svydata[]="0,0,0";
		$svycnt++;
		$lasttot=$tot;
		$prevtot=$ftot;
		$prevbot=$bot;
	}
	$db2->CloseDb();
}
function FetchProjections() {
	global $seldbname, $svycnt;
	global $svymd, $svyinc, $svyazm, $svytvd, $svyvs, $svyns, $svyew, $svyca, $svymeth, $svydata, $svytpos;
	global $svytot, $svybot, $svycd, $svydl, $svycl, $svyid, $svytype, $svyplan, $svynum, $lasttot,$svyftot;
	global $svydip, $svyfault,$prevtot,$prevbot,$svytf;

	$db3 = new dbio($seldbname);
	$db3->OpenDb();
	$db2=new dbio($seldbname);
	$db2->OpenDb();
	$db2->DoQuery("SELECT * FROM projections ORDER BY md ASC;");
	$num=$db2->FetchNumRows(); 
	$totid =null;
	$botid = null;

	$db3->DoQuery("select * from addforms;");
	while($db3->FetchRow()){
		
		if(trim($db3->FetchField('label'))=='TOT'){
			$totid = $db3->FetchField('id');
		}
		if(trim($db3->FetchField('label'))=='BOT'){
			$botid = $db3->FetchField('id');
		}
	}
	for ($i=0; $i<$num; $i++) {
		$db2->FetchRow();
		$id = $db2->FetchField("id");
		if($id){
		if($totid){
			$query = "select tot from addformsdata where projid=$id and infoid=$totid;"; 
			$db3->DoQuery($query);
			$db3->FetchRow();
			$ftot =sprintf("%.2f", $db3->FetchField("tot"));
		}
		if($botid){
			$query = "select tot from addformsdata where projid=$id and infoid=$botid;";
			$db3->DoQuery($query);
			$db3->FetchRow();
			$bot =sprintf("%.2f", $db3->FetchField("tot"));
		}}else{
			$ftot=$prevtot;
			$bot=$prevbot;
		}
		$svyid[]=$id;
		$svymd[]=sprintf("%.02f", $db2->FetchField("md"));
		$svyinc[]=sprintf("%.02f", $db2->FetchField("inc"));
		$svyazm[]=sprintf("%.02f", $db2->FetchField("azm"));
		$svyns[]=sprintf("%.02f", $db2->FetchField("ns"));
		$svyew[]=sprintf("%.02f", $db2->FetchField("ew"));
		$svyca[]=sprintf("%.02f", $db2->FetchField("ca"));
		$svycd[]=sprintf("%.02f", $db2->FetchField("cd"));
		$svydl[]=sprintf("%.02f", $db2->FetchField("dl"));
		$svycl[]=sprintf("%.02f", $db2->FetchField("cl"));
		$dip=sprintf("%.02f", $db2->FetchField("dip"));
		$fault=sprintf("%.02f", $db2->FetchField("fault"));
		$svyhide[]=sprintf("%d", $db2->FetchField("hide"));
		$method=$svymeth[]=sprintf("%d", $db2->FetchField("method"));
		$data=sprintf("%s", $db2->FetchField("data"));
		$tvd=sprintf("%.02f", $db2->FetchField("tvd"));
		$vs=sprintf("%.02f", $db2->FetchField("vs"));
		$tot=sprintf("%.02f", $db2->FetchField("tot"));
		$tf = $db2->FetchField("tf");
		if($tf){
			$svytf[]=$tf;	
		} else {
			$svytf[]='-';
		}	
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
		$svytot[]=$tot;
		$svyftot[]=$ftot;
		$svybot[]=$bot;
		$svytpos[]=$tpos;
		$svydip[]=$dip;
		$svyfault[]=$fault;
		$svytype[]="proj";
		$svyplan[]=2;
		$lasttot=$tot;

		$n=$i+1;
		$svynum[]="PA$n";
		$svycnt++;
	}
	
	$db2->CloseDb();
}

function AddProjection() {
	global $seldbname, $svycnt, $svysel,$projpostcl,$autoposdec;
	global $svymd, $svyinc, $svyazm, $svytvd, $svyvs, $svyns, $svyew, $svyca, $svymeth, $svydata, $svytpos,$svyftot;
	global $svytot, $svybot, $svycd, $svydl, $svycl, $svyid, $svytype, $svyplan, $svynum, $lasttot;
	global $svydip, $svyfault,$svytf,$pamethod,$autoposdec;
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
	$tot=$svytot[$i];
	$ftot = $svyftot[$i];
	$bot=$svybot[$i];
	$dip=$svydip[$i];
	$fault=$svyfault[$i];
	$hide=$svyhide[$i];
	$plan=$svyplan[$i];
	$method = $svymeth[$i];
	$svyid[]="";
	$svymd[]=$md;
	$svyinc[]=$inc;
	$svyazm[]=$azm;
	$svytvd[]=$tvd;
	$svyns[]=$ns;
	$svyew[]=$ew;
	if($method==8){
		$svyvs[]=$vs+$cl;
	}else{
		$svyvs[]=$vs;
	}
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
	$svymeth[]=$method;
	$svydata[]=$data;
	$svytype[]="proj";
	$svyplan[]=2;
	$svynum[]="PAn";
	if($method==8){
		$svytpos[]=sprintf("%.02f", $projpostcl-($i*$autoposdec));
	}else{
		$svytpos[]=sprintf("%.02f", $tot-$tvd);
	}
	$svytf[]='-';
	$svysel=$svycnt;
	$svycnt++;
}

function DisplaySurveys() {
	global $seldbname, $project, $svyplan, $svytype, $svycnt;
	global $svymd, $svyinc, $svyazm, $svytvd, $svyvs, $svyns, $svyew, $svyca, $svymeth, $svydata;
	global $svytot, $svybot, $svycd, $svydl, $svycl, $svyid, $svynum, $svytpos,$svyftot;
	global $svydip, $svyfault,$svytf;
	for ($i=0; $i<$svycnt; $i++) {
		
		$snum=$svynum[$i];
		$add_attribute='';
		if($svyplan[$i]>0) { $cls="proj"; if($snum=="BPrj"){$add_attribute='ambit=true';echo "<INPUT type='hidden' id='bprj_pos_tcl' name='bprj_pos_tcl' value='".$svytpos[$i]."'>";}} else { $cls="svy"; }
		$clickstr="";
		echo "<TR><TD class='$cls'>";
		if($svyplan[$i]>0 && $snum!="BPrj") {
			echo "<INPUT CLASS='edit' TYPE='submit' id='btnid$i' name='btnid$i' VALUE='$snum' \
			onclick='projws(this.form, $svyid[$i])' \
			onmouseover='showline($i)' \
			onmouseout='noshowline()'>";
			$clickstr="onclick='showMethod(event,$i)' onmouseover='showline($i)' onmouseout='noshowline()' ";
		} else {
			echo "<INPUT class='$cls' id='svytype$i' type='hidden' value='$svytype[$i]'>$snum";	
		}
		echo "</TD>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svymd$i' type='text' size='6' value='$svymd[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svyinc$i' type='text' size='4' value='$svyinc[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svyazm$i' type='text' size='4' value='$svyazm[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svytvd$i' type='text' size='6' value='$svytvd[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svyvs$i' type='text' size='6' value='$svyvs[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svyns$i' type='text' size='6' value='$svyns[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svyew$i' type='text' size='6' value='$svyew[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svycd$i' type='text' size='6' value='$svycd[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svyca$i' type='text' size='4' value='$svyca[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svydl$i' type='text' size='3' value='$svydl[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svycl$i' type='text' size='3' value='$svycl[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svytf$i' type='text' size='3' value='$svytf[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svytot$i' type='text' size='6' value='$svytot[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $add_attribute $clickstr readonly id='svytpos$i' type='text' size='4' value='$svytpos[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svyftot$i' type='text' size='4' value='$svyftot[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svybot$i' type='text' size='6' value='$svybot[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svydip$i' type='text' size='3' value='$svydip[$i]'></td>";
		echo "<td class='$cls'>";
		echo "<INPUT class='$cls' $clickstr readonly id='svyfault$i' type='text' size='3' value='$svyfault[$i]'></td>";
		echo "</TR>";
		echo "<INPUT id='svyid$i' type='hidden' value='$svyid[$i]'>";
		echo "<INPUT id='svymeth$i' type='hidden' value='$svymeth[$i]'>";
		echo "<INPUT id='svydata$i' type='hidden' value='$svydata[$i]'>";
	}
}


$seldbname=$_POST['seldbname'];
$project=$_POST['project'];
$propazm=$_POST['propazm'];
if($currid=="")	$currid=$_POST['currid'];
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
$svytot=array();
$svyftot=array();
$svybot=array();
$svydip=array();
$svyfault=array();
$svymeth=array();
$svydata=array();
$svytf = array();
$projpostcl=0;
$prevtot=0;
$prevbot=0;

require_once("dbio.class.php");

include("cleanoujia.php");
$db=new dbio($seldbname);
$db->OpenDb();
include("readwellinfo.inc.php");
$db->CloseDb();

FetchSurveys();

if($project!="bit") FetchProjections();
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

?>

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
</HEAD>

<SCRIPT language="javascript" src="mathproc.js" ></SCRIPT>
<SCRIPT language="javascript">
var RC=180.0*100.0/Math.PI;
var R2D=180.0/Math.PI;
var save=new Array();
var D2R=Math.PI/180.0;
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
	save[10]=document.getElementById("svytpos"+sel).value;
	save[11]=document.getElementById("svytot"+sel).value;
	save[12]=document.getElementById("svybot"+sel).value;
	save[13]=document.getElementById("svydip"+sel).value;
	save[14]=document.getElementById("svyfault"+sel).value;
	changeInputs();
	var svycnt=parseInt(document.getElementById("svycnt").value);
	var w=window.outerWidth;
	var h=320;
	if(svycnt>6) {
		var c=svycnt-6;
		h+=c*20;
	}
	window.resizeTo( w,h );
	calculate();
	var method=document.getElementById("method").value
	if(method=='8'){
		document.getElementById('autoposdec_div').style.display='block';
		doautopostclcalcdown();
	} else {
		document.getElementById('autoposdec_div').style.display='none';
	}
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
	document.getElementById("svybot"+sel).value=parseFloat(save[12]).toFixed(2);
	document.getElementById("svydip"+sel).value=parseFloat(save[13]).toFixed(2);
	var method=document.getElementById("svymeth"+sel).value;
	if(method!=8)
		document.getElementById("svyfault"+sel).value=parseFloat("0.0").toFixed(2);
	else
		document.getElementById("svyfault"+sel).value=parseFloat(save[14]).toFixed(2);
}
function clrInput(obj) {
	obj.setAttribute('readonly', '');
	curclass = obj.getAttribute('class');
	if(curclass == 'proj bit'){
		obj.setAttribute('class', 'proj');
	} else{
		obj.setAttribute('class', 'proj bit');
	}
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
	var ebot=document.getElementById("svybot"+sel);
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
	clrInput(ebot);
	clrInput(edip);
	clrInput(efault);
	document.getElementById("btnaccept").removeAttribute('disabled');

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
	var method=document.getElementById("method").value
	if(method=='8'){
		document.getElementById('autoposdec_div').style.display='block';
		doautopostclcalcdown();
	} else {
		document.getElementById('autoposdec_div').style.display='none';
	}
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

		if(md<=pmd) {
			alert("Warning: Measured depth less than the previous survey\n" +
			"Projections will be reordered after saving");
			return;
		}
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
	document.getElementById("svymd"+svysel).value=md.toFixed(2);
	cc(pa, svysel);
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
		alert('Delta TVD is greater than the delta MD');
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
	else alert('internal error: inclination out of range');
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
		var pbot=parseFloat(document.getElementById("svybot"+pi).value);

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
				var bot=pbot-(ptot-tot);
				tot+=fault; bot+=fault;
				tvd=tot-pos;
				document.getElementById("tot").value=tot.toFixed(2);
				document.getElementById("bot").value=bot.toFixed(2);
				document.getElementById("tvd").value=tvd.toFixed(2);
				document.getElementById("svytvd"+i).value=tvd.toFixed(2);
				document.getElementById("svytot"+i).value=tot.toFixed(2);
				document.getElementById("svybot"+i).value=bot.toFixed(2);
				document.getElementById("svydip"+i).value=dip.toFixed(2);
				document.getElementById("svyfault"+i).value=fault.toFixed(2);
			}
			if(method==7) {
				tvd=tot-pos;
				var bot=pbot+(tot-ptot);
				if(vs-pvs==0.0) var dip=0.0;
				else {
					var tdiff=tot-ptot;
					var vdiff=vs-pvs;
					var a=tdiff/vdiff;
					var dip=-degrees( Math.atan(a) );
					if(dip < (-180))	dip+=360.0;
				}
				document.getElementById("tot").value=tot.toFixed(2);
				document.getElementById("bot").value=bot.toFixed(2);
				document.getElementById("tvd").value=tvd.toFixed(2);
				document.getElementById("dip").value=dip.toFixed(2);
				document.getElementById("tpos").value=pos.toFixed(2);
				document.getElementById("svytvd"+i).value=tvd.toFixed(2);
				document.getElementById("svydip"+i).value=dip.toFixed(2);
				document.getElementById("svybot"+i).value=bot.toFixed(2);
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
				if(newinc>180.0) { alert('internal error: inclination out of range'); return; }
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
function calculate()
{
	doParseInputs();
	var project=document.getElementById("project").value;
	var svycnt=parseInt(document.getElementById("svycnt").value);
	var svysel=parseInt(document.getElementById("svysel").value);
	var pa=parseFloat(document.getElementById("propazm").value);
	if(pa>180)	pa-=360; pa*=D2R;
	for (var i=2; i<svycnt; i=i+1) {
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
		var pbot=parseFloat(document.getElementById("svybot"+pi).value);
		if(meth!=7 && meth!=8) {	// calculate TOT/POS
			var tot=ptot+(-Math.tan(dip/57.29578)*Math.abs(vs-pvs));
			var bot=pbot+(-Math.tan(dip/57.29578)*Math.abs(vs-pvs));
			tot+=fault; bot+=fault;
			var pos=tot-tvd;
			// if(project=='bit' && i==svycnt-1)
				// alert(vs +"," +pvs +"," +tot +"," +tvd +"," +pos);
			document.getElementById("svytot"+i).value=tot.toFixed(2);
			document.getElementById("svybot"+i).value=bot.toFixed(2);
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

function onaccept()
{
	var svysel=document.getElementById("svysel").value;
	var psvysel=svysel-1;
	var pmd=parseFloat(document.getElementById("svymd"+psvysel).value);
	var md=parseFloat(document.getElementById("svymd"+svysel).value);
	if(md<pmd) {
		alert("Measured depth less than last survey depth\nProjections will be re-ordered...");
		// return false;
	}
	var inc=parseFloat(document.getElementById("svyinc"+svysel).value);
	if(inc>180.0||inc<0) {
		alert("Inclination out of range");
		return false;
	}
	var azm=parseFloat(document.getElementById("svyazm"+svysel).value);
	if(azm>360.0||azm<0) {
		alert("Azimuth out of range");
		return false;
	}

	var meth=parseInt(document.getElementById("svymeth"+svysel).value);
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
	document.getElementById("bot").value=document.getElementById("svybot"+svysel).value;
	document.getElementById("pmd").value=document.getElementById("svymd"+psvysel).value;
	document.getElementById("pinc").value=document.getElementById("svyinc"+psvysel).value;
	document.getElementById("pazm").value=document.getElementById("svyazm"+psvysel).value;
	document.getElementById("ptvd").value=document.getElementById("svytvd"+psvysel).value;
	document.getElementById("pca").value=document.getElementById("svyca"+psvysel).value;
	
	document.getElementById("bitprjpostcl_input").value=document.getElementById("bprj_pos_tcl").value;
	document.getElementById("autoposdec_input").value=document.getElementById("autoposdec").value;

	rowform=document.getElementById("projwsd");
	t = 'projwsd.php';
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
	var currvs = parseFloat(document.getElementById("svyvs"+svysel).value);
	var prev=parseFloat(document.getElementById("svymd"+psvysel).value);
	var newmd=curr-((curr-prev)/2.0);
	var method=document.getElementById("method").value
	if(method==8){
		cl = parseFloat(document.getElementById("svycl"+svysel).value);
		depth = curr+0.01; 
		vs = (currvs+cl).toFixed(2);
	} else { 
		depth=prompt("MD for new projection: ", newmd.toFixed(2));
		vs = currvs
	}
	if(depth!=null && depth!="") {
		newmd=parseFloat(depth);
		rowform=document.getElementById("projwsd");
		rowform.currid.value="";
		rowform.meth.value=method;
		rowform.md.value=newmd.toFixed(2);
		rowform.inc.value=document.getElementById("svyinc"+svysel).value;
		rowform.azm.value=document.getElementById("svyazm"+svysel).value;
		rowform.tvd.value=document.getElementById("svytvd"+svysel).value;
		rowform.vs.value=vs;
		rowform.ca.value=document.getElementById("svyca"+svysel).value;
		rowform.cd.value=document.getElementById("svycd"+svysel).value;
		rowform.dip.value=document.getElementById("svydip"+svysel).value;
		rowform.fault.value=document.getElementById("svyfault"+svysel).value;
		rowform.tot.value=document.getElementById("svytot"+svysel).value;
		rowform.bot.value=document.getElementById("svybot"+svysel).value;
		rowform.pmd.value=document.getElementById("svymd"+psvysel).value;
		rowform.pinc.value=document.getElementById("svyinc"+psvysel).value;
		rowform.pazm.value=document.getElementById("svyazm"+psvysel).value;
		rowform.ptvd.value=document.getElementById("svytvd"+psvysel).value;
		rowform.pca.value=document.getElementById("svyca"+psvysel).value;
		rowform.pcd.value=document.getElementById("svycd"+psvysel).value;
		rowform.bitprjpostcl_input.value=document.getElementById("bprj_pos_tcl").value;
		rowform.autoposdec_input.value=document.getElementById("autoposdec").value;
		t = 'projwsd.php';
		t = encodeURI (t);
		rowform.action = t;
		rowform.submit();
		return true;
	}
	return false;
}
function closeupanddie()
{
	rowform=document.getElementById("projwsd");
	var seldbname=rowform.seldbname.value;
	window.close();
	if(window.opener && !window.opener.closed) {
		// window.opener.location.reload();
		// window.opener.location='gva_tab3.php?seldbname='+seldbname;
	}
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
		var ebot=document.getElementById("svybot"+i);
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
		if(ebot.getAttribute('readonly')=="") ebot.setAttribute('class', 'proj');
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
	var ebot=document.getElementById("svybot"+i);
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
	if(ebot.getAttribute('readonly')=="") ebot.setAttribute('class', 'svy');
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

function doautopostclcalcdown(){
	decval = document.getElementById('autoposdec').value
	sval   = document.getElementById('bprj_pos_tcl').value
		if(sval>0){
			svalsign='positive'
		} else{
			svalsign='negative'
		}
		decval = parseFloat(decval);
		if(decval>0){
			if(svalsign=='negative'){
				decval =decval*-1;
			}
			sval = parseFloat(sval);
			i = 0;
			do{
				
				crel = document.getElementById('svytpos'+i)
				
				if(crel){
					if(crel.className!='svy' && crel.getAttribute('ambit')==null){
						sval = sval-decval;
						if(svalsign=='positive'){
							if(sval <0){sval=0}
						}else{
							if(sval >0){sval=0}
						}
						crel.value=sval.toFixed(2);
						
					}
				}
				i++;
			}while(crel);
			calculate();
		}
		
}
</SCRIPT>
<script type="text/javascript" src="popupDiv.js"></script>

<?
if($reload=="") echo "<BODY onload='init()'>";
else echo "<BODY onload='reload_init()'>";
?>
<div id="layer1">
<a href="javascript:setVisible('layer1',0,0)" style="text-decoration: none"><strong>X</strong></a>
<input readonly type='text' id='layer1text' value='xx' style='text-align: left; border: none; background-color: transparent;'>
</div>

<table class='tabcontainer' style='width: 990px;'>
<tr>
<td colspan='5' align='right' style='padding: 8 20;'>
	<?if($project=='ahead') { ?> <h1>Project Ahead</h1>
	<?} else { ?> <h1>Project To Bit</h1> <?} ?>
</td>
<td colspan='5'>
	<input type='submit' value='Add Projection' onclick='onAddProjection();'>
</td>
<td colspan='7' align='right' style='padding: 8 20;'>
	<b>Method:</b>
	<select id='method' style='font-size: 10pt;' name='method' onchange="changemethod()">
	<option value='0' <?if($method==0) echo "selected='selected'"?>>Use last dogleg to project</option>
	<option value='3' <?if($method==3) echo "selected='selected'"?>>Input MD/Inc/Az</option>
	<?if($project=='ahead') { ?>
	<option value='4' <?if($method==4) echo "selected='selected'"?>>Solve For Measured Depth</option>
	<option value='5' <?if($method==5) echo "selected='selected'"?>>Solve For Inclination</option>
	<option value='6' <?if($method==6) echo "selected='selected'"?>>Input TVD/VS</option>
	<option value='7' <?if($method==7) echo "selected='selected'"?>>Input TOT/POS/VS</option>
	<option value='8' <?if($method==8) echo "selected='selected'"?>>Input DIP/FAULT/POS/VS</option>
	<?}?>
	</select>
	<INPUT type='submit' value='Calculate' onclick='calculate()'>
	<div style="display:<?if($method==8){ echo "block";} else{ echo "none";}?>" id='autoposdec_div'><b>Auto Pos-TCL</b><input onchange="doautopostclcalcdown()" type='text' value='<?echo $autoposdec?>' id='autoposdec' name='autoposdec'></div>
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
<th class='surveys'>TF</th>
<th class='rot'>TCL</th>
<th class='rot'>Pos-TCL</th>
<th class='rot'>TOT</th>
<th class='rot'>BOT</th>
<th class='rot'>Dip</th>
<th class='rot'>Fault</th>
</tr>

<FORM name='projwsd' id='projwsd' method='post'>
<tr>
<?  DisplaySurveys(); ?>
<INPUT id='svycnt' type='hidden' name='svycnt' value='<?echo $svycnt?>'>
<INPUT id='svysel' type='hidden' name='svysel' value='<?echo $svysel?>'>
<INPUT id='currid' type='hidden' name='currid' value='<?echo $currid?>'>
<INPUT id='newid' type='hidden' name='newid' value=''>
<INPUT id='seldbname' type='hidden' name='seldbname' value='<?echo $seldbname?>'>
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
<INPUT id='bot' type='hidden' name='bot' value='<?echo $svybot[$svysel]?>'>
<INPUT id='dip' type='hidden' name='dip' value='<?echo $svydip[$svysel]?>'>
<INPUT id='fault' type='hidden' name='fault' value='<?echo $svyfault[$svysel]?>'>
<INPUT id='pmd' type='hidden' name='pmd' value='<?echo $svymd[$svysel-1]?>'>
<INPUT id='pinc' type='hidden' name='pinc' value='<?echo $svyinc[$svysel-1]?>'>
<INPUT id='pazm' type='hidden' name='pazm' value='<?echo $svyazm[$svysel-1]?>'>
<INPUT id='ptvd' type='hidden' name='ptvd' value='<?echo $svytvd[$svysel-1]?>'>
<INPUT id='pca' type='hidden' name='pca' value='<?echo $svyca[$svysel-1]?>'>
<INPUT id='pcd' type='hidden' name='pcd' value='<?echo $svycd[$svysel-1]?>'>
<INPUT id='bitprjpostcl_input' type='hidden' name='bprjpostcl' value=''>
<INPUT id='autoposdec_input' type='hidden' name='autoposdec' value=''>
</FORM>

<td colspan='16' align='center'>
	<INPUT type='submit' value='Cancel' onclick='closeupanddie()'>
	<INPUT type='submit' id='btnaccept' value='Save and Close' onclick='onaccept();'>
</td>
</tr>
<tr>
<td colspan='16'>
	<br><center><small>&#169; 2010-2011 Digital Oil Tools</small></center>
</td>
</tr>
</table>
</BODY>
</HTML>
