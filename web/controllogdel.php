<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?php
$tablename=$_POST['tablename'];
$seldbname=$_POST['seldbname'];
require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("DROP TABLE '$tablename';");
$db->DoQuery("DELETE FROM welllogs WHERE tablename='$tablename';");
$db->CloseDb();
header("Location: gva_tab5.php?seldbname=$seldbname");
?>

