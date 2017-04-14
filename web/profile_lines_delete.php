<?php 
require_once 'sses_include.php';
require_once 'dbio.class.php';
if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_GET['seldbname']) ? $_GET['seldbname'] : '');
if($seldbname == '') include('dberror.php');
$dbname = $_GET['dbname'];
$db=new dbio($seldbname);
$db->OpenDb();
$sql = "delete from profile_lines where reference_database='$dbname'";
$db->DoQuery($sql);
$result = json_encode("done");
echo $result;
?>