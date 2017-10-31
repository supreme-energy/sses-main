<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
require_once("dbio.class.php");
$db=new dbio("$newdbname");
$db->OpenDb();
echo "\ndbupdate: Checking database $newdbname for updates...\n";
if(!$db->ColumnExists('appinfo', 'dataset')) {
	echo("alter table appinfo add dataset integer not null default 1;\n");
	$db->DoQuery("alter table appinfo add dataset integer not null default 1;");
}
if(!$db->ColumnExists('appinfo', 'tablename')) {
	echo("alter table appinfo add tablename text not null default '';\n");
	$db->DoQuery("alter table appinfo add tablename text not null default '';");
}
if(!$db->ColumnExists('wellinfo', 'bitoffset')) {
	echo("alter table wellinfo add bitoffset float not null default 0.0;\n");
	$db->DoQuery("alter table wellinfo add bitoffset float not null default 0.0;");
}
if(!$db->ColumnExists('wellinfo', 'projdip')) {
	echo("alter table wellinfo add projdip float not null default 0.0;\n");
	$db->DoQuery("alter table wellinfo add projdip float not null default 0.0;");
}

if(!$db->ColumnExists('wellinfo', 'padata')) {
	echo("alter table wellinfo add padata text not null default '0,0,0';\n");
	$db->DoQuery("alter table wellinfo add padata text not null default '0,0,0';");
}
if(!$db->ColumnExists('wellinfo', 'pbdata')) {
	echo("alter table wellinfo add pbdata text not null default '0,0,0';\n");
	$db->DoQuery("alter table wellinfo add pbdata text not null default '0,0,0';");
}
if(!$db->ColumnExists('wellinfo', 'pamethod')) {
	echo("alter table wellinfo add pamethod int not null default 0;\n");
	$db->DoQuery("alter table wellinfo add pamethod int not null default 0;");
}
if(!$db->ColumnExists('wellinfo', 'pbmethod')) {
	echo("alter table wellinfo add pbmethod int not null default 0;\n");
	$db->DoQuery("alter table wellinfo add pbmethod int not null default 0;");
}
if(!$db->ColumnExists('wellinfo', 'autodipconfig')) {
	echo("alter table wellinfo add autodipconfig text;\n");
	$db->DoQuery("alter table wellinfo add autodipconfig text;");
}

echo "dbupdate: Finished\n";
$db->CloseDb();
?>
