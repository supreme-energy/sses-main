<?php /*
	wellplancolorwpd.php

	Written by: Cynthia Bergman

	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
$seldbname=$_POST['seldbname'];
$colorwp=$_POST['colorwp'];

$badshit=array("#");
$colorwp=str_replace($badshit, "", $colorwp);

require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();

echo "UPDATE wellinfo SET colorwp='$colorwp'";
$db->DoQuery("UPDATE wellinfo SET colorwp='$colorwp';");
$db->DoQuery("COMMIT;");


$db->CloseDb();
// header("Location: gva_tab2.php?seldbname=$seldbname");
// exit();
?>
<HTML>
<HEAD>
<LINK rel='stylesheet' type='text/css' href='projws.css'/>
<TITLE>Project <?echo $project?></TITLE>
</HEAD>
<BODY onload='closeupanddie()'>
<SCRIPT language="javascript">
function closeupanddie()
{
	window.close();
	if(window.opener && !window.opener.closed) {
		window.opener.location="gva_tab2.php?seldbname=<?echo $seldbname?>"
		window.opener.location.load();
	}
}
</SCRIPT>
</BODY>
</HTML>
