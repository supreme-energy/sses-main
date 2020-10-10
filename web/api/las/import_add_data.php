<?php 
include ("../../api_header.php");
require_once('../../../classes/LasFileConnection.class.php');

$obj = new LasFileConnection($_REQUEST);
$obj->import_add_data();

?>