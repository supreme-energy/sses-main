<?php
//	gva_tab2.php
//
//	Version:	SSES v2.4.2  
//			April 20, 2012  
//
//	Modified by:	Cynthia Bergman   
//	Purpose:	To add 'color selection wheels' so that the User may 
//			select the desired colors for the Top of Target (TOT) 
//			and Bottom of Target (BOT) lines.  The color choices 
//			are stored in the addforms table (TOT and BOT) and 
//			the wellinfo table (Target Line).  For more 
//			information, please see the Release Notes for Version 
//			2.4.2.  
//
//	Written by: Richard Gonsuron
//	Copyright: 2009, Supreme Source Energy Services, Inc.
//	All rights reserved.
//	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'sses_include.php';
require_once 'dbio.class.php';

if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_GET['seldbname']) ? $_GET['seldbname'] : '');
if($seldbname == '') include('dberror.php');
$db = new dbio('sgta_index');
$db->OpenDb();
$db->DoQuery('SELECT * FROM dbindex ORDER BY id');
while($db->FetchRow())
{
	$dbids=$db->FetchField("id");
	$dbn=$db->FetchField("dbname");
	$dbreal=$db->FetchField("realname");
	if($seldbname==$dbn) $dbrealname=$dbreal;
} 
$db->CloseDb();

$db=new dbio($seldbname);
$db->OpenDb();
include "readwellinfo.inc.php";
include "readappinfo.inc.php";
$ret="gva_tab2.php";

$db->DoQuery("SELECT * FROM wellinfo;");
if($db->FetchRow()) {
	$colortot=$db->FetchField("colortot");
	$colorbot=$db->FetchField("colorbot");
	$colorwp =$db->FetchField("colorwp");
}
$cldip=0.0;
$stregdipazm=111.00;
$db->DoQuery("SELECT * FROM controllogs ORDER BY tablename");
if ($db->FetchRow()) {
	$tablename=$db->FetchField("tablename");
	$startmd=$db->FetchField("startmd");
	$endmd=$db->FetchField("endmd");
	$cltot=$db->FetchField("tot");
	$clbot=$db->FetchField("bot");
	$cldip=$db->FetchField("dip");
	$stregdipazm=$db->FetchField("azm");
}
else {
	// create an entry in the controllogs table
	$db->DoQuery("INSERT INTO controllogs (tablename) VALUES ('xxxxxx');");
	$db->DoQuery("SELECT * FROM controllogs WHERE tablename='xxxxxx';");
	$id="";
	if($db->FetchRow())
		$id = $db->FetchField("id");
	// create table which contains imported data
	if($id!="") {
		$tablename="cld_$id";
		$query="CREATE TABLE \"$tablename\" (id serial not null, md float, tvd float, vs float, value float, hide smallint not null default 0);";
		$db->DoQuery($query);
		$query="UPDATE controllogs SET tablename='$tablename' WHERE id='$id';";
		$db->DoQuery($query);
	}
	else die("<pre>Id for new table entry not found!\n</pre>");
	$startmd=0;
	$endmd=100;
}

$fn=sprintf("./tmp/%s_gva_tab2.png", $seldbname);
exec ("./sses_cc -d $seldbname -w");
$logsw=""; if($uselogscale>0)	$logsw="-log";
exec("./sses_pd -T $tablename -d $seldbname -o $fn -w 340 -h 750 -s $startmd -e $endmd -r $scaleright $logsw");
?>
<HTML>
<HEAD>
<link rel="stylesheet" type="text/css" href="gva_tab2.css" />
<link rel="stylesheet" type="text/css" href="waitdlg.css" />
<title><?echo "$dbrealname";?>-SGTA Wellplan and Control Log<?echo " ($seldbname)";?></title>
</HEAD>
<BODY>
<?
$maintab=1;
include "apptabs.inc.php";
include("waitdlg.html");
?>
<table class='tabcontainer'>
<tr>
<td>
	<table>
	<tr>
	<td>
		<TABLE class='surveys' style='padding:2;'>
		<INPUT TYPE="hidden" VALUE="0" NAME="plan">
		<TR>
		<FORM ACTION="wellplanadd.php" METHOD="post">
		<TD style='width:100px;height:45px;vertical-align:bottom'>
			<TABLE style='width:180px;border-spacing:0;padding:2;'>
			<TR>
			<TH class='surveys'>MD</TH>
			<TH class='surveys'>Inc</TH>
			<TH class='surveys' style='border-right: thin solid black;'>Azm</TH>
			</TR>
			<TR>
			<TD class='surveys'>
				<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
				<INPUT class='surveys' TYPE="text" VALUE="0.0" NAME="md" SIZE="6">
			</TD>
			<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="0.0" NAME="inc" SIZE="6">
			</TD>
			<TD class='surveys' style='border-right: thin solid black;'>
				<INPUT class='surveys' TYPE="text" VALUE="0.0" NAME="azm" SIZE="6">
			</TD>
			</TR>
			</TABLE>
		</TD>
		<TD style='vertical-align:bottom;padding:2;'>
			<INPUT TYPE="submit" VALUE="Add Survey" NAME="AddNew">
		</TD>
		</FORM>
		<TD>
			<b><big><big>
			<?echo "$wellname";?> Well Plan Surveys
			</big></big></b>
			<FORM METHOD="get">
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<input type="Submit" style="vertical-align:center" value="Import CSV" OnClick="OnImportWellplan(this.form)">
			</FORM>
			<button>Profile Lines</button>
		</TD>
		<TD>
			<br>
			<form style='padding:0 0; margin:0;' method='post' onchange='doSubmit(this.form);'>
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<INPUT type='hidden' name='scrolltop' value='<?echo $scrolltop;?>'>
			<INPUT type='hidden' name='colorrawwp' value='<?echo "$colorrawwp";?>'>
			<b>Well Plan Line Color: </b><input type="text" readonly="true" size='7' id="colorrawwp" name="colorrawwp" 
				value="<?echo "#$colorwp"?>" 
				style="vertical-align:bottom;background-color:#<?echo "$colorwp";?>;color:white;"
				onclick='openColorChoiceWellPlan(this.form);'/> 
			</form>
		</TD>
		</TR>
		</TABLE>
	</td>
	</tr>
	<tr>
	<td colspan='2'>
		<table class='surveys'>
		<TR>
		<td style='text-align: right;' colspan='12'>
			<FORM ID="delsvys" NAME="delsvys" METHOD="post">
			<INPUT TYPE="hidden" NAME="seldbname" VALUE="<?echo "$seldbname";?>">
			<INPUT TYPE="hidden" NAME="sids" VALUE="">
			</FORM>
			<INPUT TYPE="submit" VALUE="Select All" ONCLICK="OnSetChecks()">
			<INPUT TYPE="submit" VALUE="Select None" ONCLICK="OnClearChecks()">
			<INPUT TYPE="submit" VALUE="Delete Selected" ONCLICK="OnDelSurveys()">
		</td>
		</TR>
		<TR> 
		<TH class='surveys'>#</TH>
		<TH class='surveys'>MD</TH>
		<TH class='surveys'>Inc</TH>
		<TH class='surveys'>Azm</TH>
		<TH class='surveys'>TVD</TH>
		<TH class='surveys'>VS</TH>
		<TH class='surveys'>NS</TH>
		<TH class='surveys'>EW</TH>
		<TH class='surveys'>CD</TH>
		<TH class='surveys'>CA</TH>
		<TH class='surveys'>DL</TH>
		<TH class='surveys'>Dip-C</TH>
		<TH class='surveys'>Del</TH>
		</TR>
		<?
		$db->DoQuery("SELECT * FROM wellplan WHERE hide=0 ORDER BY md ASC");
		$num=$db->FetchNumRows(); 
		?><INPUT TYPE="hidden" NAME="numsvys" VALUE="<?echo $num;?>" ID="numsvys"><?
		$i=0;
		while ($i < $num) {
			$db->FetchRow();
			$id=$db->FetchField("id");
			$plan=sprintf("%d", $db->FetchField("plan"));
			$md=sprintf("%.2f", $db->FetchField("md"));
			$inc=sprintf("%.2f", $db->FetchField("inc"));
			$azmraw = $db->FetchField("azm");
			$caraw = $db->FetchField("ca");
			$cdraw = $db->FetchField("cd");
			$azm=sprintf("%.2f", $db->FetchField("azm"));
			$tvd=sprintf("%.2f", $db->FetchField("tvd"));
			$vs=sprintf("%.2f", $db->FetchField("vs"));
			$nsraw = $db->FetchField("ns");
			$ns=sprintf("%.2f", $db->FetchField("ns"));
			$ewraw = $db->FetchField("ew");
			$ew=sprintf("%.2f", $db->FetchField("ew"));
			$cd=sprintf("%.2f", $db->FetchField("cd"));
			$ca=sprintf("%.2f", $db->FetchField("ca"));
			$dl=sprintf("%.2f", $db->FetchField("dl"));
			$closure = atan2($ewraw,$nsraw);
			$regazm = deg2rad($stregdipazm);
			if($i!=0){
				$tregdip=sprintf("%.2f",atan(tan(deg2rad($cldip))*cos($regazm - $closure))*(180/pi()));
			}else{
				$tregdip=0;
			}
			if($i%4<=1) $classstr="<TD class='gridro2'>";
			else $classstr="<TD class='gridro'>";
			?>
			<TR> 
			<FORM ACTION="wellplanchange.php" NAME="F<?echo $id?>" METHOD="post">
			<INPUT TYPE="hidden" VALUE="<?echo $id;?>" NAME="id">
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<TD class='surveys'>
			<?echo $i?>
			</td>
			<? if($i==0) { ?>
				<INPUT TYPE="hidden" VALUE="<?echo $plan;?>" NAME="plan">
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $md;?>" NAME="md" SIZE="6" ONCHANGE="OnSubmit(this.form)"></TD>
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $inc;?>" NAME="inc" SIZE="4" ONCHANGE="OnSubmit(this.form)"></TD>
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $azm;?>" NAME="azm" SIZE="4" ONCHANGE="OnSubmit(this.form)"></TD>
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $tvd;?>" NAME="tvd" SIZE="6" ONCHANGE="OnSubmit(this.form)"></TD>
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $vs;?>" NAME="vs" SIZE="6" ONCHANGE="OnSubmit(this.form)"></TD>
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $ns;?>" NAME="ns" SIZE="6" ONCHANGE="OnSubmit(this.form)"></TD>
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $ew;?>" NAME="ew" SIZE="6" ONCHANGE="OnSubmit(this.form)"></TD>
				<?
				echo "$classstr $cd</TD>";
				echo "$classstr $ca</TD>";
				echo "$classstr $dl</TD>";
			}
			else {?>
				<INPUT TYPE="hidden" VALUE="<?echo $plan;?>" NAME="plan">
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $md;?>" NAME="md" SIZE="6" ONCHANGE="OnSubmit(this.form)"></TD>
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $inc;?>" NAME="inc" SIZE="4" ONCHANGE="OnSubmit(this.form)"></TD>
				<TD class='surveys'>
				<INPUT class='surveys' TYPE="text" VALUE="<?echo $azm;?>" NAME="azm" SIZE="4" ONCHANGE="OnSubmit(this.form)"></TD>
				<?
				echo "$classstr $tvd</TD>";
				echo "$classstr $vs</TD>";
				echo "$classstr $ns</TD>";
				echo "$classstr $ew</TD>";
				echo "$classstr $cd</TD>";
				echo "$classstr $ca</TD>";
				echo "$classstr $dl</TD>";
			}
				echo "$classstr $tregdip</TD>";
			?>
			</FORM>
			<FORM id="f<?echo $i;?>" NAME="f<?echo $i;?>" METHOD="post">
			<TD class='surveys'>
				<INPUT TYPE="hidden" NAME="seldbname" VALUE="<?echo "$seldbname";?>">
				<INPUT TYPE="hidden" VALUE="ASC" NAME="sortdir">
				<INPUT TYPE="hidden" NAME="id" VALUE="<?echo $id;?>">
				<INPUT class='surveys' TYPE="checkbox" VALUE="0" NAME="del">
			</TD>
			</FORM>
			</TR>
		<? ++$i;
		}
		$db->CloseDb();
		?>
		</TABLE>
	</td>
	</tr>
<tr>
<td>
	<center><small><small>&#169; 2010-2011 Supreme Source Energy Services, Inc.</small></small></center>
</td>
</tr>
	</table>
</td>
<td>
	<table class='surveys' style='width: 500px;padding:2;'>
	<tr>
	<TD style='padding: 4 8;'>
		<b><big>Control Log</big></b>
	</TD>
	<TD style='padding: 0 8;'>
		<FORM method="post">
		<INPUT type='hidden' name='returnto' value='gva_tab2.php?seldbname=<?echo $seldbname?>'>
		<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
		<INPUT type="submit" value="Import LAS" ONCLICK="OnLasImport(this.form)">
		</FORM>
	</TD>
	<td>
		<form onsubmit="return saveRefFilename(this.elements['refwellname'].value);">
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			Ref. Well Name <input type='text'  style="text-align:left;" name='refwellname' onchange="saveRefFilename(this.value)" value='<? echo $refwellname?>'>
		</form>
	</td>
	</tr>
	<tr>
		<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
		<input type="hidden" name="scrolltop" value="<?echo "$scrolltop";?>">
	<td class='surveys' style='padding: 0 8; border-top: thin solid black;'>
		<FORM action='setcld.php' method='post' style='margin: 0;' onsubmit='return ray.ajax()'>
		<INPUT type='hidden' name='ret' value='gva_tab2.php'>
		<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
		<input type='hidden' name='tablename' value='<?echo $tablename?>'>
		<b>Control: </b><?echo $tablename;?><br>
		<b>Start: </b><?echo $startmd;?><br>
		<b>End: </b><?echo $endmd;?><br>
	</td>
	<td class='surveys' style='padding: 0 12; border-top: thin solid black; border-right: thin solid black;'>
		<b>SL True Dip: </b><input style='padding:0 4;' type='text' size='3' name='dip' value='<?echo $cldip;?>'><br>
		<b>SL Dip AZM: </b><input style='padding:0 4;' type='text' size='3' name='dazm' value='<?echo $stregdipazm;?>'><br>
		<b>TCL: </b><input style='padding:0 4;' type='text' size='6' name='tot' value='<?echo $cltot;?>'><br>
		<span style='display:none'><b>BOT: </b><input style='padding:0 4;' type='text' size='6' name='bot' value='<?echo $clbot;?>'><br></span>
		<input style='text-align:right; padding: 0 4; vertical-align:center' type='submit' value='Save'>
		</FORM>
	</td>
	<td class='surveys' style='text-align:right; padding: 0 8; border-top: thin solid black;'>
		<br>
		<form style='padding:0 0; margin:0;' method='post' onchange='doSubmit(this.form);'>
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<INPUT type='hidden' name='scrolltop' value='<?echo $scrolltop;?>'>
			<input type="hidden" name="colorrawtot" value="<?echo "$colorrawtot";?>">
			<b>TCL Line Color: </b><input type="text" readonly='true' size='7' id="colorrawtot" name="colorrawtot" 
				value="<?echo "#$colortot"?>" 
				style="background-color:#<?echo "$colortot";?>;color:white;"
				onclick='openColorChoiceTOT(this.form);'/> 
		</form>
		<form style='padding:0 0; margin:0;' method='post' onchange='doSubmit(this.form);'>
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<INPUT type='hidden' name='scrolltop' value='<?echo $scrolltop;?>'>
			<INPUT type='hidden' name='colorrawbot' value='<?echo "$colorrawbot";?>'>
			<span style='display:none'><b>BOT Line Color: </b><input type="text" readonly='true' size='7' id="colorrawbot" name="colorrawbot" 
				value="<?echo "#$colorbot"?>" 
				style="background-color:#<?echo "$colorbot";?>;color:white;"
				onclick='openColorChoiceBOT(this.form);'/></span> 
		</form>
	</td>
	</tr>
	</table>
	<left>
	<img src='<?echo $fn;?>' style='border: thin solid black'>
	</left>
</td>
</tr>
</table>
</BODY>
<SCRIPT language="javascript">
function OnDelSurveys()
{
	var numsvys=document.getElementById("numsvys").value;
	var sids="";
	for(i=0; i<numsvys; i=i+1) {
		fid="f"+i;
		form=document.getElementById(fid);
		dodel=form.del.checked;
		id=form.id.value;
		if(dodel>0) {
			if(sids=="") sids=id;
			else sids=sids+","+id;
		}
	}
	if(sids=="")	return;
	rowform=document.getElementById("delsvys");
	rowform.sids.value=sids;
	result=confirm("Delete these wellplan surveys, Are you sure?");
	if(result)
	{
		t = 'wellplandel.php';
		t = encodeURI (t); // encode URL
		rowform.action = t;
		rowform.submit(); // submit form using javascript
		return ray.ajax();
	}
}
function OnClearChecks()
{
	var numsvys=document.getElementById("numsvys").value;
	var sids="";
	for(i=0; i<numsvys; i=i+1) {
		fid="f"+i;
		form=document.getElementById(fid);
		form.del.checked=0;
	}
}
function OnSetChecks()
{
	var numsvys=document.getElementById("numsvys").value;
	var sids="";
	for(i=0; i<numsvys; i=i+1) {
		fid="f"+i;
		form=document.getElementById(fid);
		form.del.checked=1;
	}
}
function OnSubmit(rowform)
{
	t = 'wellplanchange.php';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return true;
}
function OnImportWellplan(rowform) {
	t = 'wellplanimport.php?seldbname=rowform.seldbname';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return true;
}
function OnLasImport(rowform)
{
	t = 'controllogfilesel.php';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return true;
	// return ray.ajax();
}
function doSubmit(rowform)
{
	rowform.scrolltop.value=document.getElementById("scrollContent").scrollTop;
	t = 'gva_tab2.php?seldbname=rowform.seldbname';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return ray.ajax();
}
function openColorChoiceTOT(rowform) { 
    var phpcall="wellplancolortot.php?seldbname="+rowform.seldbname.value+"&colortot="+rowform.colorrawtot.value;
    newwindow=window.open(phpcall,'ColorChoice', 'height=300,width=300,scrollbars=no');
    if (window.focus) {newwindow.focus()}
    // return false;
	return ray.ajax();
}
function openColorChoiceBOT(rowform) { 
    var phpcall="wellplancolorbot.php?seldbname="+rowform.seldbname.value+"&colorbot="+rowform.colorrawbot.value;
    newwindow=window.open(phpcall,'ColorChoice', 'height=300,width=300,scrollbars=no');
    if (window.focus) {newwindow.focus()}
    // return false;
	return ray.ajax();
}
function openColorChoiceWellPlan(rowform) { 
    var phpcall="wellplancolorwp.php?seldbname="+rowform.seldbname.value+"&colorbot="+rowform.colorrawwp.value;
    newwindow=window.open(phpcall,'ColorChoice', 'height=300,width=300,scrollbars=no');
    if (window.focus) {newwindow.focus()}
    // return false;
	return ray.ajax();
}
function saveRefFilename(val){
	xmlhttp=new XMLHttpRequest();
	xmlhttp.open('GET','/sses/saverefwellname.php?seldbname=<?=$seldbname?>&refwellname='+val,true);
	xmlhttp.send();
	return false;
}

</SCRIPT>
<script language="javascript" type="text/javascript" src="waitdlg.js"></script>
</HTML>

