<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Digital Oil Tools

if($seldbname==""||$seldbname==null) $seldbname=$_GET['seldbname'];
if($title==""||$title==null) $title=$_GET['title'];
require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
include("readwellinfo.inc.php");
$ptype="LAT";
$mtype="TVD";
$inputa="60.0";
$yscale="300";
$mintvd=$minvs=99999.0;
$maxtvd=$maxvs=-99999.0;
include("readappinfo.inc.php");
if($lastptype!="")	$ptype=$lastptype;
if($lastmtype!="")	$mtype=$lastmtype;


$db->DoQuery("SELECT * FROM splotlist WHERE ptype='$ptype' AND mtype='$mtype';");
if($db->FetchRow()) {
	$inputa=$db->FetchField("inputa");
	$yscale=$db->FetchField("inputb");
	$mintvd=$db->FetchField("mintvd");
	$maxtvd=$db->FetchField("maxtvd");
	$minvs=$db->FetchField("minvs");
	$maxvs=$db->FetchField("maxvs");
}
$db->CloseDb();
if ($ptype=="POL") {
	$program="surveyplotpolar.php";
	$filename="/tmp/$seldbname.surveyplotpol.pdf";
} else if ($ptype=="VS") {
	$program="surveyplotvs.php";
	$filename="/tmp/$seldbname.surveyplotvs.pdf";
} else if ($ptype=="LAT") {
	$program="surveyplotlat.php";
	$filename="/tmp/$seldbname.surveyplotlat.pdf";
} else if ($ptype=="CL") {
	$program="surveyplotcl.php";
	$filename="/tmp/$seldbname.surveyplotcl.pdf";
} else {
	$program="surveyplot3d.php";
	$filename="/tmp/$seldbname.surveyplot3d.pdf";
}
if($ptype=="VS") $yscale*=100;
//<link rel='stylesheet' type='text/css' href='splotconfig.css?v=1.4'/>
?>
<!doctype html>
<html>
<head>
<title><?php echo $title?></title>
<style>
body {
	margin:10px;
	font-family: Helvetica, "Arial sans-serif", Arial, sans-serif;
	color:black;
	background-color:#2C4C69;
	font-size:10pt;
}
table.tabcontainer {
	border:2px solid black;
	color:black;
	background-color:rgb(240,230,200);
	margin:0 auto;
	border-collapse:collapse;
}
table.tabcontainer th {
	background-color: #497daa;
}
table.tabcontainer td {
	padding:5px 25px;
	text-align:center;
}
</style>
<script language="javascript">
function OnSubmit() {
	rowform=document.getElementById("splotconfigd");
	t = 'splotconfigd.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return true;
}

function OnEmailReport()
{
//	if(window.opener){
//		window.opener.location = window.opener.location
//	}
	var mtype=document.getElementById("mtype").value;
	var ptype=document.getElementById("ptype").value;
	if(mtype=="INC") {
		/*
		var phpcall=
		"emailmsg.php?seldbname=" + document.getElementById("seldbname").value +
		"&program=" + document.getElementById("program").value +
		"&cutoff=" + document.getElementById("inputa").value +
		"&filename=" + document.getElementById("filename").value;
		*/
		if(ptype=="CL") {
			var phpcall=
			"emailmsg.php?seldbname=" + document.getElementById("seldbname").value +
			"&program=" + document.getElementById("program").value +
			"&filename=" + document.getElementById("filename").value;
		} else if(ptype=="POL" || ptype=="3D") {
			var phpcall=
			"emailmsg.php?seldbname=" + document.getElementById("seldbname").value +
			"&program=" + document.getElementById("program").value +
			"&cutoff=" + document.getElementById("inputa").value +
			"&filename=" + document.getElementById("filename").value;
		} else {
			var phpcall=
			"emailmsg.php?seldbname=" + document.getElementById("seldbname").value +
			"&program=" + document.getElementById("program").value +
			"&cutoff=" + document.getElementById("inputa").value  +
			"&yscale=" + document.getElementById("inputb").value +
			"&filename=" + document.getElementById("filename").value;
		}
	} else {
		var phpcall=
		"emailmsg.php?seldbname=" + document.getElementById("seldbname").value +
		"&program=" + document.getElementById("program").value +
		"&mintvd=" + document.getElementById("mintvd").value +
		"&maxtvd=" + document.getElementById("maxtvd").value +
		"&minvs=" + document.getElementById("minvs").value +
		"&maxvs=" + document.getElementById("maxvs").value +
		"&yscale=" + document.getElementById("inputb").value +
		"&filename=" + document.getElementById("filename").value;
		;
	}
	newwindow=window.open(phpcall, "_blank", "width=950,height=700,left=10,top=0,status=0,scrollbars=yes");
	// newwindow=window.opener.window.open(phpcall);
	if (window.focus) {newwindow.focus()}
	window.close();
}
function OnApproveReport(){
	var mtype=document.getElementById("mtype").value;
	var ptype=document.getElementById("ptype").value;
	if(mtype=="INC") {
		/*
		var phpcall=
		"approvereport.php?seldbname=" + document.getElementById("seldbname").value +
		"&program=" + document.getElementById("program").value +
		"&cutoff=" + document.getElementById("inputa").value +
		"&filename=" + document.getElementById("filename").value+
		"&approve_report=1";
		*/
		if(ptype=="CL") {
			var phpcall=
			"approvereport.php?seldbname=" + document.getElementById("seldbname").value +
			"&program=" + document.getElementById("program").value +
			"&filename=" + document.getElementById("filename").value+"&approve_report=1";;
		} else if(ptype=="POL" || ptype=="3D") {
			var phpcall=
			"approvereport.php?seldbname=" + document.getElementById("seldbname").value +
			"&program=" + document.getElementById("program").value +
			"&cutoff=" + document.getElementById("inputa").value +
			"&filename=" + document.getElementById("filename").value+"&approve_report=1";
		} else {
			var phpcall=
			"approvereport.php?seldbname=" + document.getElementById("seldbname").value +
			"&program=" + document.getElementById("program").value +
			"&cutoff=" + document.getElementById("inputa").value  +
			"&yscale=" + document.getElementById("inputb").value +
			"&filename=" + document.getElementById("filename").value+"&approve_report=1";
		}
	} else {
		var phpcall=
		"approvereport.php?seldbname=" + document.getElementById("seldbname").value +
		"&program=" + document.getElementById("program").value +
		"&mintvd=" + document.getElementById("mintvd").value +
		"&maxtvd=" + document.getElementById("maxtvd").value +
		"&minvs=" + document.getElementById("minvs").value +
		"&maxvs=" + document.getElementById("maxvs").value +
		"&yscale=" + document.getElementById("inputb").value +
		"&filename=" + document.getElementById("filename").value+"&approve_report=1";
	}
	window.location.href=phpcall;
}
function OnViewReport()
{
//	if(window.opener){
//		window.opener.location = window.opener.location;
//	}
	var mtype=document.getElementById("mtype").value;
	var ptype=document.getElementById("ptype").value;
	if(mtype=="INC") {
		if(ptype=="CL") {
			var phpcall=
			document.getElementById("program").value +
			"?seldbname=" + document.getElementById("seldbname").value;
		} else if(ptype=="POL" || ptype=="3D") {
			var phpcall=
			document.getElementById("program").value +
			"?seldbname=" + document.getElementById("seldbname").value +
			"&cutoff=" + document.getElementById("inputa").value ;
		} else {
			var phpcall=
			document.getElementById("program").value +
			"?seldbname=" + document.getElementById("seldbname").value +
			"&cutoff=" + document.getElementById("inputa").value  +
			"&yscale=" + document.getElementById("inputb").value ;
		}
	} else {
		var phpcall=
		document.getElementById("program").value +
		"?seldbname=" + document.getElementById("seldbname").value +
		"&mintvd=" + document.getElementById("mintvd").value +
		"&maxtvd=" + document.getElementById("maxtvd").value +
		"&minvs=" + document.getElementById("minvs").value +
		"&maxvs=" + document.getElementById("maxvs").value +
		"&yscale=" + document.getElementById("inputb").value;
	}
	if(ptype=="3D") {
		phpcall+="&xaxis=<?echo $xaxis3d?>&zaxis=<?echo $zaxis3d?>&ox=<?echo $originh3d?>&oy=<?echo $originv3d?>";
		newwindow=window.open(phpcall, "_blank", "width=1300,height=750,left=10,top=0,status=0,scrollbars=yes");
		if (window.focus) {newwindow.focus()}
		window.close();
	} else {
//		alert(phpcall);
		window.location.href=phpcall;
		// progcall="progress.php?phpcall='" + phpcall + "'";
		// newwindow=window.open(progcall, "_self");
		// newwindow=window.opener.window.open(progcall, "_self");
		// newwindow=window.open(progcall, "_blank", "width=250,height=50,left=10,top=0,status=0,scrollbars=no");
	}
	return false;
}
</script>
</head>
<body style='text-align:center'>
<table class='tabcontainer'>
<form name='splotconfigd' id='splotconfigd' method='post'>
<input id='seldbname' type='hidden' name='seldbname' value='<?echo $seldbname?>'>
<input id='program' type='hidden' name='program' value='<?echo $program?>'>
<input id='filename' type='hidden' name='filename' value='<?echo $filename?>'>
<input id='phpcall' type='hidden' name='phpcall' value=''>
<tr>
<th style='padding:8px 20px;border-bottom:1px solid black'>
	<b>Plot Select</b><br />
	<select id='ptype' style='font-size: 10pt;' name='ptype' onchange="OnSubmit()">
	<option value='LAT' <?if($ptype=="LAT") echo "selected='selected'"?>>Lateral Plot</option>
	<option value='VS' <?if($ptype=="VS") echo "selected='selected'"?>>Vertical Section Plot</option>
	<option value='CL' <?if($ptype=="CL") echo "selected='selected'"?>>Horizontal Plot</option>
	<option value='3D' <?if($ptype=="3D") echo "selected='selected'"?>>3D Plot</option>
	</select>
</th>
<th style='padding:8px 20px;border-bottom:1px solid black'>
	<b>Range Select</b><br />
	<select id='mtype' <?php
if($ptype != "LAT" && $ptype != "VS" && $ptype!='3D')
	echo "disabled='true' " ?> style='font-size: 10pt;' name='mtype' onchange="OnSubmit()">
	<option value='INC' <?if($mtype=="INC") echo "selected='selected'"?>>Inclination cutoff</option>
	<option value='TVD' <?if($mtype=="TVD") echo "selected='selected'"?>>TVD/VS Range</option>
	</select>
</th>
</tr>
<tr>
<?if($mtype=="TVD"){?>
	<td align='right'>
		Min TVD:
		<input type='text' size='6' name='mintvd' id='mintvd' value='<?echo $mintvd?>' onchange="OnSubmit()">
	</td>
	<td align='right'>
		Min VS:
		<input type='text' size='6' name='minvs' id='minvs' value='<?echo $minvs?>' onchange="OnSubmit()">
	</td>
	</tr>
	<tr>
	<td align='right'>
		Max TVD:
		<input type='text' size='6' name='maxtvd' id='maxtvd' value='<?echo $maxtvd?>' onchange="OnSubmit()">
	</td>
	<td align='right'>
		Max VS:
		<input type='text' size='6' name='maxvs' id='maxvs' value='<?echo $maxvs?>' onchange="OnSubmit()">
	</td>
	</tr>

	<tr>
	<td align='right'>
		Data Scalings:
		<input type='text' size='2' name='inputb' id='inputb' value='<?if($ptype!="3D"){echo $yscale;}else{echo $zoom3d;}?>' onchange="OnSubmit()">
		<?if($ptype!="LAT") echo "%";?>
	</td>
<?}else{?>
	<?if($ptype!="3D" && $ptype!="POL" && $ptype!="CL"){?>
	<td align='right'>
		Data Scaling:
		<input type='text' size='2' name='inputb' id='inputb' value='<?if($ptype!="3D"){echo $yscale;}else{echo $zoom3d;}?>' onchange="OnSubmit()">
		<?if($ptype!="LAT") echo "%";?>
	</td>
	<?}?>
	
	<?if($ptype!="CL"){?>
		<td align='right'>
			<?
				if($ptype=="LAT" || $ptype=="3D")	echo "Starting Inclination";
				else	echo "Ending Inclination";
			?>
			<input id='inputa' type='text' size='3' name='inputa' value='<?echo $inputa?>' onchange="OnSubmit()">
		</td>
	<?}?>
<?}?>
</tr>
</form>
<tr>
<?if($ptype!="3D") { ?>
<td colspan='1' align='center'> <input type='submit' value='Email Plot' onclick='return OnEmailReport()'> </td>
<td colspan='2' align='center'> <input type='submit' value='View Plot' onclick='return OnViewReport()'> </td>
<? } else { ?>
<td colspan='3' align='center'> <input type='submit' value='View Plot' onclick='return OnViewReport()'> </td>
<? } ?>
</tr>
<tr>
<td colspan='3'>
<div style='text-align:center'><input type='submit' value='Approve and Send to Floor' onclick='OnApproveReport()' /></div>
<div style='margin-top:12px;text-align:center;font-size:10px'>&#169; 2010-2011 Digital Oil Tools</div>
</td>
</tr>
</table>
</body>
</html>
