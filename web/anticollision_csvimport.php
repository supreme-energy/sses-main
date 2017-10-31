<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?

require 'HTTP/Upload.php';
require_once("dbio.class.php");
error_reporting(E_ALL);
$upload = new http_upload('en');
$file = $upload->getFiles('userfile');
$seldbname=$_REQUEST['seldbname'];
$tablename=$_REQUEST['tablename'];
$cid = $_REQUEST['cid'];
if (PEAR::isError($file)) {
	die ($file->getMessage());
}
if ($file->isValid()) {
	// $file->setName('uniq');
	$file->setName($tablename.'.dat');
	$dest_dir = './tmp/';
	$dest_name = $file->moveTo($dest_dir);
	if (PEAR::isError($dest_name)) {
		die ($dest_name->getMessage());
	}
	$real = $file->getProp('real');
	// echo "Uploaded $real as $dest_name in $dest_dir\n";
} elseif ($file->isMissing()) {
	echo '<pre>';
	echo "No file selected\n";
	echo '</pre>';
	exit;
} elseif ($file->isError()) {
	echo '<pre>';
	echo $file->errorMsg() . "\n";
	echo '</pre>';
	exit;
}
$db=new dbio($seldbname);
$db->OpenDb();

$result=$db->DoQuery("DELETE FROM \"$tablename\";");
if($result==FALSE) die("<pre>Error on SQL statement for table: $tablename\n</pre>");

$filename="./tmp/$tablename.dat";
$infile=fopen("$filename", "r");
if(!$infile)	die("<pre>File not found: $filename\n</pre>");
$db->DoQuery("BEGIN TRANSACTION");
while (($data = fgetcsv($infile, 5000, ",")) !== FALSE) {
	if(is_numeric($data[0])){
		$md=$data[0];
		$inc=$data[1];
		$azm=$data[2];
		if(count($data)>3){
			$tvd = $data[3];
			$vs = $data[4];
			$ns = $data[5];
			$ew = $data[6];
			if($tvd){
				$result=$db->DoQuery("INSERT INTO $tablename (md,inc,azm,tvd,vs,ns,ew) VALUES ('$md','$inc','$azm','$tvd','$vs','$ns','$ew');");
			} else {
				$result=$db->DoQuery("INSERT INTO $tablename (md,inc,azm) VALUES ('$md','$inc','$azm');");
			}
		} else {
			$result=$db->DoQuery("INSERT INTO $tablename (md,inc,azm) VALUES ('$md','$inc','$azm');");
		}
		
		if($result==FALSE) die("<pre>Error on SQL statement for table: wellplan\n</pre>");
	}
}
$db->DoQuery("COMMIT");
$db->CloseDb();
fclose($infile);
exec("./sses_ac_cc -t $tablename -d $seldbname");
header("Location: anticollisionwells.php?seldbname=$seldbname&acwellid=$cid");
exit();
?>