<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?php

function StripExtraSpace($s) {
	/*
	for($i = 0; $i < strlen($s); $i++)
	{
		$newstr = $newstr . substr($s, $i, 1);
		if(substr($s, $i, 1) == ' ') {
			while(substr($s, $i + 1, 1) == ' ')
				$i++;
		}
	}
	*/
	$newstr="";
	$tok=strtok($s, " 	,");
	while($tok!=false) {
		$newstr=$newstr."$tok";
		$tok=strtok(" 	,");
		if($tok!=false)	$newstr=$newstr." ";
	}
	return $newstr;
} 
// error_reporting(E_ALL);
// if (!isset($submit)) {
	// exit;
// }

require 'HTTP/Upload.php';
require_once("dbio.class.php");

// main loop
$seldbname=$_POST['seldbname'];
$ret=$_POST['ret'];

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
	$real = $file->getProp('real');
	// echo "Uploaded $real as $dest_name in $dest_dir\n";
} elseif ($file->isMissing()) {
	echo '<pre>No file selected\n</pre>';
	include "$ret";
	exit;
} elseif ($file->isError()) {
	echo '<pre>';
	echo $file->errorMsg() . "\n";
	echo '</pre>';
	exit;
}

// get the downloaded filename
// $filename="./tmp/$seldbname.las";
// if(file_exists($filename))	unlink($filename);
// $ifn=sprintf("$dest_dir%s", $file->getProp('name'));
// rename($ifn, $filename);
$filename=sprintf("$dest_dir%s", $file->getProp('name'));


$db=new dbio($seldbname);
$db->OpenDb();


/* create a temp file for storage and parse the LAS data */
$temp=tmpfile();
$infile=fopen("$filename", "r");
if(!$infile)	die("<pre>File not found: $filename\n</pre>");

// check for valid data section
do {
	$line=fgets($infile,1024);
	if($line==FALSE) 
		die("End of file looking for ~A data section\n");
} while(stristr($line, "~A")==FALSE);

// fetch the ascii log data section and write to temp
while($line=fgets($infile,1024)) {
	$line=StripExtraSpace($line);
	$line=Trim($line);
	if(strlen($line)>1) {
		fputs($temp, $line);
		fputs($temp, "\n");
	}
	// echo "<pre>$line</pre>";
}
fclose($infile);
unlink($filename);

// set the column delimiter
$delim="x";
fseek($temp,0);
if (($data = fgetcsv($temp, 5000, "$delim")) !== FALSE) {
	if(strstr($data[0], " ")!=FALSE){ $delim=" "; }
	if(strstr($data[0], ",")!=FALSE){ $delim=","; }
	if(strstr($data[0], "\t")!=FALSE){ $delim="\t"; }
}

// get the edata definitions
$tablenames=array();
$db->DoQuery("SELECT * FROM edatalogs ORDER BY colnum;");
while($db->FetchRow()) $tablenames[]=$db->FetchField("tablename");
$tablecount=count($tablenames);

// make sure the table is empty
// $result=$db->DoQuery("DELETE FROM \"$tablename\";");
// if($result==FALSE) die("<pre>Table does not exist: $tablename\n</pre>");

// reset the file pointer and save data to table
$datacnt=0;
$gotsurvey=0;
fseek($temp,0);

while (($data = fgetcsv($temp, 5000, "$delim")) !== FALSE) {
	$cnt=count($data);
	if($cnt<4)	break;
	$md=$data[0];
	$gr=$data[1];
	$tvd=$data[2];
	$vs=$data[3];
	$inc=$data[4];
	$azm=$data[5];
	// check for edata
	$db->DoQuery("select count(*) as cnt from add_data_gamma_fb where md=$md and tvd=$tvd and vs=$vs;");
	$db->FetchRow();
	if($db->FetchField('cnt')<=0){
		$db->DoQuery("INSERT INTO add_data_gamma_fb (md,value,tvd,vs) values($md,$gr,$tvd,$vs);");
	}
	$db->DoQuery("BEGIN TRANSACTION;");
	for($i=0; $i<$tablecount; $i++) {
		$col=$i+6;
		if($cnt>$col) {
			$tn=$tablenames[$i];
			$val=$data[$col];
			if(strlen($val)<=0) {
				echo "<pre>Zero value on col:$col\n</pre>";
				echo "<pre>$md, $gr, $tvd, $vs, $inc, $azm, $val\n</pre>"; 
				exit();
			}
			else
			$db->DoQuery("INSERT INTO \"$tn\" (md,value,tvd,vs) VALUES ($md,$val,$tvd,$vs);");
		}
	}
	$result=$db->DoQuery("COMMIT;");
}

// if($result==FALSE) die("<pre>Bad bad errors on COMMIT: $tablename\n</pre>");
fclose($temp);
$db->CloseDb();

// header("Location: $ret");
// exit();
include("$ret");
?>
