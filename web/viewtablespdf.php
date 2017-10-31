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
$tofile=0;
if(strlen($filename)>0) $tofile=1;
else $filename=sprintf("/tmp/%s-viewtables.pdf", $seldbname);

$filename=sprintf("/tmp/%s-viewtables.pdf", $seldbname);
if(file_exists($filename)) unlink($filename);

$db=new dbio($seldbname);
$db->OpenDb();
include "readwellinfo.inc.php";
$db->CloseDb();

class PDF extends FPDF
{
	var $db;
	var $controltot, $controlbot;
	function LoadData()
	{
		global $controltot, $controlbot;
		$db2 = $this->db2;
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
		$this->db->DoQuery("SELECT * FROM controllogs ORDER BY tablename");
		if ($this->db->FetchRow()) {
			$controltot=$this->db->FetchField("tot");
			$controlbot=$this->db->FetchField("bot");
		}
    	$data=array();
		$this->db->DoQuery("SELECT * FROM welllogs ORDER BY startmd DESC;");
		$num=$this->db->FetchNumRows(); 
		for($i=0; $i < $num && $this->db->FetchRow(); $i++) {
			$id=$this->db->FetchField("id");
			$lkmd = $this->db->FetchField("endmd");
			if($totid){
				$query = "select tot from addformsdata where md=$lkmd and infoid=$totid;";
			 	$db2->DoQuery($query);
				$db2->FetchRow();
				$tot =sprintf("%.2f", $db2->FetchField("tot"));
			}
			if($botid){
				$query = "select tot from addformsdata where md=$lkmd and infoid=$botid;";
				$db2->DoQuery($query);
				$db2->FetchRow();
				$bot =sprintf("%.2f", $db2->FetchField("tot"));
			}	
			$startmd=sprintf("%.2f", $this->db->FetchField("startmd"));
			$endmd=sprintf("%.2f", $lkmd);
			$starttvd=sprintf("%.2f", $this->db->FetchField("starttvd"));
			$endtvd=sprintf("%.2f", $this->db->FetchField("endtvd"));
			$startvs=sprintf("%.2f", $this->db->FetchField("startvs"));
			$endvs=sprintf("%.2f", $this->db->FetchField("endvs"));
			$startdepth=sprintf("%.2f", $this->db->FetchField("startdepth"));
			$enddepth=sprintf("%.2f", $this->db->FetchField("enddepth"));
			$tcl=sprintf("%.2f", $this->db->FetchField("tot"));
			$tpos=sprintf("%.2f", $tcl-$endtvd);
			$dip=sprintf("%.2f", $this->db->FetchField("dip"));
			$fault=sprintf("%.2f", $this->db->FetchField("fault"));
			$line="$id;$startmd;$endmd;$starttvd;$endtvd;$startvs;$endvs;$tcl;$tpos;$tot;$bot;$dip;$fault";
			$data[]=explode(';',chop($line));
		} 
    	return $data;
	}

	function Header()
	{
		$h=.16;
		$w=1.5;
		$this->SetMargins(.5,.5,.5);
		$this->SetFont('Arial','',8);
		$this->db->DoQuery("SELECT * FROM wellinfo;");
		if($this->db->FetchNumRows() > 0) {
			$this->db->FetchRow();
			$timedate=date("m/d/Y H:i:s");
			$opname=$this->db->FetchField("operatorname");
			$wellname=$this->db->FetchField("wellborename");
			$rigid=$this->db->FetchField("rigid");
			$wellid=$this->db->FetchField("wellid");
		}
		$this->Cell(0, $h, "Tables Imported", 0, 0, 'C', false);
		$this->Ln();
		$this->Ln();
		$this->Cell($w, $h, "Company:", 0, 0, 'R', false);
		$this->Cell($w, $h, $opname, 0, 0, 'L', false);
		$this->Cell($w, $h, "Rig ID:", 0, 0, 'R', false);
		$this->Cell($w, $h, $rigid, 0, 0, 'L', false);
		$this->Ln();
		$this->Cell($w, $h, "Well Name:", 0, 0, 'R', false);
		$this->Cell($w, $h, $wellname, 0, 0, 'L', false);
		$this->Cell($w, $h, "API/UWI:", 0, 0, 'R', false);
		$this->Cell($w, $h, $wellid, 0, 0, 'L', false);
		$this->Ln();
		$this->Cell($w, $h, "Date/Time:", 0, 0, 'R', false);
		$this->Cell($w, $h, $timedate, 0, 0, 'L', false);
		$this->Ln();
	}

	function Footer()
	{
			$this->SetY(-.5); 
			$this->SetFont('Arial','I',8);
			$this->Cell(0,.16,'Page '.$this->PageNo().'/{nb}',0,0,'C'); //Print current and total page numbers
	}


	function PrintTable($header1,$data)
	{
		global $controltot, $controlbot;
		$this->SetFillColor(240,240,220);
		$this->SetTextColor(0);
		$this->SetDrawColor(128,0,0);
		$this->SetLineWidth(.01);
		$this->SetFont('Arial','B',8);

		$w=array(.3, .65, .65, .65, .65, .65, .65,     .65, .65,  .65, .65, .5, .5);
		$w2=array(.3, (.65+.65), (.65+.65), (.65+.65), .65, .65, .65,.65, .5, .5);
		$h=.16;
		for($i=0;$i<count($header1);$i++) {
			if($i==4||$i==5) $this->SetFillColor(240,240,220);
			else if($i==6) $this->SetFillColor(230,240,230);
			else if($i==7) $this->SetFillColor(240,240,220);
			else if($i%2==0) $this->SetFillColor(240,240,220);
			else $this->SetFillColor(230,240,230);
			$this->Cell($w2[$i],$h,$header1[$i],1,0,'C',true);
		}
		$this->Ln();

		$this->SetTextColor(0);
		$this->SetFont('');

		$fill=true;
		$i=0;
		$count=count($data);
		foreach($data as $row)
		{
			$this->SetFillColor(250,250,230);
			// $this->Cell($w[0],$h,$row[0],'1',0,'L',$fill);
			$this->Cell($w[0],$h,$count-$i,'1',0,'L',$fill);

			$this->SetFillColor(240,250,240);
			$this->Cell($w[1],$h,$row[1],'1',0,'R',$fill);
			$this->Cell($w[2],$h,$row[2],'1',0,'R',$fill);

			$this->SetFillColor(250,250,230);
			$this->Cell($w[3],$h,$row[3],'1',0,'R',$fill);
			$this->Cell($w[4],$h,$row[4],'1',0,'R',$fill);

			$this->SetFillColor(240,250,240);
			$this->Cell($w[5],$h,$row[5],'1',0,'R',$fill);
			$this->Cell($w[6],$h,$row[6],'1',0,'R',$fill);

			$this->SetFillColor(250,250,230);
			$this->Cell($w[7],$h,$row[7],'1',0,'R',$fill);
			$this->Cell($w[8],$h,$row[8],'1',0,'R',$fill);

			$this->SetFillColor(240,250,240);
			$this->Cell($w[9],$h,$row[9],'1',0,'R',$fill);
			$this->Cell($w[10],$h,$row[10],'1',0,'R',$fill);

			$this->SetFillColor(250,250,230);
			$this->Cell($w[11],$h,$row[11],'1',0,'R',$fill);
			$this->Cell($w[12],$h,$row[12],'1',0,'R',$fill);
			$this->Ln();
			$i++;
			if($i>0 && $i%55==0) {
				$this->AddPage("P");
				// $this->Header();
				// for($j=0;$j<count($header1);$j++) $this->Cell($w[$j],$h,$header1[$j],1,0,'C',true);
				for($j=0;$j<count($header1);$j++) {
					if($j==4||$j==5) $this->SetFillColor(240,240,220);
					else if($j==6) $this->SetFillColor(230,240,230);
					else if($j==7) $this->SetFillColor(240,240,220);
					else if($j%2==0) $this->SetFillColor(240,240,220);
					else $this->SetFillColor(230,240,230);
					$this->Cell($w2[$j],$h,$header1[$j],1,0,'C',true);
				}
				$this->Ln();
			}
		}
		return $i;
	}

	function CopyrightInfo() {
		// $this->SetY(-.8); 
		$this->SetTextColor(0);
		$this->SetFontSize('5');
		$copy = "Copyright";
		$this->Cell(2.0, .1, "Generated by: Subsurface Geological Tracking Analysis", 0, 1, 'L', false);
		$this->Cell(4.0, .1, "$copy 2010-2011 Digital Oil Tools", 0, 1, 'L', false);
	}
}

$header1=array('DS', 'MD', 'TVD', 'VS', 'TCL', 'Pos to TCL', 'TOT', 'BOT', 'Dip','Fault');

$pdf=new PDF('P','in','letter');
$pdf->AliasNbPages();
$pdf->db=new dbio($seldbname);
$pdf->db->OpenDb();
$pdf->db2=new dbio($seldbname);
$pdf->db2->OpenDb();
$pdf->SetFont('Arial','',8);
// $pdf->SetAutoPageBreak(1, 0.2);
$pdf->AddPage("P");
$data=$pdf->LoadData();
$lastpg=$pdf->PrintTable($header1,$data);
$pdf->CopyrightInfo();
$pdf->db->CloseDb();
$pdf->db2->CloseDb();
// if($tofile>0) $pdf->Output($filename, "F");
// else $pdf->Output($filename, "I");
$pdf->Output($filename, "I");
?>

