<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Digital Oil Tools

require_once("dbio.class.php");
$seldbname=$_POST['seldbname'];
$ret=$_POST['ret'];
$scrolltop=$_POST['scrolltop'];
$scrollleft=$_POST['scrollleft'];
$dscache_dip=$_POST['dscache_dip'];
$dscache_fault=$_POST['dscache_fault'];
$dscache_bias=$_POST['dscache_bias'];
$dscache_scale=$_POST['dscache_scale'];
$dscache_freeze=$_POST['dscache_freeze'];
$dscache_md=$_POST['dscache_md'];
$dscache_plotstart=$_POST['dscache_plotstart'];
$dscache_plotend=$_POST['dscache_plotend'];
$dsholdfault = $_POST['dsholdfault'];
$db=new dbio($seldbname);
$db->OpenDb();
if(strlen($dscache_fault)) $db->DoQuery("UPDATE appinfo SET dscache_fault='$dscache_fault'");
if(strlen($dscache_dip)) $db->DoQuery("UPDATE appinfo SET dscache_dip='$dscache_dip'");
if(strlen($dscache_bias)) $db->DoQuery("UPDATE appinfo SET dscache_bias='$dscache_bias'");
if(strlen($dscache_scale)) $db->DoQuery("UPDATE appinfo SET dscache_scale='$dscache_scale'");
if(strlen($dscache_freeze)) $db->DoQuery("UPDATE appinfo SET dscache_freeze='$dscache_freeze'");
if(strlen($dscache_md)) $db->DoQuery("UPDATE appinfo SET dscache_md='$dscache_md'");
if(strlen($dscache_plotstart)) $db->DoQuery("UPDATE appinfo SET dscache_plotstart='$dscache_plotstart'");
if(strlen($dscache_plotend)) $db->DoQuery("UPDATE appinfo SET dscache_plotend='$dscache_plotend'");
if(strlen($dsholdfault)) $db->DoQuery("UPDATE appinfo set dsholdfault = '$dsholdfault'");
$db->CloseDb();
include($ret);
?>

