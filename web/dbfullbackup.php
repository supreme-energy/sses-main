<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<? 
require_once("dbio.class.php");
$db=new dbio("sgta_index");
$db->OpenDb();
$db->DoQuery("SELECT * FROM dbindex ORDER BY id;");
$numdbs=0;
$filelist=array();
while($db->FetchRow()) {
	$dbname=$db->FetchField("dbname");
	$realname=$db->FetchField("realname");
	$badchars = array("\t"," ","/","\\","(",")");
	$goodname = str_replace($badchars, "", $realname);
	$file=sprintf("/tmp/%s.backup.zip", $goodname);
	$tmpfile=sprintf("/tmp/%s.backup", $goodname);
	shell_exec("pg_dump --format=p --user=umsdata $dbname >$tmpfile");
	shell_exec("zip -j $file $tmpfile");
	shell_exec("rm $tmpfile");
	$filelist[]=$file;
	$numdbs++;
} 

for($i=0; $i<$numdbs; $i++) {
	// header("Content-type: application/force-download");
	/*header("Content-type: application/zip");
	header("Content-Transfer-Encoding: Binary");
	header("Content-length: ".filesize($file));
	header('Content-Disposition: attachment; filename="'.basename($file).'"');
	readfile("$file");
	shell_exec("rm $file"); */
?>
<form action="file-upload.php" method="post" enctype="multipart/form-data">
  Send these files:<br />
  <input name="userfile[]" type="file" /><br />
  <input name="userfile[]" type="file" /><br />
  <input type="submit" value="Send files" />
</form>
<?  } ?> 

<?
for($i=0; $i<$numdbs; $i++) shell_exec("rm $filelist[$i]");
?>
