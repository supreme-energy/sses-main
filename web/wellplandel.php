<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
require_once("dbio.class.php");
$seldbname=$_POST['seldbname'];
$sortdir=$_POST['sortdir'];
$sids=$_POST['sids'];
$data=explode(",", $sids);
$num=count($data);
if($num>0) {
	$db=new dbio($seldbname);
	$db->OpenDb();
	$db->DoQuery("BEGIN TRANSACTION");
	for($i=0;$i<$num;$i++) {
		$db->DoQuery("DELETE FROM wellplan WHERE id='$data[$i]'");
	}
	$db->DoQuery("COMMIT");
	$db->CloseDb();
	exec ("./sses_cc -d $seldbname -w");
}
include "gva_tab2.php";
?>
