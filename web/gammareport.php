<?php
require_once('/usr/share/php/fpdf/fpdf.php');
class GammaPDF extends FPDF{
	function ReportHeader()
	{
		global $db;
		$db->DoQuery("SELECT * FROM wellinfo;");
		if($db->FetchNumRows() > 0)
		{
			$db->FetchRow();
			$ud_opname=$db->FetchField("operatorname");
		}

		$this->Image("./logo.jpg", 15, 10, 10, 10);

		$this->SetFont('','B',14);
		$this->Cell(0, 4, $ud_opname, 0, 0, 'C', false);
		$this->Ln();
		$this->Cell(0, 4, "Gamma/Rop Depth Report", 0, 0, 'C', false);
		$this->Ln();
		$this->Ln();
		$this->SetFont('','',8);
	}
	function SurveyHeader()
	{
		global $db;
		$h=4;
		$w1=45;
		$w2=40;

		$db->DoQuery("SELECT * FROM wellinfo;");
		if($db->FetchNumRows() > 0) {
			$db->FetchRow();
			$ud_timedate=date("m/d/Y");
			$ud_opname=$db->FetchField("operatorname");
			if( !strlen($ud_opname) )
				$ud_opname="Polaris";
			$opname=$db->FetchField("operatorname");
			$jobnumber=$db->FetchField("jobnumber");
			$wellname=$db->FetchField("wellborename");
			$opcontact1=$db->FetchField("operatorcontact1");
			$rigid=$db->FetchField("rigid");
			$startdate=$db->FetchField("startdate");
			$wellid=$db->FetchField("wellid");
			$enddate=$db->FetchField("enddate");
			$field=$db->FetchField("field");
			$declination=$db->FetchField("declination");
			$location=$db->FetchField("location");
			$propazm=$db->FetchField("propazm");
			$stateprov=$db->FetchField("stateprov");
			$correction=$db->FetchField("correction");
			$county=$db->FetchField("county");
			$country=$db->FetchField("country");
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
			//$this->SetY(-15);
			//Select Arial italic 8
			//$this->SetFont('Arial','I',8);
			//Print current and total page numbers
			//$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
	function generate_gamma_report($scale_type=1,$start_depth,$end_depth,$seldbname){
		$p1 = 908;
		$p2 = 1120;
		$height = ($end_depth-$start_depth);
		if($scale_type!=1){
			$height=$height*$scale_type;
		}	 
	//	 echo $height;
		 $cmd = "./sses_gpd_rv -d $seldbname -w 320 -scl $scale_type -nrm 0 -h $height -s $start_depth -e $end_depth -o tmp/gammadepth$scale_type.png -wld";
	//	 echo $cmd;
		 exec ($cmd);
		 $cmd = "./sses_gpd_rv -d $seldbname -w 280 -scl $scale_type -h $height -nrm 1 -plamd 1 -s $start_depth -e $end_depth -o tmp/gammatvd$scale_type.png -wld";
	//	 echo $cmd;
		 exec ($cmd);
		 $cmd ="./sses_gpd_rv -d $seldbname -w 187 -scl $scale_type -h $height -nrm 1 -plamd 2 -s $start_depth -e $end_depth -o tmp/ropmd$scale_type.png -nogrid"; 
	//	 echo $cmd;
		 exec ($cmd);
		 $cmd="/usr/bin/convert /tmp/gammadepth$scale_type.png /tmp/gammatvd$scale_type.png /tmp/ropmd$scale_type.png +append tmp/gammaall$scale_type.png";
//		 echo $cmd;
		 exec ($cmd);
		 $this->Image("imgs/header_data_sses.PNG");
		 $ps = 0;
		 $imgs = 0;
		 while($ps < $height){
		// 	echo $ps;
		// 	exit();
		 	if($ps==0){
		 		$crop = $height-$p1; 
		 		exec ("/usr/bin/convert tmp/gammaall$scale_type.png -crop 787x$height+0-$crop tmp/gammaall$scale_type$imgs.png");
		 		$ps = $ps+$p1;
		 	} else {
		 		$crop = $height-($ps+$p2);
		 		$crop_p = $ps;
		 		exec ("/usr/bin/convert tmp/gammaall$scale_type.png -crop 787x$height+0-$crop tmp/gammaall$scale_type$imgs.png");
		 		exec ("/usr/bin/convert tmp/gammaall$scale_type$imgs.png -crop 787x$height+0+$crop_p tmp/gammaall$scale_type$imgs.png");
		 		$ps=$ps+$p2;
		 	}
		 //	echo $ps;
		// 	exit();
		 	$this->Image("tmp/gammaall$scale_type$imgs.png");
		 	$imgs++;
		 	if($ps<$height){
		 		$this->AddPage("P");
		 	}
		 }
		
	}
}
?>
