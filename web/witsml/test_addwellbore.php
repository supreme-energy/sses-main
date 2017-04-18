<?php
include('include.php');
require_once("../dbio.class.php");
require_once('../classes/WellInfo.class.php');
require_once('../classes/WitsmlData.class.php');
$wellinfo = new WellInfo($_REQUEST); 
$witsml   = new WitsmlData($_REQUEST);
$witsml->get_obj_uid('well');
$well_uid =$witsml->well;
$witsml->get_obj_uid('wellbore');
$uid = $witsml->wellbore;
$data = $wellinfo->get_wellbore_witsml($well_uid,$uid);
$resp = $witsml->send_request($data,'wellbore');
print_r($resp);
?>