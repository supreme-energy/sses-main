<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?php

$seldbname=$_REQUEST['seldbname'];
$tablename=$_REQUEST['tablename'];
$cid = $_REQUEST['cid'];
$sids = $_REQUEST['sids'];

require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
$sids=$_REQUEST['sids'];
$data=explode(",", $sids);
$num=count($data);
if($num>0) {
	$db->DoQuery("BEGIN TRANSACTION");
	for($i=0;$i<$num;$i++) {
		$db->DoQuery("DELETE FROM $tablename WHERE id='$data[$i]'");
	}
	$db->DoQuery("COMMIT");
}
$db->CloseDb();

exec("./sses_ac_cc -t $tablename -d $seldbname");
header("Location: anticollisionwells.php?seldbname=$seldbname&acwellid=$cid");
exit();

?>