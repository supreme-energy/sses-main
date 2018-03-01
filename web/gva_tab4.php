<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Digital Oil Tools

//error_reporting(E_ALL);
//ini_set('display_errors', '1');
error_reporting(0);
require_once 'sses_include.php';

// main code loop
require_once("dbio.class.php");

if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '');
if($seldbname == '') $seldbname = (isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '');
if($seldbname == '') include("dberror.php");

// fetch database from index
$db=new dbio("sgta_index");
$db->OpenDb();
$db->DoQuery("SELECT * FROM dbindex ORDER BY id;");
while($db->FetchRow()) {
	$dbids=$db->FetchField("id");
	$dbn=$db->FetchField("dbname");
	$dbreal=$db->FetchField("realname");
	if($seldbname==$dbn) $dbrealname=$dbreal;
} 

$db=new dbio($seldbname);
$db->OpenDb();
include "readwellinfo.inc.php";
include "readappinfo.inc.php";


// search for closest matching dataset
if($lasttablename!="" && $tablename=="") {
	$tablename=$lasttablename;
}

if($tablename=="") {	// heck, just pick the last DS
	$db->DoQuery("SELECT tablename FROM welllogs ORDER BY endmd DESC LIMIT 1;");
	if($db->FetchRow()) $tablename=$db->FetchField("tablename");
}

// fetch the select dataset info
$db->DoQuery("SELECT * FROM welllogs WHERE tablename='$tablename';");
if($db->FetchNumRows()<=0) {
	$db->DoQuery("SELECT * FROM welllogs ORDER BY startmd DESC LIMIT 1;");
}
if($db->FetchRow()) {
	$tablename=$db->FetchField("tablename");
	$tableid=$db->FetchField("id");
	$realname=$db->FetchField('realname');
	$startmd=$db->FetchField('startmd');
	$endmd=$db->FetchField('endmd');
	$starttvd=$db->FetchField('starttvd');
	$endtvd=$db->FetchField('endtvd');
	$startvs=$db->FetchField('startvs');
	$endvs=$db->FetchField('endvs');
	$startdepth=$db->FetchField('startdepth');
	$enddepth=$db->FetchField('enddepth');
	$sectfault=$db->FetchField('fault');
	$sectdip=$db->FetchField('dip');
	$secttot=$db->FetchField('tot');
	$sectbot=$db->FetchField('bot');
	$bias=$db->FetchField('scalebias');
	$factor=$db->FetchField('scalefactor');
	$hide=$db->FetchField('hide');
	if($setflag!="" && $depth!="") // mark the data point w/in the dataset
		SetFlag($tablename, $depth);
	if($depth=="") $depth=$startdepth+(($enddepth-$startdepth)/2.0);
	$dsseltop=$startdepth;
	$dsselbot=$enddepth;
	// re-calculate dataset models
	$gvastart=0;
	if(!isset($dir)) $dir = '';
	if($dir!="first") $gvastart=$startmd;
	if($editmode=='redraw' || $dir=="first") {
		exec ("./sses_gva -d $seldbname -s $gvastart");
		$db->DoQuery("SELECT * FROM welllogs WHERE tablename='$tablename';");
		if($db->FetchRow()) {
			$startdepth=$db->FetchField('startdepth');
			$enddepth=$db->FetchField('enddepth');
			$secttot=$db->FetchField('tot');
			$sectbot=$db->FetchField('bot');
			$startdepth=(int)($startdepth);
			$enddepth=(int)($enddepth);
		}
	}
}
else {
	// echo "no table selected";
	$tableid=-1;
	$dsseltop=$startdepth;
	$dsselbot=$enddepth;
}

$db->DoQuery("UPDATE appinfo SET tablename='$tablename';");
$db->DoQuery("UPDATE appinfo SET dataset='$tableid';");

?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="gva_tab4.css" />
<link rel="stylesheet" type="text/css" href="waitdlg.css" />
<script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
<title><?php echo "$dbrealname"; ?>-SGTA Editor<?php echo " ($seldbname)"; ?></title>
<script language='javascript' type='text/javascript' src='waitdlg.js'></script>
<script language='javascript'>

</script>
</head>
<?php
$show_tabs = isset($_REQUEST['no_tabs']) ? false: true;
echo "<body>";
$maintab=3;
if($show_tabs){
	include 'apptabs.inc.php';
}
include 'waitdlg.html';

?>

<table class='tabcontainer'>
<tr><td colspan='3'>
	<button onclick='OnLasImport()' style='width:100%'><i class="fas fa-upload"></i>&nbsp;IMPORT DATA &nbsp;<i class="fas fa-upload"></i></button>
</td></tr>
<tr>
<td colspan='3' class="container" align='left' style='background-color:white;width: 800px;'> 
	<?php include 'graph_partials/gva_tab4/sgta_main.php' ?> 
</td>

<td class="container" align='left' onmouseover="">
	<div style='padding-top:5px;text-align:center;width:80px;' >Navigation</div>
	<div style='padding-top:5px;text-align:center;width:80px;'><hr></div>
	<div style='padding-top:5px;'><div style='text-align:center;width:80px;font-weight:bold;'>Go To MD</div>
		<div style='text-align:center;width:80px;'><input type='text' size='4' id='gotodatasetat' name='startmd' value='' onchange='goToDataSetAt()'></div>
	</div>
	<div style='padding-top:5px;text-align:center;width:80px;'><button title='First' onclick='firstDataSet()'><i class="fa fa-angle-double-up"></i></button></div>
	<div style='padding-top:5px;text-align:center;width:80px;'><button title='Previous' onclick='prevDataSet()'><i class="fa fa-angle-up"></i></button></div>
	<div style='padding-top:5px;text-align:center;width:80px;'><button title='Next' onclick='nextDataSet()'><i class="fa fa-angle-down"></i></button></div>
	<div style='padding-top:5px;text-align:center;width:80px;'><button title='Last' onclick='lastDataSet()'><i class="fa fa-angle-double-down"></i></button></div>
	<div style='padding-top:5px;text-align:center;width:80px;'>Display</div>
	<div><hr></div>
	<div style='padding-top:5px;text-align:center;width:80px;'><button id='viewallbutton' <?php echo ($viewallds == 1 ? "style='background-color:green'" : '')?> title='Zoom' onclick='viewAll(true)'>All</i></div>
	<div style='padding-top:5px;padding-bottom:3px;text-align:center;width:80px;'><button  id='viewselectedbutton' <?php echo ($viewallds == 0 ? "style='background-color:green'" : '')?> title='Zoom' onclick='viewOnlySelected()'>Current</i></div>
	<div style='padding-top:2px;padding-bottom:5px;text-align:center;width:80px;border: 1px solid black;'><button id='viewlastbutton' <?php echo ($viewallds > 1 ? "style='background-color:green'" : '')?> title='Zoom' onclick='viewPreviousXMD()'>Last MD</button></i> <input style='margin-top:5px;text-align:center;width:50px;' type='text' size='7' id='viewallprevval' name='viewallds' value='<?php
				if($viewallds<=1) echo "500"; else echo $viewallds; ?>' <?php
				if($viewallds<=1) echo "readonly='true'"; ?> onchange="viewPreviousXMD()"></div>
	<div style='padding-top:5px;text-align:center;width:80px;'>Modeling</div>
	<div style='padding-top:5px;text-align:center;width:80px;'><hr></div>
	<div style='padding-top:5px;text-align:center;width:80px;position:relative;'><button id='shadowbutton' title='Shadow' onclick='showShadow()'><i class="fab fa-snapchat-ghost"></i></button>
		
	<table class="settings" style='position:absolute; left: 80px;top:-120px;width: 270;<?php echo $viewdspcnt> 0 ? '' : 'display:none' ?>;' id='dropshadow_container'>
	<tr>
		<td class='header' style='width:100'>
			# of Pieces
		</td>
		<td class='header'>
		<input type="text" size="3" id='shadow_view_cnt' name="shadow_pieces" value="<?php echo $viewdspcnt ?>" onchange='showShadow(this.value)'>
		</td>
	</tr>
	<tr>
	<td class='header' style='width:100'>
		Fault (of First piece)
	</td><td class='header'>
		<input type="text" size="3" id='shadow_fault' name="shadow_fault" value="<?php echo $dscache_fault?>" onchange='shadowFault(this.value)'>
	</td><td class='header' style='text-align: left;'>
		<input type=button value="+" onClick="shadowFaultUpDown(1)">
		<input type=button  value="-" onClick="shadowFaultUpDown(-1)">
	</td>
	</tr>
	<tr>
	<td class='header'>
		Dip (all Pieces)
	</td><td class='header'>
		<input type="text" size="3" id='shadow_dip' name="dscache_dip" value="<?php echo $dscache_dip?>" onchange='shadowDip(this.value)'>
	</td><td class='header' style='text-align: left;'>
		<input type=button value="+" onClick="shadowDipUpDown(1)">
		<input type=button  value="-" onClick="shadowDipUpDown(-1)">
	</td>
	</tr>
	<tr>
	<td class='header'>
		Bias (all Pieces)
	</td><td class='header'>
		<input type='text' size='3' id='shadow_bias' name='dscache_bias' value="<?echo $dscache_bias ?>" onchange='shadowBias(this.value)'>
	</td><td class='header' style='text-align: left;'>
		<input type=button value="<" onClick="shadowBiasUpDown(-10)">
		<input type=button  value=">" onClick="shadowBiasUpDown(10)">
	</td>
	</tr> <tr>
	<td class='header'>
		Scale (all Pieces)
	</td><td class='header'>
		<input type='text'  size='3' id ='shadow_scale' name='shadow_scale' value="<?echo $dscache_scale ?>" onchange='shadowScale(this.value)'>
	</td><td class='header' style='text-align: left;'>
		<input type=button value="+" onClick="shadowScaleUpDown(.1)">
		<input type=button value="-" onClick="shadowScaleUpDown(-.1)">
	</td>
	</tr>
	<tr>
		<td colspan='4'>
			<button onclick="applyDipFromShadow()">Apply Dip</button>
			<button onclick="applyFaultFromShadow()">Apply Fault</button>
		</td>
	</tr>
		<tr>
		<td colspan='4'>
			<button onclick="applyDipAndFaultFromShadow()">Apply Both</button>
			<button onclick="resetDipAndFaultForShadow()">Reset</button>
		</td>
	</tr>
	</table>
	</div>
	<div style='padding-top:5px;text-align:center;width:80px;'><button style='background-color:green' id='zoombutton'   title='Zoom' onclick='scrollMode="zoom"; mouseWheelZoomOn(true);setSelectedButton(this.id);'><i class="fas fa-search-plus"></i></div>
	<div style='padding-top:5px;text-align:center;width:80px; position:relative;'><button id='faultbutton'  title='Fault' onclick='scrollMode="fault";mouseWheelZoomOn(false);setSelectedButton(this.id);' style='width:28px;height:24px;position:relative;'><i class="fas fa-square-full" style='position:absolute;left:2px;top:4px;' ></i><i class="fas fa-square-full" style='position:absolute;left:8px;top:1px;color:red;'></i></button>
		<table style='position:absolute; top:-5px;left:60px;display:none;' id='faultbutton_view'>
		<tr>
		<td class='header'>
			<input type="text" size="4" name="sectfault" id='sectfault' value="<?php echo $sectfault?>" onchange='updateFault(this.value, index_of_selected);sendWellLogFieldUpdate("fault", this.value, "wld_"+data[index_of_selected].tableid)'>
		</td>
		<td class='header' style='text-align: left;'>
			<input type=button value="+" onClick="faultupdown(1,false)">
			<input type=button value="-" onClick="faultupdown(-1,false)">
		</td>
		</tr>
		<tr>
		</table>
	</div>
	<div style='padding-top:5px;text-align:center;width:80px;position:relative;'><button id='dipbutton'  title='Dip' onclick='scrollMode="dip";mouseWheelZoomOn(false);setSelectedButton(this.id);'><i style='transform: skew(-15deg, -15deg);color:red;' class="fas fa-square-full"></i></button>
		<table style='position:absolute; top:-5px;left:60px;display:none;' id='dipbutton_view'>
		<tr>
		<td class='header'>
			<input id='sectdip_parent' type="text" size="4" name="sectdip" value="<?php echo $sectdip?>" onchange='updateDip(this.value, index_of_selected);sendWellLogFieldUpdate("dip", this.value, "wld_"+data[index_of_selected].tableid)'>
		</td>
		<td class='header' style='text-align: left;'>
			<input type=button value="+" onClick="dipupdown( 1,false)">
			<input type=button value="-" onClick="dipupdown(-1,false)">
			<input type=button value='Auto Dip' 
			onClick="window.open('sgtamodeling_autodip.php?seldbname=<?php echo $seldbname?>','sgtaavgpopup','width=700,height=400,left=200,scrollbars=yes');">
			<br />
		</td>
		</tr>
		</table>
	</div>
	<div style='padding-top:5px;text-align:center;width:80px;position:relative;'><button id='currentbiasbutton'  title='Current Bias' onclick='scrollMode="cbias";mouseWheelZoomOn(false);setSelectedButton(this.id);'  style='width:28px;height:24px;position:relative;'><i class="fas fa-expand" style='color:red' style='position:absolute;left:2px;top:4px;'></i><i class="fas fa-search-plus" style='position:absolute;left:2px;top:4px;'></i></button>
		<table style='position:absolute; top:-5px;left:60px;display:none;' id='currentbiasbutton_view'>
		<tr>
		<td class='header'>
			<input type='text' size='4' name='bias' id='scalebias' value="<?php printf('%.0f', $bias); ?>" onchange='updatePlotBias(this.value);sendWellLogFieldUpdate("scalebias", this.value, "wld_"+data[index_of_selected].tableid)'>
		</td>
		<td class='header' style='text-align: left;'>
			<input type=button value="Left" onClick="biasupdown(-10)">
			<input type=button value="Right" onClick="biasupdown(10)">
		</td>
		</tr>
		</table>
	</div>
	<div style='padding-top:5px;text-align:center;width:80px;position:relative;'><button id='currentscalebutton'  title='Current Scale' onclick='scrollMode="cscale";mouseWheelZoomOn(false);setSelectedButton(this.id);' style='width:28px;height:24px;position:relative;'><i class="fas fa-arrows-alt" style='color:red' style='position:absolute;left:2px;top:4px;'></i><i class="fas fa-search-plus" style='position:absolute;left:2px;top:4px;'></i></button>
	<table style='position:absolute; top:-5px;left:60px;display:none;' id='currentscalebutton_view'>
	<tr>
		<td class='header'>
			<input type='text' size='4' name='factor' id='scalefactor' value="<?php printf('%.2f', $factor); ?>" onchange='updateScaleFactor(this);sendWellLogFieldUpdate("scalefactor", this.value, "wld_"+data[index_of_selected].tableid)'>
		</td> <td class='header' style='text-align: left;'>
			<input type=button value="+" onClick="factorupdown(0.1)">
			<input type=button value="-" onClick="factorupdown(-.1)">
			<br>
		</td>

		</tr>
	</table>
	</div>
	<div style='padding-top:5px;text-align:center;width:80px;position:relative;'><button id='plotbiasbutton' title='Plot Bias' onclick='scrollMode="pbias";mouseWheelZoomOn(false);setSelectedButton(this.id);'><i class="fas fa-expand"></i></button>
	<table style='position:absolute; top:-5px;left:60px;display:none;' id='plotbiasbutton_view'>
	<tr>
	<td class='container' align='right'>
		<input type='text' size='3' name='plotbias' id='plotbias' value="<?printf('%.0f', $plotbias); ?>" onchange='updateAllPlotBias(this.value);'>
	</td>
	<td class='container'>
		<input type=button value="Left" onClick="allBiasUpDown(-10)">
		<input type=button value="Right" onClick="allBiasUpDown(10)">
	</td>
	</tr>
	</table>
	</div>
	
	<div style='padding-top:5px;text-align:center;width:80px;'><button id='deletedatsetbutton'  title='Delete Data Set' onclick="deleteSelectedDs()"><i class="fas fa-trash-alt" style='color:red'></i></button></div>
	
	<div style='padding-top:20px;text-align:center;width:80px;'>Site Nav</div>
	<div style='padding-top:5px;text-align:center;width:80px;'><hr></div>
	<div style='padding-top:5px;text-align:center;width:80px;'>
		<a target="_blank" href="/sses/gva_tab3.php?seldbname=<?php echo $seldbname; ?>&no_tabs=true">Surveys</a>
	</div>
	
	<div style='padding-top:5px;text-align:center;width:80px;'>
		<a target="_blank" href="/sses/gva_tab5.php?seldbname=<?php echo $seldbname; ?>&no_tabs=true">Wellbore</a>
	</div>
	
	<div style='padding-top:5px;text-align:center;width:80px;'>
		<a target="_blank" href="/sses/gva_tab2.php?seldbname=<?php echo $seldbname; ?>&no_tabs=true">Welllog</a>
	</div>
	
	<div style='padding-top:5px;text-align:center;width:80px;'>
		<a target="_blank" href="/sses/gva_tab6.php?seldbname=<?php echo $seldbname; ?>&no_tabs=true">AD Config</a>
	</div>
	
	<div style='padding-top:5px;text-align:center;width:80px;'>
		<a target="_blank" href="/sses/gva_tab7.php?seldbname=<?php echo $seldbname; ?>&no_tabs=true">Marker Beds</a>
	</div>
	
	<div style='padding-top:5px;text-align:center;width:80px;'>
		<a target="_blank" href="/sses/gva_tab1.php?seldbname=<?php echo $seldbname; ?>&no_tabs=true">Well Config</a>
	</div>
	
	
	<div style='position:absolute; left: 490px;top:100px;'>
		<table class='header' style='width: 300px;'>
			<tr>
			<td class='header'> File:</td>
			<td colspan='4'><span id='realname_tablename'><?php echo " $realname "; ?><?php echo "($tablename)"; ?></span></td>
			</tr> <tr>
			<td class='header' style='width:30px'><b>MD:</b></td>
			<td class='header' id='md_start_disp'> <?php printf("%9.2f", $startmd); ?> </td>
			<td class='toheader'><b>to</b></td>
			<td class='header'  id='md_end_disp'> <?php printf("%9.2f", $endmd); ?> </td>
			</tr> <tr>
			<td class='header'  style='width:30px'><b>TVD:</b></td>
			<td class='header'  id='tvd_start_disp'> <?php printf("%9.2f", $starttvd); ?> </td>
			<td class='toheader'><b>to</b></td>
			<td class='header'  id='tvd_end_disp'> <?php printf("%9.2f", $endtvd); ?> </td>
			</tr> <tr>
			<td class='header'  style='width:30px'><b>VS:</b></td>
			<td class='header'  id='vs_start_disp'> <?php printf("%9.2f", $startvs); ?> </td>
			<td class='toheader'><b>to</b></td>
			<td class='header' id='vs_end_disp'> <?php printf("%9.2f", $endvs); ?> </td>
			</tr> <tr>
			<td class='header'  style='width:30px'><b>TCL</b></td>
			<td class='header'> <?php printf("%9.2f", $secttot); ?> </td>
			</tr>
		</table>
	</div>

</tr>
<tr>
<td colspan='3' onmouseover="window.onwheel = function(){return true;}">
	<center><small><small>
	&#169; 2010-2011 Digital Oil Tools
	</small></small></center>
</td>
</tr>
</table>
</body>
</html>
<?php
include_once('gva_tab5_funct.php');
?>
