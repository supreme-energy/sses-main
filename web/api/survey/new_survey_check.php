<?php 
  include ("../api_header.php");

  // Created on Jan 5, 2013
  //
  // To change the template for this generated file go to
  // Window - Preferences - PHPeclipse - PHP - Code Templates
  
  $call_back = $_REQUEST['callback'];
  $do_load = false;

  if(isset($_REQUEST['load'])){
      $do_load=filter_var($_REQUEST['load'], FILTER_VALIDATE_BOOLEAN);
  }
  $do_cleanup=false;
  if(isset($_REQUEST['cleanup'])){
      $do_cleanup=filter_var($_REQUEST['cleanup'], FILTER_VALIDATE_BOOLEAN);
  }  
  require_once("../../classes/WitsmlData.class.php");  
  include("../../readwellinfo.inc.php");
  $db->CloseDb();
  if($autorc_type=='rigminder'){
      require_once('../../classes/RigMinderConnection.php');
      header('Content-type: application/json');
      $obj= new RigMinderConnection($_REQUEST);
      $next = $obj->load_next_survey($do_load);
      echo json_encode($next);
  }elseif($autorc_type=='polaris' || $autorc_type=='digidrill' || $autorc_type=='welldata'){
      require_once('../../classes/PolarisConnection.class.php');
      header('Content-type: application/json');
      $obj= new PolarisConnection($_REQUEST);
      $obj->autorc_type=$autorc_type;
      $db->OpenDB();
      $query = "select * from witsml_details";
      $db->DoQuery($query);
      $row = $db->fetchRow();
      if(!$row['wellid'] || !$row['boreid'] || !$row['logid']){
          $next = array("next_survey"=>false,"md"=>'',"inc"=>'',"azm"=>'',"msg"=>'Connection not configured. Please configure the connection selecting a well, a well bore and a log.');;
      } else {
          $obj->uidWell=$row['wellid'];
          $obj->uidWellBore=$row['boreid'];
          //$obj->logid=$row['logid'];
          $next = $obj->load_next_survey($do_load,$do_cleanup);
      }
      echo json_encode($next);
  }
  ?>
  
?>