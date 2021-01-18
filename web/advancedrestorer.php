<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="textout.css" />
</head>
<body>
<table class='container'>
<tr>
<td>
<TEXTAREA id='textarea' cols='100' rows='40'>
<?php
$seldbname=$_POST['seldbname'];
$dbname=$_POST['dbname'];
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
	echo "</pre><a href='advancedjobmanager.php?seldbname=$seldbname'>Click here to continue</a></td></tr></table>";
	exit;
} elseif ($file->isError()) {
	echo "\n" . $file->errorMsg() . "\n";
	echo "</pre><a href='advancedjobmanager.php?seldbname=$seldbname'>Click here to continue</a></td></tr></table>";
	exit;
}

$retstr=array(); $retval=0;
if(strcasecmp($ext, "zip")==0) {
	echo("Unzipping database archive...");
	exec('unzip -p /tmp/pg_restore.backup.zip >/tmp/pg_restore.backup', &$retstr, &$retval);
	if ($retval!=0) {
		echo "\n";
		foreach($retstr as $rs) { echo "$rs\n"; }
		echo "<a href='advancedjobmanager.php?seldbname=$seldbname'>Click here to continue</a></td></tr></table>";
		echo "</pre>";
		exit;
	}
}

echo "\nClearing database...";

$db=new dbio("$dbname");
$db->OpenDb();
$dbout=new dbio("$dbname");
$dbout->OpenDb();
// $db->DoQuery("DROP SCHEMA public CASCADE;");
$db->DoQuery("SHOW TABLES");
while($db->FetchRow()) {
	$tn=$db->FetchField("tablename");
	if(strstr($tn, "sql_")==false) $dbout->DoQuery("DROP TABLE \"$tn\";");
}
$db->DoQuery("VACUUM ANALYZE;");
$db->CloseDb();
$dbout->CloseDb();

echo "\nRestoring job from backup...";
exec("psql -U umsdata -d $dbname </tmp/pg_restore.backup", &$retstr, &$retval);
include "dbupdate.php";
?>
Done!
<script>
var targetWindow = window.opener;
targetWindow.postMessage('reloadWellList');
</script>
</TEXTAREA>
<a href='advancedjobmanager.php?seldbname=<?echo $dbname;?>'>Click here to continue</a>
</td>
</tr>
</table>
</body>
</html>
