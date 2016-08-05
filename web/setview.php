<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Supreme Source Energy Services, Inc.
//	All rights reserved.
//	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.

require_once("dbio.class.php");
$ret=$_POST['ret'];
$tablename=$_POST['tablename'];
$scrolltop=$_POST['scrolltop'];
$scrollleft=$_POST['scrollleft'];
$viewallds=$_POST['viewallds'];
$viewrotds=$_POST['viewrotds'];
$seldbname=$_POST['seldbname'];
$depth=$_POST['depth'];
$sgtastart=$_POST['sgtastart'];
$sgtaend=$_POST['sgtaend'];
$sgtacutin=$_POST['sgtacutin'];
$sgtacutoff=$_POST['sgtacutoff'];
$viewdspcnt=$_POST['viewdspcnt'];

/*
$startmd=$_POST['startmd'];
$endmd=$_POST['endmd'];
if($viewallds>1) {
	echo "<pre>change selected datasets from $sgtacutin,$sgtacutoff to ";
	$sgtacutin=$startmd-$viewallds;
	$sgtacutoff=$endmd;
	echo "$sgtacutin,$sgtacutoff spacing:$viewallds\n</pre>";
}
*/

$forcesel=1;

$db=new dbio($seldbname);
$db->OpenDb();
if($viewrotds!="") $db->DoQuery("UPDATE appinfo SET viewrotds='$viewrotds'");
if($viewallds!="") $db->DoQuery("UPDATE appinfo SET viewallds='$viewallds'");
if($viewdspcnt!="") $db->DoQuery("UPDATE appinfo SET viewdspcnt='$viewdspcnt'");
if($viewdspcnt<=0) $db->DoQuery("UPDATE appinfo SET dscache_freeze=0");

/*
if($sgtastart!="") $db->DoQuery("UPDATE appinfo SET sgtastart='$sgtastart' WHERE id='$id';");
if($sgtaend!="") $db->DoQuery("UPDATE appinfo SET sgtaend='$sgtaend' WHERE id='$id';");
if($sgtacutin!="") $db->DoQuery("UPDATE appinfo SET sgtacutin='$sgtacutin' WHERE id='$id';");
if($sgtacutoff!="") $db->DoQuery("UPDATE appinfo SET sgtacutoff='$sgtacutoff' WHERE id='$id';");
*/

$db->CloseDb();
include($ret);
?>

