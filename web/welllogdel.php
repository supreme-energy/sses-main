<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?php
$ret=$_POST['ret'];
$seldbname=$_POST['seldbname'];
$tn=$_POST['tn'];
$depth=$_POST['depth'];
$editmode=$_POST['editmode'];
$scrolltop=$_POST['scrolltop'];
$scrollleft=$_POST['scrollleft'];
require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();

// find the start and end depths then delete the edata too
$db->DoQuery("SELECT startmd,endmd FROM welllogs WHERE tablename='$tn';");
if($db->FetchRow()) {
	$startmd = $db->FetchField("startmd");
	$endmd = $db->FetchField("endmd");
	$etablenames=array();
	$db->DoQuery("SELECT tablename FROM edatalogs ORDER BY colnum;");
	while($db->FetchRow()) $etablenames[]=$db->FetchField("tablename");
	foreach($etablenames as $etn)
		$db->DoQuery("DELETE FROM \"$etn\" WHERE md>=$startmd AND md<=$endmd;");
}

$db->DoQuery("DROP TABLE IF EXISTS \"$tn\";");
$db->DoQuery("DELETE FROM welllogs WHERE tablename='$tn';");

$db->CloseDb();
$tablename="";
exec("./sses_gva -d -s 0 $seldbname");
include("$ret");
?>

