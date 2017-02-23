<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?php
require('/usr/share/php/fpdf/fpdf.php');
require_once("dbio.class.php");

if(!isset($now)){
	$now = new DateTime();
}
if(strlen($seldbname)<=0)	$seldbname=$_GET['seldbname'];
if(strlen($filename)<=0)	$filename=$_GET['filename'];
if(strlen($cutoff)<=0)	$cutoff=$_GET['cutoff'];
if(strlen($mintvd)<=0)	$mintvd=$_GET['mintvd'];
if(strlen($maxtvd)<=0)	$maxtvd=$_GET['maxtvd'];
if(strlen($minvs)<=0)	$minvs=$_GET['minvs'];
if(strlen($maxvs)<=0)	$maxvs=$_GET['maxvs'];
if(strlen($yscale)<=0)	$yscale=$_GET['yscale'];
if($cutoff=="")	$cutoff=0;
$approve_send = isset($_REQUEST['approve_report'])? true:false;
include "generatepdfimageleft.php";
$db=new dbio($seldbname);
$db->OpenDb();
include('readwellinfo.inc.php');
include('readappinfo.inc.php');
$additionlgraphs = array();
$db->DoQuery("select * from edatalogs where single_plot=1");
while($db->FetchRow()){
	array_push($additionlgraphs,sprintf("tmp/%s.surveyplotlat.png%s.png", $seldbname,$db->FetchField("label")));
}
$db->CloseDb();
$tofile=0;
if(strlen($filename)>0 && !$approve_send) $tofile=1;
else $filename=sprintf("tmp/%s_%s.surveyplotlat.pdf", $now->format('Y-m-d_H-i-s_T'), $seldbname);
//if(file_exists($filename)) unlink($filename);
exec("./sses_gva -d $seldbname --justsurveys");
exec("./sses_cc -d -p $seldbname -w");
//exec("./sses_as -d $seldbname");
exec("./sses_af -d $seldbname");
$imgfn1="tmp/$seldbname.surveyplotlat.png";
if(file_exists($imgfn1)) unlink($imgfn1);
$imgfn2="tmp/$seldbname.surveyplotlat.png1.png";
if(file_exists($imgfn2)) unlink($imgfn2);
$imgfn3="tmp/$seldbname.surveyplotlat.png2.png";
if(file_exists($imgfn3)) unlink($imgfn3);
$imgfn4="tmp/$seldbname.surveyplotlat.png3.png";
if(file_exists($imgfn4)) unlink($imgfn4);

// exec("./sses_ps -d $seldbname -t lat -c 35 -p $projection -o $imgfn1 -h 1024 -w 4096");
$args=" -t lat";
if(strlen($cutoff)) {
	$args=$args." -c $cutoff";
}
if(strlen($mintvd))	$args=$args." -tvd1 $mintvd";
if(strlen($maxtvd))	$args=$args." -tvd2 $maxtvd";
if(strlen($minvs))	$args=$args." -vs1 $minvs";
if(strlen($maxvs))	$args=$args." -vs2 $maxvs";
if(strlen($yscale))	$args=$args." -yscale $yscale";
$args=$args." -nodata";
$args=$args." -p $projection";
$args=$args." -o $imgfn1";
$height_mod=0;
	if(count($additionlgraphs)>0){
		$height_mod = -25+count($additionlgraphs)*75;
	}
$height = 598 - $height_mod;
$width=1148;
$args=$args." -h $height";
$args=$args." -w $width";
$args=$args." -transparent -anno";
echo "./sses_ps -d $seldbname $args";
exec("./sses_ps -d $seldbname $args");
// $retstr=array(); $retval=0;
// exec("./sses_ps -d $seldbname $args", &$retstr, &$retval);
// echo "<pre>./sses_ps -d $seldbname $args\n</pre>";
// echo "<pre>\n"; foreach($retstr as $rs) { echo "$rs\n"; } echo "</pre>";

// exec("./sses_ps -d $seldbname -t lat -c $cutoff -p $projection -o $imgfn1 -h 1536 -w 2048");


class PDF extends FPDF
{
	var $db;
	var $hdrheight=0.0;
	var $scaleright=200.0;
	var $projection_count=0;
	function ReportHeader($now)
	{
		$tformated = $now->format('m-d-Y H:i:s T');
		$this->SetMargins(.25,0,0);
		$this->SetAutoPageBreak(false);
		$this->Image("./logofull.png", .5, .2, .7, .55);
		$this->hdrheight+=0.75;

		$this->SetFont('','B',18);
		$this->Cell(8, .17, "Lateral Survey Plot - $tformated", 0, 0, 'C', false);
		$this->SetFontSize('5');
		$this->Cell(2.0, .1, "Generated by: Subsurface Geological Tracking Analysis", 0, 1, 'R', false);
		$this->Cell(10.2, .1, "Copyright 2010-2011 Supreme Source Energy Services, Inc.", 0, 1, 'R', false);
		$this->SetFontSize(7);
		$this->Ln();

		$this->db->DoQuery("SELECT * FROM wellinfo;");
		if($this->db->FetchNumRows()>0) {
			$this->db->FetchRow();
			$wellname=$this->db->FetchField("wellborename");
			if( !strlen($wellname) ) $wellname="Polaris";
			$companyname=$this->db->FetchField("operatorname");
			$jobnumber=$this->db->FetchField("jobnumber");
			$operatorcontact=$this->db->FetchField("operatorcontact1");
			$rigid=$this->db->FetchField("rigid");
			$startdate=$this->db->FetchField("startdate");
			$apiuwi=$this->db->FetchField("wellid");
			$enddate=$this->db->FetchField("enddate");
			$field=$this->db->FetchField("field");
			$location=$this->db->FetchField("location");
			$propazm=$this->db->FetchField("propazm");
			$stateprov=$this->db->FetchField("stateprov");
			$northref=$this->db->FetchField("correction");
			$county=$this->db->FetchField("county");
		}
		$h=.13;
		$w1=1.0;
		$w2=1.75;
		$w3=0.38;
		$b=0;

		$this->Cell($w1, $h, "Company Name:", $b, 0, 'R', false);
		$this->Cell($w2, $h, $companyname, $b, 0, 'L', false);
		$this->Cell($w1, $h, "Field:", $b, 0, 'R', false);
		$this->Cell($w2, $h, $field, $b, 0, 'L', false);
		$this->Cell($w1, $h, "Job Number:", $b, 0, 'R', false);
		$this->Cell($w2, $h, $jobnumber, $b, 0, 'L', false);
		$this->Ln(); $this->hdrheight+=$h;

		$this->Cell($w1, $h, "Well Name:", $b, 0, 'R', false);
		$this->Cell($w2, $h, $wellname, $b, 0, 'L', false);
		$this->Cell($w1, $h, "Location:", $b, 0, 'R', false);
		$this->Cell($w2, $h, $location, $b, 0, 'L', false);
		$this->Cell($w1, $h, "", $b, 0, 'R', false);
		$this->Cell($w2, $h, "", $b, 0, 'L', false);
		$this->Cell($w1, $h, "Proposed Azimuth:", $b, 0, 'R', false);
		$this->Cell($w3, $h, $propazm, $b, 0, 'L', false);
		$this->Ln(); $this->hdrheight+=$h;

		$this->Cell($w1, $h, "Rig ID:", $b, 0, 'R', false);
		$this->Cell($w2, $h, $rigid, $b, 0, 'L', false);
		$this->Cell($w1, $h, "State/Prov:", $b, 0, 'R', false);
		$this->Cell($w2, $h, $stateprov, $b, 0, 'L', false);
		$this->Cell($w1, $h, "Start Date:", $b, 0, 'R', false);
		$this->Cell($w2, $h, $startdate, $b, 0, 'L', false);
		$this->Cell($w1, $h, "North Reference:", $b, 0, 'R', false);
		$this->Cell($w3, $h, $northref, $b, 0, 'L', false);
		$this->Ln(); $this->hdrheight+=$h;

		$this->Cell($w1, $h, "API/UWI:", $b, 0, 'R', false);
		$this->Cell($w2, $h, $apiuwi, $b, 0, 'L', false);
		$this->Cell($w1, $h, "County:", $b, 0, 'R', false);
		$this->Cell($w2, $h, $county, $b, 0, 'L', false);
		$this->Cell($w1, $h, "End Date:", $b, 0, 'R', false);
		$this->Cell($w2, $h, $enddate, $b, 0, 'L', false);
		$this->Ln(); $this->hdrheight+=$h;
		$this->Ln(); $this->hdrheight+=$h;
	}

	function ReportSurveys() {
		$db2=$this->db2;
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
		$this->db->DoQuery("SELECT count(id) FROM surveys WHERE plan=0");
		if($this->db->FetchNumRows()>0) {
			$this->db->FetchRow();
			$numsvys=$this->db->FetchField("count");
		}

		$this->db->DoQuery("SELECT * FROM surveys WHERE plan=0 ORDER BY md DESC LIMIT 1");
		if($this->db->FetchNumRows()>0) {
			$this->db->FetchRow();
			$id= $this->db->FetchField("id");
			if($totid){
				$query = "select tot from addformsdata where svyid=$id and infoid=$totid;";
			 	$db2->DoQuery($query);
				$db2->FetchRow();
				$tot =sprintf("%.2f", $db2->FetchField("tot"));
			}
			if($botid){
				$query = "select tot from addformsdata where svyid=$id and infoid=$botid;";
				$db2->DoQuery($query);
				$db2->FetchRow();
				$bot =sprintf("%.2f", $db2->FetchField("tot"));
			}				
			$svymd=$this->db->FetchField("md");
			$svyinc=$this->db->FetchField("inc");
			$svyazm=$this->db->FetchField("azm");
			$svytvd=$this->db->FetchField("tvd");
			$svyvs=$this->db->FetchField("vs");
			$svydl=$this->db->FetchField("dl");
			$svyca=$this->db->FetchField("ca");
			$svycd=$this->db->FetchField("cd");
			$svyns=$this->db->FetchField("ns");
			$svyew=$this->db->FetchField("ew");
			$tlc = $this->db->FetchField("tot");
			$svytot=$tot;
			$svybot=$bot;
			$svydip=$this->db->FetchField("dip");
			$svyfault=$this->db->FetchField("fault");
			$tf='-';
		}

		$h=.125;
		$w1=0.50;
		$w3=0.40;
		$w2=0.36;
		$b=1;
		$left=3.75;
		$this->Ln(); $this->hdrheight+=$h;

		$this->SetFillColor(110, 174, 126);
		$this->Cell($left);
		$this->Cell($w2, $h, "Svy", $b, 0, 'C', true);
		$this->Cell($w1, $h, "Depth", $b, 0, 'C', true);
		$this->Cell($w2, $h, "Inc", $b, 0, 'C', true);
		$this->Cell($w3, $h, "Azm", $b, 0, 'C', true);
		$this->Cell($w1, $h, "TVD", $b, 0, 'C', true);
		$this->Cell($w1, $h, "VS", $b, 0, 'C', true);
		$this->Cell($w2, $h, "DL", $b, 0, 'C', true);
		$this->Cell($w1, $h, "TF", $b, 0, 'C', true);
		$this->Cell($w1, $h, "TCL", $b, 0, 'C', true);
		$this->Cell($w2, $h, "Pos-TCL", $b, 0, 'C', true);
		$this->Cell($w1, $h, "TOT", $b, 0, 'C', true);
		$this->Cell($w1, $h, "BOT", $b, 0, 'C', true);
		$this->Cell($w2, $h, "DIP", $b, 0, 'C', true);
		$this->Cell($w2, $h, "FAULT", $b, 0, 'C', true);
		$this->Ln(); $this->hdrheight+=$h;

		$this->SetFillColor(230, 230, 190);
		$this->Cell($left);
		$this->Cell($w2, $h, sprintf("%d",$numsvys-1), $b, 0, 'R', true);
		$this->Cell($w1, $h, sprintf("%0.2f",$svymd), $b, 0, 'R', true);
		$this->Cell($w2, $h, sprintf("%0.2f",$svyinc), $b, 0, 'R', true);
		$this->Cell($w3, $h, sprintf("%0.2f",$svyazm), $b, 0, 'R', true);
		$this->Cell($w1, $h, sprintf("%0.2f",$svytvd), $b, 0, 'R', true);
		$this->Cell($w1, $h, sprintf("%0.2f",$svyvs), $b, 0, 'R', true);
		$this->Cell($w2, $h, sprintf("%0.2f",$svydl), $b, 0, 'R', true);
		$this->Cell($w1,$h,$tf,$b,0,'R',true);
		$this->Cell($w1, $h, sprintf("%0.2f",$tlc), $b, 0, 'R', true);
		$this->Cell($w2, $h, sprintf("%0.2f",$tlc-$svytvd), $b, 0, 'R', true);
		$this->Cell($w1, $h, sprintf("%0.2f",$svytot), $b, 0, 'R', true);
		$this->Cell($w1, $h, sprintf("%0.2f",$svybot), $b, 0, 'R', true);
		$this->Cell($w2, $h, sprintf("%0.2f",$svydip), $b, 0, 'R', true);
		$this->Cell($w2, $h, sprintf("%0.2f",$svyfault), $b, 0, 'R', true);
		$this->Ln(); $this->hdrheight+=$h;

		$this->SetFillColor(250, 148, 148);
		$this->db->DoQuery("SELECT * FROM surveys WHERE plan>0 ORDER BY md ASC");

		if($this->db->FetchNumRows()>0) {
			$i=0;
			while ($this->db->FetchRow()) {
				$omd= $this->db->FetchField("md");
				if($totid){
					$query = "select tot from addformsdata where md=$omd and infoid=$totid;";
				 	$db2->DoQuery($query);
					$db2->FetchRow();
					$tot =sprintf("%.2f", $db2->FetchField("tot"));
				}
				if($botid){
					$query = "select tot from addformsdata where md=$omd and infoid=$botid;";
					$db2->DoQuery($query);
					$db2->FetchRow();
					$bot =sprintf("%.2f", $db2->FetchField("tot"));
				}				
				$svymd=$this->db->FetchField("md");
				$svyinc=$this->db->FetchField("inc");
				$svyazm=$this->db->FetchField("azm");
				$svytvd=$this->db->FetchField("tvd");
				$svyvs=$this->db->FetchField("vs");
				$svydl=$this->db->FetchField("dl");
				$svyca=$this->db->FetchField("ca");
				$svycd=$this->db->FetchField("cd");
				$svyns=$this->db->FetchField("ns");
				$svyew=$this->db->FetchField("ew");
				$tlc = $this->db->FetchField("tot");
				$svytot=$tot;
				$svybot=$bot;
				$svydip=$this->db->FetchField("dip");
				$svyfault=$this->db->FetchField("fault");
				$tf='-';
				$this->Cell($left);
				if($i==0) $this->Cell($w2, $h, "BPrj", $b, 0, 'R', true);
				else $this->Cell($w2, $h, "PAhd", $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svymd), $b, 0, 'R', true);
				$this->Cell($w2, $h, sprintf("%0.2f",$svyinc), $b, 0, 'R', true);
				$this->Cell($w3, $h, sprintf("%0.2f",$svyazm), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svytvd), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svyvs), $b, 0, 'R', true);
				$this->Cell($w2, $h, sprintf("%0.2f",$svydl), $b, 0, 'R', true);
				$this->Cell($w1,$h,$tf,$b,0,'R',true);
				$this->Cell($w1, $h, sprintf("%0.2f",$tlc), $b, 0, 'R', true);
				$this->Cell($w2, $h, sprintf("%0.2f",$tlc-$svytvd), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svytot), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svybot), $b, 0, 'R', true);
				$this->Cell($w2, $h, sprintf("%0.2f",$svydip), $b, 0, 'R', true);
				$this->Cell($w2, $h, sprintf("%0.2f",$svyfault), $b, 0, 'R', true);
				$this->Ln(); $this->hdrheight+=$h;
				$i++;
			}
		}


	}

	function ReportProjections() {
		$db2=$this->db2;
		$h=0.125;
		$w1=0.50;
		$w3=0.40;
		$w2=0.36;
		$b=1;
		$left=3.75;
		$this->SetFillColor(250, 148, 148);
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
		$this->db->DoQuery("SELECT * FROM projections ORDER BY md ASC");
		$this->projection_count = $this->db->FetchNumRows();
		if($this->projection_count>0) {
			$i=1;
			while ($this->db->FetchRow()) {
				$id = $this->db->FetchField("id");
				if($totid){
							$query = "select tot from addformsdata where projid=$id and infoid=$totid;";
							$db2->DoQuery($query);
							$db2->FetchRow();
							$tot =sprintf("%.2f", $db2->FetchField("tot"));
						}
				if($botid){
					$query = "select tot from addformsdata where projid=$id and infoid=$botid;";
					$db2->DoQuery($query);
					$db2->FetchRow();
					$bot =sprintf("%.2f", $db2->FetchField("tot"));
				}
				$svymd=$this->db->FetchField("md");
				$svyinc=$this->db->FetchField("inc");
				$svyazm=$this->db->FetchField("azm");
				$svytvd=$this->db->FetchField("tvd");
				$svyvs=$this->db->FetchField("vs");
				$svydl=$this->db->FetchField("dl");
				$svyca=$this->db->FetchField("ca");
				$svycd=$this->db->FetchField("cd");
				$svyns=$this->db->FetchField("ns");
				$svyew=$this->db->FetchField("ew");
				$tlc = $this->db->FetchField("tot");
				$ptype = strtoupper($this->db->FetchField('ptype'));
				$tf = $this->db->FetchField('tf');
				if(!$tf){
					$tf='-';
				}
				$svytot=$tot;
				$svybot=$bot;
				$svydip=$this->db->FetchField("dip");
				$svyfault=$this->db->FetchField("fault");

				$this->Cell($left);
				$this->Cell($w2, $h, "$ptype"."$i", $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svymd), $b, 0, 'R', true);
				$this->Cell($w2, $h, sprintf("%0.2f",$svyinc), $b, 0, 'R', true);
				$this->Cell($w3, $h, sprintf("%0.2f",$svyazm), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svytvd), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svyvs), $b, 0, 'R', true);
				if($ptype == 'SLD') $this->Cell($w2, $h, '-', $b, 0, 'R', true);
				else $this->Cell($w2, $h, sprintf("%0.2f",$svydl), $b, 0, 'R', true);
				$this->Cell($w1,$h,$tf,$b,0,'R',true);
				$this->Cell($w1, $h, sprintf("%0.2f",$tlc), $b, 0, 'R', true);
				$this->Cell($w2, $h, sprintf("%0.2f",$tlc-$svytvd), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svytot), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svybot), $b, 0, 'R', true);
				$this->Cell($w2, $h, sprintf("%0.2f",$svydip), $b, 0, 'R', true);
				$this->Cell($w2, $h, sprintf("%0.2f",$svyfault), $b, 0, 'R', true);
				$this->Ln(); $this->hdrheight+=$h;
				$i++;
			}
		}
		$this->Ln(); $this->hdrheight+=$h;
	}
	
	function ReportAnnos(){
		require_once 'classes/Annotation.class.php';
		require_once 'annotation_lib.php';
		if(!isset($anno_loader)){
			$annos_loader = new Annotation($_REQUEST);
		}
		$annos = $annos_loader->get_all('rows');
		AnnotationsCalcInZone($this->db,$annos);
		if(count($annos)){
			$sdepth=0;
			$edepth=0;
			$h=0.125;
			$w1=0.50;
			$w3=0.60;
			$w2=0.36;
			$b=1;
			$left=2.2;
			$i=0;
			while($i<(37-(count($annos)+$this->projection_count))){
				$this->Ln(); $this->hdrheight+=$h;
				$i++;
			}
			$this->SetFillColor(110, 174, 126);
			$this->Cell($left);
			$this->Cell($w2, $h, "#", $b, 0, 'C', true);
			$this->Cell($w3, $h, "Date", $b, 0, 'C', true);
			$this->Cell($w1, $h, "Time", $b, 0, 'C', true);
			if($annos_loader->showCol('md'))$this->Cell($w2, $h, "MD", $b, 0, 'C', true);
			if($annos_loader->showCol('footage'))$this->Cell($w2, $h, "FTG", $b, 0, 'C', true);
			if($annos_loader->showCol('inc'))$this->Cell($w2, $h, "INC", $b, 0, 'C', true);
			if($annos_loader->showCol('azm'))$this->Cell($w2, $h, "AZM", $b, 0, 'C', true);
			if($annos_loader->showCol('avg_dip'))$this->Cell($w1, $h, "AVG DIP", $b, 0, 'C', true);
			if($annos_loader->showCol('avg_gas'))$this->Cell($w1, $h, "AVG GAS", $b, 0, 'C', true);
			if($annos_loader->showCol('avg_rop'))$this->Cell($w1, $h, "AVG ROP", $b, 0, 'C', true);
			if($annos_loader->showCol('in_zone'))$this->Cell($w1, $h, "In-Zone", $b, 0, 'C', true);
			if($annos_loader->showCol('comment'))$this->Cell(2.5, $h, "Comment", $b, 0, 'C', true);
			$this->Ln(); $this->hdrheight+=$h;
			$cnt=1;
			$this->SetFillColor(230, 230, 190);
			foreach($annos as $anno){
				$edepth = $anno['md'];
				$avgs = $annos_loader->get_avgs($sdepth,$edepth);
				$sdepth = $edepth;
				$this->Cell($left);
				$this->Cell($w2, $h, $cnt, $b, 0, 'C', true);
				$this->Cell($w3, $h, date('m/d/Y', strtotime($anno['assigned_date'])), $b, 0, 'C', true);
				$this->Cell($w1, $h, date('h:i a', strtotime($anno['assigned_date'])), $b, 0, 'C', true);
				if($annos_loader->showCol('md'))$this->Cell($w2, $h, sprintf("%01.0f",$anno['md']), $b, 0, 'C', true);
				if($annos_loader->showCol('footage'))$this->Cell($w2, $h, sprintf("%01.0f",$avgs['footage']), $b, 0, 'C', true);
				if($annos_loader->showCol('inc'))$this->Cell($w2, $h, sprintf("%01.2f",$anno['inc']), $b, 0, 'C', true);
				if($annos_loader->showCol('azm'))$this->Cell($w2, $h, sprintf("%01.2f",$anno['azm']), $b, 0, 'C', true);
				if($annos_loader->showCol('avg_dip'))$this->Cell($w1, $h,sprintf("%01.2f",$avgs['dip']), $b, 0, 'C', true);
				if($annos_loader->showCol('avg_gas'))$this->Cell($w1, $h, sprintf("%01.2f",$avgs['gas']), $b, 0, 'C', true);
				if($annos_loader->showCol('avg_rop'))$this->Cell($w1, $h, sprintf("%01.2f",$avgs['rop']), $b, 0, 'C', true);
				if($annos_loader->showCol('in_zone'))$this->Cell($w1, $h, $anno['inzn'], $b, 0, 'C', true);
				if($annos_loader->showCol('comment'))$this->Cell(2.5, $h, $anno['detail_assignments'], $b, 0, 'C', true);
				$this->Ln(); $this->hdrheight+=$h;
				$cnt++;
			}
		}

		// check if rotate/slide data exists and generate percentages to show on the bottom

		$this->db->DoQuery('select count(*) from rotslide');
		if($this->db->FetchNumRows() > 0)
		{
			$this->db->FetchRow();
			$numrs = $this->db->FetchField('count');

			if($numrs > 0)
			{
				$this->db->DoQuery('select sum(rotendmd - rotstartmd) totrot, ' .
					'sum(slideendmd - slidestartmd) totslide from rotslide');
				if($this->db->FetchNumRows() > 0)
				{
					$this->db->FetchRow();
					$totrot = $this->db->FetchField('totrot');
					$totslide = $this->db->FetchField('totslide');
					$tot = (floatval($totrot) + floatval($totslide));
					
					if($tot > 1.0)
					{
						$prcrot = intval(round((floatval($totrot)/$tot) * 100.0,0));
						$prcslide = 100 - $prcrot;

						$this->SetY(8.04);
						$this->Cell(2.20);
						$this->SetFillColor(190,190,255);
						$this->Cell(0.19,0.12,'',1,0,'L',true);
						$this->Cell(1.2,0.15,"Slide {$prcslide}% {$totslide}'",0,0,'L',false);
						$this->Cell(0.19,0.12,'',1,0,'L',false);
						$this->Cell(1.2,0.15,"Rotate {$prcrot}% {$totrot}'",0,0,'L',false);
					}
				}
			}
		}
	}
	
	function PlotImage()
	{
		global $imgfn1, $imgfn2, $imgfn3, $imgfn4, $scaleright,$wb_show_forms,$refwellname,$additionlgraphs;
		// $this->Image($imgfn3, 0.2, $this->hdrheight+.07, 1.0, 3.84);
		// $this->Image($imgfn1, 1.2, $this->hdrheight, 8.5, 4.0);
		// $this->Image($imgfn2, 9.8, $this->hdrheight+.03, 0.8, 3.94);

		$y1=$this->hdrheight+0.16;
		$y2=$this->hdrheight;
		$y3=$this->hdrheight+0.03;

		$h1=8.25-$this->hdrheight-.52 - 1.1;
		$h2=8.25-$this->hdrheight-.2 - 1.1;
		$h3=8.25-$this->hdrheight-.26 - 1.1;
		$fn5 = generatepdfimageleft('pdf',300,900,null,null,$wb_show_forms);
		//$this->Image($imgfn2, 10.0, $y3, 0.8, $h3);
		if(filesize($imgfn4)>0) $this->Image($imgfn4, 2.375, 7.0, 8.5, 1.0);
		//foreach($additionlgraphs as $value){
		//	if(filesize($value)>0) $this->Image($value,2.375,7.0,8.5,1.0);
		//}
		$this->Image($imgfn1, 2.375, $y2, 8.5, $h2);
		if(filesize($fn5)>0) $this->Image($fn5, 0.1, $y1,2.2, 0);
		

		$left=0.01;
		$this->Cell($left);
		$this->SetFont('','',7);
		// $this->Cell(0.930, .175, "$scaleright", 0, 0, 'R', false);		
		$this->Cell(0.465, .175, "0", 0, 0, 'L', false);
		$this->Cell(1.5, .175, "$scaleright", 0, 0, 'R', false);
		$this->SetTextColor(112,112,112);
		$this->SetFont('Arial','',13);
		$wrapped = $this->smart_wordwrap($refwellname,11,"-|-");
		$namear = explode("-|-",$wrapped);
		$startp = 1.7;
		foreach($namear as $nameel){
			$this->Text(1.2,$startp, "$nameel");
			$startp+=0.2;
		}
		$this->SetTextColor(0,0,0);
		$this->SetFont('Arial','',8);
	
	}
	
	function smart_wordwrap($string, $width = 75, $break = "\n") {
    // split on problem words over the line length
    $pattern = sprintf('/([^ ]{%d,})/', $width);
    $output = '';
    $words = preg_split($pattern, $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

    foreach ($words as $word) {
        if (false !== strpos($word, ' ')) {
            // normal behaviour, rebuild the string
            $output .= $word;
        } else {
            // work out how many characters would be on the current line
            $wrapped = explode($break, wordwrap($output, $width, $break));
            $count = $width - (strlen(end($wrapped)) % $width);

            // fill the current line and add a break
            $output .= substr($word, 0, $count) . $break;

            // wrap any remaining characters from the problem word
            $output .= wordwrap(substr($word, $count), $width, $break, true);
        }
    }

    // wrap the final output
    return wordwrap($output, $width, $break);
}
	
}

$pdf=new PDF("L", "in", "Letter");
$pdf->scaleright=$scaleright;
$pdf->SetFont('Arial','',8);
$pdf->AddPage();
$pdf->db=new dbio($seldbname);
$pdf->db->OpenDb();
$pdf->db2=new dbio($seldbname);
$pdf->db2->OpenDb();
$pdf->ReportHeader($now);
$pdf->PlotImage();
$pdf->ReportSurveys();
$pdf->ReportProjections();
$pdf->ReportAnnos();
$pdf->db->CloseDb();
$pdf->db2->CloseDb();
// if($tofile>0) $pdf->Output($filename, "F");
// else $pdf->Output($filename, "I");

$pdf->Output($filename, "F");
if($approve_send){
	//echo "inserting into table";
	$db = new dbio($seldbname);
	$db->OpenDb();
	$sql = "insert into reports (report_type,report_file,approved) values ('LateralPlot','$filename',1)";
	$db->DoQuery($sql);
	$db->CloseDb();
}
if($tofile==0 && !$approve_send) {
	header("Pragma: public"); // required
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private",false); // required for certain browsers
	header("Content-Type: application/force-download");
	header("Content-Disposition: attachment; filename=\"".basename($filename)."\";" );
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".filesize($filename));
	readfile("$filename");
	#if(file_exists($imgfn1)) unlink($imgfn1);
	#if(file_exists($imgfn2)) unlink($imgfn2);
	#if(file_exists($imgfn3)) unlink($imgfn3);
	#if(file_exists($imgfn4)) unlink($imgfn4);
	exit(); 
    // header("Content-type: application/pdf");
    // header("Content-Transfer-Encoding: Binary");
    // header("Content-length: ".filesize($filename));
    // header('Content-Disposition: attachment; filename="'.basename($filename).'"');
    // readfile("$filename");
}
#if(file_exists($imgfn1)) unlink($imgfn1);
#if(file_exists($imgfn2)) unlink($imgfn2);
#if(file_exists($imgfn3)) unlink($imgfn3);
#if(file_exists($imgfn4)) unlink($imgfn4);
?>
