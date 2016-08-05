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
$sql = "select * from anticollision_wells where id=$cid";
$db->DoQuery($sql);
$db->FetchRow();
$tabledropname = $db->FetchField('tablename');
$sql = "drop table $tabledropname";
$db->DoQuery($sql);
$sql = "delete from anticollision_wells where id = $cid";
$db->DoQuery($sql);
$db->CloseDb();
header("Location: anticollisionwells.php?seldbname=$seldbname");
?>
