<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?php
$seldbname=$_POST['seldbname'];
$debug=false;
// error_reporting(E_ALL);
// if (!isset($submit)) {
	// exit;
// }
require_once("dbio.class.php");
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
$result=$db->DoQuery("DELETE FROM \"wellplan\";");
if($result==FALSE) die("<pre>Error on SQL statement for table: wellplan\n</pre>");

$filename="./tmp/wellplan.dat";
$infile=fopen("$filename", "r");
if(!$infile)	die("<pre>File not found: $filename\n</pre>");

$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("BEGIN TRANSACTION");
while (($data = fgetcsv($infile, 5000, ",")) !== FALSE) {
	$md=$data[0];
	$inc=$data[1];
	$azm=$data[2];
	$result=$db->DoQuery("INSERT INTO wellplan (md,inc,azm) VALUES ('$md','$inc','$azm');");
	if($result==FALSE) die("<pre>Error on SQL statement for table: wellplan\n</pre>");
}
$db->DoQuery("COMMIT");
$db->CloseDb();
fclose($infile);

$output = shell_exec("./sses_cc -d $seldbname -w");
// $output = shell_exec("./sses_cc -d $seldbname -w -i ./tmp/wellplan.dat");
if (strlen($output) && $debug) {
	echo '<pre>';
	echo $output;
	echo '</pre>';
	exit;
}
else {
	header("Location: gva_tab2.php?seldbname=$seldbname");
}
?>
