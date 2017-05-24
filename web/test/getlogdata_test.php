<?php 
require_once("../dbio.class.php");
require_once("../classes/WitsmlData.class.php");
$seldbname=$_REQUEST['seldbname'];
require_once('../classes/PolarisConnection.class.php');
header('Content-type: application/json');
$obj= new PolarisConnection($_REQUEST);
$obj->autorc_type=$autorc_type;
$obj->$this->prepare_las_data(0,8000,1,false,false,false);
?>