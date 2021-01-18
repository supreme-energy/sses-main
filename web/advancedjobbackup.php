<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<? 
$seldbname=$_POST['seldbname'];
$dbname=$_POST['dbname'];
$realname=$_POST['realname'];

$badchars = array("\t"," ","/","\\","(",")");
$goodname = str_replace($badchars, "", $realname);

$file=sprintf("/tmp/%s.backup.zip", $goodname);
$tmpfile=sprintf("/tmp/%s.backup", $goodname);
shell_exec("pg_dump --format=p --user=umsdata $dbname >$tmpfile");
shell_exec("zip -j $file $tmpfile");
shell_exec("rm $tmpfile");

// header("Content-type: application/force-download");
header("Content-type: application/zip");
header("Content-Transfer-Encoding: Binary");
header("Content-length: ".filesize($file));
header('Content-Disposition: attachment; filename="'.basename($file).'"');
readfile("$file");
shell_exec("rm $file");
header("Location: advancedjobmanager.php?seldbname=$seldbname");
exit();
?> 
