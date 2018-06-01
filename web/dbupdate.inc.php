<?php
//	dbupdate.inc.php 
// 
//	Version: SSES v2.4.2 
//			 April 20, 2012 
// 
//	Modified by: C. Bergman  
//	Purpose: To add columns colortot, colotbot, and colorwp 
//			 to the wellinfo table, for storing the User
//           selected colors for the Top of Target (TOT), the
//           Bottom of Target (BOT), and the Well Plan Target
//           Line, respectively.  These columns are text fields
//           and are assigned the value 'ff0000' as default.
//			 The table wellinfo is then queried for the colors 
//			 when generating a graph from any tab in the SSES 
//			 Application. It was deemed best to store these
//           User-selected colors in the wellinfo table since
//           this table contains just one record per well, and
//		     therefore, this information could be stored in a
//			 unique location, thus promoting data integrity.  
//           The User-selected colors for the Top of Target
//           (TOT) and Bottom of Target (BOT) Lines may also be
//           stored in the addforms table if these formations
//           are defined for the database. For more information,
//           please see the Release Notes for Version 2.4.2. 
//
//	Written by: Richard Gonsuron
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools 	You may NOT modify, copy,
//	or distribute this file in any manner without written permission of
//	Digital Oil Tools

$response="";
function logInfo($s) {
	global $silent;
	global $response;
	if($silent==1) $response="$response$s\n";
	else echo $s;
}
function logDoQuery($dbp, $s) {
	logInfo($s);
	$dbp->DoQuery($s);
}

$dbu=new dbio("$seldbname");

$dbu->OpenDb();
$dbu->DoQuery("VACUUM ANALYZE;");

$gotinfo=0;
$gotlist=0;
$gotplist=0;
$gotproj=0;
$gotedatalogs=0;
$gotaddforms=0;
$gotaddformsdata=0;
$gotwitsml_details=0;
$gotwitsml_log=0;
$gorigminder_con=0;
$goadgfb=0;
$delsvyd = 0;
$delsvyg = 0;
$annot=0;
$reports=0;
$anticollision=0;
$admconfig=0;
$rotslide=0;
$ghostsurveys=0;
$ghostprojects = 0;
$ghostdata = 0;
$ghostwelllogs=0;
$nogo_zone=0;
$nogo_point=0;
$profile_lines = 0;
$import_config = 0;
$imported_files = 0;
$file_values_map = 0;
$dbu->DoQuery("SHOW TABLES");
while($dbu->FetchRow()) {
	$tn=$dbu->FetchField("tablename");
	if($tn=="emailinfo")	$gotinfo=1;
	elseif($tn=="emaillist")	$gotlist=1;
	elseif($tn=="splotlist")	$gotplist=1;
	elseif($tn=="projections")	$gotproj=1;
	elseif($tn=="edatalogs")	$gotedatalogs=1;
	elseif($tn=="addforms")	$gotaddforms=1;
	elseif($tn=="addformsdata")	$gotaddformsdata=1;
	elseif($tn=="witsml_details") $gotwitsml_details=1;
	elseif($tn=="witsml_log") $gotwitsml_log=1;
	elseif($tn=="rigminder_connection") $gorigminder_con=1;
	elseif($tn=="add_data_gamma_fb") $goadgfb=1;
	elseif($tn=="deleted_survey_data") $delsvyd = 1;
	elseif($tn=="deleted_survey_group") $delsvyg=1;
	elseif($tn=='annos') $annot=1;
	elseif($tn=='reports') $reports=1;
	elseif($tn=="anticollision_wells") $anticollision=1;
	elseif($tn=="adm_config") $admconfig=1;
	elseif($tn=="rotslide") $rotslide=1;
	elseif($tn=="ghost_surveys") $ghostsurveys=1;
	elseif($tn=="ghost_projects") $ghostprojects=1;
	elseif($tn=="ghost_data") $ghostdata=1;
	elseif($tn=="nogo_zone") $nogo_zone=1;
	elseif($tn=="nogo_point") $nogo_point=1;
	elseif($tn=="profile_lines") $profile_lines=1;
	elseif($tn=="import_config") $import_config=1;
	elseif($tn=="imported_files") $imported_files=1;
	elseif($tn=="file_values_map") $file_values_map=1;
	
}

foreach (glob("db/tables/*.php") as $filename)
{
    include $filename;
}

// logInfo("dbupdate: Finished");
$dbu->CloseDb();

if(strlen($response)>0) { ?>
	<BODY style='background-color: rgb(255, 255, 252);'>
	<H2>Database has been updated with the following changes:</H2>
	<?echo "<pre>$response</pre>"; ?>
	<A href='gva_tab1.php?seldbname=<?echo $seldbname;?>'>Click To Continue</A>
	</BODY>
<?
	exit;
} ?>
