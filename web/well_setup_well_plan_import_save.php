<?php 
require_once("dbio.class.php");
$seldbname=$_REQUEST['seldbname'];
$wellname = $_REQUEST['wellname'];
$color =  $_REQUEST['colorrawwp'];
$color = str_replace('#','',$color);
$db=new dbio('sgta_index');
$db->OpenDb();
$query="UPDATE dbindex SET realname='$wellname' WHERE dbname='$seldbname';";
$db->DoQuery($query);
$db->CloseDb();

$db=new dbio($seldbname);
$db->OpenDb();
$query="Update wellinfo set colorwp='$color'";
$db->DoQuery($query);

$result=$db->DoQuery("DELETE FROM \"wellplan\";");
if($result==FALSE) die("<pre>Error on SQL statement for table: wellplan\n</pre>");
print_r($_FILES);
$fh = fopen($_FILES['userfile']['tmp_name'], 'r+');

$db->DoQuery("BEGIN TRANSACTION");
$indata = false;

while( ($data = fgetcsv($fh, 8192, ",")) !== FALSE ) {
	
	if($indata){	
		$md=$data[0];
		$inc=$data[1];
		$azm=$data[2];
		if($md!=''){
			$result=$db->DoQuery("INSERT INTO wellplan (md,inc,azm) VALUES ('$md','$inc','$azm');");
			if($result==FALSE) die("<pre>Error on SQL statement for table: wellplan\n</pre>");
		}
	}
	if($data[0]=='MD'){
		$indata = true;		
	}
}
$db->DoQuery("COMMIT");
$db->CloseDb();
fclose($fh);

$output = shell_exec("./sses_cc -d $seldbname -w");
// $output = shell_exec("./sses_cc -d $seldbname -w -i ./tmp/wellplan.dat");
if (strlen($output) && $debug) {
	echo '<pre>';
	echo $output;
	echo '</pre>';
	exit;
}

header("Location: well_setup_control_log_import.php?seldbname=$seldbname");
?>