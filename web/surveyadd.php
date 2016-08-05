<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Supreme Source Energy Services, Inc.
//	All rights reserved.
//	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.

$seldbname=$_POST['seldbname'];
$sortdir=$_POST['sortdir'];
$md=$_POST['md']; if($md=="") $md=0;
$inc=$_POST['inc']; if($inc=="") $inc=0;
$azm=$_POST['azm']; if($azm=="") $azm=0;
$ret=(isset($_POST['ret']) ? $ret = $_POST['ret'] : 'gva_tab3.php');
require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("INSERT INTO surveys (md,inc,azm) VALUES ($md,$inc,$azm)");
$db->DoQuery("UPDATE wellinfo SET pamethod='-1';");
$db->DoQuery("delete from projections where ptype='rot' or ptype='sld'");
exec("./sses_gva -d $seldbname");
exec("./sses_cc -d $seldbname");
exec("./sses_cc -d $seldbname -p");
exec("./sses_af -d $seldbname");
$db->CloseDb();
header("Location: {$ret}?seldbname=$seldbname&sortdir=$sortdir");
exit();
?>
