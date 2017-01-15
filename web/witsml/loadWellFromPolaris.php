<?php
include('include.php');
require_once("../dbio.class.php");
require_once('../classes/WellInfo.class.php');
require_once('../classes/WitsmlData.class.php');
$wellinfo = new WellInfo($_REQUEST); 
$witsml   = new WitsmlData($_REQUEST);
$body = "<well uid='80f45c54-c9d7-4855-9197-aa06bcb6a2f4'><name/></well>";
$resp = $witsml->retrieve_fromstore($body,'well');
//echo "REQUEST:".$witsml->client->__getLastRequest();
echo($resp['XMLout']);
?>
