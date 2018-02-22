<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Digital Oil Tools

//error_reporting(E_ALL);
//if (!isset($submit)) {
//exit;
//}

require 'HTTP/Upload.php';
require_once("dbio.class.php");
$seldbname=$_POST['seldbname'];
$ret=$_POST['ret'];
$db=new dbio($seldbname);
$db->OpenDb();

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
$filename="./tmp/$seldbname.las";
if(file_exists($filename))	unlink($filename);
$ifn=sprintf("$dest_dir%s", $file->getProp('name'));
rename($ifn, $filename);

$retstr=array();
$retval=0;
exec("./sses_laschk -f $filename -d $seldbname", &$retstr, &$retval);
?>
<?php  if($_REQUEST['JSON']){
	$display_str = join('|',$retstr)
?>
{"status":"success", "message": "<?php echo $display_str ?>"}
<?php } else { ?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="gva_styles.css" />
</head>
<body>
<table class='container'>
<tr>
<td colspan='3'>
	<h1>LAS File Checker Results:
	<?php if($retval!=0) echo "<font color='red'>*** ERROR ***</font>" ?>
	</h1>
</td>
</tr>
<tr>
<td colspan='3' class="container" align='left'>
<?
printf("<pre>");
echo "Uploaded $real as $dest_name in $dest_dir\n";
echo "LAS Checker returned:$retval\n";
foreach($retstr as $rs) {
	echo "$rs\n";
}
echo "</pre>";
?>
</td>
</tr>
<tr>
<td class="container">
	<form method="post" action='<?echo $ret;?>'>
	<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
	<input type='hidden' name='ret' value='<?echo $ret;?>'>
	<input type="submit" value="Cancel Import">
	</form>
</td>
<?if($retval!=0) {?>
<td class="container">
	<form method="post" action='welllogfilesel.php'>
	<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
	<input type='hidden' name='ret' value='<?echo $ret;?>'>
	<input type="submit" value="Choose Another File">
	</form>
</td>
<?}?>
<td class="container">
	<form method="post" action='wellloguploadd.php'>
	<input type='hidden' name='real' value='<?echo $real;?>'>
	<input type='hidden' name='filename' value='<?echo $filename;?>'>
	<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
	<input type='hidden' name='ret' value='<?echo $ret;?>'>
	<input type="submit" value="Continue<?if($retval!=0) echo ' Anyway'?>">
	</form>
</td>
</tr>
</table>
</body>
</html>
<?php } ?>