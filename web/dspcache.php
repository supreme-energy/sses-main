<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
require_once("dbio.class.php");
// if("$seldbname"=="")	$seldbname=$_GET['seldbname'];
// if("$seldbname"=="") include("dberror.php");
$seldbname="sgta_120";
$db=new dbio("sgta_index");
$db->OpenDb();
$db->DoQuery("SELECT * FROM dbindex ORDER BY id;");
while($db->FetchRow()) {
	$dbids=$db->FetchField("id");
	$dbn=$db->FetchField("dbname");
	$dbreal=$db->FetchField("realname");
	if($seldbname==$dbn) $dbrealname=$dbreal;
} 
$db->CloseDb();
$db=new dbio($seldbname);
$db->OpenDb();
include "readwellinfo.inc.php";
include "readappinfo.inc.php";
$db->DoQuery("SELECT * FROM controllogs ORDER BY tablename");
if ($db->FetchRow()) {
	$tablename=$db->FetchField("tablename");
	$startmd=$db->FetchField("startmd");
	$endmd=$db->FetchField("endmd");
}
$db->CloseDb();
$fn=sprintf("./tmp/%s_gva_tab3_dspcache.png", $seldbname);
$fn=sprintf("/tmp/t.png", $seldbname);
$logsw=""; if($uselogscale>0)	$logsw="-log";
exec("./sses_pd -T $tablename -d $seldbname -o $fn -w 200 -h 750 -s $startmd -e $endmd -r $scaleright $logsw");
?>

