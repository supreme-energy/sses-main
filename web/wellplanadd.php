<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
$seldbname=$_POST['seldbname'];
$md=$_POST['md']; if($md=="") $md=0;
$inc=$_POST['inc']; if($inc=="") $inc=0;
$azm=$_POST['azm']; if($azm=="") $azm=0;
$tvd=$_POST['tvd']; if($tvd=="") $tvd=0;
$vs=$_POST['vs']; if($vs=="") $vs=0;
$ns=$_POST['ns']; if($ns=="") $ns=0;
$ew=$_POST['ew']; if($ew=="") $ew=0;
$plan=$_POST['plan']; if($plan=="") $plan=0;
require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("INSERT INTO wellplan (md, inc, azm, vs, tvd, ns, ew, hide, plan) VALUES ('$md', '$inc', '$azm', '$vs', '$tvd', '$ns', '$ew', 0, $plan);");
$db->CloseDb();
// exec ("./sses_cc -d $seldbname -w");
header("Location: gva_tab2.php?seldbname=$seldbname");
exit();
?>
