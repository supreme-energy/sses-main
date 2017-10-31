<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?php
// error_reporting(E_ALL);
// if (!isset($submit)) {
	// exit;
// }
require 'HTTP/Upload.php';
// print_r($HTTP_POST_FILES);
$upload = new http_upload('en');
$file = $upload->getFiles('userfile');
if (PEAR::isError($file)) {
	die ($file->getMessage());
}
if ($file->isValid()) {
	$file->setName('uniq');
	// $file->setName('wellplan.dat');
	$dest_dir = './tmp/';
	$dest_name = $file->moveTo($dest_dir);
	if (PEAR::isError($dest_name)) {
		die ($dest_name->getMessage());
	}
	// $real = $file->getProp('real');
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
$filename=sprintf("$dest_dir%s", $file->getProp('name'));
// echo "Filename: $filename\n";

require_once("dbio.class.php");
$seldbname=$_POST['seldbname'];
$db=new dbio($seldbname);
$db->OpenDb();
if($filename=="")	die("<pre>CSV file name not given\n</pre>");

$temp=tmpfile();
$infile=fopen("$filename", "r");
if(!$infile)	die("<pre>File not found: $filename\n</pre>");

while($line=fgets($infile,1024)) {
	$line=trim($line);
	$line=preg_replace( '/\s+/', ',', $line );
	$data=explode(",", $line);
	if(is_numeric($data[0]))
		fputs($temp, "$line\n");
}
fclose($infile);
fseek($temp,0);

// find first and last survey depths
$result=$db->DoQuery("SELECT md FROM \"surveys\" ORDER BY md DESC;");
if($result==FALSE) die("<pre>Error on SQL statement for table: surveys\n</pre>");
if($db->FetchRow()) $enddepth=$db->FetchField("md");
$result=$db->DoQuery("SELECT md FROM \"surveys\" ORDER BY md ASC;");
if($result==FALSE) die("<pre>Error on SQL statement for table: surveys\n</pre>");
if($db->FetchRow()) $startdepth=$db->FetchField("md");

// $result=$db->DoQuery("DELETE FROM \"surveys\";");
// if($result==FALSE) die("<pre>Error on SQL statement for table: surveys\n</pre>");

$db2=new dbio($seldbname);
$db2->OpenDb();
$db2->DoQuery("BEGIN TRANSACTION");
fseek($temp,0);
while (($data = fgetcsv($temp, 5000, ",")) !== FALSE) {
	$md=$data[0];
	$inc=$data[1];
	$azm=$data[2];
	$result=$db->DoQuery("SELECT * FROM \"surveys\" WHERE md=$md;");
	if($result==FALSE) die("<pre>Error on SQL statement for table: surveys\n</pre>");
	if($db->FetchNumRows()>0) {
		$db->FetchRow();
		$id=$db->FetchField("id");
		$smd=$db->FetchField("md");
		if($smd!=$md) {
			$result=$db2->DoQuery("UPDATE surveys SET md='$md',inc='$inc',azm='$azm' WHERE id=$id;");
			if($result==FALSE) die("<pre>Error on SQL statement for table: surveys\n</pre>");
		}
	}
	else {
		$result=$db2->DoQuery("INSERT INTO surveys (md,inc,azm) VALUES ('$md','$inc','$azm');");
		if($result==FALSE) die("<pre>Error on SQL statement for table: surveys\n</pre>");
	}
}
$db2->DoQuery("COMMIT");
fclose($temp);
unlink($filename);
$db->DoQuery("delete from projections where ptype='rot' or ptype='sld'");
$db->CloseDb();
$db2->CloseDb();
// exec ("./sses_cc -d $seldbname");
// exec ("./sses_gva -d $seldbname");
header("Location: gva_tab3.php?seldbname=$seldbname");
?>
