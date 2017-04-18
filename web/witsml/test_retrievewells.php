<?php
include('include.php');
require_once("../dbio.class.php");
require_once('../classes/WellInfo.class.php');
require_once('../classes/WitsmlData.class.php');
require_once('../classes/DigiDrillConnection.class.php');

//$wellinfo = new WellInfo($_REQUEST); 
$witsml   = new DigiDrillConnection($_REQUEST);
//$witsml->retrieve_version();
$body = "<well uid=''><name/></well>";
$resp = $witsml->retrieve_fromstore($body,"well");

$body = "<wellbore uidWell='b00b623d-e516-4610-b082-b22ce8db23bd'><nameWell/><name/><numGovt/></wellbore>";
$resp = $witsml->retrieve_fromstore($body,"wellbore");

$body = "<log uidWell='b00b623d-e516-4610-b082-b22ce8db23bd' uidWellbore='92509c35-df90-43d7-996e-f7e720ab3d5c' uid=''><name/><logCurveInfo uid=''><mnemonic/></logCurveInfo></log>";
$resp = $witsml->retrieve_fromstore($body,'log');
//$resp = $witsml-> get_well_id_from_name('SSES_Test');
//$witsml->get_well_bore_id($resp);
//$data = $wellinfo->get_wellbore_witsml($witsml->well,$witsml->wellbore);
//$uid=$witsml->get_traj_uid();
//$data = $wellinfo->get_trajectory_witsml($witsml->well,$witsml->wellbore,$uid);
//$resp = $witsml->send($data,'trajectory');
print_r($resp);
?>
