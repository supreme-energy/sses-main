<?php
include("../../api_header.php");
$debug=false;

require 'HTTP/Upload.php';
// print_r($HTTP_POST_FILES);
$upload = new http_upload('en');
$file = $upload->getFiles('userfile');
if (PEAR::isError($file)) {
	die ($file->getMessage());
}
if ($file->isValid()) {
	// $file->setName('uniq');
	$file->setName('wellplan.dat');
	$dest_dir = '../../../tmp/';
	$dest_name = $file->moveTo($dest_dir);
	if (PEAR::isError($dest_name)) {
		die ($dest_name->getMessage());
	}
	$real = $file->getProp('real');
	// echo "Uploaded $real as $dest_name in $dest_dir\n";
} elseif ($file->isMissing()) {
    echo json_encode(array('status'=>'error', 'message'=>'file missing'));
    exit;
} elseif ($file->isError()) {
    echo json_encode(array('status'=>'error', 'message'=>$file->errorMsg()));	
	exit;
}

$db->DoQuery("BEGIN TRANSACTION");

$result=$db->DoQuery("DELETE FROM \"wellplan\";");
if($result==FALSE) die("<pre>Error on SQL statement for table: wellplan\n</pre>");

$filename="../../../tmp/wellplan.dat";
$infile=fopen("$filename", "r");
if(!$infile){
    echo json_encode(array('status'=>'error', 'message'=>"File not found: $filename\n"));
    exit;
}

const NUMERICREGEX = "/[^0-9.]/";
while (($data = fgetcsv($infile, 5000, ",")) !== FALSE) {
    
    preg_match(NUMERICREGEX,$data[0],$md);
    preg_match(NUMERICREGEX,$data[1],$inc);
    preg_match(NUMERICREGEX,$data[2],$azm);
	$query = "INSERT INTO wellplan (md,inc,azm) VALUES ('$md[0]','$inc[0]','$azm[0]');";
	echo $query;
	$result=$db->DoQuery($query);	
}
$db->DoQuery("COMMIT");
$db->CloseDb();
fclose($infile);

$output = shell_exec("../../../sses_cc -d $seldbname -w");
// $output = shell_exec("./sses_cc -d $seldbname -w -i ./tmp/wellplan.dat");
//header("Location: ../../wellplan.php?seldbname=$seldbname");
?>
