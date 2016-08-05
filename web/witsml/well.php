<?php
include('include.php');
require_once('../classes/WellInfo.class.php');
require_once('../classes/WitsmlData.class.php');

$wellinfo = new WellInfo($_REQUEST);
$witsml   = new WitsmlData($_REQUEST);
$witsml->get_obj_uid('well'); 
$well_uid = $witsml->well;
$data = $wellinfo->get_well_witsml($well_uid);
$witsml->construct_witsml($data,'well');
echo $witsml->witsml;
?>
