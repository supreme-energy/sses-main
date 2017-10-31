<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools

	2011-01-20
	Probably not required anymore but this converts really old db backups to the "sgta" model
*/ ?>
<?
require_once("dbio.class.php");
$dbMaster=new dbio("template1");
$dbMaster->OpenDb();
// rename the index and template databases
echo("alter database gtta rename to sgta_index;\n");
$dbMaster->DoQuery("alter database gtta rename to sgta_index;");
echo("alter database gtta_template rename to sgta_template;\n");
$dbMaster->DoQuery("alter database gtta_template rename to sgta_template;");

// open index
$dbiRead=new dbio("sgta_index");
$dbiRead->OpenDb();
$dbiWrite=new dbio("sgta_index");
$dbiWrite->OpenDb();
// rename all dbs created and update index
$dbiRead->DoQuery("SELECT * FROM dbindex ORDER BY id DESC;");
while($dbiRead->FetchRow()) {
	$id=$dbiRead->FetchField("id");
	$dbn=$dbiRead->FetchField("dbname");
	$newdbn=str_ireplace("gtta", "sgta", $dbn);
	$dbMaster->DoQuery("alter database $dbn rename to $newdbn;\n");
	$dbiWrite->DoQuery("update dbindex set dbname='$newdbn' WHERE id=$id;\n");
} 

$dbMaster->CloseDb();
$dbiRead->CloseDb();
$dbiWrite->CloseDb();
?>
