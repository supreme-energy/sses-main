<?php /*
	emailsend.php

	Version:     2.4.1
	Date:        March 26, 2012
        Modified by: Cnthia Bergman
	Purpose:     To correct issues observed with the email message body.
                     Please see the Release Notes for Version 2.4.1 for
                     additional information.

	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
require_once("dbio.class.php");
require_once('classes/WellInfo.class.php');
require_once('classes/WitsmlData.class.php');
require_once('classes/Reports.class.php');
function check_email_address($email) {
  // First, we check that there's one @ symbol, 
  // and that the lengths are right.
  if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
    // Email invalid because wrong number of characters 
    // in one section or wrong number of @ symbols.
    return false;
  }
  // Split it into sections to make life easier
  $email_array = explode("@", $email);
  $local_array = explode(".", $email_array[0]);
  for ($i = 0; $i < sizeof($local_array); $i++) {
    if
(!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&
↪'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",
$local_array[$i])) {
      return false;
    }
  }
  return true;
  // Check if domain is IP. If not, 
  // it should be valid domain name
  if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
    $domain_array = explode(".", $email_array[1]);
    if (sizeof($domain_array) < 2) {
        return false; // Not enough parts to domain
    }
    for ($i = 0; $i < sizeof($domain_array); $i++) {
      if
(!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|
↪([A-Za-z0-9]+))$",
$domain_array[$i])) {
        return false;
      }
    }
  }
  return true;
}
function sendmail($vt) {
	global $smtp_from, $to, $subject, $smtp_server, $smtp_login, $smtp_password, $outfile,$las_filename;
	global $email_attach,$filename_gamma_1,$filename_gamma_5;
	if(file_exists("/tmp/sendemail.log")) exec(">/tmp/sendemail.log"); 
	$email_attach_str="";
	if($email_attach!=""){
		$email_attach_str = " -a '".$email_attach."'";
	}
	if($las_filename!="" && ($vt=='all'||$vt=='las_report_1'||$vt=='las_report_2')){
		$email_attach_str .= " '".$las_filename."'";
	}
	if($filename_gamma_1!="" && ($vt=='all'||$vt=='las_report_1'||$vt=='report_1_2')){
		$email_attach_str .= " '".$filename_gamma_1."'";
	}
	
	if($filename_gamma_5!=""&& ($vt=='all'||$vt=='las_report_2'||$vt=='report_1_2')){
		$email_attach_str .= " '".$filename_gamma_5."'";
	}
	if($email_attach_str=="") {
		$strcmd=sprintf("sendEmail -l /tmp/sendemail.log -f %s -t %s -u \"%s\" -s %s -xu %s -xp %s -o message-file='%s'",
		$smtp_from, $to, $subject, $smtp_server, $smtp_login, $smtp_password, $outfile);
	}
	else {
		$strcmd=sprintf("sendEmail -l /tmp/sendemail.log -f %s -t %s -u '%s' -s '%s' %s -xu '%s' -xp '%s' -o message-file='%s'",
		$smtp_from, $to, $subject, $smtp_server, $email_attach_str, $smtp_login, $smtp_password, $outfile);
	}
	echo "<p>$strcmd</p>";
	$output=shell_exec($strcmd);
	echo "$output";
}
$now = new DateTime();
$seldbname=$_POST['seldbname'];
$email_attach=$_POST['email_attach'];
$gen_las = isset($_POST['las'])?$_POST['las']:0;
$gen_1inch = isset($_POST['inch1gr'])?1:0;
$gen_5inch = isset($_POST['inch5gr'])?1:0;
$program=$_POST['program'];
$filename=$_POST['filename'];
$filename=str_replace($seldbname,$now->format('Y-m-d_H-i-s_T')."_".$seldbname,$filename);
//$messagein=html_entity_decode($_POST['message']);
$messagein=trim($_POST['message']);
$messagex=$_POST['message'];
$cutoff=$_POST['cutoff'];
$plotstart=$_POST['plotstart'];
$plotend=$_POST['plotend'];
$wlid=$_POST['wlid'];

if(strlen($mintvd)<=0)	$mintvd=$_POST['mintvd'];
if(strlen($maxtvd)<=0)	$maxtvd=$_POST['maxtvd'];
if(strlen($minvs)<=0)	$minvs=$_POST['minvs'];
if(strlen($maxvs)<=0)	$maxvs=$_POST['maxvs'];
if(strlen($yscale)<=0)	$yscale=$_POST['yscale'];
$ci=$_POST['ci'];
$co=$_POST['co'];
$tablename=$_POST['tablename'];

$badshit = "'";
//$sidxcom = strrpos($messagein,'<p id="comments">');
//$eidxcom = strrpos($messagein,"</p>");
//$comment = str_replace($badshit,"''",substr($messagein,$sidxcom,$eidxcom));
if(strpos($_POST["comments"],'PLACE COMMENTS HERE')) $comment = '';
else $comment = trim($_POST["comments"]);
$messagein = str_replace('<p>TOP COMMENT RIGHT HERE</p>',"",$messagein);
$messagein = str_replace('COMMENTS WILL GO HERE',$comment,$messagein);
$message_db = "<html>" . str_replace($badshit,"''",$messagein) . "</html>";

?>
<!DOCTYPE html>
<html>
<head>
<title><?echo "$seldbname:$wellname";?>-Email report</title>
<style>
body {
	margin:10px;
	font-size:10pt;
	color:black;
	background-color: #2C4C69;
}
h2 {
	font-size:1.5em;
}
</style>
</head>
<body>
<div style='border:1px solid black;background-color:rgb(255, 255, 252)'>
<div style='padding:5px'>
<h2>Sending report via email</h2>
<p style='padding:10px 0'>
<input type='submit' value='Close' onclick="window.close()">
</p>
</div>
<div style='height:250px;overflow:auto;border-top:1px solid gray;padding:5px'>
<pre>
<?
echo "Report program:$program\n";

if(strlen($program)>0 && file_exists($program)) {
	// special handling: check for HTML to text conversion here
	if($program=="surveyprint.php") {
		$filename=sprintf("/tmp/%s.surveys.txt", $seldbname);
		$program="surveytext.php";
	}

	echo "Generating report $filename...";
	include_once($program);
	
	if($program=='surveyplotlat.php'){
		$wellinfo = new WellInfo($_REQUEST); 
		$witsml   = new WitsmlData($_REQUEST);
		echo "checking for active witsml export..\n";
		if($witsml->active()){
			echo "active witsml export found\n";
			$body = "<well uid=''><name/></well>";
			echo "checking for active well ".$wellinfo->wellborename;
			$resp = $witsml-> get_well_id_from_name($wellinfo->wellborename);
			echo "active well id found:$resp\n";
			if($resp){
				$wellborid=$witsml->get_well_bore_id($resp);
				echo "active well bore id found:$wellborid\n";
				if($wellborid){
					//$data = $wellinfo->get_wellbore_witsml($witsml->well,$witsml->wellbore);
					$uid=$witsml->get_traj_uid();
					echo "sending trajectory data to petrolink\n";
					$data = $wellinfo->get_trajectory_witsml($witsml->well,$witsml->wellbore,$uid);
					$witsml->send($data,'trajectory');
				}
			}
		}
	}
	echo "done\n";
	if(file_exists($filename)) {
		echo "Attaching report $filename to email\n";
		$email_attach=$filename;
	}
}


require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();

$query ="update wellinfo set emailcomments = '".$comment."'";
$db->DoQuery($query);

include("readwellinfo.inc.php");
include("readsurveys.inc.php");

if($smtp_from==""||$smtp_server==""||$smtp_login==""||$smtp_password=="") {
	printf("Error: SMTP information not setup\n");
	return;
}
if($svy_plan[0]==1){
	$start_depth = $svy_md[count($svy_md)-1];
	$end_depth = $svy_md[1];
} else {
	$start_depth = $svy_md[count($svy_md)-1];
	$end_depth = $svy_md[0];
}	
$reports=new Reports();
$las_filename="";
if($gen_las){
	echo "Generating LAS file attachment...\n";
	$las_filename = $reports->generate_las_file($seldbname);
	echo $las_filename;
	if(file_exists($las_filename)){
		echo "Attaching report $las_filename to email\n";
	}
}
$filename_gamma_1="";
$filename_gamma_5="";	
require_once("gammareport.php");
if($gen_1inch){
	echo "Generating GR 1 inch attachment...\n";
	$inchtype=1;
	$filename_gamma_1="tmp/gammareport_1inch.pdf";	
	$pdf=new GammaPDF();
	$pdf->SetMargins(1,0,0);
	$pdf->SetFont('Arial','',8);
	$pdf->SetAutoPageBreak(0, 0);
	$pdf->AddPage("P");
	$pdf->ReportHeader();
	$pdf->SetFont('Arial','',9);
	$pdf->SurveyHeader();
	$pdf->generate_gamma_report($inchtype,$start_depth,$end_depth,$seldbname);
	$pdf->Output($filename_gamma_1, "F");
}
if($gen_5inch){
	echo "Generating GR 5 inch attachment...\n";
	$inchtype=5;
	$filename_gamma_5="tmp/gammareport_5inch.pdf";	
	$pdf=new GammaPDF();
	$pdf->SetMargins(1,0,0);
	$pdf->SetFont('Arial','',8);
	$pdf->SetAutoPageBreak(0, 0);
	$pdf->AddPage("P");
	$pdf->ReportHeader();
	$pdf->SetFont('Arial','',9);
	$pdf->SurveyHeader();
	$pdf->generate_gamma_report($inchtype,$start_depth,$end_depth,$seldbname);
	$pdf->Output($filename_gamma_5, "F");
}
$contacts=array();
$contacts_variance=array("all"=>array("emails"=>array(),"filename"=>""),
						 "none"=>array("emails"=>array(),"filename"=>""),
						 "las"=>array("emails"=>array(),"filename"=>""),
						 "report_1"=>array("emails"=>array(),"filename"=>""),
						 "report_2"=>array("emails"=>array(),"filename"=>""),
						 "las_report_1"=>array("emails"=>array(),"filename"=>""),
						 "las_report_2"=>array("emails"=>array(),"filename"=>""),
						 "report_1_2"=>array("emails"=>array(),"filename"=>"")
);

$db->DoQuery("SELECT * FROM emaillist WHERE enabled>0;");
while($db->FetchRow()) {
	$c=$db->FetchField("email");
	$las=$db->FetchField("las_file");
	$r1 = $db->FetchField("report_1");
	$r2 = $db->FetchField("report_2");
	if(strlen($c)>1) {
		if(check_email_address($c)==true) {
			$contacts[]="'$c'";
			if(!$las && !$r1 && !$r2){
				$contacts_variance['none']["emails"][]="'$c'";
				if($contacts_variance['none']["filename"]==""){
					$contacts_variance['none']["filename"]=tempnam("/tmp", $seldbname);
				}
			}elseif($las && !$r1 && !$r2){
				$contacts_variance['las']["emails"][]="'$c'";
				if($contacts_variance['las']["filename"]==""){
					$contacts_variance['las']["filename"]=tempnam("/tmp", $seldbname);
				}
			}elseif(!$las && $r1 && !$r2){
				$contacts_variance['report_1']["emails"][]="'$c'";
				if($contacts_variance['report_1']["filename"]==""){
					$contacts_variance['report_1']["filename"]=tempnam("/tmp", $seldbname);
				}
			}elseif(!$las && !$r1 && $r2){
				$contacts_variance['report_2']["emails"][]="'$c'";
				if($contacts_variance['report_2']["filename"]==""){
					$contacts_variance['report_2']["filename"]=tempnam("/tmp", $seldbname);
				}
			}elseif($las && $r1 && !$r2){
				$contacts_variance['las_report_1']["emails"][]="'$c'";
				if($contacts_variance['las_report_1']["filename"]==""){
					$contacts_variance['las_report_1']["filename"]=tempnam("/tmp", $seldbname);
				}
			}elseif($las && !$r1 && $r2){
				$contacts_variance['las_report_2']["emails"][]="'$c'";
				if($contacts_variance['las_report_2']["filename"]==""){
					$contacts_variance['las_report_2']["filename"]=tempnam("/tmp", $seldbname);
				}
			}elseif(!$las && $r1 && $r2){
				$contacts_variance['report_1_2']["emails"][]="'$c'";
				if($contacts_variance['report_1_2']["filename"]==""){
					$contacts_variance['report_1_2']["filename"]=tempnam("/tmp", $seldbname);
				}
			} else {
				$contacts_variance['all']["emails"][]="'$c'";
				if($contacts_variance['all']["filename"]==""){
					$contacts_variance['all']["filename"]=tempnam("/tmp", $seldbname);
				}
			}
			
		} else {
			echo "Email address not valid:$c\n";
		}
	}
}

$db->DoQuery("UPDATE emailinfo SET smtp_message='$message_db';");
$db->CloseDb();


$subject=sprintf("Email report from %s", $wellname);
$outfile="";
//$handle=fopen($outfile, "w");
$to="";

foreach($contacts_variance as $variant=>$contact){
	if($contact["filename"]!=""){
		$outfile = $contact["filename"];
		$handle=fopen($outfile, "w");
		fwrite($handle, "<html><body style='color:#050505'>".$messagein."</body></html>");
		fclose($handle);
		$to="";
		foreach($contact["emails"] as $c){
			$to="$to $c";
		}
		echo "send to recepient listTo: $to\n<br>";
		if($to!="") sendmail($variant);
		unlink($outfile);
	}
}
//if($handle!=NULL) {
//	fwrite($handle, "<html><body style='color:#050505'>".$messagein."</body></html>");
//	fclose($handle);
//	foreach($contacts as $c) {
//		$to="$to $c";
//	}
//	echo "To: $to\n";
//	if($to!="") sendmail();
//}
//echo "Removing temporary files\n";
//unlink($outfile);
//if(strlen($email_attach)>0 && file_exists($email_attach))	unlink($email_attach);
?>
</pre>
</div>
</div>
</body>
</html>
