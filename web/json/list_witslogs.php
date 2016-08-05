<?php
require_once("../dbio.class.php");
require_once('../classes/WellInfo.class.php');
require_once('../classes/WitsmlData.class.php');
$wellinfo = new WellInfo($_REQUEST); 
$witsml   = new WitsmlData($_REQUEST);
$wellUid = $_REQUEST['uidwell'];
$wellborUid = $_REQUEST['uidwellbore'];
$body = "<log uidWell='$wellUid' uidWellbore='$wellborUid' uid=''><name/><logCurveInfo uid=''><mnemonic/></logCurveInfo></log>";
$resp = $witsml->retrieve_fromstore($body,'log');
//echo "REQUEST:".$witsml->client->__getLastRequest();
$xml = simplexml_load_string($resp['XMLout']);
echo json_encode($xml);
?>