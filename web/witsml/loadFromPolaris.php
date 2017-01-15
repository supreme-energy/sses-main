<?php
include('include.php');
require_once("../dbio.class.php");
require_once("../classes/WitsmlData.class.php");
require_once('../classes/PolarisConnection.class.php');
$witsml   = new PolarisConnection($_REQUEST);
$witsml->prepare_las_data(8000,8300,1);
?>