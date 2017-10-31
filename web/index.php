<?php /*
	Written by: Richard R Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?
header("Location: gva_tab1.php");
include_once("sses_include.php");
require_once("dbio.class.php");
require_once("tabs.php");
require_once("login.class.php");
require_once("version.php");
?>
   <LINK href="gva_tab0.css" rel="stylesheet" type="text/css">
   <TABLE class='tabcontainer'>
   <TR>
       <TD>
           <TABLE style='margin: 2 0; padding: 0 0;' class='container'>
	           <TD> 
		       <img src='digital_tools_logo.png' width='76' height='74' align='left'>
		       <H2 style='line-height: 2.0; font-style: italic; color: #040;' align='center'>Supreme Source Energy Services, Inc.</H2>
		       <H1 style='line-height: 1.0;' align='center'>Subsurface Geological Tracking Analysis</H1>
		       <p align="center">Version <?echo $version; ?> (<?echo $_SERVER['SERVER_NAME']?>:<?echo $_SERVER['SERVER_PORT']?> - <?echo $hostname?>)</p>
		   </TD>
	   </TABLE>
	   <TABLE class='container'>
	       <TR>
	           <TD style='text-align: center;'>
		       <h2>LOGIN: <font color="FF0000"> <? if ($_GET['login_err'] == 1) { echo "Login Failed"; }	       ?></font></h2>
		       <?
                   $log = new LogMeIn();
                   $log->encrypt = true;
                   $log->loginform("sgta_form","container","sgta_login.php");
		       ?>
		   </TD>
	       </TR>
	  </TABLE>
</TD>
</TR>
</TABLE>


