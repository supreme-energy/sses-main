<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
require_once("dbio.class.php");
if(strlen($seldbname)<=0)	$seldbname=$_GET['seldbname'];
$filename=$_GET['filename'];
if(strlen($filename)<=0) $filename=sprintf("/tmp/%s.surveys.txt", $seldbname);
// if(file_exists($filename)) unlink($filename);

$out=fopen($filename, "w");
if(!$out) {
	echo "Error creating temporary file\n";
	exit(1);
}

$db=new dbio($seldbname);
$db->OpenDb();
include("readwellinfo.inc.php");
include("readappinfo.inc.php");
$currdate=date('m/d/Y');

fprintf($out, "%s\nSurvey Report\n", $wellname);
fprintf($out, "Date: %s\n", $currdate);

fprintf($out, "%12s%-30s", "Company: ", $opname);
fprintf($out, "%18s%-20s\n", "RigId: ", $rigid);

fprintf($out, "%12s%-30s", "Well: ", $wellname);
fprintf($out, "%18s%-20s\n", "API/UWI: ", $wellid);

fprintf($out, "%12s%-30s", "Field: ", $field);
fprintf($out, "%18s%-20s\n", "Job Number: ", $jobnumber);

fprintf($out, "%12s%-30s", "Location: ", $location);
fprintf($out, "%18s%-20s\n", "Operator: ", $opcontact1);

fprintf($out, "%12s%-30s", "State/Prov: ", $stateprov);
fprintf($out, "%18s%-20s\n", "Proposed Azimuth: ", $propazm);

fprintf($out, "%12s%-30s", "County: ", $county);
fprintf($out, "%18s%-20s\n\n", "North Reference: ", $correction);

fprintf($out, "%4s", "Svy");
fprintf($out, "%9s", "Depth");
fprintf($out, "%9s", "Inc");
fprintf($out, "%9s", "Azm");
fprintf($out, "%9s", "CL");
fprintf($out, "%9s", "TVD");
fprintf($out, "%9s", "VS");
fprintf($out, "%9s", "NS");
fprintf($out, "%9s", "EW");
if($showxy==1) {
	fprintf($out, "%9s", "Northing");
	fprintf($out, "%9s", "Easting");
} else {
	fprintf($out, "%9s", "CD");
	fprintf($out, "%9s", "CA");
}
fprintf($out, "%9s\n", "DL");

$db->DoQuery("SELECT * FROM surveys ORDER BY md ASC");
$numsvys=$db->FetchNumRows(); 
$lastdepth=0;
$gotbprj=0;
for($i=0; $i < $numsvys; $i++) {
	$db->FetchRow();
	$md=sprintf("%.2f", $db->FetchField("md"));
	$inc=sprintf("%.2f", $db->FetchField("inc"));
	$azm=sprintf("%.2f", $db->FetchField("azm"));
	$tvd=sprintf("%.2f", $db->FetchField("tvd"));
	$ns=sprintf("%.2f", $db->FetchField("ns"));
	$ew=sprintf("%.2f", $db->FetchField("ew"));
	$vs=sprintf("%.2f", $db->FetchField("vs"));
	$ca=sprintf("%.2f", $db->FetchField("ca"));
	$cd=sprintf("%.2f", $db->FetchField("cd"));
	$dl=sprintf("%.2f", $db->FetchField("dl"));
	$plan=sprintf("%.2f", $db->FetchField("plan"));
	if($showxy==1) {
		$cd=sprintf("%.0f", $survey_northing+$ns);
		$ca=sprintf("%.0f", $survey_easting+$ew);
	}

	if($plan==0) fprintf($out, "%4d", $i);
	else {
		if(!$gotbprj)	{ fprintf($out, "BPrj"); $gotbprj=1; }
		else fprintf($out, "PAhd");
	}

	fprintf($out, "%9.2f", $md);
	fprintf($out, "%9.2f", $inc);
	fprintf($out, "%9.2f", $azm);
	
	if($i>0) {
		$courselen=$md-$lastdepth;
		fprintf($out, "%9.2f", $courselen);
	} else fprintf($out, "         ");
	$lastdepth=$md;
	
	fprintf($out, "%9.2f", $tvd);
	fprintf($out, "%9.2f", $vs);
	fprintf($out, "%9.2f", $ns);
	fprintf($out, "%9.2f", $ew);
	if($showxy==1) {
		fprintf($out, "%9.0f", $cd);
		fprintf($out, "%9.0f", $ca);
	}else{
		fprintf($out, "%9.2f", $cd);
		fprintf($out, "%9.2f", $ca);
	}

	if($i>0)fprintf($out, "%9.2f\n", $dl);else fprintf($out, "\n");
} 
$db->CloseDb();
fclose($out);
?>
