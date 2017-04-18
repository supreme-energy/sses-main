<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
$seldbname=$_POST['seldbname'];
$sortdir=$_POST['sortdir'];
$id=$_POST['id'];
$md=$_POST['md']; if($md=='')	$md=0.0;
$inc=$_POST['inc']; if($inc=='')	$inc=0.0;
$azm=$_POST['azm']; if($azm=='')	$azm=0.0;
$tvd=$_POST['tvd']; if($tvd=='')	$tvd=0.0;
$ns=$_POST['ns']; if($ns=='')	$ns=0.0;
$ew=$_POST['ew']; if($ew=='')	$ew=0.0;
$vs=$_POST['vs']; if($vs=='')	$vs=0.0;
$dip=$_POST['dip']; if($dip=='')	$dip=0.0;
require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("UPDATE surveys SET md=$md, inc=$inc, azm=$azm, dip=$dip WHERE id=$id");
$db->DoQuery("UPDATE surveys SET tvd=$tvd, ns=$ns, ew=$ew, vs=$vs WHERE id=$id");
$db->CloseDb();
// exec ("./sses_cc -d $seldbname");
// exec("./sses_gva -d $seldbname");
header("Location: gva_tab3.php?seldbname=$seldbname&sortdir=$sortdir");
exit();
?>
