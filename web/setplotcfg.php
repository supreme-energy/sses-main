<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?php
require_once("dbio.class.php");
$seldbname=$_POST['seldbname'];
$ret=$_POST['ret'];
$scrolltop=$_POST['scrolltop'];
$scrollleft=$_POST['scrollleft'];
$plotbias=$_POST['plotbias'];
$scaleright=$_POST['scaleright'];
$uselogscale=$_POST['uselogscale'];
$dataavg=$_POST['dataavg'];
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("SELECT id FROM appinfo LIMIT 1;");
if ($db->FetchRow()) {
	$id=$db->FetchField("id");
	if(strlen($plotbias))	$db->DoQuery("UPDATE appinfo SET bias='$plotbias' WHERE id='$id';");
	$db->DoQuery("UPDATE appinfo SET scale='1.0' WHERE id='$id';");
	if(strlen($scaleright))	$db->DoQuery("UPDATE appinfo SET scaleright='$scaleright' WHERE id='$id';");
	if(strlen($uselogscale)>0)	$db->DoQuery("UPDATE appinfo SET uselogscale='$uselogscale' WHERE id='$id';");
	if(strlen($dataavg)>0)	$db->DoQuery("UPDATE appinfo SET dataavg='$dataavg' WHERE id='$id';");
}
$db->CloseDb();
include($ret);
?>
