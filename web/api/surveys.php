 <?php
 header("Access-Control-Allow-Origin: *");
 header('Content-type: application/json');
 require_once '../dbio.class.php';
 if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '');
 $db=new dbio("sgta_index");
 $db->OpenDb();
 $db->DoQuery("SELECT * FROM dbindex ORDER BY id;");
 while($db->FetchRow()) {
 	$dbn=$db->FetchField("dbname");
 	if($seldbname==$dbn) $dbrealname=$db->FetchField("realname");
 }
 $db->CloseDb();
 $db=new dbio($seldbname);
 $db->OpenDb();
 include("../readsurveys.inc.php");
 echo json_encode($srvys_joined);
 ?>