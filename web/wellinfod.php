<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
require_once("dbio.class.php");
$badshit = array("'", "%%");
$seldbname=$_POST['seldbname'];
$id=$_POST['id'];
$currtab=$_POST['currtab'];
$propazm=$_POST['propazm'];
$wellname=$_POST['wellname'];
$field=$_POST['field'];
$jobnumber=$_POST['jobnumber'];
$rigid=$_POST['rigid'];
$wellid=$_POST['wellid'];
$locationin=$_POST['location'];
$location = str_replace($badshit, "\'", $locationin);
$welldesc=$_POST['welldesc'];
$country=$_POST['country'];
$county=$_POST['county'];
$stateprov=$_POST['stateprov'];
$opname=$_POST['opname'];
$opcontact1=$_POST['opcontact1'];
$opcontact2=$_POST['opcontact2'];
$opemail1=$_POST['opemail1'];
$opemail2=$_POST['opemail2'];
$opphone1=$_POST['opphone1'];
$opphone2=$_POST['opphone2'];
$dirname=$_POST['dirname'];
$dirzip=$_POST['dirzip'];
$diraddress1=$_POST['diraddress1'];
$diraddress2=$_POST['diraddress2'];
$dircontact1=$_POST['dircontact1'];
$dircontact2=$_POST['dircontact2'];
$diremail1=$_POST['diremail1'];
$diremail2=$_POST['diremail2'];
$dirphone1=$_POST['dirphone1'];
$dirphone2=$_POST['dirphone2'];
$plantot=$_POST['tot'];
$planbot=$_POST['bot'];
$projection=$_POST['projection'];

$pbhl_easting=$_POST['pbhl_easting']; if($pbhl_easting=="")	$pbhl_easting=0.0;
$pbhl_northing=$_POST['pbhl_northing']; if($pbhl_northing=="")	$pbhl_northing=0.0;
$survey_easting=$_POST['survey_easting']; if($survey_easting=="")	$survey_easting=0.0;
$survey_northing=$_POST['survey_northing']; if($survey_northing=="")	$survey_northing=0.0;
$landing_easting=$_POST['landing_easting']; if($landing_easting=="")	$landing_easting=0.0;
$landing_northing=$_POST['landing_northing']; if($landing_northing=="")	$landing_northing=0.0;
$elev_ground=$_POST['elev_ground']; if($elev_ground=="")	$elev_ground=0.0;
$elev_rkb=$_POST['elev_rkb']; if($elev_rkb=="")	$elev_rkb=0.0;
$correction=$_POST['correction']; if($correction=="")	$correction="True North";
$coordsys=$_POST['coordsys']; if($coordsys=="")	$coordsys="Polar";
$startdate=$_POST['startdate'];
$enddate=$_POST['enddate'];

$smtp_server=$_POST['smtp_server'];
$smtp_login=$_POST['smtp_login'];
$smtp_password=$_POST['smtp_password'];
$smtp_from=$_POST['smtp_from'];

$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("BEGIN TRANSACTION;");

$db->DoQuery("UPDATE wellinfo SET rigid='$rigid',wellborename='$wellname',jobnumber='$jobnumber' WHERE id='$id';");
$db->DoQuery("UPDATE wellinfo SET field='$field',wellid='$wellid',location='$location' WHERE id='$id';");
$db->DoQuery("UPDATE wellinfo SET operatorcontact1='$opcontact1',operatorcontact2='$opcontact2' WHERE id='$id';");
$db->DoQuery("UPDATE wellinfo SET operatoremail1='$opemail1',operatoremail2='$opemail2' WHERE id='$id';");
$db->DoQuery("UPDATE wellinfo SET operatorphone1='$opphone1',operatorphone2='$opphone2' WHERE id='$id';");
$db->DoQuery("UPDATE wellinfo SET operatorname='$opname',directionalname='$dirname' WHERE id='$id';");
$db->DoQuery("UPDATE wellinfo SET directionalcontact1='$dircontact1' WHERE id='$id';");
$db->DoQuery("UPDATE wellinfo SET directionalcontact2='$dircontact2' WHERE id='$id';");
$db->DoQuery("UPDATE wellinfo SET directionalemail1='$diremail1',directionalemail2='$diremail2' WHERE id='$id';");
$db->DoQuery("UPDATE wellinfo SET directionalphone1='$dirphone1',directionalphone2='$dirphone2' WHERE id='$id';");
$db->DoQuery("UPDATE wellinfo SET description='$welldesc',country='$country',County='$county' WHERE id='$id';");
$db->DoQuery("UPDATE wellinfo SET stateprov='$stateprov' WHERE id='$id';");
$db->DoQuery("UPDATE wellinfo SET pbhl_easting='$pbhl_easting',pbhl_northing='$pbhl_northing' WHERE id='$id';");
$db->DoQuery("UPDATE wellinfo SET survey_easting='$survey_easting',survey_northing='$survey_northing' WHERE id='$id';");
$db->DoQuery("UPDATE wellinfo SET landing_easting='$landing_easting',landing_northing='$landing_northing' WHERE id='$id';");
$db->DoQuery("UPDATE wellinfo SET elev_ground='$elev_ground',elev_rkb='$elev_rkb' WHERE id='$id';");
$db->DoQuery("UPDATE wellinfo SET correction='$correction',coordsys='$coordsys' WHERE id='$id';");
if(strlen($startdate)) $db->DoQuery("UPDATE wellinfo SET startdate='$startdate' WHERE id='$id';");
if(strlen($enddate)) $db->DoQuery("UPDATE wellinfo SET enddate='$enddate' WHERE id='$id';");

$db->DoQuery("UPDATE emailinfo SET smtp_server='$smtp_server',smtp_login='$smtp_login';");
$db->DoQuery("UPDATE emailinfo SET smtp_password='$smtp_password',smtp_from='$smtp_from';");

$result=$db->DoQuery("COMMIT;");

$db->CloseDb();
header("Location: gva_tab1.php?seldbname=$seldbname&currtab=$currtab");
exit();
?>
