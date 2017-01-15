<?php
/*
 * Created on Aug 21, 2014
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once("dbio.class.php");
extract($_REQUEST);
$db=new dbio($seldbname);
$db->OpenDb();
$sql = "update anticollision_wells set realname='$newacn' where id=$cid";
$db->DoQuery($sql);
$db->CloseDb();
header("Location: anticollisionwells.php?seldbname=$seldbname&acwellid=$cid");
?>
