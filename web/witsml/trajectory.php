<?php
include('include.php');
require_once("../dbio.class.php");
require_once("../classes/Survey.class.php");
require_once('../classes/WellInfo.class.php');

$dbname = $_REQUEST['seldbname'];
	
$wellinfo = new WellInfo($_REQUEST); 
$r = $wellinfo->get_trajectory_witsml('test','testbore','test');
echo $r;
?>
