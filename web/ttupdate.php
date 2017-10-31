<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
require_once("dbio.class.php");
if(isset($_POST['ret']) and $_POST['ret'] != '') $ret = $_POST['ret'];
else $ret = 'gva_tab3.php';
$seldbname=$_POST['seldbname'];
$id=$_POST['id'];
$propazm=$_POST['propazm'];
$plantot=$_POST['tot'];
$planbot=$_POST['bot'];
$projection=$_POST['projection'];
$bitoffset=$_POST['bitoffset'];
$projdip=$_POST['projdip'];
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("BEGIN TRANSACTION;");
$query="UPDATE wellinfo SET tot=$plantot,bot=$planbot,projdip=$projdip WHERE id=$id;";
$db->DoQuery($query);
$query="UPDATE wellinfo SET propazm=$propazm WHERE id=$id;";
$db->DoQuery($query);
$db->DoQuery("COMMIT;");
$db->CloseDb();
// exec ("./sses_cc -d $seldbname");
// exec ("./sses_gva -d $seldbname");
header("Location: {$ret}?seldbname={$seldbname}");
exit();
?>
