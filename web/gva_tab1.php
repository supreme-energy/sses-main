<?php
// Written by: Richard R Gonsuron
// Copyright: 2009, Supreme Source Energy Services, Inc.
// All rights reserved.
// NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
// or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once("dbio.class.php");
require_once("tabs.php");
include_once("sses_include.php");
include_once("version.php");

if(!isset($seldbname) or $seldbname == '')	$seldbname = (isset($_GET['seldbname']) ? $_GET['seldbname'] : '');
$currtab = (isset($_GET['currtab']) ? $_GET['currtab'] : '');
if($currtab == '')$currtab = (isset($_POST['currtab']) ? $_POST['currtab'] : '');
$entity_id = (isset($_SESSION['entity_id']) ? $_SESSION['entity_id'] : '');;
$dbids=array();
$dbnames=array();
$realnames=array();
$db=new dbio("sgta_index");
$db->OpenDb();

if($seldbname=="") {
	$db->DoQuery("SELECT * FROM dbinfo ORDER BY id DESC;");
	if($db->FetchRow()) {
		$lastid=$db->FetchField("lastid");
		$db->DoQuery("SELECT * FROM dbindex WHERE id=$lastid;");
		if($db->FetchRow()) $seldbname=$db->FetchField("dbname");
	}
}

$db->DoQuery("SELECT * FROM dbindex ORDER BY id DESC;");
while($db->FetchRow()) {
	$id=$db->FetchField("id");
	$dbids=$id;
	$dbn=$db->FetchField("dbname");
	$dbreal=$db->FetchField("realname");
	$dbnames[]=$dbn;
	$realnames[]=$dbreal;
	if($seldbname==$dbn) {
		$dbrealname=$dbreal;
		$lastid=$id;
	}
} 
if(isset($lastid)) $db->DoQuery("UPDATE dbinfo SET lastid=$lastid");
$db->CloseDb();
if($seldbname=="" && count($dbnames)>0) $seldbname=$dbnames[0];
$db=new dbio($seldbname);
$silent=1;
include("dbupdate.inc.php");
$db->OpenDb();
include("readwellinfo.inc.php");
include("readappinfo.inc.php");
$emailid=array();
$emaillist=array();
$emailname=array();
$emailphone=array();
$emailcat=array();
$db->DoQuery("SELECT * FROM emaillist ORDER BY cat;");
while($db->FetchRow()) {
	$emailid[]=$db->FetchField("id");
	$emaillist[]=$db->FetchField("email");
	$emailname[]=$db->FetchField("name");
	$emailphone[]=$db->FetchField("phone");
	$emailcat[]=$db->FetchField("cat");
}
$db->CloseDb();
// $hostname=gethostname();
$hostname=exec("uname -n");
?>
<!DOCTYPE html>
<head>
<link rel="stylesheet" type="text/css" href="gva_tab1.css" />
<link rel="stylesheet" type="text/css" href="tabs.css" />
<link rel="stylesheet" type="text/css" href="waitdlg.css" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
<title><?echo (isset($dbrealname) ? $dbrealname : '') ?>-SGTA Front Page<?echo " ($seldbname)";?></title>
</head>
<body>
<?
$maintab=0;
include "apptabs.inc.php";
include("waitdlg.html");
?>
<TABLE class='tabcontainer'>
<TR>
<TD>
	<TABLE style='margin:2px 0;padding:0 0;border-collapse:collapse' class='container'>
	<TR>
	<TD style='vertical-align:middle;text-align:left;padding:0 10px'>
		<img style='width:76px' src='digital_tools_logo.png' />
	</TD>
	<TD style='vertical-align:middle;padding:5px 10px 10px 10px'>
		<H2 style='line-height:0.6em;font-style:italic;color:#040;'>Supreme Source Energy Services, Inc.</H2>
		<H1 style='line-height:0.3em;'>Subsurface Geological Tracking Analysis</H1>
		Version <?echo $version; ?> (<?echo $_SERVER['SERVER_NAME']?>:<?echo $_SERVER['SERVER_PORT']?> - <?echo $hostname?>)
	</TD>
	<TD style='vertical-align:middle;text-align:right;padding:0 10px'>
		<img src='Geology.gif' style='border:2px solid #080;border-style:inset;width:126px'>
	</TD>
	</TR>
	</TABLE>
	<TABLE class='container'>
	<TR>
	<TD style='text-align:right;'>
		<FORM method="get">
		<big>Database selection:</big>
		<select style='font-size: 10pt;' name='seldbname' ONCHANGE="OnChangeDB(this.form)">
		<?
		$cnt=count($dbnames);
		for($i=0; $i<$cnt; $i++) {
			echo "<option value='{$dbnames[$i]}'";
			if($seldbname==$dbnames[$i])	echo " selected='selected'";
			echo ">{$realnames[$i]}</option>";
		}
		?>
		</select>
		</FORM>
	</TD>
	<TD style='text-align: left;'>
		<FORM method='get' action='dbindex.php'>
		<input type='hidden' name='seldbname' value="<?echo "$seldbname";?>">
		<input type='submit' value='Manage Databases'>
		</FORM>
	</TD>
	</TR>
	</TABLE>

	<FORM style='padding: 4 0;' action="wellinfod.php" method="post">
	<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
	<input type="hidden" name="id" value="<? echo "$infotableid"; ?>">
	<input type="hidden" name="currtab" value="<? echo "$currtab"; ?>">
	<input type="hidden" name="emailname" value="">
	<input type="hidden" name="emailaddr" value="">
<?$tabs = new tabs("Configuration");
	$tabs->start("General"); ?>
	<TABLE class='container2' style='padding-top:20px'>
	<TR>
	<TD class='container'>
			Operator: <input type="text" size="40" name="opname" value="<? echo "$opname"?>"><br>
			<br>
			Well Name: <input type="text" size="40" name="wellname" value="<? echo "$wellname"?>"><br>
			<br>
			Rig Id: <input type="text" size="40" name="rigid" value="<? echo "$rigid"?>"><br>
			<br>
			Job Number: <input type="text" size="40" name="jobnumber" value="<? echo "$jobnumber"?>"><br>
			<br>
			API or UWI: <input type="text" size="40" name="wellid" value="<? echo "$wellid"?>"><br>
			<br>
			Directional: <input type="text" size="40" name="dirname" value="<? echo "$dirname"?>">
	</TD>
	<TD class='container'>
			Field: <input type="text" size="40" name="field" value="<? echo "$field"?>"><br>
			<br>
			Location: <input type="text" size="40" name="location" value="<? echo "$location"?>"><br>
			<br>
			State or Province: <input type="text" size="40" name="stateprov" value="<? echo "$stateprov"?>"><br>
			<br>
			County: <input type="text" size="40" name="county" value="<? echo "$county"?>"><br>
			<br>
			Country: <input type="text" size="40" name="country" value="<? echo "$country"?>"><br>
			<br>
			Start Date:&nbsp;<small>(yyyy-mm-dd)</small>&nbsp;<input type="text" size="12" name="startdate" value="<? echo "$startdate"?>" id='startdate'>
			<a href="javascript:NewCal('startdate','yyyymmdd')"><img src="cal.gif" width="16" height="16" border="0" alt="Pick a date"></a>
			<br>
			End Date:&nbsp;<small>(yyyy-mm-dd)</small>&nbsp;<input type="text" size="12" name="enddate" value="<? echo "$enddate"?>" id='enddate'>
			<a href="javascript:NewCal('enddate','yyyymmdd')"><img src="cal.gif" width="16" height="16" border="0" alt="Pick a date"></a>
	</TD>
	</TR>
	<TR>
	<TD colspan='2'>
		<center>
		<input type="Submit" value="Save Changes" OnClick="OnSubmit(this.form, 1)">
		</center>
	</TD>
	</TR>
	</TABLE>
	<?
	$tabs->end();
	$tabs->start("Well Info"); ?>
	<TABLE class='container2'>
	<TR>
	<TD class='container2'>
			<center><big>Survey Location</big></center><br>
			Easting(X): <input type="text" size="10" name="survey_easting" value="<? echo "$survey_easting"?>">
			<br>
			Northing(Y): <input type="text" size="10" name="survey_northing" value="<? echo "$survey_northing"?>">
	</TD>
	<TD class='container2'>
			<center><big>Landing Point</big></center><br>
			Easting(X): <input type="text" size="10" name="landing_easting" value="<? echo "$landing_easting"?>">
			<br>
			Northing(Y): <input type="text" size="10" name="landing_northing" value="<? echo "$landing_northing"?>">
	</TD>
	<TD class='container2'>
			<center><big>PBHL</big></center><br>
			Easting(X): <input type="text" size="10" name="pbhl_easting" value="<? echo "$pbhl_easting"?>">
			<br>
			Northing(Y): <input type="text" size="10" name="pbhl_northing" value="<? echo "$pbhl_northing"?>">
	</TD>
	</TR>
	</TABLE>
	<TABLE class='container2'>
	<TR>
	<TD class='container2'>
			<center><big>Elevations</big></center><br>
			Ground: <input type="text" size="10" name="elev_ground" value="<? echo "$elev_ground"?>">
			<br>
			RKB: <input type="text" size="10" name="elev_rkb" value="<? echo "$elev_rkb"?>">
	</TD>
	<TD class='container2'>
		<big>Correction:</big> 
		<select name='correction'>
			<option value="Grid" <?if($correction=="Grid") echo "selected='selected'";?>>Grid</option>";
			<option value="True North" <?if($correction=="True North") echo "selected='selected'";?>>True North</option>";
		</select>
		<br>
		<br>
		<big>Coordinate System:</big> 
		<select name='coordsys'>
			<option value="Cartesian" <?if($coordsys=="Cartesian") echo "selected='selected'";?>>Cartesian</option>";
			<option value="Polar" <?if($coordsys=="Polar") echo "selected='selected'";?>>Polar</option>";
		</select>
	</TD>
	<TD class='container2'>
	</TD>
	</TR>
	<TR>
	<TD colspan='3'>
		<center>
		<input type="Submit" value="Save Changes" OnClick="OnSubmit(this.form, 2)">
		</center>
	</TD>
	</TR>
	</TABLE>
	<?
	$tabs->end();
	$tabs->start("Email");
	?>
	<TABLE class='container2' style='border:1px solid black'>
	<TR>
	<TD style='text-align:center;padding-top:10px'>
		<table style='width:100%'>
		<tr>
		<th colspan='2' style='padding:4px'>Email Server Information</th>
		</tr>
		<tr>
		<td class='email' style='text-align:right;width:32%'>SMTP Server Address:</td>
		<td class='email' style='text-align:left'><INPUT type='text' name='smtp_server' value='<?echo $smtp_server;?>'></td>
		</tr>
		<tr>
		<td class='email' style='text-align:right;width:32%'>SMTP Login:</td>
		<td class='email' style='text-align:left'><INPUT type='text' name='smtp_login' value='<?echo $smtp_login;?>'></td>
		</tr>
		<tr>
		<td class='email' style='text-align:right;width:32%'>SMTP Password:</td>
		<td class='email' style='text-align:left'><INPUT type='password' name='smtp_password' value='<?echo $smtp_password;?>'></td>
		</tr>
		<tr>
		<td class='email' style='text-align:right;width:32%'>Reply To:</td>
		<td class='email' style='text-align:left'><INPUT type='text' name='smtp_from' value='<?echo $smtp_from;?>'></td>
		</tr>
		</table>
		<center>
		<input type="Submit" value="Save Changes" OnClick="OnSubmit(this.form, 3)">
		</center>
	</TD>
	</TR>
	</FORM>
	</TABLE>
	<?
	$tabs->end();
	$tabs->start("Contacts"); 
	require_once('classes/Reports.class.php');
	$reporting = new Reports();
	$access_for_db= $reporting->user_access_list($seldbname);
	//$access_for_db=array();
	if(is_array($access_for_db)) $access_for_db = (object) $access_for_db;
	?>
	<TABLE class='container2'>
	<TR>
	<TD align='center'>
		<table>
		<TR>
		<TH style='text-align:center'>Personnel</TH>
		<TH style='text-align:center'>Name</TH>
		<TH style='text-align:center'>Email</TH>
		<TH style='text-align:center'>Phone</TH>
		<TH style='text-align:center'>Reports</TH>
		</TR>
		<?for($i=0; $i<count($emaillist); $i++) { ?>
			<tr>
			<FORM style='padding:0' method='post'>
			<INPUT type='hidden' name='emailid' value='<?echo $emailid[$i];?>'>
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<input type="hidden" name="id" value="<? echo "$infotableid"; ?>">
			<input type="hidden" name="currtab" value="4">
			<td style='padding:2px 0;'>
			<input type="text" name="emailcat" value="<? echo $emailcat[$i]; ?>" onchange='ChangeEmail(this.form)'>
			</td><td style='padding:2px 0;'>
			<INPUT type='text' name='emailname' value='<?echo $emailname[$i];?>' onchange='ChangeEmail(this.form)'>
			</td><td style='padding:2px 0;'>
			<INPUT type='text' name='emailaddr' style='width:250px' value='<?echo $emaillist[$i];?>' onchange='ChangeEmail(this.form)'>
			</td><td style='padding:2px 0;'>
			<INPUT type='text' name='emailphone' value='<?echo $emailphone[$i];?>' onchange='ChangeEmail(this.form)'>
			</FORM>
			</td>
			<td style='text-align:center;vertical-align:middle;padding:0'><input <?php if(property_exists($access_for_db,$emaillist[$i])){echo 'checked';}?> type='checkbox' style='padding:0' onchange="if(this.checked){ window.location='do_reports_association.php?seldbname=<?echo "$seldbname";?>&id=<?echo $emailid[$i]?>'}else{ window.location='do_reports_association.php?del=1&seldbname=<?echo "$seldbname";?>&id=<?echo $emailid[$i]?>'}" /></td>
			<td style='padding:2px 0;'>
			<FORM style='padding:0' method='post'>
			<INPUT type='hidden' name='emailid' value='<?echo $emailid[$i];?>'>
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<input type="hidden" name="id" value="<? echo "$infotableid"; ?>">
			<input type="hidden" name="currtab" value="4">
			<INPUT type='hidden' name='emailname' value='<?echo $emailname[$i];?>'>
			<INPUT type='submit' value='Del' onclick='DelEmail(this.form)'>
			</FORM>
			</td>
			</tr>
		<?}?>
		</table>
	</TD>
	</TR>
	<TR>
		<td colspan='2'>
			<center>
			<FORM method='post'>
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<input type="hidden" name="currtab" value="4">
			<input type="hidden" name="emailcat" value="">
			<INPUT type='hidden' name='emailname' value='newname'>
			<INPUT type='hidden' name='emailaddr' value=''>
			<INPUT type='hidden' name='emailphone' value=''>
			<input type="Submit" value="Add Email" OnClick="AddEmail(this.form)">
			</FORM>
			</center>
		</td>
	</TR>
	</TABLE>
	<?
	$tabs->end();
	$tabs->start("AutoRC"); 
	?>
	<TABLE class='container2'>
	<TR>
	<TD align='left'>
		<form method='post'>
		<input type='hidden' name='currtab' value='6'>
		<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
		<table>
			<tr><td>Type:</td><td>
				<select name='connection_type' id='ac_con_type'>
					<option value='welldata' <?php 
						if(isset($autorc_type) and $autorc_type=='welldata'){echo 'selected';}?>>WellData</option>
					<option value='rigminder' <?php 
						if(isset($autorc_type) and $autorc_type=='rigminder'){echo 'selected';}?>>RigMinder</option>
					<option value='polaris' <?php
						if(isset($autorc_type) and $autorc_type=='polaris'){echo 'selected';}?>>Polaris</option>
					<option value='hess' <?php if(isset($autorc_type) and $autorc_type=='hess'){echo 'selected';}?>>Hess</option>
					<option value='digidrill' <?php if(isset($autorc_type) and $autorc_type=='digidrill'){echo 'selected';}?>>DigiDrill</option>
				</select></td>
			</tr>
			<tr><td>Endpoint:</td><td><input size=80 name='connection_addr' type='text' value='<?php
				echo (isset($autorc_host) ? $autorc_host : '') ?>'></td></tr>
			<tr><td>Username:</td><td><input size=80 name='connection_uname' type='text' value='<?php
				echo (isset($autorc_username) ? $autorc_username : '') ?>'></td></tr>
			<tr><td>Password:</td><td><input size=80 name='connection_pass' type='text' value='<?php
				echo (isset($autorc_password) ? $autorc_password : '') ?>'></td></tr>
			<tr><td>Survey Start Depth:</td><td><input size=80 name='acsd' type='text' value='<?php
				echo (isset($autorc_sd) ? $autorc_sd : '') ?>'></td></tr>
			<tr><td>GammaRay import mnemonic:</td><td><input size=80 type='text' value="<?echo $gr_import_mnemonic?>" name='gr_import_mnemonic'></td></tr>
			<tr><td>Enable import alarm:</td><td><input <?echo($import_alarm_enabled?'checked':'')?> type='checkbox' value=1 name='importalarmenable'></td></tr>
			<tr><td>Select import alarm:</td><td>
				<select name="importalarm" id="audioselect">
					<option <?echo($import_alarm=="BOMB_SIREN-BOMB_SIREN.mp3"?'selected':'')?> value="BOMB_SIREN-BOMB_SIREN.mp3">BOMB SIREN</option>
					<option <?echo($import_alarm=="Loud_Alarm_Clock_Buzzer.mp3"?'selected':'')?> value="Loud_Alarm_Clock_Buzzer.mp3">Loud Alarm Clock Buzzer</option>
					<option <?echo($import_alarm=="Massive_War_With_Alarm.mp3"?'selected':'')?> value="Massive_War_With_Alarm.mp3">Massive War With Alarm</option>
					<option <?echo($import_alarm=="Plectron_tones.mp3"?'selected':'')?> value="Plectron_tones.mp3">Plectron tones</option>
					<option <?echo($import_alarm=="School_Fire_Alarm.mp3"?'selected':'')?> value="School_Fire_Alarm.mp3">School Fire Alarm</option>
					<option <?echo($import_alarm=="railroad_crossing_bell.mp3"?'selected':'')?> value="railroad_crossing_bell.mp3">railroad crossing bell</option>
				</select> <a style="cursor:pointer" onclick="selel = document.getElementById('audioselect'); selaudio = selel.options[selel.selectedIndex].value;audio = new Audio('/sses/alarm_sounds/'+selaudio);audio.play()">Play</a>&nbsp;<a style="cursor:pointer" onclick="if(audio){audio.pause()}">Stop</a>
			</td></tr>
			<tr><td colspan='2'><input type='submit' value='Save' OnClick="SubmitAutoRC(this.form,6)"</td></tr>
			<tr><td colspan='2'><button OnClick="SubmitAutoRCandConfigure(this.form,6);">configure</button></td></tr>
		</table>
		</form>
	</TD>
	</TR>
	<TR>
		<td colspan='2'>

		</td>
	</TR>
	</TABLE>
	<?
	$tabs->end();	
	if($currtab==1) $tabs->active="General";
	if($currtab==2) $tabs->active="Well Info";
	if($currtab==3) $tabs->active="Email";
	if($currtab==4) $tabs->active="Contacts";
	if($currtab==5) $tabs->active="Witsml";
	if($currtab==6) $tabs->active="AutoRC";
	$tabs->run();
	?>
	<br><center><small><small>&#169; 2010-2011 Supreme Source Energy Services, Inc.</small></small></center>
</TD>
</TR>
</TABLE>
<script language="javascript" type="text/javascript" src="datetimepicker.js"></script>
<script language="javascript" type="text/javascript" src="waitdlg.js"></script>
<SCRIPT language="javascript">
var audio=null;
function SubmitAutoRCandConfigure(rowform,currtab){
	rowform.currtab.value=currtab
	contype=document.getElementById('ac_con_type').value
	t= './autorcsave_'+contype+'.php';
	t=encodeURI(t);
	rowform.action=t;
	win = window.open('','formpopup','width=800,height=300,resizeable,scrollbars');
	rowform.target='formpopup';
	rowform.submit();
	return false;	
}

function SubmitAutoRC(rowform,currtab){
	alert('After saving completes do not forget to configure the well,wellbore and welllog fot this well.');

	rowform.currtab.value=currtab
	t= './autorcsave.php';
	t=encodeURI(t);
	rowform.action=t;
	rowform.target='';
	rowform.submit();
	return save.ajax();
}
function SubmitWitsmlData(rowform,currtab){
	rowform.currtab.value=currtab
	t= './witsmldatasave.php';
	t=encodeURI(t);
	rowform.action=t;
	rowform.submit();
	return save.ajax();
}
function OnSubmit(rowform, currtab)
{
	rowform.currtab.value=currtab;
	t = './wellinfod.php';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return save.ajax();
}
function AddEmail(rowform) {
	name=prompt("Name: ", "");
	if(name!=null && name!="") {
		rowform.emailname.value=name;
		t = 'emailadd.php';
		t = encodeURI (t); // encode URL
		rowform.action = t;
		rowform.submit(); // submit form using javascript
		return ray.ajax();
	}
}
function DelEmail(rowform) {
	var name=rowform.emailname.value;
	r=confirm("Delete "+name+" from the contact list?");
	if(r==true) {
		t = 'emaildel.php';
		t = encodeURI (t); // encode URL
		rowform.action = t;
		rowform.submit(); // submit form using javascript
		return ray.ajax();
	}
}
function ChangeEmail(rowform) {
	t = 'emailchange.php';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return ray.ajax();
}
function OnChangeDB(rowform)
{
	t = 'gva_tab1.php';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return ray.ajax();
}
function OnSaveAs(rowform)
{
	t = './dbsaveas.php';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return save.ajax();
}
</SCRIPT>
</body>
</html>
