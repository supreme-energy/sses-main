<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Digital Oil Tools
?>
<!DOCTYPE html>
<head>
<link rel="stylesheet" type="text/css" href="textout.css" />
</head>
<body>
<table class='container'>
<tr>
<td>
<pre>
<?php
$seldbname=$_POST['seldbname'];
$entity_id=$_SESSION['entity_id'];
require_once("dbio.class.php");
require 'HTTP/Upload.php';
$upload=new http_upload('en');
printf("Uploading backup file...");
$file=$upload->getFiles('userfile');
if (PEAR::isError($file)) { die ($file->getMessage()); }
if ($file->isValid()) {
	$ext = pathinfo($file->getProp("name"), PATHINFO_EXTENSION);
	shell_exec("rm /tmp/pg_restore.backup.zip");
	shell_exec("rm /tmp/pg_restore.backup");

	if(strcasecmp($ext, "zip")==0)
		$file->setName('pg_restore.backup.zip');
	else
		$file->setName('pg_restore.backup');
	$dest_dir = '/tmp/';
	$dest_name = $file->moveTo($dest_dir);
	if (PEAR::isError($dest_name)) {
		die ($dest_name->getMessage());
	}
	$real = $file->getProp('real');
	echo "\nUploaded $real as $dest_name in $dest_dir\n";
}
elseif ($file->isMissing()) {
	echo "\nNo file selected\n";
	echo "</pre><a href='dbindex.php?seldbname=$seldbname'>Click here to continue</a></td></tr></table>";
	exit;
} elseif ($file->isError()) {
	echo "\n" . $file->errorMsg() . "\n";
	echo "</pre><a href='dbindex.php?seldbname=$seldbname'>Click here to continue</a></td></tr></table>";
	exit;
}

$retstr=array(); $retval=0;
if(strcasecmp($ext, "zip")==0) {
	echo("Unzipping database archive...");
	exec('unzip -p /tmp/pg_restore.backup.zip >/tmp/pg_restore.backup', &$retstr, &$retval);
	if ($retval!=0) {
		echo "\n";
		foreach($retstr as $rs) { echo "$rs\n"; }
		echo "<a href='dbindex.php?seldbname=$seldbname'>Click here to continue</a></td></tr></table>";
		echo "</pre>";
		exit;
	}
}

echo "\nCreating database and index...";
$realname=basename($real, ".backup.zip");
echo $realname;
$db=new dbio("sgta_index");
$db->OpenDb();
$db->DoQuery("INSERT INTO dbindex (dbname) VALUES ('xxx');");
$db->DoQuery("SELECT id,dbname FROM dbindex WHERE dbname='xxx';");
if($db->FetchRow()) $id = $db->FetchField("id");
if($id!="") {
	$newdbname="sgta_$id";
	$query="CREATE DATABASE $newdbname;";
	$result=$db->DoQuery($query);
	if($result!=FALSE) {
		$query="UPDATE dbindex SET dbname='$newdbname',realname='$realname' WHERE id='$id';";
		$db->DoQuery($query);
	}
	else	die("Failed to create new database\n");
} else die("Failed to update dbindex!\n");

echo "\nRestoring database from backup...";
$retstr=array(); $retval=0;
exec("export PGPASSWORD=umsdata;psql -U umsdata -d $newdbname </tmp/pg_restore.backup", &$retstr, &$retval);
if(count($retstr)<2) {
	echo "ERROR: Invalid backup file: $real\n";
	foreach($retstr as $rs) { echo "$rs\n"; }
	echo "Aborting...\n";
	echo "DELETE FROM dbindex WHERE id='$id';\n";
	echo "DROP DATABASE $newdbname;\n";
	$query="DELETE FROM dbindex WHERE id='$id';";
	$db->DoQuery($query);
	$query="DROP DATABASE $newdbname;";
	$db->DoQuery($query);
	$db->CloseDb();
	$newdbname="";
} else {
	$db->CloseDb();
	include "dbupdate.php";
}
?>
Done!
</pre>
<a href='dbindex.php?seldbname=<?echo $newdbname;?>'>Click here to continue</a>
</td>
</tr>
</table>
</body>
</html>
