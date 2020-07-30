<?php 

$enddepth = $_REQUEST['edepth'];

include("../api_header.php");
include("import.php");

$query = "select md from surveys where plan= 0 and md < $enddepth order by md desc limt 1";
$db->DoQuery($query);
$frow = $db->FetchRow();
import_from_stored_file($frow[0],$enddepth,$seldbname);
?>