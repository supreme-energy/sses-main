<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Digital Oil Tools

require_once("dbio.class.php");
$ret=$_POST['ret'];
$tablename=$_POST['tablename'];
$scrolltop=$_POST['scrolltop'];
$scrollleft=$_POST['scrollleft'];
$dscnt=$_POST['dscnt'];
$dsnum=$_POST['dsnum'];
$startmd=$_POST['startmd'];
$endmd=$_POST['endmd'];
$bias=$_POST['bias'];
$sectdip=$_POST['sectdip'];
$sectfault=$_POST['sectfault'];
$factor=$_POST['factor'];
$seldbname=$_POST['seldbname'];
$sgtastart=$_POST['sgtastart'];
$sgtaend=$_POST['sgtaend'];
$sgtacutin=$_POST['sgtacutin'];
$sgtacutoff=$_POST['sgtacutoff'];
$forcesel=1;
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("SELECT id FROM welllogs WHERE tablename='$tablename';");
if($db->FetchRow())
{
	$id=$db->FetchField("id");
	if(strlen($bias))	$db->DoQuery("UPDATE welllogs SET scalebias='$bias' WHERE id='$id';");
	if(strlen($factor))	$db->DoQuery("UPDATE welllogs SET scalefactor='$factor' WHERE id='$id';");
	if(strlen($sectfault))	$db->DoQuery("UPDATE welllogs SET fault='$sectfault' WHERE id='$id';");
	if(strlen($sectdip))
	{
		if($sectdip < -89.9) $sectdip=-89.9;
		if($sectdip > 89.9)	$sectdip=89.9;
		$db->DoQuery("UPDATE welllogs SET dip='$sectdip' WHERE id='$id';");
	}
}
$db->CloseDb();
$editmode='redraw';
include($ret);
?>
