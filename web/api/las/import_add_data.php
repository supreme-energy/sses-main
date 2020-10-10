<?php 
include ("../api_header.php");
require_once('../../classes/LasFileConnection.class.php');
require_once("../../classes/WitsmlData.class.php");
include("../../readwellinfo.inc.php");
$obj = new LasFileConnection($_REQUEST);
$obj->import_add_data();

?>