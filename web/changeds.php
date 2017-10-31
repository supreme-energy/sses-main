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
$tablename=$_POST['tablename'];
$startmd=$_POST['startmd'];
$endmd=$_POST['endmd'];
$dir=$_POST['dir'];
$sgtastart=$_POST['sgtastart'];
$sgtaend=$_POST['sgtaend'];
$sgtacutin=$_POST['sgtacutin'];
$sgtacutoff=$_POST['sgtacutoff'];
$scrolltop=$_POST['scrolltop'];
$scrollleft=$_POST['scrollleft'];
$plotstart=$_POST['plotstart'];
$plotend=$_POST['plotend'];
require_once("dbio.class.php");

$db=new dbio($seldbname);
$db->OpenDb();
include "readappinfo.inc.php";
$db->DoQuery("SELECT tablename FROM welllogs WHERE startmd isnull AND endmd isnull");
if($db->FetchNumRows()>0) {
	$db2=new dbio($seldbname);
	$db2->OpenDb();
	while($db->FetchRow()) {
		$tn=$db->FetchField("tablename");
		$db2->DoQuery("DROP TABLE \"$tn\";");
		$db2->DoQuery("DELETE FROM welllogs WHERE tablename='$tn';");
	}
}

if($dir=="directmd")
	$db->DoQuery("SELECT tablename FROM welllogs WHERE endmd>=$startmd ORDER BY startmd ASC LIMIT 1;");
else if($dir=="prev"){
	$do_reset=true;
	$db->DoQuery("SELECT tablename FROM welllogs WHERE endmd<=$startmd ORDER BY endmd DESC LIMIT 1;");
}else if($dir=="next")
	$db->DoQuery("SELECT tablename FROM welllogs WHERE startmd>=$endmd ORDER BY endmd ASC LIMIT 1;");
else if($dir=="first")
	$db->DoQuery("SELECT tablename FROM welllogs ORDER BY endmd ASC LIMIT 1;");
else
	$db->DoQuery("SELECT tablename FROM welllogs ORDER BY endmd DESC LIMIT 1;");

if($db->FetchRow()) {
	$tablename=$db->FetchField("tablename");
}

$db->CloseDb();
include($ret);
?>

