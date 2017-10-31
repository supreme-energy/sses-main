<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
$seldbname=$_REQUEST['seldbname'];
$tablename=$_REQUEST['tablename'];
$cid = $_REQUEST['cid'];
$id=$_REQUEST['id'];
$md=$_REQUEST['md']; if($md=='')	$md=0.0;
$inc=$_REQUEST['inc']; if($inc=='')	$inc=0.0;
$azm=$_REQUEST['azm']; if($azm=='')	$azm=0.0;
$tvd=$_REQUEST['tvd']; if($tvd=='')	$tvd=0.0;
$ns=$_REQUEST['ns']; if($ns=='')	$ns=0.0;
$ew=$_REQUEST['ew']; if($ew=='')	$ew=0.0;
$vs=$_REQUEST['vs']; if($vs=='')	$vs=0.0;
$plan=$_REQUEST['plan']; if($plan=='')	$plan=0;
require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("UPDATE $tablename SET md=$md, inc=$inc, azm=$azm, tvd=$tvd, vs=$vs, ns=$ns, ew=$ew WHERE id=$id;");
$db->CloseDb();
exec("./sses_ac_cc -t $tablename -d $seldbname");
header("Location: anticollisionwells.php?seldbname=$seldbname&acwellid=$cid");
exit();
?>