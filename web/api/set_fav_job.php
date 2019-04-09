<?php
  error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
  header("Access-Control-Allow-Origin: *");
  header('Content-type: application/json');
  require_once '../dbio.class.php';
  if(!isset($seldbname) or $seldbname == '') $seldbname = mysql_real_escape_string((isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : ''));
  $fav_val = mysql_real_escape_string($_REQUEST['favorite']);
  $db=new dbio("sgta_index");
  $db->OpenDb();
  $query = "update dbindex set favorite='$fav_val' where dbname='$seldbname'";
  $db->DoQuery($query);
  $response = array("status"=>"success", "message" => "operation successful");
  echo json_encode($response);
?>