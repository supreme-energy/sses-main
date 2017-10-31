<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?php
require('/usr/share/php/fpdf/fpdf.php');
require_once("dbio.class.php");
if(strlen($seldbname)<=0)	$seldbname=$_GET['seldbname'];
if(strlen($filename)<=0)	$filename=$_GET['filename'];
if(strlen($showxy)<=0)	$showxy=$_GET['showxy'];
$tofile=0;
if(strlen($filename)>0) $tofile=1;
else $filename=sprintf("/tmp/%s.surveys.pdf", $seldbname);
if(file_exists($filename)) unlink($filename);

$db=new dbio($seldbname);
$db->OpenDb();
include "readappinfo.inc.php";
include "readwellinfo.inc.php";
$db->CloseDb();

class PDF extends FPDF
{
	var $db;
	var $db2;
	var $doxy;
	var $survey_northing, $survey_easting;
	function LoadData($dbn)
	{
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
		$this->db->DoQuery("SELECT * FROM surveys ORDER BY md ASC;");
		$num=$this->db->FetchNumRows(); 
    	$data=array();
		$i=0;
		for($i=0; $i < $num; $i++) {
			$this->db->FetchRow();
			$plan=sprintf("%.2f", $this->db->FetchField("plan"));
			$tot='NF';
			$bot='NF';
			$omd = $this->db->FetchField("md");
			$id = $this->db->FetchField("id");
			if($totid){
				if($plan){
					$query = "select tot from addformsdata where md=$omd and infoid=$totid;";
				}else{
					$query = "select tot from addformsdata where svyid=$id and infoid=$totid;";
				}
				 
				$db2->DoQuery($query);
				$db2->FetchRow();
				$tot =sprintf("%.2f", $db2->FetchField("tot"));
			}
			if($botid){
				if($plan){
					$query = "select tot from addformsdata where md=$omd and infoid=$botid;";
				}else{
					$query = "select tot from addformsdata where svyid=$id and infoid=$botid;";
				}
				$db2->DoQuery($query);
				$db2->FetchRow();
				$bot =sprintf("%.2f", $db2->FetchField("tot"));
			}
			$md=sprintf("%.2f",$omd );
			$inc=sprintf("%.2f", $this->db->FetchField("inc"));
			$azm=sprintf("%.2f", $this->db->FetchField("azm"));
			$tvd=sprintf("%.2f", $this->db->FetchField("tvd"));
			$ns=sprintf("%.2f", $this->db->FetchField("ns"));
			$ew=sprintf("%.2f", $this->db->FetchField("ew"));
			$vs=sprintf("%.2f", $this->db->FetchField("vs"));
			$ca=sprintf("%.2f", $this->db->FetchField("ca"));
			$cd=sprintf("%.2f", $this->db->FetchField("cd"));
			$dl=sprintf("%.2f", $this->db->FetchField("dl"));
			$tcl = sprintf("%.2f", $this->db->FetchField("tot"));
			$dip=sprintf("%.2f", $this->db->FetchField("dip"));
			
			$tclw=sprintf("%.2f", $tcl-$tvd);		
			if($this->doxy==1) {
				$cd=sprintf("%.0f", $this->survey_northing+$ns);
				$ca=sprintf("%.0f", $this->survey_easting+$ew);
			}
			$tf='-';
			$line="$md;$inc;$azm;$tvd;$vs;$ns;$ew;$cd;$ca;$dl;$tcl;$tclw;$tot;$bot;$dip;$plan;$tf;svy";
        	$data[]=explode(';',chop($line));
		} 
    	return $data;
	}
	function LoadProjData($dbn)
	{
		$db2=$this->db2;
		$this->db->DoQuery("SELECT * FROM projections ORDER BY md ASC;");
		$num=$this->db->FetchNumRows(); 
    	$data=array();
		$i=0;
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
		for($i=0; $i < $num; $i++) {
			$this->db->FetchRow();
			$omd = $this->db->FetchField("md");
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
			$md=sprintf("%.2f", $this->db->FetchField("md"));
			$inc=sprintf("%.2f", $this->db->FetchField("inc"));
			$azm=sprintf("%.2f", $this->db->FetchField("azm"));
			$tvd=sprintf("%.2f", $this->db->FetchField("tvd"));
			$ns=sprintf("%.2f", $this->db->FetchField("ns"));
			$ew=sprintf("%.2f", $this->db->FetchField("ew"));
			$vs=sprintf("%.2f", $this->db->FetchField("vs"));
			$ca=sprintf("%.2f", $this->db->FetchField("ca"));
			$cd=sprintf("%.2f", $this->db->FetchField("cd"));
			$dl=sprintf("%.2f", $this->db->FetchField("dl"));
			$tcl = sprintf("%.2f", $this->db->FetchField("tot"));
			$dip=sprintf("%.2f", $this->db->FetchField("dip"));
			$plan=sprintf("%.2f", $this->db->FetchField("plan"));
			$ptype=$this->db->FetchField('ptype');
			$tf = $this->db->FetchField("tf");
			if($tf){
				$tf  =$tf;
			}else{
				$tf  ='-';
			}
			
			$tclw=sprintf("%.2f", $tcl-$tvd);		
			
			if($this->doxy==1) {
				$cd=sprintf("%.0f", $this->survey_northing+$ns);
				$ca=sprintf("%.0f", $this->survey_easting+$ew);
			}
			$line="$md;$inc;$azm;$tvd;$vs;$ns;$ew;$cd;$ca;$dl;$tcl;$tclw;$tot;$bot;$dip;$plan;$tf;$ptype";
        	$data[]=explode(';',chop($line));
		} 
    	return $data;
	}

	function ReportHeader()
	{
		$this->db->DoQuery("SELECT * FROM wellinfo;");
		if($this->db->FetchNumRows() > 0)
		{
			$this->db->FetchRow();
			$ud_opname=$this->db->FetchField("operatorname");
		}

		$this->Image("./logo.jpg", 15, 10, 10, 10);

		$this->SetFont('','B',14);
		$this->Cell(0, 4, $ud_opname, 0, 0, 'C', false);
		$this->Ln();
		$this->Cell(0, 4, "Directional Survey Report", 0, 0, 'C', false);
		$this->Ln();
		$this->Ln();
		$this->SetFont('','',8);
	}

	function SurveyHeader()
	{
		$h=4;
		$w1=45;
		$w2=40;

		$this->db->DoQuery("SELECT * FROM wellinfo;");
		if($this->db->FetchNumRows() > 0) {
			$this->db->FetchRow();
			$ud_timedate=date("m/d/Y");
			$ud_opname=$this->db->FetchField("operatorname");
			if( !strlen($ud_opname) )
				$ud_opname="Polaris";
			$opname=$this->db->FetchField("operatorname");
			$jobnumber=$this->db->FetchField("jobnumber");
			$wellname=$this->db->FetchField("wellborename");
			$opcontact1=$this->db->FetchField("operatorcontact1");
			$rigid=$this->db->FetchField("rigid");
			$startdate=$this->db->FetchField("startdate");
			$wellid=$this->db->FetchField("wellid");
			$enddate=$this->db->FetchField("enddate");
			$field=$this->db->FetchField("field");
			$declination=$this->db->FetchField("declination");
			$location=$this->db->FetchField("location");
			$propazm=$this->db->FetchField("propazm");
			$stateprov=$this->db->FetchField("stateprov");
			$correction=$this->db->FetchField("correction");
			$county=$this->db->FetchField("county");
			$country=$this->db->FetchField("country");
		}
		$this->Cell($w1, $h, "Company:", 0, 0, 'R', false);
		$this->Cell($w1, $h, $opname, 0, 0, 'L', false);
		$this->Cell($w1, $h, "Job Number:", 0, 0, 'R', false);
		$this->Cell($w1, $h, $jobnumber, 0, 0, 'L', false);
		$this->Ln();

		$this->Cell($w1, $h, "Well Name:", 0, 0, 'R', false);
		$this->Cell($w1, $h, $wellname, 0, 0, 'L', false);
		$this->Cell($w1, $h, "", 0, 0, 'R', false);
		$this->Cell($w1, $h, "", 0, 0, 'L', false);
		$this->Ln();

		$this->Cell($w1, $h, "Rig ID:", 0, 0, 'R', false);
		$this->Cell($w1, $h, $rigid, 0, 0, 'L', false);
		$this->Cell($w1, $h, "Start Date:", 0, 0, 'R', false);
		$this->Cell($w1, $h, $startdate, 0, 0, 'L', false);
		$this->Ln();

		$this->Cell($w1, $h, "API/UWI:", 0, 0, 'R', false);
		$this->Cell($w1, $h, $wellid, 0, 0, 'L', false);
		$this->Cell($w1, $h, "End Date:", 0, 0, 'R', false);
		$this->Cell($w1, $h, $enddate, 0, 0, 'L', false);
		$this->Ln();

		$this->Cell($w1, $h, "Field:", 0, 0, 'R', false);
		$this->Cell($w1, $h, $field, 0, 0, 'L', false);
		// $this->Cell($w1, $h, "Declination:", 0, 0, 'R', false);
		// $this->Cell($w1, $h, $declination, 0, 0, 'L', false);
		$this->Ln();

		$this->Cell($w1, $h, "Location:", 0, 0, 'R', false);
		$this->Cell($w1, $h, $location, 0, 0, 'L', false);
		$this->Cell($w1, $h, "Proposed Azimuth:", 0, 0, 'R', false);
		$this->Cell($w1, $h, $propazm, 0, 0, 'L', false);
		$this->Ln();

		$this->Cell($w1, $h, "State/Prov:", 0, 0, 'R', false);
		$this->Cell($w1, $h, $stateprov, 0, 0, 'L', false);
		$this->Cell($w1, $h, "North Reference:", 0, 0, 'R', false);
		$this->Cell($w1, $h, $correction, 0, 0, 'L', false);
		$this->Ln();

		$this->Cell($w1, $h, "County:", 0, 0, 'R', false);
		$this->Cell($w1, $h, $county, 0, 0, 'L', false);
		$this->Ln();
		$this->Ln();
	}

	function Footer()
	{
			//Go to 1.5 cm from bottom
			$this->SetY(-15);
			//Select Arial italic 8
			$this->SetFont('Arial','I',8);
			//Print current and total page numbers
			$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}


	function SurveyTable($header1, $header2,$data)
	{
		$this->SetFillColor(240,240,220);
		$this->SetTextColor(0);
		$this->SetDrawColor(128,0,0);
		$this->SetLineWidth(.3);
		$this->SetFont('','B');

		$w2=array((10+20+16+16+20+20+18+18+18+16+14+10),(36+40),14);
		for($i=0;$i<count($header1);$i++)
			$this->Cell($w2[$i],4,$header1[$i],1,0,'C',true);
		$this->Ln();
		$w=array(10,20,16,16,20,20,18,18,18,16,14,10,18,20,18,20,14);
		for($i=0;$i<count($header2);$i++)
			$this->Cell($w[$i],4,$header2[$i],1,0,'C',true);
		$this->Ln();

		// $this->SetFillColor(224,235,255);
		$this->SetFillColor(255,255,220);
		$this->SetTextColor(0);
		$this->SetFont('');

		$fill=false;
		$i=0;
		$gotbprj=0;
		foreach($data as $row)
		{
			$plan=$row[15];
			if($plan==0) $svy=sprintf("%4d", $i);
			else {
				if(!$gotbprj)	{ $svy="BPrj"; $gotbprj=1; }
				else $svy="PAhd";
			}

			// $this->Cell($w[0],4,$i,'LRB',0,'L',$fill);
			
			$this->Cell($w[0],4,$svy,'LRB',0,'L',$fill);
			$this->Cell($w[1],4,$row[0],'LRB',0,'R',$fill);
			$this->Cell($w[2],4,$row[1],'LRB',0,'R',$fill);
			$this->Cell($w[3],4,$row[2],'LRB',0,'R',$fill);
			$this->Cell($w[4],4,$row[3],'LRB',0,'R',$fill);
			$this->Cell($w[5],4,$row[4],'LRB',0,'R',$fill);
			$this->Cell($w[6],4,$row[5],'LRB',0,'R',$fill);
			$this->Cell($w[7],4,$row[6],'LRB',0,'R',$fill);
			$this->Cell($w[8],4,$row[7],'LRB',0,'R',$fill);
			$this->Cell($w[9],4,$row[8],'LRB',0,'R',$fill);

			if($i>0)
				$this->Cell($w[10],4,$row[9],'LRB',0,'R',$fill);
			else
				$this->Cell($w[10],4," ",'LRB',0,'R',$fill);
			
			$this->Cell($w[11],4,$row[16],'LRB',0,'R',$fill);
			$this->Cell($w[12],4,$row[10],'LRB',0,'R',$fill);
			$this->Cell($w[13],4,$row[11],'LRB',0,'R',$fill);
			$this->Cell($w[14],4,$row[12],'LRB',0,'R',$fill);
			$this->Cell($w[15],4,$row[13],'LRB',0,'R',$fill);
			$this->Cell($w[16],4,$row[14],'LRB',0,'R',$fill);

			$this->Ln();
			$fill=!$fill;
			$i++;
			if($i>0 && $i%32==0) {
				$this->SetDrawColor(128,0,0);
				$this->AddPage("L");
				$this->ReportHeader();
				$this->SurveyHeader();
				for($j=0;$j<count($header2);$j++)
					$this->Cell($w[$j],4,$header2[$j],1,0,'C',true);
				$this->Ln();
			}
		}
		return $i;
	}

	function ReportProjections($lastpg, $data) {
		$w2=array((10+20+16+16+20+20+18+18+18+16+14+10),(18+20),(18+20),14);
		// for($i=0;$i<count($header1);$i++)
			// $this->Cell($w2[$i],4,$header1[$i],1,0,'C',true);
		// $this->Ln();
		$w=array(10,20,16,16,20,20,18,18,18,16,14,10,18,20,18,20,14);
		// for($i=0;$i<count($header2);$i++)
			// $this->Cell($w[$i],4,$header2[$i],1,0,'C',true);
		// $this->Ln();

		// $this->SetFillColor(224,235,255);
		$this->SetFillColor(255,255,220);
		$this->SetTextColor(0);
		$this->SetFont('');

		if($lastpg%2!=0) $fill=true;
		else $fill=false;
		$pi=1;
		$i=$lastpg;
		foreach($data as $row)
		{
			$svy=strtoupper ($row[17])."$pi";
			
			// $this->Cell($w[0],4,$i,'LRB',0,'L',$fill);
			$this->Cell($w[0],4,$svy,'LRB',0,'L',$fill);
			$this->Cell($w[1],4,$row[0],'LRB',0,'R',$fill);
			$this->Cell($w[2],4,$row[1],'LRB',0,'R',$fill);
			$this->Cell($w[3],4,$row[2],'LRB',0,'R',$fill);
			$this->Cell($w[4],4,$row[3],'LRB',0,'R',$fill);
			$this->Cell($w[5],4,$row[4],'LRB',0,'R',$fill);
			$this->Cell($w[6],4,$row[5],'LRB',0,'R',$fill);
			$this->Cell($w[7],4,$row[6],'LRB',0,'R',$fill);
			$this->Cell($w[8],4,$row[7],'LRB',0,'R',$fill);
			$this->Cell($w[9],4,$row[8],'LRB',0,'R',$fill);
			$this->Cell($w[10],4,$row[9],'LRB',0,'R',$fill);
			
			$this->Cell($w[11],4,$row[16],'LRB',0,'R',$fill);
			$this->Cell($w[12],4,$row[10],'LRB',0,'R',$fill);
			$this->Cell($w[13],4,$row[11],'LRB',0,'R',$fill);
			$this->Cell($w[14],4,$row[12],'LRB',0,'R',$fill);
			$this->Cell($w[15],4,$row[13],'LRB',0,'R',$fill);
			$this->Cell($w[16],4,$row[14],'LRB',0,'R',$fill);
			$this->Ln();
			$fill=!$fill;
			$i++;
			$pi++;
			if($i>0 && $i%32==0) {
				$this->SetDrawColor(128,0,0);
				$this->AddPage("L");
				$this->ReportHeader();
				$this->SurveyHeader();
				for($j=0;$j<count($header2);$j++)
					$this->Cell($w[$j],4,$header2[$j],1,0,'C',true);
				$this->Ln();
			}
		}
	}

	function CopyrightInfo() {
		$this->SetTextColor(0);
		$this->Ln();
		$this->Ln();
		$this->SetFontSize('5');
		$copy = "Copyright";
		$this->Cell(2.0, 2, "Generated by: Subsurface Geological Tracking Analysis", 0, 1, 'L', false);
		$this->Cell(4.0, 2, "$copy 2010-2011 Digital Oil Tools", 0, 1, 'L', false);
	}
}

$header1=array('Survey Data', 'Target Tracker Section', ' ');
if($showxy==1)
	$header2=array('Svy', 'MD', 'Inc', 'Azm', 'TVD', 'VS', 'NS', 'EW', 'Northing', 'Easting', 'DL','TF', 'TCL', 'Pos to TCL', 'TOT', 'BOT', 'DIP');
else
	$header2=array('Svy', 'MD', 'Inc', 'Azm', 'TVD', 'VS', 'NS', 'EW', 'CD', 'CA', 'DL', 'TCL','TF', 'Pos to TCL', 'TOT', 'BOT', 'DIP');

$pdf=new PDF();
$pdf->AliasNbPages();
$pdf->doxy=$showxy;
$pdf->survey_northing=$survey_northing;
$pdf->survey_easting=$survey_easting;

$pdf->db=new dbio($seldbname);
$pdf->db->OpenDb();
$pdf->db2=new dbio($seldbname);
$pdf->db2->OpenDb();
$data=$pdf->LoadData($seldbname);
$pdata=$pdf->LoadProjData($seldbname);
$pdf->SetFont('Arial','',8);
$pdf->SetAutoPageBreak(1, .25*25.4);
$pdf->AddPage("L");
$pdf->ReportHeader();
$pdf->SetFont('Arial','',9);
$pdf->SurveyHeader();
$lastpg=$pdf->SurveyTable($header1,$header2,$data);
$pdf->ReportProjections($lastpg, $pdata);
$pdf->CopyrightInfo();
$pdf->db->CloseDb();
$pdf->db2->CloseDb();
if($tofile>0) $pdf->Output($filename, "F");
else $pdf->Output($filename, "I");
?>

