<?php 
require_once 'sses_include.php';
require_once 'dbio.class.php';
if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_GET['seldbname']) ? $_GET['seldbname'] : '');
if($seldbname == '') include('dberror.php');
$dbname = $_GET['dbname'];
$color  = $_GET['color'];
$db=new dbio($seldbname);
$db->OpenDb();
$sql = "select * from profile_lines where reference_database='$dbname'";
$db->DoQuery($sql);
if($db->FetchRow()){
 $sql = "update profile_lines set color='$color' where reference_database='$dbname'";
 $db->DoQuery($sql);
} else{
 $sql = "insert into profile_lines (color,reference_database,show_plot,show_report) values ('$color','$dbname',1,1)";
 $db->DoQuery($sql);
}
$result = json_encode("done");
$request_back_to = str_replace("_add","",$_SERVER[REQUEST_URI]);
$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$request_back_to";
header("Location: $actual_link"); /* Redirect browser */
exit();
?>