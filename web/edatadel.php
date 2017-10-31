<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
$seldbname=$_POST['seldbname'];
$seledata=$_POST['seledata'];
$badshit=array("#");
$color=str_replace($badshit, "", $color);
require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();

if(strlen($seledata)>0) {
	$tablename="edl_$seledata";
	$db->DoQuery("DROP TABLE \"$tablename\";");
	$db->DoQuery("DELETE FROM edatalogs WHERE id=$seledata;");
}
$ids=array();
$db->DoQuery("SELECT id FROM edatalogs ORDER BY colnum ASC;");
while($db->FetchRow()) $ids[]= $db->FetchField("id");

$cnt=count($ids);
for($i=0; $i<$cnt; $i++) $db->DoQuery("UPDATE edatalogs SET colnum=$i WHERE id=$ids[$i];");

$db->DoQuery("SELECT id FROM edatalogs ORDER BY colnum DESC LIMIT 1;");
if($db->FetchRow()) $seledata= $db->FetchField("id");

$db->CloseDb();
header("Location: gva_tab6.php?seldbname=$seldbname&seledata=$seledata");
exit();
?>
