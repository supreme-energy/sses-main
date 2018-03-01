<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Digital Oil Tools

error_reporting(E_ALL);
ini_set('display_errors','1');

require_once 'sses_include.php';
require_once 'dbio.class.php';
require_once 'tabs.php';

$seldbname=$_GET['seldbname'];
$infoid = isset($_GET['infoid']) ? $_GET['infoid'] : '';
if($seldbname=="") $seldbname=$_POST['seldbname'];
if($seldbname=="") include("dberror.php");
$db=new dbio("sgta_index");
$db->OpenDb();

$db->DoQuery("SELECT * FROM dbindex ORDER BY id;");
while($db->FetchRow()) {
	$dbn=$db->FetchField("dbname");
	$dbreal=$db->FetchField("realname");
	if($seldbname==$dbn) $dbrealname=$dbreal;
} 
$db->CloseDb();
$db=new dbio("$seldbname");
$db->OpenDb();
include("readappinfo.inc.php");
include("readwellinfo.inc.php");
$labels=array();
$infoids=array();
$colors=array();
$tot=array();
$bot=array();
$bg_colors=array();
$bg_percents=array();
$pat_colors=array();
$pat_nums=array();
$show_lines=array();
exec ("./sses_af -d $seldbname");
$db->DoQuery('select * from addforms order by thickness');
while($db->FetchRow()) {
	$infoids[]=$db->FetchField("id");
	$labels[]=$db->FetchField("label");
	$colors[]=$db->FetchField("color");
	$tot[]=$db->FetchField("tot");
	$bot[]=$db->FetchField("bot");
	$bg_colors[]=$db->FetchField('bg_color');
	$bg_percents[]=$db->FetchField('bg_percent');
	$pat_colors[]=$db->FetchField('pat_color');
	$pat_nums[]=$db->FetchField('pat_num');
	$show_lines[]=$db->FetchField('show_line');
}
?>
<!doctype html>
<html>
<head>
<title><?echo "$dbrealname";?>-Additional Formations<?echo " ($seldbname)";?></title>
<link rel="stylesheet" type="text/css" href="gva_tab7.css" />
<link rel="stylesheet" type="text/css" href="waitdlg.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<script src="colpick.js" type="text/javascript"></script>
<link rel="stylesheet" href="colpick.css" type="text/css"/>
<style>
div[id^=slider] {
	margin-left:5px;
}
.ui-slider-horizontal {
	height: 8px;
}

.ui-slider .ui-slider-handle {
	height: 15px;
	width: 5px;
	padding-left:5px;
}
.the_colpick {
	width:60px;
	height:20px;
	border:1px solid #dddddd;
	border-right:30px solid #dddddd;
	line-height:20px;
	padding-left:3px;
}
select[id^=pat_num_menu] option
{
	width:60px;
	height:20px;
	padding-left:50px;
	background-repeat:no-repeat;
	background-position:left center;
}
div[id^=show_line]
{
	padding-top:3px;
	color:#000055;
	font-weight:bolder;
}
div[id^=show_line]:hover
{
	cursor:pointer;
	color:#0000aa;
}
</style>
<script>
var do_change = false;

function brJsonRunData(json_data)
{
//  alert('data=' + JSON.stringify(json_data) + ' a=' + json_data.a + ' i=' + json_data.i);
//  return;

    // call remote PHP process called rundat.php and send data in JSON
	$.getJSON('rundat.php',json_data,function(data) {
		if(data.res == 'ERR') alert(data.msg);
	}).fail(function( jqxhr, textStatus, error ) {
		alert('Request Failed: ' + textStatus + ", " + error);
	});
}
$(document).ready(function() {
	$("[id^=slider]").slider({
		range: "max",
		min: 0,
		max: 1.0,
		step: 0.1,
		value: 0.2,
		slide: function( event, ui ) {
			$( "#the_" + this.id + "_amount" ).text( ui.value );
		},
		change: function(event,ui) {
			var mval = ui.value;
//          alert('id=' + this.id + ' and formid=' + $(this).data('formid'));
			if(do_change) {
				brJsonRunData({'sdb':'<?php
					echo $seldbname ?>','a':'setform','i':$(this).data('formid'),'c':'bg_percent','v':ui.value});
			}
		}
	});
<?php
for($i=0;$i<count($infoids);$i++) {
	echo "	$('#slider{$i}').slider('option','value',{$bg_percents[$i]});\n";
}
for($i=0;$i<count($infoids);$i++) {
	echo "	$('#the_slider{$i}_amount').text('{$bg_percents[$i]}');\n";
}
?>

	$("[id^=the_color_]").colpick({
		onSubmit:function(hsb,hex,rgb,el,bySetColor) {
			$(el).colpickHide();
			$(el).css('border-color','#'+hex);
			$(el).val(hex);
			brJsonRunData({'sdb':'<?php
				echo $seldbname ?>','a':'setform','i':$(el).data('formid'),'c':$(el).data('col'),'v':hex});
		}
	});

<?php
for($i=0;$i<count($infoids);$i++) {
	echo "	$('#the_color_line{$i}').css('border-color','#{$colors[$i]}');\n";
	echo "	$('#the_color_bg{$i}').css('border-color','#{$bg_colors[$i]}');\n";
	echo "	$('#the_color_pat{$i}').css('border-color','#{$pat_colors[$i]}');\n";
}
for($i=0;$i<count($infoids);$i++) {
	echo "	$('#the_color_line{$i}').colpickSetColor('{$colors[$i]}');\n";
	echo "	$('#the_color_bg{$i}').colpickSetColor('{$bg_colors[$i]}');\n";
	echo "	$('#the_color_pat{$i}').colpickSetColor('{$pat_colors[$i]}');\n";
}
?>

	$("[id^=pat_num_menu]").change(function() {
		brJsonRunData({'sdb':'<?php
			echo $seldbname ?>','a':'setform','i':$(this).data('formid'),'c':'pat_num','v':$(this).val()});
	});

	$("[id^=show_line]").click(function() {
		if($(this).data('show') == 'Yes')
		{
			brJsonRunData({'sdb':'<?php
				echo $seldbname ?>','a':'setform','i':$(this).data('formid'),'c':'show_line','v':'No'});
			$(this).data('show','No');
			$(this).text('Do Not Show Line');
		}
		else
		{
			brJsonRunData({'sdb':'<?php
				echo $seldbname ?>','a':'setform','i':$(this).data('formid'),'c':'show_line','v':'Yes'});
			$(this).data('show','Yes');
			$(this).text('Show Line');
		}
	});

	do_change = true;
});
</script>
</head>
<body>
<?php
$maintab=6;
$show_tabs = isset($_REQUEST['no_tabs']) ? false: true;
if($show_tabs){
	include "apptabs.inc.php";
}
include("waitdlg.html");
?>
<div class='tabcontainer'>
<div>
<?
$tabs = new tabs("AddForms");
$layer=array();
for($i=0;$i<count($infoids);$i++) {
	$layer[]=$i+1;
	$tabs->start("{$layer[$i]}: {$labels[$i]}");
	?>

	<table class='scroll_table'>
	<tr>
		<td style='font-size:13px'>
		<div style='padding:5px 0;float:left;text-align:left;width:45%'>
			<div style='width:60%;float:left'>
			<form style='padding:0 0; margin:0;' method='post'>
				<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
				<input type='hidden' name='infoid' value='<?echo $infoids[$i];?>'>
				Label: <input type='text' class='pretty' style='width:140px' name='label'
					value='<?echo $labels[$i]?>' onchange='doSubmit(this.form)'>
			</form>
			</div>
			<div style='width:40%;float:left'>
				Color # <input type='text' class='the_colpick' id='the_color_line<?php
					echo $i ?>' value='<?php echo $colors[$i] ?>' data-formid='<?php echo $infoids[$i] ?>' data-col='color' />
			</div>
			<div style='clear:both'></div>
		</div>
		<div style='padding:5px 0;float:left;text-align:right;width:55%'>
			<div style='width:30%;float:left'>Background (<span id='the_slider<?php echo $i ?>_amount'></span>):</div>
			<div style='width:25%;float:left;padding-top:4px'>
				<div id='slider<?php echo $i ?>' data-formid='<?php echo $infoids[$i] ?>'></div>
			</div>
			<div style='width:45%;float:left'>
				Color # <input type='text' class='the_colpick' id='the_color_bg<?php
					echo $i ?>' value='<?php echo $bg_colors[$i] ?>' data-formid='<?php echo $infoids[$i] ?>' data-col='bg_color'  />
			</div>
			<div style='clear:both'></div>
		</div>
		<div style='padding:5px 0;float:left;text-align:left;width:45%'>
			<div style='width:60%;float:left'>
			<form style='padding:0 0; margin:0;' method='post'>
				<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
				<input type='hidden' name='infoid' value='<?echo $infoids[$i];?>'>
				<input type='submit' value='Delete' onclick='doDel(this.form);'>
			</form>
			</div>
			<div style='width:40%;float:left;text-align:center'>
				<div id='show_line<?php echo $i ?>' data-formid='<?php echo $infoids[$i] ?>' data-show='<?php
				echo $show_lines[$i] ?>'><?php
				if($show_lines[$i] == 'Yes') echo "Show Line";
				else echo "Do Not Show Line";
				?></div>
			</div>
			<div style='clear:both'></div>
		</div>
		<div style='padding:5px 0;float:left;text-align:right;width:55%'>
			<div style='width:30%;float:left;padding-top:3px'>Pattern:</div>
			<div style='width:25%;float:left'>
				<div style='padding-left:5px'>
		      		<select id="pat_num_menu<?php echo $i ?>" data-formid='<?php echo $infoids[$i] ?>'>
<?php
for($j=0;$j<8;$j++)
{
	echo "                    <option value='{$j}' ";
	if(intval($pat_nums[$i]) == $j) echo "selected ";
	echo "style='background-image:url(pattern0{$j}.jpg)'>Pattern {$j}</option>\n";
}
?>
					</select>
				</div>
			</div>
			<div style='width:45%;float:left'>
				Color # <input type='text' class='the_colpick' id='the_color_pat<?php
					echo $i ?>' value='<?php echo $pat_colors[$i] ?>' data-formid='<?php echo $infoids[$i] ?>' data-col='pat_color'  />
			</div>
			<div style='clear:both'></div>
		</div>
		<div style='clear:both'></div>
		</td>
	</tr>
	<tr>
		<td>
			<table class='svys'>
				<tr>
				<th class='svysw'>MD</th>
				<th class='svysw'>TVD</th>
				<th class='svysw'>VS</th>
				<th class='svysw'>Thickness</th>
				<th class='svysw'>Fault</th>
				<th class='svysw'><?php echo $labels[$i] ?></th>
				<th class='svysw'>Pos</th>
				</tr>
			</table>

			<div style='height:480px;width:640px;overflow:auto'>
				<table class='svys'>
				<?
				$db->DoQuery("SELECT * FROM addformsdata WHERE infoid={$infoids[$i]} order by md");
				while($db->FetchRow()) {
					$id=$db->FetchField("id");
					$md=$db->FetchField("md");
					$tvd=$db->FetchField("tvd");
					$vs=$db->FetchField("vs");
					$tot=$db->FetchField("tot");
					$bot=$db->FetchField("bot");
					$thickness=$db->FetchField("thickness");
					$fault=$db->FetchField("fault");
				?>
					<tr>
					<td class='svys' style='background-color:rgb(220,235,200);'><?printf("%.2f",$md);?></td>
					<td class='svys' style='background-color:rgb(220,235,200);'><?printf("%.2f",$tvd);?></td>
					<td class='svys' style='background-color:rgb(220,235,200);'><?printf("%.2f",$vs);?></td>
					<td class='svyin' style='background-color:rgb(220,235,200);'>
						<form style='padding:0 0; margin:0;' method='post' action='addformsdatad.php'>
						<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
						<input type='hidden' name='infoid' value='<?echo $infoids[$i];?>'>
						<input type='hidden' name='md' value='<?echo $md?>'>
						<input type='hidden' name='id' value='<?echo $id?>'>
						<input type='text' size='3' name='thickness' value='<?echo $thickness?>' onchange='setThick(this.form)'>
						</form>
					</td>
					<td class='svys'><?printf("%.2f",$fault);?></td>
					<td class='svys'><?printf("%.2f",$tot);?></td>
					<td class='svys'><?printf("%.2f",$tot-$tvd);?></td>
					</tr>
				<?}?>
				</table>
			</div>
		</td>
		</tr>
	</table>
	<?
	$tabs->end();
}
for($i=0;$i<count($infoids);$i++)
	if($infoid==$infoids[$i]) $tabs->active="{$layer[$i]}: {$labels[$i]}";
$tabs->run();
$db->CloseDb();
?>
</div>
<div style='clear:both'></div>
<div style='font-size:16px;margin-top:10px'>
	<form method="post" action='addformsadd.php' enctype="multipart/form-data">
	<input type='hidden' name='ret' value='gva_tab7.php'>
	<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
	<input type="submit" style='padding:0 20px' value="Add Formation">
	</form>
	<button id='b1' style="<?= $sgta_show_forms==0?'':'display:none'?>"
		onclick="this.style.display='none';document.getElementById('b2').style.display='inline';
			xmlhttp=new XMLHttpRequest();
			xmlhttp.open('GET','/sses/setshowforms.php?seldbname=<?=$seldbname?>&wts=0&vts=1',true);
			xmlhttp.send();"
	>Show on SGTA Main</button>
	<button id='b2' 
	onclick="this.style.display='none';document.getElementById('b1').style.display='inline';
			xmlhttp=new XMLHttpRequest();
			xmlhttp.open('GET','/sses/setshowforms.php?seldbname=<?=$seldbname?>&wts=0&vts=0',true);
			xmlhttp.send();"
	style="<?= $sgta_show_forms==1?'':'display:none'?>">Hide on SGTA Main</button>
	<button id='b3'
	onclick="this.style.display='none';document.getElementById('b4').style.display='inline';
			xmlhttp=new XMLHttpRequest();
			xmlhttp.open('GET','/sses/setshowforms.php?seldbname=<?=$seldbname?>&wts=1&vts=1',true);
			xmlhttp.send();"
	 style="<?= $wb_show_forms==0?'':'display:none'?>">Show on Wellbore Side</button>
	<button id='b4'
	onclick="this.style.display='none';document.getElementById('b3').style.display='inline';
			xmlhttp=new XMLHttpRequest();
			xmlhttp.open('GET','/sses/setshowforms.php?seldbname=<?=$seldbname?>&wts=1&vts=0',true);
			xmlhttp.send();"
	 style="<?= $wb_show_forms==1?'':'display:none'?>">Hide on Wellbore Side</button> 
</div>
<div>
	<center><small>&#169; 2010-2011 Digital Oil Tools</small></center>
</div>
</div>
<script language="javascript" type="text/javascript" src="waitdlg.js"></script>
<script language="javascript">
function doDel(rowform)
{
	var r=confirm("Delete this formation.\nARE YOU SURE?");
	if (r==true)
  	{
		t = 'addformsdel.php';
		t = encodeURI (t); // encode URL
		rowform.action = t;
		rowform.submit(); // submit form using javascript
		return ray.ajax();
  	}
	return true;
}
function setThick(rowform)
{
	t = 'addformsdatad.php';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return ray.ajax();
}
function doSubmit(rowform)
{
	t = 'addformschange.php';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return ray.ajax();
}
function openColorChoice(rowform) {
	var phpcall="addformscolor.php?seldbname="+rowform.seldbname.value+"&infoid="+rowform.infoid.value+"&color="+rowform.colorraw.value;
    newwindow=window.open(phpcall,'ColorChoice', 'height=300,width=300,scrollbars=no');
    if (window.focus) {newwindow.focus()}
    // return false;
	return ray.ajax();
}
</script>
</body>
</html>
