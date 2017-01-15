<?php
//	Written by: Richard Gonsuron
//	Modified by: John Arnold
//	Copyright: 2009, Supreme Source Energy Services, Inc.
//	All rights reserved.
//	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.

$db->DoQuery("select * from wellinfo");
if($db->FetchRow()) {
	$infotableid=$db->FetchField("id");
	$jobnumber=$db->FetchField("jobnumber");
	$propazm=$db->FetchField("propazm");
	$wellname=$db->FetchField("wellborename");
	$rigid=$db->FetchField("rigid");
	$wellid=$db->FetchField("wellid");
	$location=$db->FetchField("location");
	$field=$db->FetchField("field");
	$welldesc=$db->FetchField("description");
	$country=$db->FetchField("country");
	$county=$db->FetchField("county");
	$stateprov=$db->FetchField("stateprov");
	$opname=$db->FetchField("operatorname");
	$opcontact1=$db->FetchField("operatorcontact1");
	$opcontact2=$db->FetchField("operatorcontact2");
	$opemail1=$db->FetchField("operatoremail1");
	$opemail2=$db->FetchField("operatoremail2");
	$opphone1=$db->FetchField("operatorphone1");
	$opphone2=$db->FetchField("operatorphone2");
	$dirname=$db->FetchField("directionalname");
	$dirzip=$db->FetchField("directionalzip");
	$diraddress1=$db->FetchField("directionaladdress1");
	$diraddress2=$db->FetchField("directionaladdress2");
	$dircontact1=$db->FetchField("directionalcontact1");
	$dircontact2=$db->FetchField("directionalcontact2");
	$diremail1=$db->FetchField("directionalemail1");
	$diremail2=$db->FetchField("directionalemail2");
	$dirphone1=$db->FetchField("directionalphone1");
	$dirphone2=$db->FetchField("directionalphone2");
	$plantot=$db->FetchField("tot");
	$planbot=$db->FetchField("bot");
	$projection=$db->FetchField("projection");
	$bitoffset=$db->FetchField("bitoffset");
	$projdip=$db->FetchField("projdip");
	$wi_motoryield = $db->FetchField('motoryield');
	$pterm_method = $db->FetchField('pterm_method');
	$colortot=$db->FetchField("colortot");
	$colorwp =$db->FetchField("colorwp");
	$xaxis3d= $db->FetchField("xaxis");
	$zaxis3d= $db->FetchField("zaxis");
	$zoom3d= $db->FetchField("zoom3d");
	$originh3d= $db->FetchField("originh3d");
	$originv3d= $db->FetchField("originv3d");
	
	$pbhl_easting=$db->FetchField("pbhl_easting");
	$pbhl_northing=$db->FetchField("pbhl_northing");
	$survey_easting=$db->FetchField("survey_easting");
	$survey_northing=$db->FetchField("survey_northing");
	$landing_easting=$db->FetchField("landing_easting");
	$landing_northing=$db->FetchField("landing_northing");
	$elev_ground=$db->FetchField("elev_ground");
	$elev_rkb=$db->FetchField("elev_rkb");
	$correction=$db->FetchField("correction");
	$coordsys=$db->FetchField("coordsys");
	$startdate=$db->FetchField("startdate");
	$enddate=$db->FetchField("enddate");

	$padata=$db->FetchField("padata");
	$pbdata=$db->FetchField("pbdata");
	$pamethod=$db->FetchField("pamethod");
	$autoposdec=$db->FetchField("autoposdec");
	$pbmethod=$db->FetchField("pbmethod");
	$sgta_show_forms = $db->FetchField("sgta_show_formations");
	$wb_show_forms   = $db->FetchField("wb_show_formations");
	$refwellname = $db->FetchField("refwellname");
} 
$db->DoQuery("select * from emailinfo");
if($db->FetchRow()) {
	$smtp_server=$db->FetchField("smtp_server");
	$smtp_login=$db->FetchField("smtp_login");
	$smtp_password=$db->FetchField("smtp_password");
	$smtp_from=$db->FetchField("smtp_from");
}
$db->DoQuery("select * from witsml_details");
if($db->FetchRow()){
	$witsml_id = $db->FetchField("id");
	$witsml_endpoint =$db->FetchField("endpoint");
	$witsml_username =$db->FetchField("username");
	$witsml_password =$db->FetchField("password");
	$witsml_active =$db->FetchField("send_data");
} else {
	$witsml_id=-1;
}
$db->DoQuery("select * from rigminder_connection");
if($db->FetchRow()){
	$autorc_type=$db->FetchField("connection_type");
	$autorc_dbname =$db->FetchField("dbname");
	$autorc_password = $db->FetchField("password");
	$autorc_username=$db->FetchField("username");
	$autorc_host = $db->FetchField("host");
	$autorc_sd = $db->FetchField("aisd");
}
?>
