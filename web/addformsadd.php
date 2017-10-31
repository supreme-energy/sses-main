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
$db=new dbio($seldbname);
$db->OpenDb();
$result=$db->DoQuery("INSERT INTO addforms (label) VALUES ('xxxxx');");
if($result==FALSE) die("<pre>Database error attempting to insert new information block\n</pre>");
$db->DoQuery("SELECT id FROM addforms WHERE label='xxxxx';");
if($db->FetchRow()) {
	$infoid=$db->FetchField("id");
	$db->DoQuery("UPDATE addforms SET label='new formation',color='0000ff' WHERE id=$infoid;");
}
else die("<pre>Id for new table entry not found!\n</pre>");
$db->CloseDb();
include("$ret");
?>
