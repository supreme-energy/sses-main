<?php
require_once("../dbio.class.php");
require_once('../classes/WellInfo.class.php');
require_once('../classes/WitsmlData.class.php');
require_once('../classes/DigiDrillConnection.class.php');
$wellinfo = new WellInfo($_REQUEST); 
$witsml   = new DigiDrillConnection($_REQUEST);
$wellUid = $_REQUEST['uidwell'];
$body = "<wellbore uidWell='$wellUid'><nameWell/><name/><numGovt/></wellbore>";
$resp = $witsml->retrieve_fromstore($body,'wellbore');
print_r($resp);
//echo "REQUEST:".$witsml->client->__getLastRequest();
$xml = simplexml_load_string($resp['XMLout']);
echo json_encode($xml);
?>
