<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?php
// $seldbname=$_GET['seldbname'];
if(strlen($seldbname)<=0)	$seldbname=$_GET['seldbname'];
if(strlen($filename)<=0)	$filename=$_GET['filename'];
$cutoff=$_GET['cutoff']; if($cutoff==""||$cutoff==null)	$cutoff=20;
require('/usr/share/php/fpdf/fpdf.php');
require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
include('readwellinfo.inc.php');
$db->CloseDb();

$tofile=0;
if(strlen($filename)>0) $tofile=1;
else $filename=sprintf("/tmp/%s.surveys.pdf", $seldbname);
if(file_exists($filename)) unlink($filename);
$imagefn="/tmp/$seldbname.surveyplotvs.png";
if(file_exists($imagefn)) unlink($imagefn);

exec("./sses_ps -d $seldbname -t pol -c $cutoff -f 14 -w 1536 -o $imagefn");

class PDF extends FPDF
{
	var $db;
	var $hdrheight=0.0;

	function ReportHeader()
	{
		$this->SetMargins(.25,0,0);
		$this->Image("./logofull.png", .5, .2, .7, .55);
		$this->hdrheight+=0.75;

		$this->SetFont('','B',18);
		$this->Cell(10, .17, "Polar Survey Plot", 0, 1, 'C', false);
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
		$this->SetFont('','',7);

		$this->Cell($w1, $h, "Company Name:", $b, 0, 'R', false);
		$this->Cell($w2, $h, $companyname, $b, 0, 'L', false);
		$this->Cell($w1, $h, "Field:", $b, 0, 'R', false);
		$this->Cell($w2, $h, $field, $b, 0, 'L', false);
		$this->Cell($w1, $h, "Job Number:", $b, 0, 'R', false);
		$this->Cell($w2, $h, $jobnumber, $b, 0, 'L', false);
		$this->Cell($w1, $h, " ", $b, 0, 'R', false);
		$this->Cell($w3, $h, " ", $b, 0, 'L', false);
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
		$this->db->DoQuery("SELECT count(id) FROM surveys WHERE plan=0");
		if($this->db->FetchNumRows()>0) {
			$this->db->FetchRow();
			$numsvys=$this->db->FetchField("count");
		}

		$this->db->DoQuery("SELECT * FROM surveys WHERE plan=0 ORDER BY md DESC LIMIT 1");
		if($this->db->FetchNumRows()>0) {
			$this->db->FetchRow();
			$svymd=$this->db->FetchField("md");
			$svyinc=$this->db->FetchField("inc");
			$svyazm=$this->db->FetchField("azm");
			$svytvd=$this->db->FetchField("tvd");
			$svyvs=$this->db->FetchField("vs");
			$svyvs=$this->db->FetchField("vs");
			$svyca=$this->db->FetchField("ca");
			$svycd=$this->db->FetchField("cd");
			$svyns=$this->db->FetchField("ns");
			$svyew=$this->db->FetchField("ew");
			$svytot=$this->db->FetchField("tot");
			$svybot=$this->db->FetchField("bot");
			$svydip=$this->db->FetchField("dip");
			$svyfault=$this->db->FetchField("fault");
		}

		$h=.125;
		$w1=0.56;
		$b=1;
		$left=0.75;
		$this->Ln(); $this->hdrheight+=$h;

		$this->SetFillColor(110, 174, 126);
		$this->Cell($left);
		$this->Cell($w1, $h, "Svy", $b, 0, 'C', true);
		$this->Cell($w1, $h, "Depth", $b, 0, 'C', true);
		$this->Cell($w1, $h, "Inc", $b, 0, 'C', true);
		$this->Cell($w1, $h, "Azm", $b, 0, 'C', true);
		$this->Cell($w1, $h, "N/-S", $b, 0, 'C', true);
		$this->Cell($w1, $h, "E/-W", $b, 0, 'C', true);
		$this->Cell($w1, $h, "Pos-TCL", $b, 0, 'C', true);
		$this->Cell($w1, $h, "TOT", $b, 0, 'C', true);
		$this->Cell($w1, $h, "BOT", $b, 0, 'C', true);
		$this->Cell($w1, $h, "DIP", $b, 0, 'C', true);
		$this->Cell($w1, $h, "FAULT", $b, 0, 'C', true);
		$this->Ln(); $this->hdrheight+=$h;

		$this->SetFillColor(230, 230, 190);
		$this->Cell($left);
		$this->Cell($w1, $h, sprintf("%d",$numsvys-1), $b, 0, 'R', true);
		$this->Cell($w1, $h, sprintf("%0.2f",$svymd), $b, 0, 'R', true);
		$this->Cell($w1, $h, sprintf("%0.2f",$svyinc), $b, 0, 'R', true);
		$this->Cell($w1, $h, sprintf("%0.2f",$svyazm), $b, 0, 'R', true);
		$this->Cell($w1, $h, sprintf("%0.2f",$svyns), $b, 0, 'R', true);
		$this->Cell($w1, $h, sprintf("%0.2f",$svyew), $b, 0, 'R', true);
		$this->Cell($w1, $h, sprintf("%0.2f",$svytot-$svytvd), $b, 0, 'R', true);
		$this->Cell($w1, $h, sprintf("%0.2f",$svytot), $b, 0, 'R', true);
		$this->Cell($w1, $h, sprintf("%0.2f",$svybot), $b, 0, 'R', true);
		$this->Cell($w1, $h, sprintf("%0.2f",$svydip), $b, 0, 'R', true);
		$this->Cell($w1, $h, sprintf("%0.2f",$svyfault), $b, 0, 'R', true);
		$this->Ln(); $this->hdrheight+=$h;

		$this->SetFillColor(250, 148, 148);
		$this->db->DoQuery("SELECT * FROM surveys WHERE plan>0 ORDER BY md ASC");
		if($this->db->FetchNumRows()>0) {
			$i=0;
			while ($this->db->FetchRow()) {
				$svymd=$this->db->FetchField("md");
				$svyinc=$this->db->FetchField("inc");
				$svyazm=$this->db->FetchField("azm");
				$svytvd=$this->db->FetchField("tvd");
				$svyvs=$this->db->FetchField("vs");
				$svyvs=$this->db->FetchField("vs");
				$svyca=$this->db->FetchField("ca");
				$svycd=$this->db->FetchField("cd");
				$svyns=$this->db->FetchField("ns");
				$svyew=$this->db->FetchField("ew");
				$svytot=$this->db->FetchField("tot");
				$svybot=$this->db->FetchField("bot");
				$svydip=$this->db->FetchField("dip");
				$svyfault=$this->db->FetchField("fault");

				$this->Cell($left);
				if($i==0) $this->Cell($w1, $h, "BPrj", $b, 0, 'R', true);
				else $this->Cell($w1, $h, "PAhd", $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svymd), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svyinc), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svyazm), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svyns), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svyew), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svytot-$svytvd), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svytot), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svybot), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svydip), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svyfault), $b, 0, 'R', true);
				$this->Ln(); $this->hdrheight+=$h;
				$i++;
			}
		}
		// $this->Ln(); $this->hdrheight+=$h;
	}

	function ReportProjections() {
		$h=.125;
		$w1=0.56;
		$b=1;
		$left=0.75;
		$this->SetFillColor(250, 148, 148);
		$this->db->DoQuery("SELECT * FROM projections ORDER BY md ASC");
		if($this->db->FetchNumRows()>0) {
			$i=1;
			while ($this->db->FetchRow()) {
				$svymd=$this->db->FetchField("md");
				$svyinc=$this->db->FetchField("inc");
				$svyazm=$this->db->FetchField("azm");
				$svytvd=$this->db->FetchField("tvd");
				$svyvs=$this->db->FetchField("vs");
				$svyvs=$this->db->FetchField("vs");
				$svyca=$this->db->FetchField("ca");
				$svycd=$this->db->FetchField("cd");
				$svyns=$this->db->FetchField("ns");
				$svyew=$this->db->FetchField("ew");
				$svytot=$this->db->FetchField("tot");
				$svybot=$this->db->FetchField("bot");
				$svydip=$this->db->FetchField("dip");
				$svyfault=$this->db->FetchField("fault");

				$this->Cell($left);
				$this->Cell($w1, $h, "PA$i", $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svymd), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svyinc), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svyazm), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svyns), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svyew), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svytot-$svytvd), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svytot), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svybot), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svydip), $b, 0, 'R', true);
				$this->Cell($w1, $h, sprintf("%0.2f",$svyfault), $b, 0, 'R', true);
				$this->Ln(); $this->hdrheight+=$h;
				$i++;
			}
		}
		$this->Ln(); $this->hdrheight+=$h;
	}

	function PlotImage($fn)
	{
		$this->Image($fn, .5, 3.0, 7.5, 7.5);
		// $this->Image($fn, .5, $this->hdrheight, 7.5, 7.5);
	}
}

$pdf=new PDF("P", "in", "Letter");
$pdf->SetFont('Arial','',8);
$pdf->AddPage();
$pdf->db=new dbio($seldbname);
$pdf->db->OpenDb();
$pdf->ReportHeader();
$pdf->PlotImage($imagefn);
$pdf->ReportSurveys();
$pdf->ReportProjections();
$pdf->db->CloseDb();

// if($tofile>0) $pdf->Output($filename, "F");
// else $pdf->Output($filename, "I");
$pdf->Output($filename, "F");
if($tofile==0) {
	header("Pragma: public"); // required
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private",false); // required for certain browsers
	header("Content-Type: application/force-download");
	header("Content-Disposition: attachment; filename=\"".basename($filename)."\";" );
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".filesize($filename));
	readfile("$filename");
    if(file_exists($filename)) unlink("$filename");
}
if(file_exists($imagefn)) unlink($imagefn);
?>
