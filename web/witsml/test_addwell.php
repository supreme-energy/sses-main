<?php
include('include.php');
require_once('../classes/WellInfo.class.php');
require_once('../classes/WitsmlData.class.php');

$wellinfo = new WellInfo($_REQUEST);
$witsml   = new WitsmlData($_REQUEST);
$witsml->get_obj_uid('well'); 
$well_uid = $witsml->well;
$data = $wellinfo->get_well_witsml($well_uid);
$resp = $witsml->send_request($data,'well');
print_r($resp);
?>
