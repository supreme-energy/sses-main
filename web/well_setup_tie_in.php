<?php /*
	Written by: Richard R Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
//header("Location: gva_tab1.php");
include_once("sses_include.php");
require_once("dbio.class.php");
require_once("tabs.php");
require_once("login.class.php");
require_once("version.php");
$seldbname=$_REQUEST['seldbname'];
$db=new dbio($seldbname);
$db->OpenDb();
$query = "select * from surveys order by id asc limit 1";
$db->DoQuery($query);
$survey_0 = $db->FetchRow();

$query = "select projdip, propazm, bitoffset from wellinfo";
$db->DoQuery($query);
$tiein_info = $db->FetchRow();
 
?>
   <LINK href="gva_tab0.css?x=<?=time();?>" rel="stylesheet" type="text/css">
   <TABLE class='tabcontainer'>
   <TR>
       <TD>
           <TABLE style='margin: 2 0; padding: 0 0;' class='container'>
	           <TD> 
		       <img src='digital_tools_logo.png' align='left'>
		       <H2 style='line-height: 2.0; font-style: italic; color: #040;' align='center'>Digital Oil Tools</H2>
		       <H1 style='line-height: 1.0;' align='center'>Tie In Config</H1>
		       <p align="center">Version <?echo $version; ?> (<?echo $_SERVER['SERVER_NAME']?>:<?echo $_SERVER['SERVER_PORT']?> - <?echo $hostname?>)</p>
		   </TD>
	   </TABLE>
	   <TABLE class='container'>
		<tr>
			<td>
			<FORM method='post' action='well_setup_tie_in_save.php'>
			<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
			<input type='hidden' name='survey_id' value='<?php echo $survey_0['id']?>'>
			<div>
				
				<div>Bit to Survey Distance: <input type='text' name='bitoffset' value='<?php echo $tiein_info['bitoffset']?>'></div>
				<div>Proposed VS Direction: <input type='text' name ='propazm' value='<?php echo $tiein_info['propazm']?>'></div>
				<div>Estimated Regional Dip: <input type='text' name ='projdip' value='<?php echo $tiein_info['projdip']?>'></div>
				<div>Tie In Data:</div>
				<table class='surveys'>
				<TR> 
				<TR> 
				<TH class='surveys'>Depth</TH>
				<TH class='surveys'>Inc</TH>
				<TH class='surveys'>Azm</TH>
				<TH class='surveys'>TVD</TH>
				<TH class='surveys'>VS</TH>
				<TH class='surveys'>NS</TH>
				<TH class='surveys'>EW</TH>
				<TH class='surveys'>CD</TH>
				<TH class='surveys' style='border-right: 1px solid black'>CA</TH>
				</TR>
				<tr>
				<td class="grid gridmdcl"><input class='surveys' type='text' name='md' style='width:60px' value="<?php echo $survey_0['md']?>"></td>
				<td class="grid gridmdcl"><input class='surveys' type='text' name='inc' style='width:60px' value="<?php echo $survey_0['inc']?>"></td>
				<td class="grid gridmdcl"><input class='surveys' type='text' name='azm' style='width:60px' value="<?php echo $survey_0['azm']?>"></td>
				<td class="grid gridmdcl"><input class='surveys' type='text' name='tvd' style='width:60px' value="<?php echo $survey_0['tvd']?>"></td>
				<td class="grid gridmdcl"><input class='surveys' type='text' name='vs' style='width:60px' value="<?php echo $survey_0['vs']?>"></td>
				<td class="grid gridmdcl"><input class='surveys' type='text' name='ns' style='width:60px' value="<?php echo $survey_0['ns']?>"></td>
				<td class="grid gridmdcl"><input class='surveys' type='text' name='ew' style='width:60px' value="<?php echo $survey_0['ew']?>"></td>
				<td class="grid gridmdcl"><input class='surveys' type='text' name='cd' style='width:60px' value="<?php echo $survey_0['cd']?>"></td>
				<td class="grid gridmdcl" style='border-right: 1px solid black'><input class='surveys' type='text' name='ca' style='width:60px' value="<?php echo $survey_0['ca']?>"></td>
				</table>
				<div>
					<input type='submit' value='Save'>
				</div>
			</div>
			</FORM></td>
		</tr>
	  </TABLE>
</TD>
</TR>
</TABLE>


