<?php
  error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
  header("Access-Control-Allow-Origin: *");
  header('Content-type: application/json');
  require_once(realpath( dirname( __FILE__ ) ).'/../dbio.class.php');
  if(!isset($seldbname) || $seldbname == '') $seldbname = (isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '');
  $db=new dbio($seldbname);
  $db->OpenDb();
?>