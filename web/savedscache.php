<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?php
require_once("dbio.class.php");
$seldbname=$_POST['seldbname'];
$ret=$_POST['ret'];
$scrolltop=$_POST['scrolltop'];
$scrollleft=$_POST['scrollleft'];
$viewdspcnt=$_POST['viewdspcnt'];
$dscache_dip=$_POST['dscache_dip'];
$dscache_md=$_POST['dscache_md'];
$dscache_fault=$_POST['dscache_fault'];
$db=new dbio($seldbname);
$db2=new dbio($seldbname);
$db->OpenDb();
$db2->OpenDb();
$db->DoQuery("SELECT * FROM welllogs WHERE endmd<=$dscache_md ORDER BY startmd DESC LIMIT $viewdspcnt;");
$sd=$dscache_md;
while ($db->FetchRow()) {
	$id=$db->FetchField("id");
	$d=$db->FetchField("startmd");
	$db2->DoQuery("UPDATE welllogs SET dip='$dscache_dip' WHERE id='$id';");
	if(strlen($dscache_fault)) $db2->DoQuery("UPDATE welllogs SET fault='0' WHERE id='$id';");
	if($d<$sd)	$sd=$d;
}
if(strlen($dscache_fault)) {
	$db2->DoQuery("UPDATE welllogs SET fault='$dscache_fault' WHERE id='$id';");
	$db2->DoQuery("UPDATE appinfo SET dscache_fault=0;");
}
$db->CloseDb();
$db2->CloseDb();
exec ("./sses_gva -d $seldbname -s $sd --nosurveys");
include($ret);
?>

