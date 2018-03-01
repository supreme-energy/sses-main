<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Digital Oil Tools

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'sses_include.php';
require_once 'dbio.class.php';

$seldbname = (isset($_GET['seldbname']) ? $_GET['seldbname'] : '');
if($seldbname=="") $seldbname=$_POST['seldbname'];
$seledata = (isset($_GET['seledata']) ? $_GET['seledata'] : '');
if($seledata == "" and isset($_POST['seledata'])) $seledata=$_POST['seledata'];
if($seldbname=="") include("dberror.php");
$db=new dbio("$seldbname");
$db->OpenDb();
include("readwellinfo.inc.php");
include("readappinfo.inc.php");
$ids=array(); $colnums=array(); $labels=array();
$colors=array();
if(isset($autorc_host) and $autorc_host){
	$db->DoQuery("SELECT * FROM edatalogs ORDER BY enabled desc,label;");
}else{
	$db->DoQuery("SELECT * FROM edatalogs ORDER BY colnum;");
}
while($db->FetchRow()) {
	$i=$ids[]=$db->FetchField("id");
	$colnums[]=$db->FetchField("colnum");
	$labels[]=$db->FetchField("label");
	$colors[]=$db->FetchField("color");
	$enabled=$db->FetchField("enabled");
	$singleplot = $db->FetchField("single_plot");
	if($seledata==$i) {
		$colnum=$db->FetchField("colnum");
		$label=$db->FetchField("label");
		$tablename=$db->FetchField("tablename");
		$scalelo=$db->FetchField("scalelo");
		$scalehi=$db->FetchField("scalehi");
		$color=$db->FetchField("color");
		$logscale=$db->FetchField("logscale");
		$eshow = $db->FetchField("enabled");
		$esingle = $db->FetchField("single_plot");
	}
	else if(!isset($seledata) && $enabled>0) {
		$colnum=$db->FetchField("colnum");
		$label=$db->FetchField("label");
		$tablename=$db->FetchField("tablename");
		$scalelo=$db->FetchField("scalelo");
		$scalehi=$db->FetchField("scalehi");
		$color=$db->FetchField("color");
		$logscale=$db->FetchField("logscale");
		$seledata=$i;
		$eshow=$enabled;
		$esingle = $singleplot;
	}
}
if(!isset($seledata))	$seledata=-1;

$startmd=0;
$endmd=0;
//$db->DoQuery("UPDATE edatalogs SET enabled=0;");
if($seledata>=0) {
	//$db->DoQuery("UPDATE edatalogs SET enabled=1 WHERE id=$seledata;");
	if(isset($tablename) && strlen($tablename)>0) {
		$query = "select max(md) as mdmax,min(md) as mdmin,max(value),min(value),avg(value) from \"$tablename\";";
		$db->DoQuery($query);
		$db->FetchRow();
		$startmd= $db->FetchField("mdmin");
		$endmd  = $db->FetchField("mdmax");
		$vallow = $db->FetchField('min');
		$valhigh = $db->FetchField('max');
		$valavg  = $db->FetchField('avg');
	} else $seledata=-1;
}
else {
	$tablename="";
	$colnum="";
	$label="";
	$scalelo="";
	$scalehi="";
	$color="000000";
	$logscale="";
}
?>
<HTML>
<HEAD>
<title><? echo (isset($dbrealname) ? $dbrealname : '') ?>-Additional Data<?echo " ($seldbname)";?></title>
<link rel="stylesheet" href="farbtastic.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="gva_tab6.css" />
<link rel="stylesheet" type="text/css" href="waitdlg.css" />
</HEAD>
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="farbtastic.js"></script>
<script type="text/javascript" charset="utf-8">
  $(document).ready(function() {
    $('#demo').hide();
    $('#picker').farbtastic('#color');
  });
</script>

<BODY>
<div id="demo" style="color: red; font-size: 1.4em">
jQuery.js is not present. You must install jQuery in this folder for the color wheel to work.</div>
<?
$maintab=5;
$show_tabs = isset($_REQUEST['no_tabs']) ? false: true;
if($show_tabs){
	include "apptabs.inc.php";
}
include("waitdlg.html");
?>
<table class='tabcontainer'>
<form action="edatachange.php" method='post' id='edataform' name='edataform'>
<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
<input type='hidden' name='seledata' value='<?echo $seledata;?>'>
<input type='hidden' name='tablename' value='<?echo $tablename?>'>
<input type='hidden' name='colnum' value='<?echo $colnum?>'>
<input type='hidden' name='logscale' value='<?echo $logscale?>'>
<tr>
<td colspan='2' align='center'>
	<table class='example'>
<?php
if(!isset($autorc_host) or !$autorc_host)
{
?>
	<tr>
	<td>LAS column ordering:</td>
	<td class='example'>MD</td>
	<td class='example'>GR</td>
	<td class='example'>TVD</td>
	<td class='example'>VS</td>
	<td class='example'>INC</td>
	<td class='example'>AZM</td>
	<?
	$cnt=count($ids);
	for($i=0; $i<$cnt; $i++) {
		if($ids[$i]==$seledata) {
			echo "<td class='examplered' style='color: #{$colors[$i]};'>";
			echo "{$labels[$i]}</td>";
		} else {
			?>
			<td class='example' style="color: #<?echo $colors[$i]?>;">
			<input type='text' class='example' \
				size='<?$a=strlen($labels[$i]); echo $a;?>' \
				style="color: #<?echo $colors[$i]?>;" \
				value='<?echo $labels[$i];?>' \
				onclick="doSelectEdata(this.form,<?echo $ids[$i];?>)">
			<?
		}
	} 
	?>
	</tr>
<?php
}
else
{
?>
		<tr>
			<td>Available Log Data</td>
			<td><select id='logdatas' name='id'  ONCHANGE="OnSelectEdata(this.form)">
				<option name='opt' value='-1' <?if($seledata<0) echo " selected='selected '";?>> None </option>
						<? 
						$cnt=count($ids);
					for($i=0; $i<$cnt; $i++) {
									$cnt=count($ids);
						echo "<option name='opt$i' value='{$ids[$i]}'";
						if($ids[$i]==$seledata)	echo " selected='selected'";
						$c=$colnums[$i] + 7;
						echo ">{$labels[$i]}</option>";
					} 
						?>
						</select>
		  </td>
		 </tr>
<?php
}
?>
	</table>
	<br>
</td>
</tr>
<tr>
<th width='50%'>Select Line Color</th>
<th>Choose Data Column To Plot</th>
</tr>
<tr>
<td>
	<table class='buttons'>
	<tr>
	<td>
	<div id="picker"></div>
	</td>
	</tr>
	</table>
</td>
<td>
	<table class='buttons' style='width:90%'>
	<tr>
	<td class='right' colspan='2'>
		<?if(!isset($autorc_host) or !$autorc_host){?>
		<select style='font-size: 10pt;' name='id' ONCHANGE="OnSelectEdata(this.form)">
		<option name='opt' value='-1' <?if($seledata<0) echo " selected='selected '";?>> None </option>
		<? 
		$cnt=count($ids);
		for($i=0; $i<$cnt; $i++) {
			echo "<option name='opt$i' value='{$ids[$i]}'";
			if($ids[$i]==$seledata)	echo " selected='selected'";
			$c=$colnums[$i] + 7;
			echo ">LAS column $c: {$labels[$i]}</option>";
		} 
		?>
		</select>
		<?}else{
			$cnt=count($ids);
			for($i=0; $i<$cnt; $i++) {
				if($ids[$i]==$seledata)	echo $labels[$i];
			} 
		}?>
	</td>
	</tr>

	<tr>
	<td class='right'> Start MD </td>
	<td class='left'> <input type='text' readonly='true' size='7' value='<?echo (isset($startmd) ? $startmd : '') ?>'> </td>
	</tr>
	<tr>
	<td class='right'> Last MD </td>
	<td class='left'> <input type='text' readonly='true' size='7' value='<?echo (isset($endmd) ? $endmd : '') ?>'> </td>
	</tr>

	<tr>
	<td class='right'> Label name </td>
	<td class='left'> <input type='text' <?if($seledata<0) echo " disabled='true' ";?> size='10' name='label' value='<?echo (isset($label) ? $label : '') ?>' onchange="OnChangeEdata(this.form)"> </td>
	</tr>
	<tr>
	<td class='right'> High Value</td>
	<td class='left'> <input disabled type='text' <?if($seledata<0) echo " disabled='true' ";?> size='4' name='valhigh' value='<?echo (isset($valhigh) ? $valhigh : '') ?>'> </td>
	</tr>
	<tr>
	<td class='right'> Low Value</td>
	<td class='left'> <input disabled  type='text' <?if($seledata<0) echo " disabled='true' ";?> size='4' name='vallow' value='<?echo (isset($vallow) ? $vallow : '') ?>'> </td>
	</tr>
	<tr>
	<td class='right'> Avg Value </td>
	<td class='left'> <input disabled  type='text' <?if($seledata<0) echo " disabled='true' ";?> size='4' name='avgval' value='<?echo (isset($valavg) ? $valavg : '') ?>'> </td>
	</tr>
	<tr>
	<td class='right'> Scale Low </td>
	<td class='left'> <input type='text' <?if($seledata<0) echo " disabled='true' ";?> size='4' name='scalelo' value='<?echo (isset($scalelo) ? $scalelo : '')?>' onchange="OnChangeEdata(this.form)"> </td>
	</tr>
	<tr>
	<td class='right'> Scale High </td>
	<td class='left'> <input type='text' <?if($seledata<0) echo " disabled='true' ";?> size='4' name='scalehi' value='<?echo (isset($scalehi) ? $scalehi : '') ?>' onchange="OnChangeEdata(this.form)"> </td>
	</tr>
	<tr>
	<div class="form-item">
	<td class='right'> Color </td>
	<td class='left'> <input type="text" readonly='true' size='7' id="color" name="color" value="<?echo (isset($color) ? "#$color" : '') ?>" /> </td>
	</div>
	</tr>
	<tr>
	<div class="form-item">
	<td class='right'> Enabled </td>
	<td class='left'> <input type="checkbox" name="enabled" value="1" <? echo ($eshow=='1'?'checked':'')?> onchange="OnChangeEdata(this.form)"/> </td>
	</div>
	</tr>
	<tr>
	<div class="form-item">
	<td class='right'> Single Plot </td>
	<td class='left'> <input type="checkbox" name="singleplot" value="1" <? echo ($esingle=='1'?'checked':'')?> onchange="OnChangeEdata(this.form)"/> </td>
	</div>
	</tr>
	<tr>
	<td colspan='2' style='text-align: center;'>
		<table>
		<tr>
		<td style='text-align: center; width: 40%;'>
		<input type='submit' <?if($seledata<0) echo " disabled='true' ";?> value='Update' onclick="OnChangeEdata(this.form)">
		</td>
		<td style='text-align: center; width: 25%;'>
		<input type='submit' <?if($seledata<0) echo " disabled='true' ";?> value='Delete' onclick="OnDeleteEdata()">
		</td>
		<td style='text-align: center; width: 35%;'>
		<input type='submit' value='Add' onclick="OnAddEdata()">
		</td>
		</tr>
		</table>
	</td>
	</tr>
	</table>
</td>
</tr>
</form>

<tr>
<td colspan'2'>
<br><br>
</td>
</tr>
<tr>
<td colspan='2'>
<?php
if(!isset($autorc_host) or !$autorc_host)
{
?>
<div class="container" style='float:left;text-align:left;width:50%'><div style='font-weight:bolder'>Select LAS File to Import from:</div>
<div style='margin-top:10px'>
<form method="post" enctype="multipart/form-data">
<INPUT type='hidden' name='ret' value='gva_tab6.php'>
<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
<INPUT type="file" name="userfile" size="70">
<INPUT type="submit" value="Import" onclick="show_alert(this.form)">
</form>
</div>
</div>
<?php
}
?>
<div class="container" style='float:left;text-align:left;width:50%'>
<div style='font-weight:bolder'>Select CSV File to Import Slide Sheet:</div>
<div style='margin-top:10px'>
<form method="post" enctype="multipart/form-data">
<input type="file" name="rotslide_csv_file" size="70">
<input type="submit" value="Import" onclick="return confirm('Ready to upload CSV file?')">
</form>
</div>
<?php
if(isset($_FILES) and isset($_FILES['rotslide_csv_file']))
{
	echo "<div style='font-size:12px'>";
	//echo '<pre>'; print_r($_FILES); echo '</pre>';
	if($_FILES['rotslide_csv_file']['name'] == '')
	{
		echo "<span style='color:red'>Oops! Did not choose a file.</span>\n";
	}
	else
	{
		require_once 'rotslidelib.php';
		$db->error_print = true;
		if(($num = ImportRotSlideFromFile($db,$_FILES['rotslide_csv_file']['tmp_name'])) !== false)
		{
			echo "<span style='color:green'>Uploaded $num records successfully.</span>\n";
		}
	}
	echo "</div>\n";
}
?>
</div>
<div style='clear:both'></div>
<div style='text-align:center;font-size:12px;margin-top:10px'>&#169; 2010-2011 Digital Oil Tools</div>
</td>
</tr>
</table>
<script language="javascript" type="text/javascript" src="waitdlg.js"></script>
<SCRIPT language="javascript">
function show_alert(rowform)
{
	var r=confirm("Ready to import LAS file?");
	if (r==true)
  	{
		t = 'edatauploadd.php';
		t = encodeURI (t); // encode URL
		rowform.action = t;
		rowform.submit(); // submit form using javascript
		return ray.ajax();
  	}
	rowform.userfile.value="";
	document.location=rowform.ret.value;
	// return ray.ajax();
	return true;
}

function doSelectEdata(rowform,id)
{
	rowform.seledata.value=id;
	t = 'gva_tab6.php';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	// return ray.ajax();
	return false;
}
function OnSelectEdata(rowform)
{
	rowform.seledata.value=rowform.id.value;
	t = 'gva_tab6.php';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	// return ray.ajax();
	return false;
}
function OnChangeEdata(rowform)
{
	rowform.seledata.value=rowform.id.value;
	t = 'edatachange.php';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return false;
}
function OnDeleteEdata()
{
	var r =confirm("Delete this data definition and all of the data stored?");
	if(r) {
		var f=document.getElementById("edataform");
		f.action.value="edatadel.php";
		t = 'edatadel.php';
		t = encodeURI (t); // encode URL
		f.action = t;
		f.submit(); // submit form using javascript
		return false;
	}
}
function OnAddEdata()
{
	var f=document.getElementById("edataform");
	f.action.value="edataadd.php";
	t = 'edataadd.php';
	t = encodeURI (t); // encode URL
	f.action = t;
	f.submit(); // submit form using javascript
	return false;
}
</SCRIPT>
</BODY>
</HTML>
<?php
$db->CloseDb();
?>
