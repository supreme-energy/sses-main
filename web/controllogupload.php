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
	$dest_dir = './tmp/';
	$dest_name = $file->moveTo($dest_dir);
	if (PEAR::isError($dest_name)) {
		die ($dest_name->getMessage());
	}
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

require_once("dbio.class.php");
$seldbname=$_POST['seldbname'];
$db=new dbio($seldbname);
$db->OpenDb();

// drop all control log tables and remove their entries in controllogs table
$tablelist=array();
$tableids=array();
$db->DoQuery("SELECT * FROM controllogs;");
while($db->FetchRow()) {
	$id = $db->FetchField("id");
	$tn = $db->FetchField("tablename");
	$tablelist[]=$tn;
	$tableids[]=$id;
}
foreach($tablelist as $tn)
	$db->DoQuery("DROP TABLE \"$tn\";");
foreach($tableids as $id)
	$db->DoQuery("DELETE FROM controllogs WHERE id='$id';");

// create an entry in the controllogs table
$db->DoQuery("INSERT INTO controllogs (tablename) VALUES ('xxxxxx');");
$db->DoQuery("SELECT * FROM controllogs WHERE tablename='xxxxxx';");
$id="";
if($db->FetchRow())
	$id = $db->FetchField("id");

// create table which contains imported data
if($id!="") {
	$tablename="cld_$id";
	$query="CREATE TABLE \"$tablename\" (id serial not null primary key, md float, tvd float, vs float, value float, hide smallint not null default 0);";
	$db->DoQuery($query);
	$query="UPDATE controllogs SET tablename='$tablename' WHERE id='$id';";
	$db->DoQuery($query);
}
else die("<pre>Id for new table entry not found!\n</pre>");

$startmd=$startvs=$starttvd=99999;
$endmd=$endvs=$endtvd=-99999;
if($tablename=="")	die("<pre>Table name not given $tablename\n</pre>");
if($filename=="")	die("<pre>LAS file name not given\n</pre>");

$temp=tmpfile();
$infile=fopen("$filename", "r");
if(!$infile)	die("<pre>File not found: $filename\n</pre>");
do {
	$line=fgets($infile,1024);
	if($line==FALSE) 
		die("End of file looking for ~A data section\n");
} while(stristr($line, "~A")==FALSE);

while($line=fgets($infile,1024)) {
	$line=trim($line);
	$line=preg_replace( '/\s+/', ',', $line );
	fputs($temp, "$line\n");
}
fclose($infile);
fseek($temp,0);
$result=$db->DoQuery("DELETE FROM \"$tablename\";");
if($result==FALSE)
	die("<pre>Table does not exist: $tablename\n$result</pre>");

fseek($temp,0);
$db->DoQuery("BEGIN TRANSACTION;");
while (($data = fgetcsv($temp, 5000, ",")) !== FALSE) {
	$md=$data[0];
	$val=$data[1];
	$tvd=$data[2];
	$vs=$data[3];
	if($md=="")	$md=0;
	if($tvd=="")	$tvd=0;
	if($vs=="")	$vs=0;
	if($value=="")	$value=0;
	$result=$db->DoQuery("INSERT INTO \"$tablename\" (md,value,tvd,vs) VALUES ($md,$val,$tvd,$vs);");
	if($result==FALSE)
		die("<pre>Table does not exist: $tablename\n$result</pre>");
	if($md>$endmd)	$endmd=$md;
	if($md<$startmd)	$startmd=$md;
}
$result=$db->DoQuery("COMMIT;");
if($result==FALSE)
	die("<pre>Bad bad errors on COMMIT: $tablename\n</pre>");
fclose($temp);
unlink($filename);
$db->DoQuery("UPDATE controllogs SET startmd=$startmd,endmd=$endmd WHERE id=$id;");
$db->CloseDb();
header("Location: gva_tab2.php?seldbname=$seldbname");
?>
