<?php /*
	wellplancolorbotd.php

	Written by: Cynthia Bergman

	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
$seldbname=$_POST['seldbname'];
$colorbot=$_POST['colorbot'];

$badshit=array("#");
$colorbot=str_replace($badshit, "", $colorbot);

require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();

echo "UPDATE wellinfo SET colorbot='$colorbot'";
$db->DoQuery("UPDATE wellinfo SET colorbot='$colorbot';");

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
