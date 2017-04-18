<?php
/*
 * Created on Jan 5, 2013
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
$call_back = $_REQUEST['callback'];
$seldbname=$_REQUEST['seldbname'];
$startdepth=$_REQUEST['sdepth'];
$enddepth = $_REQUEST['edepth'];
$wo = $_REQUEST['wo']=='true'?true:false;
$groupid  = isset($_REQUEST['groupid'])?($_REQUEST['groupid']=='false'?false:$_REQUEST['groupid']):false;
require_once("../dbio.class.php");
require_once("../classes/WitsmlData.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
include("../readwellinfo.inc.php");
$db->CloseDb();
if($autorc_type=='rigminder'){
	require_once('../classes/RigMinderConnection.php');
	header('Content-type: application/json');
	//$obj= new RigMinderConnection($_REQUEST);
	//$next = $obj->load_next_survey($do_load);
	$next = array('status'=>'not implemented for rigminder');
	echo json_encode($next);
}elseif($autorc_type=='polaris'){
	require_once('../classes/PolarisConnection.class.php');
	header('Content-type: application/json');
	$obj= new PolarisConnection($_REQUEST);
	$db->OpenDB();
	$query = "select * from witsml_details";
	$db->DoQuery($query);
	$row = $db->fetchRow();
	if(!$row['wellid'] || !$row['boreid'] || !$row['logid']){
		$next = array("next_survey"=>false,"md"=>'',"inc"=>'',"azm"=>'',"msg"=>'Connection not configured. Please configure the connection selecting a well, a well bore and a log.');;
		$survey_range_loaded=array("surveys"=>$next,'startdepth'=>'N/A','enddepth'=>'N/A');
	} else{
		$obj->uidWell=$row['wellid'];
		$obj->uidWellBore=$row['boreid'];
		$next = $obj->load_surveys_in_range($startdepth,$enddepth,$groupid);
		$survey_range_loaded=array("surveys"=>$next,'startdepth'=>$startdepth,'enddepth'=>$enddepth);
	}
	$db->CloseDb();
	echo json_encode($survey_range_loaded);
}
?>
