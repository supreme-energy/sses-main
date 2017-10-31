<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Digital Oil Tools

require_once 'sses_include.php';

// Function block

$timestart=microtime_float();
function microtime_float()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
function SetFlag($tn, $d) {
	global $seldbname;
	$db2=new dbio($seldbname);
	$db2->OpenDb();
	$db2->DoQuery("SELECT * FROM \"$tn\" ORDER BY abs($d-depth) LIMIT 1;");
	if ($db2->FetchRow()) {
		$depth=$db2->FetchField("depth");
		if(abs($depth-$d)<=1) {
			$id=$db2->FetchField("id");
			$hide=$db2->FetchField("hide");
			$db2->DoQuery("UPDATE \"$tn\" SET hide=0;");
			$db2->DoQuery("UPDATE \"$tn\" SET hide=1 WHERE id=$id;");
		}
	}
	$db2->CloseDb();
}
function SetAlign($tn, $depth, $fault) {
	global $seldbname;
	$db2=new dbio($seldbname);
	$db2->OpenDb();
	$db2->DoQuery("SELECT * FROM welllogs ORDER BY endmd ASC LIMIT 1;");
	if ($db2->FetchRow()) {
		$sf=$db2->FetchField("fault");
	}
	$db2->DoQuery("SELECT * FROM \"$tn\" WHERE hide=1 ORDER BY depth LIMIT 1;");
	if ($db2->FetchRow()) {
		$d=$db2->FetchField("depth");
		$f=sprintf("%.2f", $sf+($d-$depth));
		// write to first welllog dataset
		$db2->DoQuery("SELECT * FROM welllogs ORDER BY endmd ASC LIMIT 1;");
		if ($db2->FetchRow()) {
			$i=$db2->FetchField("id");
			$db2->DoQuery("UPDATE welllogs SET fault='$f' WHERE id=$i;");
		}
	}
	$db2->CloseDb();
	return $f;
}
function SetTrim($tn, $depth, $fault) {
	global $seldbname;
	$db2=new dbio($seldbname);
	$db2->OpenDb();
	$db2->DoQuery("SELECT * FROM \"$tn\" WHERE hide=1 ORDER BY depth LIMIT 1;");
	if ($db2->FetchRow()) {
		$mark=$db2->FetchField("depth");
		if($depth<$mark)
			$db2->DoQuery("DELETE FROM \"$tn\" WHERE depth<$depth OR depth>$mark;");
		else
			$db2->DoQuery("DELETE FROM \"$tn\" WHERE depth<$mark OR depth>$depth;");
		$db2->DoQuery("SELECT * FROM \"$tn\" ORDER BY md ASC LIMIT 1;");
		if ($db2->FetchRow()) {
			$depth=$db2->FetchField("depth");
			$tvd=$db2->FetchField("tvd");
			$vs=$db2->FetchField("vs");
			$md=$db2->FetchField("md");
			$db2->DoQuery("UPDATE welllogs SET startdepth=$depth WHERE tablename='$tn';");
			$db2->DoQuery("UPDATE welllogs SET starttvd=$tvd WHERE tablename='$tn';");
			$db2->DoQuery("UPDATE welllogs SET startvs=$vs WHERE tablename='$tn';");
			$db2->DoQuery("UPDATE welllogs SET startmd=$md WHERE tablename='$tn';");
		}
		$db2->DoQuery("SELECT * FROM \"$tn\" ORDER BY md DESC LIMIT 1;");
		if ($db2->FetchRow()) {
			$depth=$db2->FetchField("depth");
			$tvd=$db2->FetchField("tvd");
			$vs=$db2->FetchField("vs");
			$md=$db2->FetchField("md");
			$db2->DoQuery("UPDATE welllogs SET enddepth=$depth WHERE tablename='$tn';");
			$db2->DoQuery("UPDATE welllogs SET endtvd=$tvd WHERE tablename='$tn';");
			$db2->DoQuery("UPDATE welllogs SET endvs=$vs WHERE tablename='$tn';");
			$db2->DoQuery("UPDATE welllogs SET endmd=$md WHERE tablename='$tn';");
		}
	}
	$db2->CloseDb();
	return $f;
}

// main code loop
require_once("dbio.class.php");
$sgtastart=$_POST['sgtastart'];
$sgtaend=$_POST['sgtaend'];
$sgtacutoff=$_POST['sgtacutoff'];
$sgtacutin=$_POST['sgtacutin'];
$scrolltop=$_POST['scrolltop'];
$scrollleft=$_POST['scrollleft'];
if($tablename=="") $tablename=$_POST['tablename'];
$s=$_POST['editmode']; if($s!="") $editmode=$s;
$s=$_POST['depth']; if($s!="")	$depth=$s;
$setflag=$_POST['setflag'];
$ret="gva_tab4.php";
if("$seldbname"=="") $seldbname=$_GET['seldbname'];
if("$seldbname"=="") $seldbname=$_POST['seldbname'];
if("$seldbname"=="") include("dberror.php");

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
if($editmode=="search") {
	$db->DoQuery("SELECT tablename FROM welllogs WHERE startdepth<$depth AND enddepth>$depth;");
	if($db->FetchRow()) $tablename=$db->FetchField("tablename");
	else {
		$db->DoQuery("SELECT tablename FROM welllogs WHERE startdepth>$depth ORDER BY startmd DESC LIMIT 1;");
		if($db->FetchRow()) $tablename=$db->FetchField("tablename");
		else {
			$db->DoQuery("SELECT tablename FROM welllogs WHERE startdepth<$depth ORDER BY startmd DESC LIMIT 1;");
			if($db->FetchRow()) $tablename=$db->FetchField("tablename");
		}
	}
	$editmode="";
}	// perform edits requested
else if( ($editmode=="align"||$editmode=="trim") && $tablename!="") {
	$db->DoQuery("SELECT * FROM welllogs WHERE tablename='$tablename';");
	if ($db->FetchRow()) {
		$sectfault=$db->FetchField('fault');
		if($editmode=="align") {
			SetAlign($tablename, $depth, $sectfault);
			$editmode="redraw";
		}
		if($editmode=="trim") {
			SetTrim($tablename, $depth, $sectfault);
			$editmode="redraw";
		}
	}
}	// restore from db
else if($lasttablename!="" && $tablename=="") {
	$tablename=$lasttablename;
}
else if($tablename=="") {	// heck, just pick the last DS
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
	$gvastart=$startmd;
	if($editmode=='redraw') {
		exec ("./sses_gva -d $seldbname -s $gvastart");
		// exec ("./sses_gva -d $seldbname -s $gvastart --nosurveys");
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
	echo "no table selected";
	$tableid=-1;
	$dsseltop=$startdepth;
	$dsselbot=$enddepth;
}
$db->DoQuery("UPDATE appinfo SET tablename='$tablename';");
$db->DoQuery("UPDATE appinfo SET dataset='$tableid';");

// data scaling
if($scaleright=="") $scaleright=150;
else $scaleright=$scaleright;

// zoom button config
$maxzoom=500;
if($zoom<.1||$zoom=="") $zoom=.1;
if($zoom>$maxzoom)	$zoom=$maxzoom;
$zoomdec=$zoom-10;
$zoominc=$zoom+10;
if($zoom<=10){
	if($zoom<10) $zoominc=$zoom+1;
	$zoomdec=$zoom-1;
} else if($zoom<20 && $zoom+$zoomdec<10) {
	$zoomdec=$zoom-(10-$zoom);
}

// plot start and end depths
$zoomfactor=($zoom*.01);
$plotstart=(int)($startdepth-($zoom*10));
$plotend=(int)($enddepth+($zoom*10));

if($plotend>$controlend&&$controlend>0) $plotend=(int)$controlend;
// reverse if inc>90
if($plotend<$plotstart) {
	$p=$plotstart;
	$plotstart=$plotend;
	$plotend=$p;
}
$plotstart-=1.0;
$plotend+=1.0;
$plotrange=$plotend-$plotstart;
if($viewallds<=1) {
	$cutinMD=0;
	$cutoffMD=99999.0;
}
else {
	if($startmd<$sgtacutin) {
		// echo "(scroll to previous datasets)";
		$sgtastart=$plotstart;
		$sgtaend=$plotend;
		$cutoffMD=$sgtacutoff=$endmd+$viewallds;
		$cutinMD=$sgtacutin=$startmd;
	}
	else if($endmd>$sgtacutoff) {
		// echo "(scroll to next datasets)";
		$sgtastart=$plotstart;
		$sgtaend=$plotend;
		$cutinMD=$sgtacutin=$startmd-$viewallds;
		$cutoffMD=$sgtacutoff=$endmd;
	}
	else if($forcesel<1 && $sgtastart!="" && $sgtaend!="" && $sgtacutin!="" && $sgtacutoff!="") {
		// echo "(scroll within datasets)";
		$cutinMD=$sgtacutin;
		$cutoffMD=$sgtacutoff;
		$plotrange=$plotend-$plotstart;
	}
	else {
		// echo "(reset range)";
		$sgtacutin=$cutinMD=$startmd-$viewallds;
		$sgtacutoff=$cutoffMD=$endmd;
		$sgtastart=$plotstart;
		$sgtaend=$plotend;
	}
	if($sgtastart!="") $db->DoQuery("UPDATE appinfo SET sgtastart='$sgtastart';");
	if($sgtaend!="") $db->DoQuery("UPDATE appinfo SET sgtaend='$sgtaend';");
	if($sgtacutin!="") $db->DoQuery("UPDATE appinfo SET sgtacutin='$sgtacutin';");
	if($sgtacutoff!="") $db->DoQuery("UPDATE appinfo SET sgtacutoff='$sgtacutoff';");
}

// display setup
$pheight=590*4;
if($viewrotds>=1) $pwidth=574;
else $pwidth=680;

// find a view window to work in
$scrollpt=abs($pheight/$plotrange)*abs($depth-$plotstart);
$scrollpt-=295;
if($scrolltop=="") $scrolltop=$scrollpt;
if($scrollleft=="") $scrollleft=$scrollpt;

// generate editor image
$fn=sprintf("./tmp/%s_gva_tab4.png", $seldbname);
$logsw=""; if($uselogscale>0)	$logsw="-log";
if($viewallds>0) {
	exec ("./sses_gpd -d $seldbname \
		-w $pwidth -h $pheight -s $plotstart -e $plotend \
		-o $fn -cld -wld \
		-wlid $tableid \
		-ci $cutinMD \
		-co $cutoffMD \
		-r $scaleright $logsw");
}
else {
	exec ("./sses_gpd -d $seldbname \
		-w $pwidth -h $pheight -s $plotstart -e $plotend \
		-o $fn -cld \
		-T $tablename \
		-r $scaleright $logsw");
}
if($viewdspcnt>0) {
	$fn2=sprintf("./tmp/%s_gva_tab4.png", $seldbname);
	$r=$plotrange*0.25;
	$retstr=array(); $retval=0;
	exec("./sses_dsp -dip $dscache_dip -scale $dscache_scale -bias $dscache_bias -d $seldbname -o $fn2 -c $viewdspcnt -nd -h 590 -w $pwidth -r $scaleright -range $r",
	&$retstr, &$retval);
	// echo "<pre>\n"; foreach($retstr as $rs) { echo "$rs\n"; } echo "</pre>";
}

$editmode="";
$db->CloseDb();
$timestop=microtime_float();
$timelapse=$timestop-$timestart;
?>
<HTML>
<HEAD>
<link rel="stylesheet" type="text/css" href="gva_tab4.css" />
<title><?echo "$dbrealname";?>-SGTA Editor<?echo " ($seldbname)";?></title>
</HEAD>

<?if($viewrotds>=1) { ?>
	<BODY onload="document.getElementById('div1').scrollLeft=<?echo $scrollleft?>;">
<?}
else { ?>
	<BODY onload="document.getElementById('div1').scrollTop=<?echo $scrolltop?>;">
<?}?>

<?
$maintab=3;
include "apptabs.inc.php";
?>
<table class='tabcontainer'>
<tr>
<td class='header' style='width: 20px; padding: 0;'> </td>
<td class='header' style='text-align: left;'><?if($viewrotds==0) echo '0';else echo '.';?></td>
<td class='header'><?if($viewrotds==0) echo $scaleright;else echo '.';?></td>
<td class='header'></td>
</tr>

<tr>
<td style='width: 20px; font-size: 8pt; vertical-align: top; padding: 0;'>
<?if($viewrotds==1)echo $scaleright; else echo ' ';?>
</td>

<td colspan='2' class="container" align='left' style='background-color: white;'>
	<FORM name="pointform" method="post">
	<Div class='transbox'>
	<Div class='tableContainer' id='div1' STYLE="background: url(<?echo $fn2?>);">
	<table border="0" cellpadding="0" cellspacing="0">
	<tbody class="scrollContent">
		<td>
		<input type='hidden' name='ret' value='gva_tab4.php'>
		<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
		<input type='hidden' name='scrolltop' value='<?echo $scrolltop?>'>
		<input type='hidden' name='scrollleft' value='<?echo $scrollleft?>'>
		<input type='hidden' name='plotstart' value='<?echo $plotstart?>'>
		<input type='hidden' name='plotend' value='<?echo $plotend?>'>
		<input type='hidden' name='viewrotds' value='<?echo $viewrotds?>'>
		<input type='hidden' name='depth' value=''>
		<input type='hidden' name='val' value=''>
		<input type='hidden' name='setflag' value=''>
		<input type='hidden' name='tablename' value='<?echo $tablename?>'>
		<input type='hidden' name='editmode' value='<?echo $editmode?>'>
		<input type='hidden' name='scaleright' value='<?echo $scaleright?>'>
		<input type='hidden' name='dsseltop' value='<?echo $dsseltop?>'>
		<input type='hidden' name='dsselbot' value='<?echo $dsselbot?>'>
		<? if($tableid>0 && $endmd>0) { ?>
		<IMG id='clickimage' SRC="<?echo $fn?>" onclick="getpoint(event, this)">
		<? } else { ?>
			<h1>No well log data found</h1>
		<? } ?>
		</td>
	</tbody>
	</table>
	</Div>
	</Div>
	</FORM> 
</td>

<td class="container" align='left'>
	<table class="settings">
	<tr><th colspan='5' class='header'>Data Sections - <?echo $wellname;?></th></tr>
	<tr>
	<td>
		<table style='font-size: 1em; width: 100%;'>
		<tr>
		<td>
			<FORM action='changeds.php' method='post'>
			<input type='hidden' name='ret' value='gva_tab4.php'>
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<input type='hidden' name='tablename' value='<?echo $tablename?>'>
			<input type='hidden' name='startmd' value='<?echo $startmd?>'>
			<input type='hidden' name='endmd' value='<?echo $endmd?>'>
			<input type='hidden' name='zoom' value='<?echo $zoom;?>'>
			<input type='hidden' name='dir' value='first'>
			<INPUT type="submit" value="First">
			</FORM>
		</td>
		<td>
			<FORM action='changeds.php' method='post'>
			<input type='hidden' name='ret' value='gva_tab4.php'>
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<input type='hidden' name='tablename' value='<?echo $tablename?>'>
			<input type='hidden' name='startmd' value='<?echo $startmd?>'>
			<input type='hidden' name='endmd' value='<?echo $endmd?>'>
			<input type='hidden' name='zoom' value='<?echo $zoom;?>'>
			<input type='hidden' name='dir' value='prev'>
			<input type='hidden' name='sgtastart' value='<?echo $sgtastart?>'>
			<input type='hidden' name='sgtaend' value='<?echo $sgtaend?>'>
			<input type='hidden' name='sgtacutin' value='<?echo $sgtacutin?>'>
			<input type='hidden' name='sgtacutoff' value='<?echo $sgtacutoff?>'>
			<input type='hidden' name='scrolltop' value='<?echo $scrolltop?>'>
			<input type='hidden' name='scrollleft' value='<?echo $scrollleft?>'>
			<INPUT type="submit" value="Prev">
			</FORM>
		</td>
		<td>
			<FORM action='changeds.php' method='post'>
			<input type='hidden' name='ret' value='gva_tab4.php'>
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<input type='hidden' name='tablename' value='<?echo $tablename?>'>
			<input type='hidden' name='startmd' value='<?echo $startmd?>'>
			<input type='hidden' name='endmd' value='<?echo $endmd?>'>
			<input type='hidden' name='zoom' value='<?echo $zoom;?>'>
			<input type='hidden' name='dir' value='next'>
			<input type='hidden' name='sgtastart' value='<?echo $sgtastart?>'>
			<input type='hidden' name='sgtaend' value='<?echo $sgtaend?>'>
			<input type='hidden' name='sgtacutin' value='<?echo $sgtacutin?>'>
			<input type='hidden' name='sgtacutoff' value='<?echo $sgtacutoff?>'>
			<input type='hidden' name='scrolltop' value='<?echo $scrolltop?>'>
			<input type='hidden' name='scrollleft' value='<?echo $scrollleft?>'>
			<INPUT type="submit" value="Next">
			</FORM>
		</td>
		<td>
			<FORM action='changeds.php' method='post'>
			<input type='hidden' name='ret' value='gva_tab4.php'>
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<input type='hidden' name='tablename' value='<?echo $tablename?>'>
			<input type='hidden' name='startmd' value='<?echo $startmd?>'>
			<input type='hidden' name='endmd' value='<?echo $endmd?>'>
			<input type='hidden' name='zoom' value='<?echo $zoom;?>'>
			<input type='hidden' name='dir' value='last'>
			<INPUT type="submit" value="Last">
			</FORM>
		</td>
		<td align='right'>
			<FORM method="post">
			<input type='hidden' name='ret' value='gva_tab4.php'>
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<input type='hidden' name='scrolltop' value='<?echo $scrolltop?>'>
			<input type='hidden' name='scrollleft' value='<?echo $scrollleft?>'>
			<input type='hidden' name='zoom' value='<?echo $zoom;?>'>
			<INPUT type="submit" value="Import" ONCLICK="OnLasImport(this.form)">
			</FORM>
		</td>
		</tr>
		<tr>
		<td colspan='5'>
			<table class='header' style='width: 100%;'>
			<tr>
			<th class='header' colspan=5> <b>File:</b><?echo " $realname ($tablename)";?> </th>
			</tr> <tr>
			<td class='header'><b>MD:</b></td>
			<td class='header'> <?printf("%9.2f", $startmd);?> </td>
			<td class='toheader'><b>to</b></td>
			<td class='header'> <?printf("%9.2f", $endmd);?> </td>
			</tr> <tr>
			<td class='header'><b>TVD:</b></td>
			<td class='header'> <?printf("%9.2f", $starttvd);?> </td>
			<td class='toheader'><b>to</b></td>
			<td class='header'> <?printf("%9.2f", $endtvd);?> </td>
			</tr> <tr>
			<td class='header'><b>VS:</b></td>
			<td class='header'> <?printf("%9.2f", $startvs);?> </td>
			<td class='toheader'><b>to</b></td>
			<td class='header'> <?printf("%9.2f", $endvs);?> </td>
			</tr> <tr>
			<td class='header'><b>TOT/BOT:</b></td>
			<td class='header'> <?printf("%9.2f", $secttot);?> </td>
			<td class='toheader'><b>to</b></td>
			<td class='header'> <?printf("%9.2f", $sectbot);?> </td>
			</tr>
			</table>
			<FORM method='post'>
			<input type='hidden' name='ret' value='gva_tab4.php'>
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<input type='hidden' name='tablename' value='<?echo $tablename?>'>
			<input type='hidden' name='depth' value='<?echo $depth?>'>
			<input type='hidden' name='scrolltop' value='<?echo $scrolltop?>'>
			<input type='hidden' name='scrollleft' value='<?echo $scrollleft?>'>
			<input type='hidden' name='viewrotds' value='<?echo $viewrotds?>'>
			<input type='hidden' name='sgtastart' value='<?echo $plotstart?>'>
			<input type='hidden' name='sgtaend' value='<?echo $plotend?>'>
			<?if($viewallds<=1) $ci=$startmd-500; else $ci=$startmd-$viewallds;?>
			<input type='hidden' name='sgtacutin' value='<?echo $ci?>'>
			<input type='hidden' name='sgtacutoff' value='<?echo $endmd?>'>
			<input type="radio" name='viewallds' \
				value='<?if($viewallds<=1) echo "500"; else echo $viewallds;?>' \
				<?if($viewallds>1) echo " checked='true';"?> onclick="OnViewDS(this.form)">View previous 

			<input type='text' size='7' name='viewallds' \
				value='<?if($viewallds<=1) echo "500"; else echo $viewallds;?>' \
				<?if($viewallds<=1) echo "readonly='true'";?> onchange="OnViewDS(this.form)"> MD
			<br>

			<input type="radio" name='viewallds' value='1' <?if($viewallds==1) echo " checked='true';"?> \
			onclick="OnViewDS(this.form)">View All Datasets<br>

			<input type="radio" name='viewallds' value='0' <?if($viewallds==0) echo " checked='true';"?> \
			onclick="OnViewDS(this.form)">View Only Selected

			</FORM>

			<FORM method='post'>
			<input type='hidden' name='ret' value='gva_tab4.php'>
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<input type='hidden' name='tablename' value='<?echo $tablename?>'>
			<input type='hidden' name='scrolltop' value='<?echo $scrolltop;?>'>
			<input type='hidden' name='scrollleft' value='<?echo $scrollleft;?>'>
			<input type='hidden' name='viewrotds' value='<?echo $viewrotds?>'>
			<input type='hidden' name='viewallds' value='<?echo $viewallds?>'>
			<input type="checkbox" name='viewrot' <?if($viewrotds>=1)echo " checked='true' ";?> value="<?echo $viewrotds;?>" onclick="OnRotateDS(this.form)">Rotate Dataset

			<br>
			<br>
			Show shadow of last 
			<input type='text' size='3' name='viewdspcnt' value='<?echo $viewdspcnt?>' onchange="OnViewDS(this.form)">
			modeled datasets

			</FORM>
		</td>
		</tr>
		</table>
	</td>
	</tr>
	</table>
<br style='font-size: 2pt;'>
	<table class="settings">
	<tr> <th colspan='3' class='header'>Data Modeling</th> </tr>
	<tr>
	<td colspan='5'>
		<table class='header' style='width: 100%;'>
		<tr>
		<FORM method='post'>
		<input type='hidden' name='ret' value='gva_tab4.php'>
		<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
		<input type='hidden' name='tablename' value='<?echo $tablename?>'>
		<input type='hidden' name='scrolltop' value='<?echo $scrolltop?>'>
		<input type='hidden' name='scrollleft' value='<?echo $scrollleft?>'>
		<td class='header'>
			Fault
		</td> <td class='header'>
			<input type="text" size="4" name="sectfault" value="<?echo $sectfault?>" onchange='setdscfg(this.form)'>
		</td> <td class='header' style='text-align: left;'>
			<input type=button value="+" onClick="faultupdown(this.form, 1)">
			<input type=button value="-" onClick="faultupdown(this.form, -1)">
		</td>
		</tr>
		<tr>
		<td class='header'>
			Dip
		</td> <td class='header'>
			<input type="text" size="3" name="sectdip" value="<?echo $sectdip?>" onchange='setdscfg(this.form)'>
		</td> <td class='header' style='text-align: left;'>
			<input type=button value="+" onClick="dipupdown(this.form, 1)">
			<input type=button value="-" onClick="dipupdown(this.form, -1)">
			<br>
		</td>
		</tr>
		<tr>
		<td class='header'>
			Bias
		</td> <td class='header'>
			<input type='text' size='3' name='bias' value="<?printf('%.0f', $bias);?>" onchange='setdscfg(this.form)'>
		</td> <td class='header' style='text-align: left;'>
			<input type=button value="Left" onClick="biasupdown(this.form, -10)">
			<input type=button value="Right" onClick="biasupdown(this.form, 10)">
		</td>
		</tr>
		<tr>
		<td class='header'>
			Scale
		</td> <td class='header'>
			<input type='text' size='3' name='factor' value="<?printf('%.2f', $factor);?>" onchange='setdscfg(this.form)'>
		</td> <td class='header' style='text-align: left;'>
			<input type=button value="+" onClick="scaleupdown(this.form, 0.1)">
			<input type=button value="-" onClick="scaleupdown(this.form, -.1)">
			<br>
		</td>
		</FORM>
		</tr>
		</table>
	</td>
	</tr>
	<tr>
	<td>
		<table style='font-size: 1em;'>
		<tr>
		<td>
			<input type="submit" id='btntrim' value="Trim Section" onclick="setTrimMode()">
		</td>
		<td>
			<input type="submit" id="btnalign" value="Align Section" onclick="setAlignMode()">
		</td>
		<td>
			<FORM name='deleteds' method='post'>
			<input type='hidden' name='ret' value='gva_tab4.php'>
			<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
			<INPUT type='hidden' name='tn' value='<?echo $tablename?>'>
			<INPUT type='hidden' name='depth' value='<?echo $startmd?>'>
			<INPUT type='hidden' name='editmode' value='search'>
			<INPUT type='hidden' name='scrolltop' value='<?echo $scrolltop?>'>
			<input type='hidden' name='scrollleft' value='<?echo $scrollleft?>'>
			<input type='hidden' name='sgtastart' value='<?echo $sgtastart?>'>
			<input type='hidden' name='sgtaend' value='<?echo $sgtaend?>'>
			<input type='hidden' name='sgtacutin' value='<?echo $sgtacutin?>'>
			<input type='hidden' name='sgtacutoff' value='<?echo $sgtacutoff?>'>
			<INPUT type="submit" value="DELETE DS" onclick="OnDeleteDS()">
			</FORM>
		</td>
		</tr>
		</table>
	</td>
	</tr>
	</table>
<br style='font-size: 2pt;'>
	<table class="settings">
	<tr>
	<th colspan='3' class='header'>View Scaling</th>
	</tr>

	<tr>
	<FORM action='setplotcfg.php' method='post'>
	<input type='hidden' name='ret' value='gva_tab4.php'>
	<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
	<input type='hidden' name='tablename' value='<?echo $tablename?>'>
	<input type='hidden' name='scrolltop' value='<?echo $scrolltop?>'>
	<input type='hidden' name='scrollleft' value='<?echo $scrollleft?>'>
	<input type='hidden' name='sgtastart' value='<?echo $sgtastart?>'>
	<input type='hidden' name='sgtaend' value='<?echo $sgtaend?>'>
	<input type='hidden' name='sgtacutin' value='<?echo $sgtacutin?>'>
	<input type='hidden' name='sgtacutoff' value='<?echo $sgtacutoff?>'>
	<input type='hidden' name='uselogscale' value='<?echo $uselogscale?>'>
	<td colspan='3' class='container'>
		Plot bias <input type='text' size='3' name='plotbias' value="<?printf('%.0f', $plotbias);?>" onchange='OnSetPlotCfg(this.form);'>
		<input type=button value="Left" onClick="plotbiasupdown(this.form, -10)">
		<input type=button value="Right" onClick="plotbiasupdown(this.form, 10)">
	</td>
	</tr>

	<tr>
	<td colspan='2' class="container" align='left'>
		Data max scale <input type='text' size='3' name='scaleright' value='<?echo $scaleright;?>' onchange='OnSetPlotCfg(this.form);'>
	</td>
	<td class="container" align='left'>
		<input type='checkbox' <?if($uselogscale!=0) echo " checked='true' ";?> id='lscb' name='lscb' onclick='OnLogScale(this.form);'>
		Logarithmic scale
	</td>
	</FORM>
	</tr>

	<tr>
	<th colspan='3' class='header'><br>Depth Scale (.1-<?echo $maxzoom?>)</th>
	</tr>
	<tr>
	<FORM method='post'>
	<input type='hidden' name='ret' value='gva_tab4.php'>
	<input type='hidden' name='scrolltop' value='<?echo $scrolltop?>'>
	<input type='hidden' name='scrollleft' value='<?echo $scrollleft?>'>
	<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
	<input type='hidden' name='sgtastart' value='<?echo $sgtastart?>'>
	<input type='hidden' name='sgtaend' value='<?echo $sgtaend?>'>
	<input type='hidden' name='sgtacutin' value='<?echo $sgtacutin?>'>
	<input type='hidden' name='sgtacutoff' value='<?echo $sgtacutoff?>'>
	<input type='hidden' name='zoom' value='<?echo $zoom?>'>
	<td class='container' align='center'>
		<input type='text' id='zoomtext' name='zoomtext' size='3' value='<?echo $zoom;?>' onchange="setzoom(this.form)">
	</td>
	<td class='container' align='center'>
		<input type="submit" value="Zoom In" <?if($zoom<=.5)echo " disabled='true' ";?> onmouseup="setzoomto(this.form, <?echo $zoomdec;?>)">
	</td>
	<td class='container' align='left'>
		<input type="submit" value="Zoom Out" <?if($zoom>=$maxzoom)echo " disabled='true' ";?> onmouseup="setzoomto(this.form, <?echo $zoominc;?>)">
	</td>
	</FORM>
	</tr>

	<tr><td> <br style='font-size: 2pt;'> </td></tr>

	<tr>
	<td colspan='2' class='container'>
		<FORM name='directinput' method='post' action='gva_tab4.php'>
		<input type='hidden' name='ret' value='gva_tab4.php'>
		<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
		<input type='hidden' name='editmode' value=''>
		<input type='hidden' name='setflag' value='1'>
		<input type='hidden' name='scrolltop' value='<?echo $scrolltop?>'>
		<input type='hidden' name='scrollleft' value='<?echo $scrollleft?>'>
		<input type='hidden' name='dsseltop' value='<?echo $dsseltop?>'>
		<input type='hidden' name='dsselbot' value='<?echo $dsselbot?>'>
		<input type='hidden' name='sgtastart' value='<?echo $sgtastart?>'>
		<input type='hidden' name='sgtaend' value='<?echo $sgtaend?>'>
		<input type='hidden' name='sgtacutin' value='<?echo $sgtacutin?>'>
		<input type='hidden' name='sgtacutoff' value='<?echo $sgtacutoff?>'>
		Last clicked depth:
		<input size='7' type='text' name='depth' id='dbgdepth' value="<?printf("%.2f", $depth);?>" onchange="directInput(this.form)">
		</FORM>
	</td>
	<td class='container'>
		<input type=button name=choice onClick="window.open('outputpicker.php?seldbname=<?echo $seldbname?>&title=View%20Snapshot&program=welllogpdf.php&filename=/tmp/<?echo $seldbname;?>.snapshot.pdf&plotstart=<?echo $plotstart;?>&plotend=<?echo $plotend;?>&wlid=<?echo $tableid;?>','popuppage','width=200,height=220,left=500');" value="Snapshot">
	</td>
	</tr>

	<tr>
	<td colspan='2' class='container' style='vertical-align: bottom;'>
		Process time: <?printf("%.3f",$timelapse);?> seconds
	</td>
	<td class='container'>
		<FORM action='viewtables.php' target='_blank' method='post'>
		<input type='hidden' name='ret' value='gva_tab4.php'>
		<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
		<input type="submit" value="View Tables" onclick="viewform(this.form)">
		</FORM>

	</td>
	</tr>
	</table>
</td>
<?if($viewdspcnt>0) {?>
<td class="container" align='left'>
	<table class="settings" style='width: 180;'>
	<tr><th colspan='3' class='header'>Shadowed Sections Modeling</th></tr>
	<FORM action='setdscache.php' method='post'>
	<input type="hidden" name="seldbname" value="<?echo "$seldbname";?>">
	<input type='hidden' name='ret' value='gva_tab4.php'>
	<input type="hidden" name="startmd" value="<?echo "$gvastart";?>">
	<input type="hidden" name="endmd" value="<?echo "$gvaend";?>">
	<input type='hidden' name='tablename' value='<?echo $tablename?>'>
	<input type='hidden' name='scrolltop' value='<?echo $scrolltop?>'>
	<input type='hidden' name='scrollleft' value='<?echo $scrollleft?>'>
	<input type='hidden' name='sgtastart' value='<?echo $sgtastart?>'>
	<input type='hidden' name='sgtaend' value='<?echo $sgtaend?>'>
	<input type='hidden' name='sgtacutin' value='<?echo $sgtacutin?>'>
	<input type='hidden' name='sgtacutoff' value='<?echo $sgtacutoff?>'>
	<tr>
	<td class='header'>
		Dip
	</td><td class='header'>
		<input type="text" size="3" name="dscache_dip" value="<?echo $dscache_dip?>" onchange='setdscache(this.form)'>
	</td><td class='header' style='text-align: left;'>
		<input type=button value="+" onClick="setdscache(this.form, 'dip', 1)">
		<input type=button value="-" onClick="setdscache(this.form, 'dip', -1)">
	</td>
	</tr> <tr>
	<td class='header'>
		Bias
	</td><td class='header'>
		<input type='text' size='3' name='dscache_bias' value="<?printf('%.0f', $dscache_bias);?>" onchange='setdscache(this.form)'>
	</td><td class='header' style='text-align: left;'>
		<input type=button value="<" onClick="setdscache(this.form, 'bias', -10)">
		<input type=button value=">" onClick="setdscache(this.form, 'bias', 10)">
	</td>
	</tr> <tr>
	<td class='header'>
		Scale
	</td><td class='header'>
		<input type='text' size='3' name='dscache_scale' value="<?printf('%.2f', $dscache_scale);?>" onchange='setdscache(this.form)'>
	</td><td class='header' style='text-align: left;'>
		<input type=button value="+" onClick="setdscache(this.form, 'scale', 0.1)">
		<input type=button value="-" onClick="setdscache(this.form, 'scale', -0.1)">
	</td>
	</tr>
	</FORM>
	</table>
</td>
<?}?>
</tr>
</table>
<?include("waitdlg.html");?>
<div id="load" style="display:none;">Submitting... Please wait</div>
</BODY>
<SCRIPT language="javascript">
<!--
var ray={
ajax:function(st) { this.show('load'); },
show:function(el) { this.getID(el).style.display=''; },
getID:function(el) { return document.getElementById(el); }
}
function OnLasImport(rowform)
{
	rowform.scrolltop.value=document.getElementById("div1").scrollTop;
	rowform.scrollleft.value=document.getElementById("div1").scrollLeft;
	t = 'welllogfilesel.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function OnRotateDS(rowform) {
	if(rowform.viewrotds.value==0) {
		rowform.scrollleft.value=document.getElementById("div1").scrollTop;
		rowform.scrolltop.value=document.getElementById("div1").scrollLeft;
		rowform.viewrotds.value="1";
	}
	else {
		rowform.scrolltop.value=document.getElementById("div1").scrollLeft;
		rowform.scrollleft.value=document.getElementById("div1").scrollTop;
		rowform.viewrotds.value="0";
	}
	t = 'setview.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function OnViewDS(rowform) {
	rowform.scrollleft.value=document.getElementById("div1").scrollLeft;
	rowform.scrolltop.value=document.getElementById("div1").scrollTop;
	t = 'setview.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function OnDeleteDS()
{
	var r=confirm("Delete this data set?");
	if (r==true)
 	{
		t = 'welllogdel.php';
		t = encodeURI (t);
		document.deleteds.action = t;
		document.deleteds.submit();
		return ray.ajax();
 	}
}
function plotbiasupdown (rowform, val) {
	rowform.plotbias.value=parseFloat(rowform.plotbias.value)+parseFloat(val);
	rowform.scrolltop.value=document.getElementById("div1").scrollTop;
	rowform.scrollleft.value=document.getElementById("div1").scrollLeft;
	OnSetPlotCfg(rowform);
}
function OnSetPlotCfg(rowform)
{
	rowform.scrolltop.value=document.getElementById("div1").scrollTop;
	rowform.scrollleft.value=document.getElementById("div1").scrollLeft;
	t = 'setplotcfg.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function OnLogScale(rowform)
{
	rowform.scrolltop.value=document.getElementById("div1").scrollTop;
	rowform.scrollleft.value=document.getElementById("div1").scrollLeft;
	var ischecked = document.getElementById("lscb").checked;
	if(ischecked==true) rowform.uselogscale.value='1';
	else rowform.uselogscale.value='0';
	t = 'setplotcfg.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function setzoomto (rowform, val) {
	rowform.scrolltop.value=document.getElementById("div1").scrollTop;
	rowform.scrollleft.value=document.getElementById("div1").scrollLeft;
	rowform.zoom.value=val;
	t = 'setzoom.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function setzoom (rowform) {
	rowform.scrolltop.value=document.getElementById("div1").scrollTop;
	rowform.scrollleft.value=document.getElementById("div1").scrollLeft;
	rowform.zoom.value=rowform.zoomtext.value;
	t = 'setzoom.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}

function getpoint(event,el){
  var top = 0, left = 0; 
  if (!event) { event = window.event; } 
  var myTarget = event.currentTarget; 
  if (!myTarget) { 
   myTarget = event.srcElement; 
  } 
  else if (myTarget == "undefined") { 
   myTarget = event.srcElement; 
  } 
  while(myTarget!= document.body) { 
     top += myTarget.offsetTop; 
     left += myTarget.offsetLeft; 
     myTarget = myTarget.offsetParent; 
  } 

	pos_x = (event.offsetX?(event.offsetX):event.pageX);
	pos_y = (event.offsetY?(event.offsetY):event.pageY);
	pos_x -= left;
	pos_y -= top;

	var plotstart=document.pointform.plotstart.value;
	var plotend=document.pointform.plotend.value;
	var scaleright=document.pointform.scaleright.value;
	var pheight=document.getElementById("clickimage").height;
	var pwidth=document.getElementById("clickimage").width;

	pos_y+=document.getElementById("div1").scrollTop;
	pos_x+=document.getElementById("div1").scrollLeft;
	// document.getElementById("dbgdepth").value=pos_y;

	var rot=document.pointform.viewrotds.value;
	var depth, value;
	if(rot>=1) {
		depth=(pos_x*(plotend-plotstart)/pwidth)+parseFloat(plotstart);
		value=pos_y*(scaleright/pheight);
	}
	else {
		depth=(pos_y*(plotend-plotstart)/pheight)+parseFloat(plotstart);
		value=pos_x*(scaleright/pwidth);
	}

	document.pointform.scrolltop.value=document.getElementById("div1").scrollTop;
	document.pointform.scrollleft.value=document.getElementById("div1").scrollLeft;
	document.pointform.depth.value=depth;
	document.pointform.val.value=value;
	document.pointform.setflag.value=1;

	// alert("depth: " + depth);
	document.getElementById("dbgdepth").value=depth.toFixed(2);
	// document.getElementById("dbgdepth").value=pos_x;

	if( document.pointform.editmode.value=="align" ) {
		document.pointform.val.value="";
		t = 'gva_tab4.php';
		t = encodeURI (t);
		document.pointform.action = t;
		document.pointform.submit();
		return ray.ajax();
	}
	else if( document.pointform.editmode.value=="trim" ) {
		document.pointform.val.value="";
		t = 'gva_tab4.php';
		t = encodeURI (t);
		document.pointform.action = t;
		document.pointform.submit();
		return ray.ajax();
	}
	else {
		var topsel=document.pointform.dsseltop.value;
		var botsel=document.pointform.dsselbot.value;
		if(topsel>botsel) {
			var t=topsel;
			topsel=botsel;
			botsel=t;
		}
		if( depth>=topsel && depth<=botsel ) {
			t = 'gva_tab4.php';
			t = encodeURI (t);
			document.pointform.action = t;
			document.pointform.submit();
			return true;
		}
		else {
			document.pointform.editmode.value="search";
			document.pointform.scrolltop.value="";
			document.pointform.scrollleft.value="";
			t = 'gva_tab4.php';
			t = encodeURI (t);
			document.pointform.action = t;
			document.pointform.submit();
			return true;
		}
	}
}
function directInput(rowform) {
	var topsel=document.directinput.dsseltop.value;
	var botsel=document.directinput.dsselbot.value;
	var depth=rowform.depth.value;
	if(topsel>botsel) {
		var t=topsel;
		topsel=botsel;
		botsel=t;
	}
	if( depth>=topsel && depth<=botsel ) {
		t = 'gva_tab4.php';
		t = encodeURI (t);
		document.directinput.action = t;
		document.directinput.submit();
		return true;
	}
	else {
		document.directinput.editmode.value="search";
		document.directinput.scrolltop.value="";
		document.directinput.scrollleft.value="";
		t = 'gva_tab4.php';
		t = encodeURI (t);
		document.directinput.action = t;
		document.directinput.submit();
		return true;
	}
}
function setTrimMode() {
	if(document.pointform.editmode.value!="trim") {
		document.pointform.editmode.value="trim";
		document.getElementById("btntrim").value="Click To Turn Trim Off";
		alert("Trim mode ON\nClick the depth to trim the data section to...");
	}
	else {
		document.pointform.editmode.value="";
		document.getElementById("btntrim").value="Trim Section";
	}
}
function setAlignMode() {
	if(document.pointform.editmode.value!="align") {
		document.pointform.editmode.value="align";
		document.getElementById("btnalign").value="Click To Turn Alignment Off";
		alert("Align mode is ON\nClick on a depth to align the selected point to...\n");
	}
	else {
		document.pointform.editmode.value="";
		document.getElementById("btnalign").value="Align Welllog";
	}
}
function setdscache (rowform, param, val) {
	if(param=="dip") {
		var t = parseFloat(rowform.dscache_dip.value);
		t+=val;
		if(t<-89.9) t=-89.9;
		if(t>89.9)	t=89.9;
		rowform.dscache_dip.value=t;
	}
	if(param=="bias") {
		var t = parseFloat(rowform.dscache_bias.value);
		t+=val;
		rowform.dscache_bias.value=t;
	}
	if(param=="scale") {
		var t = parseFloat(rowform.dscache_scale.value);
		t+=val;
		rowform.dscache_scale.value=t;
	}

	rowform.scrolltop.value=document.getElementById("div1").scrollTop;
	rowform.scrollleft.value=document.getElementById("div1").scrollLeft;
	t = 'setdscache.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function dipupdown (rowform, val) {
	var t = parseFloat(rowform.sectdip.value);
	t+=val;
	if(t<-89.9) t=-89.9;
	if(t>89.9)	t=89.9;
	rowform.sectdip.value=t;
	setdscfg(rowform);
}
function faultupdown (rowform, val) {
	var t = parseFloat(rowform.sectfault.value);
	t+=val;
	rowform.sectfault.value=t;
	setdscfg(rowform);
}
function biasupdown (rowform, val) {
	rowform.bias.value=parseFloat(rowform.bias.value)+parseFloat(val);
	setdscfg(rowform);
}
function scaleupdown (rowform, val) {
	var t = parseFloat(rowform.factor.value);
	t+=val;
	if(t<0) t=0;
	if(t>100)	t=100;
	rowform.factor.value=t;
	setdscfg(rowform);
}
function setdscfg (rowform) {
	rowform.scrolltop.value=document.getElementById("div1").scrollTop;
	rowform.scrollleft.value=document.getElementById("div1").scrollLeft;
	t = 'setdscfg.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function viewform(myform)
{
if (! window.focus)return true;
window.open('', 'View Tables', 'height=600,width=900,scrollbars=yes');
myform.target='View Tables';
return true;
}
//-->
</SCRIPT>
</HTML>
