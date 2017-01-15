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
$eastingsl = str_replace(',','',$eastingsl);
$northingsl = str_replace(',','',$northingsl);
$sql = "update anticollision_wells set color='$color',propdir=$propdir," .
		"eastingsl=$eastingsl,northingsl=$northingsl," .
		"ground=$ground,rkb=$rkb,correction='$correction',coor_system='$coor_system' where id=$cid";
$db->DoQuery($sql);
$db->CloseDb();
exec("./sses_ac_cc -t $tablename -d $seldbname");
header("Location: anticollisionwells.php?seldbname=$seldbname&acwellid=$cid");
?>
