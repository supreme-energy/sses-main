<?php
extract($_REQUEST);
if(!$md)$md=0;
if(!$inc)$inc=0;
if(!$azm)$azm=0;
if(!$tvd)$tvd=0;
if(!$vs)$vs=0;
if(!$ns)$ns=0;
if(!$ew)$ew=0;

require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
$sql = "select * from anticollision_wells where id = $cid";
$db->DoQuery($sql);
$db->FetchRow();
$tablename = $db->FetchField('tablename');
$sql = "insert into $tablename (md,inc,azm,vs,tvd,ns,ew) values($md,$inc,$azm,$vs,$tvd,$ns,$ew)";

$db->DoQuery($sql);
$db->CloseDb();
exec("./sses_ac_cc -t $tablename -d $seldbname");
header("Location: anticollisionwells.php?seldbname=$seldbname&acwellid=$cid");
exit();
?>
