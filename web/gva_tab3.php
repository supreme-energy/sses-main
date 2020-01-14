<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Supreme Source Energy Services, Inc.
//	All rights reserved.
//	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'sses_include.php';
require_once 'gva_tab3_funct.php';
require_once 'dbio.class.php';
$debug = (isset($_REQUEST['debug']) ? true : false);
if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_GET['seldbname']) ? $_GET['seldbname'] : ''); 
include("cleanoujia.php");
if(!isset($noshowxy) or $noshowxy == '') $noshowxy = (isset($_GET['noshowxy']) ? $_GET['noshowxy'] : '');
$sortdir = (isset($_GET['sortdir']) ? $_GET['sortdir'] : '');
if($seldbname == '') include("dberror.php");

$md=0; $inc=0; $azm=0;
$recalc = (isset($_REQUEST['recalc']) ? $_REQUEST['recalc'] : '');
$numprojs=0;

$db=new dbio("sgta_index");
$db->OpenDb();
$db->DoQuery("SELECT * FROM dbindex ORDER BY id;");
while($db->FetchRow()) {
	$dbn=$db->FetchField("dbname");
	if($seldbname==$dbn) $dbrealname=$db->FetchField("realname");
} 
$db->CloseDb();
$db=new dbio($seldbname);
$db->OpenDb();
include("readwellinfo.inc.php");
include("readappinfo.inc.php");
/* if($autoposdec>0){
	$db2=new dbio($seldbname);
	$db2->OpenDb();
	$sql = "select (tot-tvd) as bprjtops from surveys where plan = 1;";
	$db->DoQuery($sql);
	$bprjtpos_r=$db->FetchRow();
	$sval = $bprjtpos_r['bprjtops']; 
	if($sval>0){
		$svalsign='positive';
	} else{
		$svalsign='negative';
	}
	$decval= $autoposdec;
	if($svalsign=='negative') $decval=$decval*-1;
	$sql = "select * from projections order by md";
	$db->DoQuery($sql);
	while($r1 = $db->FetchRow()){
		$sval = $sval - $decval;
		if($db->FetchField('method')==8){			
			$rowid = $db->FetchField('id');
			$data = $db->FetchField('data');
			$split = explode(',',$data);
			if($svalsign=='positive'){
				if($sval < 0) $sval = 0;
			} else{
				if($sval > 0) $sval = 0;
			}
			$split[1]=$sval;
			$ndata = implode(',',$split);
			$sql = "update projections set data='$ndata' where id=$rowid";
			$db2->DoQuery($sql);
		}
	}
	$db2->CloseDb();
} */
$jsurvs = '';
if($sgta_off){
	$jsurvs= '  --justsurveys';
}
exec("./sses_gva -d $seldbname ");
exec("./sses_cc -d $seldbname");
// if("$recalc"!="")
	// exec("./sses_gva -d $seldbname --justsurveys");
exec("./sses_cc -d $seldbname -p");
exec ("./sses_af -d $seldbname");
include("readsurveys.inc.php");

?>
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE><?echo "$dbrealname";?>-SGTA Target Tracker<?echo " ($seldbname)";?></TITLE>
<LINK rel='stylesheet' type='text/css' href='gva_tab3.css'/>
<LINK rel='stylesheet' type='text/css' href='style.css'/>
<link rel="stylesheet" type="text/css" href="waitdlg.css" />
<STYLE>
#layer1 {
	position: fixed;
	visibility: hidden;
	background-color: #eee;
	border: 1px solid #000;
	padding: 0 2;
}
#layer1 input {
	border: none;
	background-color: transparent;
	color: blue;
	padding: 0 0;
	margin: 0;
}
#close {
	float: left;
}
</STYLE>
</HEAD>

<BODY onload="if(document.getElementById('div1')){document.getElementById('div1').scrollTop=document.getElementById('div1').scrollHeight;}">
<div id="layer1">
<a href="javascript:setVisible('layer1',0,0)" style="text-decoration: none"><strong>X</strong></a>
<INPUT readonly TYPE='text' ID='layer1text' VALUE='xx'>
</div>

<input type='hidden' id='seldbn' value='<?echo $seldbname;?>'>
<?
$maintab=2;
include "apptabs.inc.php";
include("waitdlg.html");
?>
<table class='tabcontainer'>
<tr>
<td class='tabcontainer' colspan='3' height='10' width='10'>
	<table class='buttons'><tr>
	<td>
	<H2 style='margin: 0 0; line-height: 1.0; padding: 0 0 0 0;'><?echo $wellname;?></H2>
	</td>
	<td>
		<input type=button name=choice value="PDF Report" onclick="window.open('outputpicker.php?seldbname=<?echo $seldbname?>&title=Survey%20Report&program=surveypdf.php&filename=/tmp/<?echo $seldbname;?>.surveys.pdf&showxy=<?echo $showxy?>','popuppage','width=200,height=220,left=500');">

	</td>
	<td>
		<input type=button name=choice value="Text Report" onclick="window.open('outputpicker.php?seldbname=<?echo $seldbname?>&title=Survey%20Report&program=surveyprint.php&filename=/tmp/<?echo $seldbname;?>.surveys.pdf&showxy=<?echo $showxy?>','popuppage','width=200,height=220,left=500');">
	</td>
	<td>
		<INPUT TYPE="submit" VALUE="Export to CSV" OnClick="OnSurveyCSV()">
	</td>
	<td>
		<input type=button name=choice value="Plot Surveys" onclick="window.open('splotconfig.php?seldbname=<?echo $seldbname?>&title=Survey%20Plot','popuppage','width=450,height=260,left=250');">
	</td>
	<td>
		<input type=button name=choice value="Auto Importer" onclick="window.open('autosurveyloader.php?seldbname=<?echo $seldbname?>','_blank','width=500,height=280,left=250,location=no,menubar=no,resizable=no,status:no,toolbar=no');">
	</td>
	<td>
		<input type=button name=choice value="AntiCollision" onclick="window.open('anticollisionwells.php?seldbname=<?echo $seldbname?>','_blank','width=1300,height=700,top=50,left=150,location=no,menubar=no,resizable=yes,status:no,toolbar=no');">
	</td>
	<td>
		<input type=button name=choice value="Slide Sheet" onclick="window.open('rotslide.php?seldbname=<?echo $seldbname?>','_blank','width=900,height=600,top=50,left=150,location=no,menubar=no,resizable=yes,status:no,toolbar=no,scrollbars=yes');">
	</td>
	<td>
		<input type='button' name='choice' value='Real Time'
			onclick="window.open('realtime.php?seldbname=<?php
			echo $seldbname ?>','_blank','width=450,height=750,left=250,location=no,menubar=no,resizable=no,status:no,toolbar=no')">	
	</td>
	</tr></table>
</td>
</tr>
<tr>
<td class='tabcontainer' align='left' height='10' width='10'>
	<TABLE class='container'>
	<TR>
	<TH>Depth</TH>
	<TH>Inc</TH>
	<TH>Azm</TH>
	</TR>
	<TR>
	<FORM action="surveyadd.php" method="post">
	<?php 
		if($debug){
	?>
		<input type="hidden" name="debug" value="true">
	<?php } ?>
	<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
	<TD><INPUT TYPE="text" VALUE="0" NAME="md" SIZE="6"></TD>
	<TD><INPUT TYPE="text" VALUE="<?echo $inc?>" NAME="inc" SIZE="4"></TD>
	<TD><INPUT TYPE="text" VALUE="<?echo $azm?>" NAME="azm" SIZE="4"></TD>
	<TD><INPUT TYPE="submit" VALUE="Add Survey"></TD>
	</FORM>
	<FORM method="post">
	<td style='padding: 0 4;' height='26px'>
		<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
		<INPUT type='hidden' name='returnto' value='gva_tab3.php'>
		<INPUT type="submit" value="Import Surveys From CSV" ONCLICK="OnImport(this.form)">
	</td>
	</FORM>
	</TR>
	</TABLE>
</td>
<td class='tabcontainer' align='left' height='10' width='10'>
	<TABLE class='container'>
	<TR>
	<TH colspan='4'>Projection Calculator</TH>
	</TR>
	<TR>
	<TD style='padding: 0 4; vertical-align: bottom;' height='26px'>
		<FORM action='projws.php' target='_blank' method='post'>
		<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
		<input type='hidden' name='ret' value='gva_tab3.php'>
		<input type='hidden' name='project' value='bit'>
		<input type="hidden" name="propazm" value="<? echo "$propazm"?>">
		<input type="submit" value="Bit Projection" onclick="projws(this.form)" <?if($svy_total<2) echo "disabled='true'";?>>
		</FORM>
	</TD>
	<td style='padding: 0 4; vertical-align: bottom;' height='26px'>
		<FORM action='oujiaws.php' target='_blank' method='post'>
		<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
		<input type='hidden' name='ret' value='gva_tab3.php'>
		<input type='hidden' name='project' value='ahead'>
		<input type="hidden" name="propazm" value="<? echo "$propazm"?>">
		<input type="submit" value="Ouija" onclick="oujiaws(this.form)" <?if($svy_total<2) echo "disabled='true'";?>>
		</FORM>	
	</td>
	<TD style='padding: 0 4; vertical-align: bottom;' height='26px'>
		<FORM action='projws.php' target='_blank' method='post'>
		<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
		<input type='hidden' name='ret' value='gva_tab3.php'>
		<input type='hidden' name='project' value='ahead'>
		<input type="hidden" name="propazm" value="<? echo "$propazm"?>">
		<input type="submit" value="Add Projection" onclick="projws(this.form)" <?if($svy_total<2) echo "disabled='true'";?>>
		</FORM>
	</TD>
	<td style='padding: 0 4; vertical-align: bottom;' height='26px'>
		<FORM action='fixedlanding.php' target='_blank' method='get'>
		<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
		<input type='hidden' name='ret' value='gva_tab3.php'>
		<input type="submit" value="Fixed Landing" onclick = "fixedlanding(this.form)">
		</FORM>	
	</td>	
	</TR>
	</TABLE>
</td>
<td class='tabcontainer' align='left'>
	<TABLE class='container'>
	<FORM action="ttupdate.php" method="post">
	<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
	<input type="hidden" name="id" value="<?echo "$infotableid";?>">
	<TR>
	<TD align='right' height='22px'>
		Proposed Direction: <input type="text" size="5" name="propazm" value="<? echo "$propazm"?>">
	</TD>
	<TD align='right'>
		TCL: <input type="text" size="5" name="tot" value="<? echo "$plantot"?>">
	</TD>
	<TD align='right'>
		<input type='submit' value='Save Changes' onclick="return ray.ajax()">
	</TD>
	</TR>
	<TR>
	<TD align='right'>
		Projection Dip: <input type="text" size="5" name="projdip" value="<? echo "$projdip"?>">
	</TD>
	<TD align='right'>
		<input style='display:none' type="text" size="5" name="bot" value="<? echo "$planbot"?>">
	</TD>
	</TR>
	</FORM>
	</TABLE>
</td>
<td class='tabcontainer' align='left'>
	<select name='pterm_method' onchange="window.location='setptermmethod.php?seldbname=<?echo $seldbname;?>&method='+this.value;">
	<option value='bc' <?if($pterm_method=='bc'){echo 'selected';}?>>bit consume</option>
	<option value='bp' <?if($pterm_method=='bp'){echo 'selected';}?>>bit push</option>
	</select><button onclick='document.getElementById("bit_method").style.display="block";'>?</button>
	<div style='display:none;position:absolute;padding:5px 5px 5px 5px;background-color:#E6DCB1;z-index:1000;border:solid 1px black;' id='bit_method'>
		<table width='370px'>
		<tr><td colspan='2' align='right'><button onclick='document.getElementById("bit_method").style.display="none";'>x</button></td></tr>
		<tr><td style='width:70px;'>bit consume</td><td>when selected projection stations will not move and when the bit passes the projection station it will be consumed and be removed from the target tracker</td></tr>
		<tr><td colspan='2'>&nbsp;</td></tr>
		<tr><td >bit push</td><td>when selected projection stations will be pushed forward by the bit projection. As the bit moves with newly imported surveys, the VS of the projection stations will change by the amount of VS difference between the previous station and the newly imported station. The final station will not be changed and as projections pass your final station they will disappear.</td></tr>
		</table>
	</div>
	<?php
		
		$dis = $svy_cnt>0?'disabled':'';
		if(isset($_REQUEST['enable_disable'])){
			$dis='';
		}
		if($sgta_off){
			$dis = $svy_cnt>0?'disabled':'';
			echo "<button $dis onclick='window.location=\"sgta_on_off.php?seldbname=$seldbname&value=0\"'>Enable SGTA</button>";
		} else {
			echo "<button $dis onclick='window.location=\"sgta_on_off.php?seldbname=$seldbname&value=1\"'>Disable SGTA</button>";
		}
	?>
</td>
</tr>
<tr>
<td class='tabcontainer' colspan='4'>
	<TABLE class="surveys">
	<TR>
	<td colspan='8'>
		<FORM class='raw' action='gva_tab3.php' method='get' onsubmit='return ray.ajax()'>
		<input type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<input type='hidden' name='sortdir' value='<?echo $revsortdir?>'>
		<INPUT TYPE="submit" VALUE="Reverse Sort">
		</FORM>
	</td>
	<td colspan='2' align='center'>
		<FORM class='raw' action='gva_tab3.php' method='get' onsubmit='return ray.ajax()'>
		<input type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<input type='hidden' name='noshowxy' value='<?echo $noshowxy?>'>
		<INPUT TYPE="submit" VALUE="<?if($showxy==1) echo 'Show CA/CD'; else echo 'Show X/Y';?>">
		</FORM>
	</td>
	<td colspan='2' align='center'>
		<FORM ID="avgdipform" NAME="avgdipform" METHOD="get">
		<input type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<input type='hidden' name='dip' value='<?echo $projdip?>'>
		<INPUT type='hidden' name='ret' value='gva_tab3.php'>
		<INPUT TYPE="submit" VALUE="Average Dip" ONCLICK="OnAvgDip()">
		</FORM>
	</td>
	<td colspan='2' align='center'>
		<FORM ID="recalc" NAME="recalc" METHOD="post">
		<INPUT TYPE="hidden" NAME="seldbname" VALUE="<?echo "$seldbname";?>">
		<input type='hidden' name='sortdir' value='<?echo $sortdir?>'>
		<input type='hidden' name='recalc' value='true'>
		<INPUT TYPE="submit" VALUE="Recalculate Postions">
		</FORM>
	</td>
	<td colspan='5' align='right'>
		<FORM ID="delsvys" NAME="delsvys" METHOD="post">
		<INPUT TYPE="hidden" NAME="seldbname" VALUE="<?echo "$seldbname";?>">
		<input type='hidden' name='sortdir' value='<?echo $sortdir?>'>
		<INPUT TYPE="hidden" NAME="sids" VALUE="">
		<input type='hidden' name='deccl' value='f'>
		<input type='hidden' name='deccl_m' value='0'>
		</FORM>
		<INPUT TYPE="submit" VALUE="Select All" ONCLICK="OnSetChecks()">
		<INPUT TYPE="submit" VALUE="Select None" ONCLICK="OnClearChecks()">
		<INPUT TYPE="submit" VALUE="Delete Selected" ONCLICK="OnDelSurveys()">
	</td>
	</TR>
	<TR> 
	<TH colspan='12' >Survey Data</TH>
	<TH colspan='4' >Target Tracker Section</TH>
	</TR>
	<TR> 
	<TH class='surveys'>Svy</TH>
	<TH class='surveys'>Depth</TH>
	<TH class='surveys'>Inc</TH>
	<TH class='surveys'>Azm</TH>
	<TH class='surveys'>TVD</TH>
	<TH class='surveys'>VS</TH>
	<TH class='surveys'>NS</TH>
	<TH class='surveys'>EW</TH>
	<?if($showxy==1){
		echo "<TH class='surveys'>Northing</TH>";
		echo "<TH class='surveys'>Easting</TH>";
	}else{
		echo "<TH class='surveys'>CD</TH>";
		echo "<TH class='surveys'>CA</TH>";
	}?>
	<TH class='surveys'>DL</TH>
	<TH class='surveys'>CL</TH>
	<TH class='surveys'>TF</TH>
	<TH class='rot'>TCL</TH>
	<TH class='rot'>Pos-TCL</TH>
	<TH class='rot'>TOT</TH>
	<TH class='rot'>BOT</TH>
	<TH class='rot'>Dip</TH>
	<TH class='rot'>Fault</TH>
	<TH class='rot'>Del</TH>
	</TR>
	<?
	if($surveysort=="DESC") PrintProjections();
	PrintSurveys();
	if($surveysort=="ASC") PrintProjections();
	$db->CloseDb();
	?>
	<INPUT TYPE="hidden" NAME="surveysort" VALUE="<?echo $surveysort;?>" ID="surveysort">
	<INPUT TYPE="hidden" NAME="numsvys" VALUE="<?echo $svy_total;?>" ID="numsvys">
	<INPUT TYPE="hidden" NAME="numprojs" VALUE="<?echo $numprojs;?>" ID="numprojs">
	<INPUT TYPE="hidden" NAME="svy_cnt" VALUE="<?echo $svy_cnt;?>" ID="svy_cnt">
	<INPUT TYPE="hidden" NAME="svy_total" VALUE="<?echo $svy_total;?>" ID="svy_total">
	<TR> 
	<TH class='surveys'>Svy</TH>
	<TH class='surveys'>Depth</TH>
	<TH class='surveys'>Inc</TH>
	<TH class='surveys'>Azm</TH>
	<TH class='surveys'>TVD</TH>
	<TH class='surveys'>VS</TH>
	<TH class='surveys'>NS</TH>
	<TH class='surveys'>EW</TH>
	<?if($showxy==1){
		echo "<TH class='surveys'>Northing</TH>";
		echo "<TH class='surveys'>Easting</TH>";
	}else{
		echo "<TH class='surveys'>CD</TH>";
		echo "<TH class='surveys'>CA</TH>";
	}?>
	<TH class='surveys'>DL</TH>
	<TH class='surveys'>CL</TH>
	<TH class='surveys'>TF</TH>
	<TH class='rot'>TCL</TH>
	<TH class='rot'>Pos-TCL</TH>
	<TH class='rot'>TOT</TH>
	<TH class='rot'>BOT</TH>
	<TH class='rot'>Dip</TH>
	<TH class='rot'>Fault</TH>
	<TH class='rot'>Del</TH>
	</TR>
	</TABLE>
	<br><center><small>&#169; 2010-2011 Supreme Source Energy Services, Inc.</small></center>
</td>
</tr>
</table>
<SCRIPT language="javascript" src="waitdlg.js"></SCRIPT>
<script type="text/javascript" src="popupDiv.js"></script>
<SCRIPT language="javascript">
function save_dip(el){
 id = el.id.split("_")[1]
 window.location="surveyfaultdipupdate.php?seldbname=<?php echo $seldbname?>&action=dip&value="+el.value+"&id="+id
}

function save_fault(el){
 id = el.id.split("_")[1]
 window.location="surveyfaultdipupdate.php?seldbname=<?php echo $seldbname?>&action=fault&value="+el.value+"&id="+id
}

function OnAvgDip()
{
	var phpcall="projavgdip.php?seldbname=" + document.getElementById("seldbn").value;
	var newwindow=window.open(phpcall, 'Average Dip Calulator',
		'height=320,width=400,left=500,top=60,scrollbars=no,location=no,resizable=no');
	if (window.focus) {newwindow.focus()}
	return false;
}
function projws(myform)
{
	var l=0;
	var t=window.screenTop;
	if (! window.focus)return true;
window.open('', 'Projection Worksheet',
'height=260,width=1020,left=100,top='+t +', scrollbars=yes, location=no, resizable=no');
	myform.target='Projection Worksheet';
	return false;
}
function oujiaws(myform)
{
	var l=0;
	var t=window.screenTop;
	if (! window.focus)return true;
window.open('', 'Oujia Worksheet',
'height=600,width=1020,left=100,top='+t +', scrollbars=yes, location=yes, resizable=yes');
	myform.target='Oujia Worksheet';
	return false;	
}
function fixedlanding(myform){
	var l=0;
	var t=window.screenTop;
	if (! window.focus)return true;
window.open('', 'Fixed Landing',
'height=350,width=585,left=100,top='+t +', scrollbars=yes, location=yes, resizable=yes');
	myform.target='Fixed Landing';
	return false;	
}
function OnDelSurveys()
{
	var numsvys=parseFloat(document.getElementById("numsvys").value);
	var numprojs=parseFloat(document.getElementById("numprojs").value);
	var high_num = parseFloat(document.getElementById("high_num").value)
	var high_cl  = parseFloat(document.getElementById("high_cl").value)
	numsvys+=numprojs;
	var do_cldel = false;
	var sids="";
	num = -1;
	for(i=0; i<numsvys; i=i+1) {
		fid="f"+i;
		form=document.getElementById(fid);
		dodel=form.del.checked;
		id=form.id.value;
		try{
			num = parseFloat(form.num.value);
		} catch(e){}
		if(dodel>0) {
			if(sids=="") sids=id;
			else sids=sids+","+id;
			if(high_num==num){
				do_cldel=true;
			}
		}
	}
	if(sids=="")	return;
	rowform=document.getElementById("delsvys");
	rowform.sids.value=sids;
	if(do_cldel){
		rowform.deccl.value='t';
		rowform.deccl_m.value=high_cl;
	}else {
		rowform.deccl.value='f';
		rowform.deccl_m.value=0;
	}
	result=confirm("Delete these surveys, Are you sure?");
	if(result)
	{
		t = 'surveydel.php';
		t = encodeURI (t); // encode URL
		rowform.action = t;
		rowform.submit(); // submit form using javascript
		return ray.ajax();
	}
}
function OnClearChecks()
{
	var numsvys=parseFloat(document.getElementById("numsvys").value);
	var numprojs=parseFloat(document.getElementById("numprojs").value);
	numsvys+=numprojs;
	for(i=0; i<numsvys; i=i+1) {
		fid="f"+i;
		form=document.getElementById(fid);
		form.del.checked=0;
	}
}
function OnSetChecks()
{
	var numsvys=parseFloat(document.getElementById("numsvys").value);
	var numprojs=parseFloat(document.getElementById("numprojs").value);
	numsvys+=numprojs;
	for(i=0; i<numsvys; i=i+1) {
		fid="f"+i;
		form=document.getElementById(fid);
		form.del.checked=1;
	}
}
function OnImport(rowform)
{
	t = 'surveysfilesel.php';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return true;
	// return ray.ajax();
}
function OnSurvey(rowform)
{
	t = 'surveychange.php';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return ray.ajax();
}
function OnSurveyPDF(rowform)
{
	var phpcall="surveypdf.php?seldbname=" + document.getElementById("seldbn").value;
	// newwindow=window.open('surveypdf.php');
	newwindow=window.open(phpcall);
	if (window.focus) {newwindow.focus()}
	return false;
}
function OnSurveyCSV(rowform)
{
	var phpcall="surveycsv.php?seldbname=" + document.getElementById("seldbn").value;
	newwindow=window.open(phpcall);
	if (window.focus) {newwindow.focus()}
	return false;
}
function OnSurveyPlotVS()
{
	var phpcall="surveyplotvs.php?seldbname=" + document.getElementById("seldbn").value;
	newwindow=window.open(phpcall,'VerticalSection',
		'height=950,width=1250,scrollbars=yes');
	if (window.focus) {newwindow.focus()}
	return false;
}
function OnSurveyPrint()
{
	var phpcall="surveyprint.php?seldbname=" + document.getElementById("seldbn").value;
	newwindow=window.open(phpcall,'SurveyPrintout',
		'height=650,width=950,left=100,top=0,scrollbars=yes');
	if (window.focus) {newwindow.focus()}
	return false;
}
function OnSurveyPlotLateral(rowform)
{
	var phpcall="surveyplotlat.php?seldbname=" + document.getElementById("seldbn").value;
	newwindow=window.open(phpcall,'PolarPlot',
		'height=950,width=1250,scrollbars=yes');
	if (window.focus) {newwindow.focus()}
	return false;
}
function OnSurveyPlotPolar()
{
	var phpcall="surveyplotpolar.php?seldbname=" + document.getElementById("seldbn").value;
	newwindow=window.open(phpcall,'PolarPlot',
		'height=850,width=950,scrollbars=yes');
	if (window.focus) {newwindow.focus()}
	return false;
	// window.open ('surveyplotpolar.php');
	// return true;
}
function OnSurveyPlot3D() {
	var phpcall="surveyplot3d.php?xaxis=70&zaxis=10&seldbname=" + document.getElementById("seldbn").value;
	newwindow=window.open(phpcall,'3DPlot',
		'height=950,width=1050,scrollbars=yes');
	if (window.focus) {newwindow.focus()}
	return false;
}

/* Display handling */
function noshowline() {
	var numprojs=document.getElementById("numprojs").value;
	var i;
	for(i=0;i<numprojs;i++)  {
		var emd=document.getElementById("gridmd"+i);
		var einc=document.getElementById("gridinc"+i);
		var eazm=document.getElementById("gridazm"+i);
		var etvd=document.getElementById("gridtvd"+i);
		var evs=document.getElementById("gridvs"+i);
		var ens=document.getElementById("gridns"+i);
		var eew=document.getElementById("gridew"+i);
		var ecd=document.getElementById("gridcd"+i);
		var eca=document.getElementById("gridca"+i);
		var edl=document.getElementById("griddl"+i);
		var ecl=document.getElementById("gridcl"+i);
		var etpos=document.getElementById("gridtpos"+i);
		var etot=document.getElementById("gridtot"+i);
		var ebpos=document.getElementById("gridbpos"+i);
		var ebot=document.getElementById("gridbot"+i);
		var edip=document.getElementById("griddip"+i);
		var efault=document.getElementById("gridfault"+i);
		emd.setAttribute('class', 'gridproj gridmdcl');
		einc.setAttribute('class', 'gridproj gridmdcl');
		eazm.setAttribute('class', 'gridproj gridmdcl');
		etvd.setAttribute('class', 'gridproj gridmdcl');
		evs.setAttribute('class', 'gridproj gridmdcl');
		ens.setAttribute('class', 'gridproj gridmdcl');
		eew.setAttribute('class', 'gridproj gridmdcl');
		ecd.setAttribute('class', 'gridproj gridmdcl');
		eca.setAttribute('class', 'gridproj gridmdcl');
		edl.setAttribute('class', 'gridproj gridmdcl');
		ecl.setAttribute('class', 'gridproj gridmdcl');
		etpos.setAttribute('class', 'gridproj gridtclbot');
		etot.setAttribute('class', 'gridproj gridtclbot');
		try{
			ebpos.setAttribute('class', 'gridproj gridtclbot');
		}catch(e){}
		ebot.setAttribute('class', 'gridproj gridtclbot');
		edip.setAttribute('class', 'gridproj gridtclbot');
		efault.setAttribute('class', 'gridproj gridtclbot');
		// clrVisible('layer1');
	}
}
function showline(i) {
	noshowline();
	
	var emd=document.getElementById("gridmd"+i);
	var einc=document.getElementById("gridinc"+i);
	var eazm=document.getElementById("gridazm"+i);
	var etvd=document.getElementById("gridtvd"+i);
	var evs=document.getElementById("gridvs"+i);
	var ens=document.getElementById("gridns"+i);
	var eew=document.getElementById("gridew"+i);
	var ecd=document.getElementById("gridcd"+i);
	var eca=document.getElementById("gridca"+i);
	var edl=document.getElementById("griddl"+i);
	var ecl=document.getElementById("gridcl"+i);
	var etpos=document.getElementById("gridtpos"+i);
	var etot=document.getElementById("gridtot"+i);
	var ebpos=document.getElementById("gridbpos"+i);
	var ebot=document.getElementById("gridbot"+i);
	var edip=document.getElementById("griddip"+i);
	var efault=document.getElementById("gridfault"+i);
	emd.setAttribute('class', 'gridro');
	einc.setAttribute('class', 'gridro');
	eazm.setAttribute('class', 'gridro');
	etvd.setAttribute('class', 'gridro');
	evs.setAttribute('class', 'gridro');
	ens.setAttribute('class', 'gridro');
	eew.setAttribute('class', 'gridro');
	ecd.setAttribute('class', 'gridro');
	eca.setAttribute('class', 'gridro');
	edl.setAttribute('class', 'gridro');
	ecl.setAttribute('class', 'gridro');
	etpos.setAttribute('class', 'gridro');
	etot.setAttribute('class', 'gridro');
	try{
		ebpos.setAttribute('class', 'gridro');
	}catch(e){}
	ebot.setAttribute('class', 'gridro');
	edip.setAttribute('class', 'gridro');
	efault.setAttribute('class', 'gridro');
	
}
function showMethod(event, i) {
	var emeth=parseInt(document.getElementById("gridmeth"+i).value);
	var msg=document.getElementById("layer1text");
	if(emeth==0) msg.value="Last dogleg";
	else if(emeth==1) msg.value="Solve for INC";
	else if(emeth==2) msg.value="Solve for AZM";
	else if(emeth==3) msg.value="Input MD/INC/AZM";
	else if(emeth==4) msg.value="Solve for MD";
	else if(emeth==5) msg.value="Solve for INC";
	else if(emeth==6) msg.value="Input TVD/VS";
	else if(emeth==7) msg.value="Input TOT/POS/VS";
	else if(emeth==8) msg.value="Input DIP/FAULT/POS/VS";
	else msg.value="Unknown method";
	x=event.clientX-10;
	y=event.clientY-10;
	setVisible('layer1', x, y);
}
</SCRIPT>
</BODY>
</HTML>
<?php include_once('gva_tab5_funct.php');?>
