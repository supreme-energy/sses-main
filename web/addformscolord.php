<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
$seldbname=$_POST['seldbname'];
$infoid=$_POST['infoid'];
$color=$_POST['color'];
$badshit=array("#");
$color=str_replace($badshit, "", $color);
require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
if(strlen($infoid)>0) {
	echo "UPDATE addforms SET color='$color' WHERE id=$infoid";
	$db->DoQuery("UPDATE addforms SET color='$color' WHERE id=$infoid");
}
$db->CloseDb();
// header("Location: gva_tab7.php?seldbname=$seldbname&infoid=$infoid");
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
		window.opener.location="gva_tab7.php?seldbname=<?echo $seldbname?>&infoid=<?echo $infoid?>";
		window.opener.location.load();
	}
}
</SCRIPT>
</BODY>
</HTML>
