<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Supreme Source Energy Services, Inc.
//	All rights reserved.
//	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.

//error_reporting(E_ALL);
//ini_set('display_errors', '1');

require_once 'sses_include.php';

$timestart=microtime_float();
$timelog[] = array(microtime(),'starting from the top');
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
	// exec ("./sses_gva -d $seldbname -s 0 --nosurveys");
	exec ("./sses_gva -d $seldbname");
	return $f;
}
function SetTrim($tn, $depth, $fault) {
	global $seldbname;
	$db2=new dbio($seldbname);
	$db3=new dbio($seldbname);
	$db3->OpenDb();
	$db2->OpenDb();
	$query = "select * from welllogs where tablename='$tn'";
	$db2->DoQuery($query);
	$db2->FetchRow();
	$sbias = $db2->FetchField('scalebias');
	$sfactor = $db2->FetchField('scalefactor');
	$curid = $db2->FetchField('id');
	
	$db2->DoQuery("SELECT * FROM \"$tn\" WHERE hide=1 ORDER BY depth LIMIT 1;");
	if ($db2->FetchRow()) {
		$sections=array();
		$mark=$db2->FetchField("depth");
		if($depth<$mark){
			//before section
			$query = "select * FROM $tn WHERE depth<$depth order by depth;";
			array_push($sections,$query);
			//mid section
			$query = "select * from $tn where depth >= $depth and depth <= $mark order by depth";
			array_push($sections,$query);
			//after section
			$query = "select * FROM $tn WHERE depth>$mark order by depth;";
			array_push($sections,$query);
			$final_tn_query = "select tablename from welllogs where startdepth >= $depth and enddepth <=$mark";
			
		} else {	
			//before section
			$query = "select * FROM $tn WHERE depth<$mark order by depth;";
			array_push($sections,$query);
			//mid section
			$query = "select * from $tn where depth >= $mark and depth <= $depth order by depth";
			array_push($sections,$query);
			//after section
			$query = "select * FROM $tn WHERE depth>$depth order by depth;";
			array_push($sections,$query);
			$final_tn_query = "select tablename from welllogs where startdepth >= $mark and enddepth <=$depth";
			
		}
		$section_cnt=0;
		foreach($sections as $section_query){
			$db3->DoQuery($section_query);
			$data_to_rinsert=array();
			$cnt = 0;
			while($db3->FetchRow()){
					$set = array('md'=>$db3->FetchField('md'),'tvd'=>$db3->FetchField('tvd'),'vs'=>$db3->FetchField('vs'),'value'=>$db3->FetchField('value'),'depth'=>$db3->FetchField('depth'));
					if($cnt==0){
						$startmd=$set['md'];
						$starttvd=$set['tvd'];
						$startvs = $set['vs'];
						$startdepth=$set['depth'];
					}
					
						$endmd=$set['md'];
						$endtvd=$set['tvd'];
						$endvs = $set['vs'];
						$enddepth=$set['depth'];
					
					array_push($data_to_rinsert,$set);
					$cnt++;
			}
			if($cnt>0){
				$query = "INSERT INTO welllogs (tablename,startmd,endmd,startvs,endvs,starttvd,endtvd,startdepth,enddepth,dip,fault,scalebias,scalefactor,filter,scaleleft,scaleright) " .
						"VALUES ('wld_xxxxxx','$startmd','$endmd','$startvs','$endvs','$starttvd','$endtvd','$startdepth','$enddepth',0,0,'$sbias','$sfactor',0,0,0);";
				$result = $db3->DoQuery($query);
				if($result==FALSE) die("<pre>Database error attempting to insert a new welllog information block\n</pre>");
				$query = "select id,tablename from welllogs where tablename='wld_xxxxxx'";
				$db3->DoQuery($query);
				if($db3->FetchRow()){
					
					$id=$db3->FetchField("id");
					$tablename="wld_$id";
			    	if($section_cnt==1){
			    		$selected_sectiontn=$tablename;
			    	}
			    	$real="$tn trim section $startmd - $endmd";
			    	$query="CREATE TABLE \"$tablename\" (id serial not null primary key, md float, tvd float, vs float, value float, hide smallint not null default 0, depth float not null default 0);";
			    	$result=$db3->DoQuery($query);  
					if($result!=FALSE){
						$query="UPDATE welllogs SET tablename='$tablename',realname='$real' WHERE id='$id';";
						$result = $db3->DoQuery($query);
					}
				} else die("<pre>Id for new table entry not found!\n</pre>");
				 if($result==FALSE) {
					if($id!="")$db->DoQuery("DELETE FROM welllogs WHERE id='$id';");
					$db3->DoQuery("DROP TABLE IF EXISTS\"$tablename\";");
					die("<pre>Database error attempting to create table: $tablename\n</pre>");
				}
				foreach($data_to_rinsert as $data){
					$imd = $data['md'];
					$ival = $data['value'];
					$itvd = $data['tvd'];
					$ivs = $data['vs'];
					$idepth=$data['depth'];
					$query="INSERT INTO \"$tablename\" (md,value,tvd,vs,depth) VALUES ($imd,$ival,$itvd,$ivs,$idepth);";
					$db3->DoQuery($query);
				}
				
			}
			
			$section_cnt++;
		}
		$query= "delete from welllogs where tablename='$tn'";
		$db2->DoQuery($query);
		$query= "drop table \"$tn\"";
		$db2->DoQuery($query);
		$db2->DoQuery($final_tn_query);
		$db2->FetchRow();
		$fntn = $db2->FetchField('tablename');
		$db2->DoQuery("UPDATE appinfo set tablename='$fntn';");
	}
	$db2->CloseDb();
	return $f;
}

// main code loop
require_once("dbio.class.php");
$sgtastart = (isset($_POST['sgtastart']) ? $_POST['sgtastart'] : '');
$sgtaend = (isset($_POST['sgtaend']) ? $_POST['sgtaend'] : '');
$sgtacutoff = (isset($_POST['sgtacutoff']) ? $_POST['sgtacutoff'] : '');
$sgtacutin = (isset($_POST['sgtacutin']) ? $_POST['sgtacutin'] : '');
$scrolltop = (isset($_POST['scrolltop']) ? $_POST['scrolltop'] : '');
$scrollleft = (isset($_POST['scrollleft']) ? $_POST['scrollleft'] : '');
if(!isset($tablename)) $tablename = (isset($_POST['tablename']) ? $_POST['tablename'] : '');
$s = (isset($_POST['editmode']) ? $_POST['editmode'] : ''); if($s != '') $editmode = $s;
$s = (isset($_POST['depth']) ? $_POST['depth'] : ''); if($s != '') $depth = $s;
$setflag = (isset($_POST['setflag']) ? $_POST['setflag'] : '');
$ret="gva_tab4.php";
if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_GET['seldbname']) ? $_GET['seldbname'] : '');
if($seldbname == '') $seldbname = (isset($_POST['seldbname']) ? $_POST['seldbname'] : '');
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

$timelog[] = array(microtime(),'before search');
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
$timelog[] = array(microtime(),'after updating appinfo');

// data scaling
if($scaleright=="") $scaleright=150;
else $scaleright=$scaleright;

// zoom and button config
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
$zoomfactor=($zoom*.01);

// handle freeze state
// if($dscache_freeze==0) {
	$plotstart=(int)($startdepth-($zoom*10));
	$plotend=(int)($enddepth+($zoom*10));
	if(!isset($controlend)) $controlend = 0;
	if($plotend > $controlend and $controlend > 0) $plotend=(int)$controlend;
	if($plotend<$plotstart) {
		$p=$plotstart;
		$plotstart=$plotend;
		$plotend=$p;
	}
	$plotstart-=1.0;
	$plotend+=1.0;
	$dscache_plotstart=$plotstart;
	$dscache_plotend=$plotend;
// } else {
	// if(strlen($plotstart)<=0)	$plotstart=$dscache_plotstart;
	// if(strlen($plotend)<=0)	$plotend=$dscache_plotend;
// }

$plotrange=$plotend-$plotstart;
if($viewallds<=1) {
	$cutinMD=0;
	$cutoffMD=99999.0;
}
else {
	if($startmd<$sgtacutin) {
//		echo "(scroll to previous datasets)";
		$sgtastart=$plotstart;
		$sgtaend=$plotend;
		$cutoffMD=$sgtacutoff=$endmd+$viewallds;
		$cutinMD=$sgtacutin=$startmd;
	}
	else if($endmd>$sgtacutoff) {
//		echo "(scroll to next datasets)";
		$sgtastart=$plotstart;
		$sgtaend=$plotend;
		$cutinMD=$sgtacutin=$startmd-$viewallds;
		$cutoffMD=$sgtacutoff=$endmd;
	}
	else if($forcesel<1 && $sgtastart!="" && $sgtaend!="" && $sgtacutin!="" && $sgtacutoff!="") {
//		echo "(scroll within datasets)";
		$cutinMD=$sgtacutin;
		$cutoffMD=$sgtacutoff;
	}
	else {
//		echo "(reset range)";
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
// MARK: I believe the window height (610) is related to the 590 below.
// MARK: The full image height will be 4 times the height of the window
$pheight=590*4;	// 2360
if($viewrotds>=1) $pwidth=594;
else $pwidth=700;

// MARK: Basically if not frozen then restore the scroll postition
// MARK: Otherwise, scroll to center
// find a view window to work in


if($viewdspcnt>0){
	$depthmod = $dscache_fault/2;
} else {
	$depthmod=0;
}
$scrollpt=abs($pheight/$plotrange)*abs($depth-$depthmod-$plotstart);
$scrollpt-=295;
if($scrolltop=="") $scrolltop=$scrollpt;
if($scrollleft=="") $scrollleft=$scrollpt;


if($dscache_freeze==0 && $endmd!="") $db->DoQuery("UPDATE appinfo SET dscache_md='$endmd';");

// MARK: Here we generate the full scrollable editor image generate editor image

$fn=sprintf("./tmp/%s_gva_tab4.png", $seldbname);
$fnsnap=sprintf("./tmp/%s_gva_tab4snap.png", $seldbname);
$snaph=900;
$snapw=600;
$logsw=""; if($uselogscale>0)	$logsw="-log";
if($sgta_show_forms){
	$addformsstr = '-aforms';
} else {
	$addformsstr='';
}

$timelog[] = array(microtime(),'before generating editor image');

if($viewallds>0) {

	// $retstr=array(); $retval=0;
	$com = "./sses_gpd -d $seldbname " .
			"-s $plotstart -e $plotend " .
			"-cld -wld -wlid $tableid " .
			"-ci $cutinMD -co $cutoffMD " .
			"-avg $dataavg -r $scaleright $logsw $addformsstr";
	exec ($com." -w $pwidth -h $pheight -o $fn");
	echo ($com." -w $pwidth -h $pheight -o $fn");
	exec ($com." -w $snapw -h $snaph -o $fnsnap");
	//echo ($com." -w $snapw -h $snaph -o $fnsnap");
	//&$retstr, &$retval);
	//echo "<pre>\n"; foreach($retstr as $rs) { echo "$rs\n"; } echo "</pre>";
}
else {
	$com="./sses_gpd -d $seldbname -ad " .
			"-s $plotstart -e $plotend " .
			"-cld -T $tablename -avg $dataavg " .
			"-r $scaleright $logsw $addformsstr";
	//echo $com." -w $pwidth -h $pheight -o $fn\n<br>";
	exec ($com." -w $pwidth -h $pheight -o $fn");
	//echo $com." -w $snapw -h $snaph -o $fnsnap\n<br>";
	exec ($com." -w $snapw -h $snaph -o $fnsnap");
}

$timelog[] = array(microtime(),'after generating editor image');

// MARK: The "Shadow", as it has been dubbed, is nothing more than a background image
// MARK: the same size as the window which contains the scrollable/editor image
// MARK: Here we calculate the amount of data beyond the window extents
if($viewrotds>=1) $dspoffset=($plotend-$plotstart)/$pheight*($scrollleft-885)*-1;
else $dspoffset=($plotend-$plotstart)/$pheight*($scrolltop-885)*-1;
$dbfault=0.0;
$faultmod = isset($_POST['faultmod'])&&$_POST['faultmod']!=''?$_POST['faultmod']:0;
$timelog[] = array(microtime(),'fault log is ' . $faultmod . ' dspoffset is ' . $dspoffset);
if($viewdspcnt>0) {
	$fn2=sprintf("./tmp/%s_gva_tab41.png", $seldbname);

	$cmd.="./sses_dsp";
	// MARK: Set the background image size the same as the window
	// MARK: The full editor image scrolls over this background
	if($dscache_freeze<=0) {
		$plotstart-=$dspoffset;
		$plotend-=$dspoffset;
		$r=$plotrange*0.25;
		$h=590;
		$d=$endmd;
		$diff=($plotend-$plotstart)/4.0*1.5;
		$p1=$plotstart+$diff;
		$p2=$plotend-$diff;
		$cmd.=" -pstart $p1";
		$cmd.=" -pend $p2";
	} else {
	// MARK: Set the background image the same size as the editor image
	// MARK: Now the background will scroll with the editor image
	// MARK: giving the effect of being "Frozen" or locked in place
		$r=$plotrange;
		$h=$pheight;
		$d=$dscache_md;
		$cmd.=" -pstart $plotstart";
		$cmd.=" -pend $plotend";
	}

	$cmd.=" -e $d";
	$cmd.=" -dip $dscache_dip";
	$cmd.=" -fault $dscache_fault";
	$cmd.=" -scale $dscache_scale";
	$cmd.=" -bias $dscache_bias";
	$cmd.=" -wlid $tableid";
	$cmd.=" -d $seldbname";
	$cmd.=" -o $fn2";
	$cmd.=" -c $viewdspcnt";
	$cmd.=" -w $pwidth";
	$cmd.=" -r $scaleright";
	$cmd.=" -h $h";
	$cmd.=" -range $r";
	$cmd.=" -nd";
	exec($cmd);
//	echo "<p>cmd=$cmd</p>";
	$timelog[] = $cmd;
	$db->DoQuery("SELECT fault FROM welllogs WHERE endmd<=$d ORDER BY endmd DESC LIMIT $viewdspcnt;");
	while($db->FetchRow()){$dbfault=$db->FetchField('fault');} // fetch the first fault value
}

$editmode="";
$random=md5(uniqid(rand(),1));
$timestop=microtime_float();
$timelapse=$timestop-$timestart;
$timelog[] = array(microtime(),'right before html, timelapse = ' . $timelapse);
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="gva_tab4.css" />
<link rel="stylesheet" type="text/css" href="waitdlg.css" />
<title><?php echo "$dbrealname"; ?>-SGTA Editor<?php echo " ($seldbname)"; ?></title>
<script language='javascript' type='text/javascript' src='waitdlg.js'></script>
<script language='javascript'>
var scrllcnt=0;
var gdist=0;
function Init(viewrotds,stop,sleft,freeeze) {
	var freeze=parseInt(document.pointform.dscache_freeze.value);
	if(viewrotds>0) document.getElementById('div1').scrollLeft=sleft;
	else document.getElementById('div1').scrollTop=stop;
	//alert('viewrotds=' + viewrotds + ' stop=' + stop + ' sleft=' + sleft + ' freeze=' + freeze);
	
	if(freeze<=0) {
		var scrolltop=document.getElementById("div1").scrollTop;
		var scrollleft=document.getElementById("div1").scrollLeft;
		var plotstart=document.pointform.plotstart.value;
		var plotend=document.pointform.plotend.value;
		var pheight=document.getElementById("clickimage").height;
		var dist=0.0;
		if(viewrotds>0) {
			dist=(plotend-plotstart)/pheight*(scrollleft-885);
			dist=dist+parseFloat(document.pointform.dspoffset.value);
			if(document.getElementById("dbgscrolltop")!=undefined)
				document.getElementById("dbgscrolltop").value=scrollleft.toFixed(0);
		} else {
			dist=(plotend-plotstart)/pheight*(scrolltop-885);
			dist=dist+parseFloat(document.pointform.dspoffset.value);
			if(document.getElementById("dbgscrolltop")!=undefined)
				document.getElementById("dbgscrolltop").value=scrolltop.toFixed(0);
		}
		gdist=dist	
		if(document.getElementById("dbgscrolldist")!=undefined)
			document.getElementById("dbgscrolldist").value=-dist.toFixed(2);
		showProposedFault();	
	}
}

function scrollAction(div){
	if(scrllcnt>0){
		console.log('scrolling');
		var viewrotds=parseInt(document.pointform.viewrotds.value);
		var freeze=parseInt(document.pointform.dscache_freeze.value);
		scrolltop  = div.scrollTop;
		scrollleft = div.scrollLeft;
		var plotstart=document.pointform.plotstart.value;
		var plotend=document.pointform.plotend.value;
		var pheight=document.getElementById("clickimage").height;
		var scrolltopin = document.getElementById('scrolltop_input');
		var scrollleftin = document.getElementById('scrollleft_input');
		//scrollleftin = scrollleft;
		//scrolltopin.value = scrolltop;
		if(freeze<=0){
			var dist=0.0;
			if(viewrotds>0) {
				dist=(plotend-plotstart)/pheight*(scrollleft-885);
				document.getElementById("dbgscrolltop").value=scrollleft.toFixed(0);
			} else {
				dist=(plotend-plotstart)/pheight*(scrolltop-885);
				document.getElementById("dbgscrolltop").value=scrolltop.toFixed(0);
			}		
			showProposedFault();
		} 
	}
	scrllcnt++;
}

function SetScroll(rowform)
{
	rowform.scrolltop.value=document.getElementById("div1").scrollTop;
	rowform.scrollleft.value=document.getElementById("div1").scrollLeft;
	//alert('scrollTop=' + rowform.scrollTop.value + ' scrollLeft=' + rowform.scrollLeft.value);
}
function ClearScroll(rowform){
	rowform.scrolltop.value='';
	rowform.scrollleft.value='';
}
function OnLasImport(rowform)
{
	SetScroll(rowform);
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
	SetScroll(rowform);
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
	SetScroll(rowform);
	OnSetPlotCfg(rowform);
}
function OnSetPlotCfg(rowform)
{
	SetScroll(rowform);
	t = 'setplotcfg.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function OnLogScale(rowform)
{
	SetScroll(rowform);
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
	SetScroll(rowform);
	rowform.zoom.value=val;
	t = 'setzoom.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function setzoom (rowform) {
	SetScroll(rowform);
	rowform.zoom.value=rowform.zoomtext.value;
	t = 'setzoom.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}

function showvalue(event,el){
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

	var rot=document.pointform.viewrotds.value;
	var depth, value;
	if(rot>=1) {
		// take off gnuplot margin
		pos_y-=16; pheight-=32;
		depth=(pos_x*(plotend-plotstart)/pwidth)+parseFloat(plotstart);
		value=scaleright-(pos_y*(scaleright/pheight));
	}
	else {
		// take off gnuplot margin
		pos_x-=16; pwidth-=32;
		depth=(pos_y*(plotend-plotstart)/pheight)+parseFloat(plotstart);
		value=pos_x*(scaleright/pwidth);
	}
	// scrolltop=document.getElementById("div1").scrollTop;
	document.getElementById("dbgdepth").value=depth.toFixed(2);
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
// MARK: Here is where we calculate how far the data has been scrolled
// MARK: giving us a "fault" value to use
function showProposedFault() {
	var freeze=parseInt(document.pointform.dscache_freeze.value);
	
	var lastdist = gdist*-1;
	var dist=0.0;
	if(freeze==0) {
		var viewrotds=parseInt(document.pointform.viewrotds.value);
		var plotstart=document.pointform.plotstart.value;
		var plotend=document.pointform.plotend.value;
		var pheight=document.getElementById("clickimage").height;
		var pwidth=document.getElementById("clickimage").width;
		var scrolltop=document.getElementById("div1").scrollTop;
		var scrollleft=document.getElementById("div1").scrollLeft;
		var scrollwidth=document.getElementById("div1").scrollWidth;
		if(viewrotds>0) dist=(plotend-plotstart)/pwidth*(scrollleft-885)*-1;
		else dist=(plotend-plotstart)/pheight*(scrolltop-885)*-1;
		dist=dist-parseFloat(document.pointform.dspoffset.value);
	} else {
		dist=document.pointform.dscache_fault.value;
	}
	console.log('last dist:'+lastdist);
	console.log('new dist:'+dist);
	gdist = dist*-1;
	
	if(document.getElementById("dbgscrollfault")!=undefined){
		var dbfault=document.getElementById("dbgscrollfault").value;
		var fault=parseFloat(dbfault)+(dist-lastdist);
		document.getElementById("dbgscrollfault").value=fault.toFixed(2);
	}
	return dist;
}
function showdistance(event,el) {
	var viewrotds=parseInt(document.pointform.viewrotds.value);
	var freeze=parseInt(document.pointform.dscache_freeze.value);
	var plotstart=document.pointform.plotstart.value;
	var plotend=document.pointform.plotend.value;
	var pheight=document.getElementById("clickimage").height;
	var scrolltop=document.getElementById("div1").scrollTop;
	var scrollleft=document.getElementById("div1").scrollLeft;
	var scrollwidth=document.getElementById("div1").scrollWidth;

	//alert('freeze=' + freeze + ' plotstart=' + plotstart + ' plotend=' + plotend + ' pheight=' + pheight +
	//	' scrolltop=' + scrolltop + ' scrollwidth=' + scrollwidth);

	if(freeze==0) {
		if(viewrotds>0) var dist=(plotend-plotstart)/pheight*(scrollleft-885);
		else var dist=(plotend-plotstart)/pheight*(scrolltop-885);
		if(viewrotds<=0)
			dist=dist+parseFloat(document.pointform.dspoffset.value);
		if(document.getElementById("dbgscrolldist")!=undefined)
			document.getElementById("dbgscrolldist").value=-dist.toFixed(2);
	}
	showProposedFault();
	if(viewrotds>0) document.getElementById("dbgscrolltop").value=scrollleft.toFixed(0);
	else document.getElementById("dbgscrolltop").value=scrolltop.toFixed(0);
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
	if(param=="freeze") {
		if(rowform.freeze.checked==1) {
			faultmod = <?php echo $dbfault?>;
			//alert('faultmod=' + faultmod);
			scrlldiv= document.getElementById('div1')
			SetScroll(rowform);
			rowform.dscache_md.value=rowform.endmd.value;
			rowform.dscache_freeze.value="1";
			var dist=showProposedFault();
			rowform.dscache_fault.value=(document.getElementById("dbgscrollfault").value-faultmod);
			rowform.dsholdfault.value='0';
		} else {
			rowform.dscache_freeze.value="0";
			if(rowform.dsholdfault.value<=0){
				rowform.dscache_fault.value ="0";
				SetScroll(rowform);
			} 
		}
	}
	if(param=='reset'){
		rowform.dscache_freeze.value="0";
		rowform.dscache_fault.value="0";
		ClearScroll(rowform);
	}
	if(param=='holdfault'){
		if(rowform.holdfault.checked==1){
			rowform.dsholdfault.value=document.getElementById("div1").scrollTop;
		} else {
			rowform.dsholdfault.value='0';
		}
	}
	t = 'setdscache.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}
function savedscache(bDoFault) {
	var rowform=document.getElementById("savedscache");
	SetScroll(rowform);
	var freeze=parseInt(document.pointform.dscache_freeze.value);
	var dbfault=document.pointform.dbfault.value;
	var dist=document.pointform.dscache_fault.value;
	var fault=parseFloat(dbfault)+parseFloat(dist);
	rowform.dscache_fault.value=fault.toFixed(3);
	var str="Save shadow dip value of "+rowform.dscache_dip.value+"\n";
	if(bDoFault==1) {
		str+="and fault to "+rowform.dscache_fault.value+"\n";
	}
	else rowform.dscache_fault.value="";
	str+="to "+rowform.viewdspcnt.value+" data sets\n";
	str+="starting at depth "+rowform.dscache_md.value+"\n\n";
	str+="Are you sure you want to do this?";
	var r=confirm(str);
	if(r!=true) return;
	SetScroll(rowform);
	t = 'savedscache.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();
	return ray.ajax();
}

function dipupdown (rowform, val) {
	SetScroll(rowform);
	var t = parseFloat(rowform.sectdip.value);
	t+=val;
	if(t<-89.9) t=-89.9;
	if(t>89.9)	t=89.9;
	rowform.sectdip.value=t;
	setdscfg(rowform);
}
function faultupdown (rowform, val) {
	SetScroll(rowform);
	var t = parseFloat(rowform.sectfault.value);
	t+=val;
	rowform.sectfault.value=t;
	setdscfg(rowform);
}
function biasupdown (rowform, val) {
	SetScroll(rowform);
	rowform.bias.value=parseFloat(rowform.bias.value)+parseFloat(val);
	setdscfg(rowform);
}
function scaleupdown (rowform, val) {
	SetScroll(rowform);
	var t = parseFloat(rowform.factor.value);
	t+=val;
	if(t<0) t=0;
	if(t>100)	t=100;
	rowform.factor.value=t;
	setdscfg(rowform);
}
function setdscfg (rowform) {
	if(rowform) {
		SetScroll(rowform);
		t = 'setdscfg.php';
		t = encodeURI (t);
		rowform.action = t;
		rowform.submit();
		return ray.ajax();
	} else {
		rowform = document.getElementById('dipform');
		t = 'setdscfg.php';
		t = encodeURI (t);
		rowform.action = t;
		rowform.submit();
		return ray.ajax();
	}
}
</script>
</head>
<?php
echo "<body onload='Init($viewrotds,$scrolltop,$scrollleft)'>";
$timelog[] = array(microtime(),'right after the body');
$maintab=3;
include 'apptabs.inc.php';
include 'waitdlg.html';

?>

<table class='tabcontainer'>

<tr style='background-color:none'>
<td class='header' style='width: 20px; padding: 0;'><br /></td>
<td class='header' style='text-align: left;'><?php if($viewrotds==0) echo '0'; else echo '.'; ?></td>
<td class='header'><?php if($viewrotds==0) echo $scaleright;else echo '.'; ?></td>
<td class='header'><br /></td>
</tr>

<tr>
<td style='width: 20px; font-size: 8pt; vertical-align: top; padding: 0;'>
<?php if($viewrotds==1) echo $scaleright; else echo ' '; ?>
</td>

<td colspan='2' class="container" align='left' style='background-color:white;'>

	<form name='pointform' method='post'>
	<div class='transbox'>
<!--
MARK: Here is where we use the background image generated from above
-->
<?php
if($dscache_freeze > 0)
{
	echo "	<div class='tableContainer' id='div1' style='background-color:white' " .
		"onmouseup='showdistance(event,this)'>\n";
}
else
{
	echo "	<div class='tableContainer' id='div1' style=\"background-color:white";
	if($fn2 != '') echo ";background: url('{$fn2}?{$random}') no-repeat";
	echo "\" onmouseup='showdistance(event,this)' onscroll='scrollAction(this)'>\n";
}
$timelog[] = array(microtime(),'before the chart');
?>
	<table border='0' cellpadding='0' cellspacing='0'>
<?php if($dscache_freeze > 0 and $fn2 != '') { ?>
	<tbody class='scrollContent' STYLE='background: url(<?php echo "$fn2?$random"?>) no-repeat center center;'>
<?php } else { ?>
	<tbody class="scrollContent">
<?php } ?>
		<tr>
		<td>
		<input type='hidden' name='ret' value='gva_tab4.php'>
		<input type='hidden' name='seldbname' value='<?php echo $seldbname; ?>'>
		<input type='hidden' name='dspoffset' value='<?php echo $dspoffset?>'>
		<input type='hidden' id='scroll0' name='scroll0' value='<?php echo $scrollpt?>'>
		<input type='hidden' id='initscrolltop' name='scrolltop' value='<?php echo $scrolltop?>'>
		<input type='hidden' id='initscrollleft' name='scrollleft' value='<?php echo $scrollleft?>'>
		<input type='hidden' name='plotstart' value='<?php echo $plotstart?>'>
		<input type='hidden' name='plotend' value='<?php echo $plotend?>'>
		<input type='hidden' name='viewrotds' value='<?php echo $viewrotds?>'>
		<input type='hidden' name='depth' value=''>
		<input type='hidden' name='val' value=''>
		<input type='hidden' name='setflag' value=''>
		<input type='hidden' name='tablename' value='<?php echo $tablename?>'>
		<input type='hidden' name='editmode' value='<?php echo $editmode?>'>
		<input type='hidden' name='scaleright' value='<?php echo $scaleright?>'>
		<input type='hidden' name='dsseltop' value='<?php echo $dsseltop?>'>
		<input type='hidden' name='dsselbot' value='<?php echo $dsselbot?>'>
		<input type='hidden' name='dbfault' value='<?php echo $dbfault?>'>
		<input type='hidden' name='dscache_fault' value='<?php echo $dscache_fault?>'>
		<input type='hidden' name='dscache_freeze' value='<?php echo $dscache_freeze?>'>
<?php
	if($tableid > 0 and $endmd > 0 and $fn != '') {
		echo "		<img id='clickimage' src='{$fn}?{$random}' onmouseup='showvalue(event, this)' " .
			"onclick='getpoint(event, this)'>\n";
	} else {
		echo "		<h1>No well log data found ($tableid,$endmd,$fn)</h1>\n";
	}
?>
		</td>
		</tr>
	</tbody>
	</table>
<?php
$timelog[] = array(microtime(),'after the chart');
?>
	</div> <!-- end of div class tablecontainer -->
	</div> <!-- end of div class transbox -->
	</form> <!-- end of pointform --> 
</td>

<td class="container" align='left'>
	<table class="settings">
	<tr><th colspan='5' class='header'>Data Sections - <?php echo $wellname; ?></th></tr>
	<tr>
	<td>
		<table style='font-size: 1em; width: 100%;'>
		<tr>
		<td>
			<FORM action='changeds.php' method='post'>
			<input type='hidden' name='ret' value='gva_tab4.php'>
			<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
			<input type='hidden' name='tablename' value='<?php echo $tablename?>'>
			<input type='hidden' name='startmd' value='<?php echo $startmd?>'>
			<input type='hidden' name='endmd' value='<?php echo $endmd?>'>
			<input type='hidden' name='zoom' value='<?php echo $zoom; ?>'>
			<input type='hidden' name='dir' value='first'>
			<INPUT type="submit" value="First">
			</FORM>
		</td>
		<td>
			<FORM action='changeds.php' method='post'>
			<input type='hidden' name='ret' value='gva_tab4.php'>
			<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
			<input type='hidden' name='tablename' value='<?php echo $tablename?>'>
			<input type='hidden' name='startmd' value='<?php echo $startmd?>'>
			<input type='hidden' name='endmd' value='<?php echo $endmd?>'>
			<input type='hidden' name='zoom' value='<?php echo $zoom; ?>'>
			<input type='hidden' name='dir' value='prev'>
			<input type='hidden' name='sgtastart' value='<?php echo $sgtastart?>'>
			<input type='hidden' name='sgtaend' value='<?php echo $sgtaend?>'>
			<input type='hidden' name='sgtacutin' value='<?php echo $sgtacutin?>'>
			<input type='hidden' name='sgtacutoff' value='<?php echo $sgtacutoff?>'>
			<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
			<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
			<input type='hidden' name='plotstart' value='<?php echo $plotstart?>'>
			<input type='hidden' name='plotend' value='<?php echo $plotend?>'>
			<INPUT type="submit" value="Prev" onclick='SetScroll(this.form)'>
			</FORM>
		</td>
		<td>
			<FORM action='changeds.php' method='post'>
			<input type='hidden' name='ret' value='gva_tab4.php'>
			<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
			<input type='hidden' name='tablename' value='<?php echo $tablename?>'>
			<input type='hidden' name='startmd' value='<?php echo $startmd?>'>
			<input type='hidden' name='endmd' value='<?php echo $endmd?>'>
			<input type='hidden' name='zoom' value='<?php echo $zoom; ?>'>
			<input type='hidden' name='dir' value='next'>
			<input type='hidden' name='sgtastart' value='<?php echo $sgtastart?>'>
			<input type='hidden' name='sgtaend' value='<?php echo $sgtaend?>'>
			<input type='hidden' name='sgtacutin' value='<?php echo $sgtacutin?>'>
			<input type='hidden' name='sgtacutoff' value='<?php echo $sgtacutoff?>'>
			<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
			<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
			<input type='hidden' name='plotstart' value='<?php echo $plotstart?>'>
			<input type='hidden' name='plotend' value='<?php echo $plotend?>'>
			<INPUT type="submit" value="Next" onclick='SetScroll(this.form)'>
			</FORM>
		</td>
		<td>
			<FORM action='changeds.php' method='post'>
			<input type='hidden' name='ret' value='gva_tab4.php'>
			<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
			<input type='hidden' name='tablename' value='<?php echo $tablename?>'>
			<input type='hidden' name='startmd' value='<?php echo $startmd?>'>
			<input type='hidden' name='endmd' value='<?php echo $endmd?>'>
			<input type='hidden' name='zoom' value='<?php echo $zoom; ?>'>
			<input type='hidden' name='dir' value='last'>
			<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
			<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
			<input type='hidden' name='plotstart' value='<?php echo $plotstart?>'>
			<input type='hidden' name='plotend' value='<?php echo $plotend?>'>
			<INPUT type="submit" value="Last" onclick='SetScroll(this.form);'>
			</FORM>
		</td>
		<td align='right'>
			<FORM method="post">
			<input type='hidden' name='ret' value='gva_tab4.php'>
			<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
			<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
			<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
			<input type='hidden' name='zoom' value='<?php echo $zoom; ?>'>
			<INPUT type="submit" value="Import" ONCLICK="OnLasImport(this.form)">
			</FORM>
		</td>
		</tr>

		<tr>
		<td colspan='5'>
			<table class='header' style='width: 100%;'>
			<tr>
			<th class='header'> </th>
			<th class='header' colspan='4'> <b>File:</b><?php echo " $realname ($tablename)"; ?> </th>
			</tr> <tr>
			<td class='header'><b>MD:</b></td>
			<td class='header'> <?php printf("%9.2f", $startmd); ?> </td>
			<td class='toheader'><b>to</b></td>
			<td class='header'> <?php printf("%9.2f", $endmd); ?> </td>
			</tr> <tr>
			<td class='header'><b>TVD:</b></td>
			<td class='header'> <?php printf("%9.2f", $starttvd); ?> </td>
			<td class='toheader'><b>to</b></td>
			<td class='header'> <?php printf("%9.2f", $endtvd); ?> </td>
			</tr> <tr>
			<td class='header'><b>VS:</b></td>
			<td class='header'> <?php printf("%9.2f", $startvs); ?> </td>
			<td class='toheader'><b>to</b></td>
			<td class='header'> <?php printf("%9.2f", $endvs); ?> </td>
			</tr> <tr>
			<td class='header'><b>TOT/BOT:</b></td>
			<td class='header'> <?php printf("%9.2f", $secttot); ?> </td>
			<td class='toheader'><b>to</b></td>
			<td class='header'> <?php printf("%9.2f", $sectbot); ?> </td>
			</tr>
			</table>
		</td>
		</tr>

		<tr>
		<td colspan='3' class="container" align='right'>
			<FORM action='changeds.php' method='post'>
			<input type='hidden' name='ret' value='gva_tab4.php'>
			<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
			<input type='hidden' name='dir' value='directmd'>
			Go to dataset at <input type='text' size='4' name='startmd' value=''> MD
			</FORM>
		</td>
		</tr>

		<tr><td colspan='5'>plotstart=<?php echo $plotstart ?> plotend=<?php echo $plotend ?></td></tr>

		<tr>
		<td colspan='5'>
			<FORM method='post'>
			<input type='hidden' name='ret' value='gva_tab4.php'>
			<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
			<input type='hidden' name='tablename' value='<?php echo $tablename?>'>
			<input type='hidden' name='depth' value='<?php echo $depth?>'>
			<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
			<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
			<input type='hidden' name='viewrotds' value='<?php echo $viewrotds?>'>
			<input type='hidden' name='sgtastart' value='<?php echo $plotstart?>'>
			<input type='hidden' name='sgtaend' value='<?php echo $plotend?>'>
			<?php if($viewallds<=1) $ci=$startmd-500; else $ci=$startmd-$viewallds; ?>
			<input type='hidden' name='sgtacutin' value='<?php echo $ci?>'>
			<input type='hidden' name='sgtacutoff' value='<?php echo $endmd?>'>
			<input type="radio" name='viewallds' value='<?php
				if($viewallds<=1) echo "500"; else echo $viewallds; ?>' <?php
				if($viewallds>1) echo " checked='true';"?> onclick="OnViewDS(this.form)">View previous 
			<input type='text' size='7' name='viewallds' value='<?php
				if($viewallds<=1) echo "500"; else echo $viewallds; ?>' <?php
				if($viewallds<=1) echo "readonly='true'"; ?> onchange="OnViewDS(this.form)"> MD<br>
			<input type="radio" name='viewallds' value='1' <?php
				if($viewallds==1) echo " checked='true'"?> onclick="OnViewDS(this.form)">View All Datasets<br>
			<input type="radio" name='viewallds' value='0' <?php
				if($viewallds==0) echo " checked='true'"?> onclick="OnViewDS(this.form)">View Only Selected
			</FORM>
			<FORM method='post'>
			<input type='hidden' name='ret' value='gva_tab4.php'>
			<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
			<input type='hidden' name='tablename' value='<?php echo $tablename?>'>
			<input type='hidden' name='scrolltop' value='<?php echo $scrolltop; ?>'>
			<input type='hidden' name='scrollleft' value='<?php echo $scrollleft; ?>'>
			<input type='hidden' name='viewrotds' value='<?php echo $viewrotds?>'>
			<input type='hidden' name='viewallds' value='<?php echo $viewallds?>'>
			<input type="checkbox" name='viewrot' <?php if($viewrotds>=1)echo " checked='true' "; ?> value="<?php echo $viewrotds; ?>" onclick="OnRotateDS(this.form)">Rotate Dataset
			<br> <br>
			Show shadow of last 
			<input type='text' size='3' name='viewdspcnt' value='<?php echo $viewdspcnt?>' onchange="OnViewDS(this.form)">
			modeled datasets
			</FORM>
		</td>
		</tr>
		<tr>
		<td colspan='5' style='border-top:1px solid black'>
			<div style='font-weight:bolder'>Select CSV File to Import Rotate/Slide:</div>
			<form method="post" enctype="multipart/form-data">
			<input type="file" name="rotslide_csv_file" size="40">
			<input type="submit" value="Import" onclick="return confirm('Ready to upload CSV file?')">
			</form>
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
		<form method='post' id='dipform'>
		<input type='hidden' name='ret' value='gva_tab4.php'>
		<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
		<input type='hidden' name='tablename' value='<?php echo $tablename?>'>
		<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
		<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
		<td class='header'>Fault</td>
		<td class='header'>
			<input type="text" size="4" name="sectfault" value="<?php echo $sectfault?>" onchange='setdscfg(this.form)'>
		</td>
		<td class='header' style='text-align: left;'>
			<input type=button value="+" onClick="faultupdown(this.form, 1)">
			<input type=button value="-" onClick="faultupdown(this.form, -1)">
		</td>
		</tr>
		<tr>
		<td class='header'>Dip</td>
		<td class='header'>
			<input id='sectdip_parent' type="text" size="4" name="sectdip" value="<?php echo $sectdip?>" onchange='setdscfg(this.form)'>
		</td>
		<td class='header' style='text-align: left;'>
			<input type=button value="+" onClick="dipupdown(this.form, 1)">
			<input type=button value="-" onClick="dipupdown(this.form, -1)">
			<input type=button value='Auto Dip' 
			onClick="window.open('sgtamodeling_autodip.php?seldbname=<?php echo $seldbname?>','sgtaavgpopup','width=700,height=400,left=200,scrollbars=yes');">
			<br />
		</td>
		</tr>
		<tr>
		<td class='header'>Bias</td>
		<td class='header'>
			<input type='text' size='4' name='bias' value="<?php printf('%.0f', $bias); ?>" onchange='setdscfg(this.form)'>
		</td>
		<td class='header' style='text-align: left;'>
			<input type=button value="Left" onClick="biasupdown(this.form, -10)">
			<input type=button value="Right" onClick="biasupdown(this.form, 10)">
		</td>
		</tr>
		<tr>
		<td class='header'>Scale</td>
		<td class='header'>
			<input type='text' size='4' name='factor' value="<?php printf('%.2f', $factor); ?>" onchange='setdscfg(this.form)'>
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
			<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
			<INPUT type='hidden' name='tn' value='<?php echo $tablename?>'>
			<INPUT type='hidden' name='depth' value='<?php echo $startmd?>'>
			<INPUT type='hidden' name='editmode' value='search'>
			<INPUT type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
			<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
			<input type='hidden' name='sgtastart' value='<?php echo $sgtastart?>'>
			<input type='hidden' name='sgtaend' value='<?php echo $sgtaend?>'>
			<input type='hidden' name='sgtacutin' value='<?php echo $sgtacutin?>'>
			<input type='hidden' name='sgtacutoff' value='<?php echo $sgtacutoff?>'>
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
	<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
	<input type='hidden' name='tablename' value='<?php echo $tablename?>'>
	<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
	<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
	<input type='hidden' name='sgtastart' value='<?php echo $sgtastart?>'>
	<input type='hidden' name='sgtaend' value='<?php echo $sgtaend?>'>
	<input type='hidden' name='sgtacutin' value='<?php echo $sgtacutin?>'>
	<input type='hidden' name='sgtacutoff' value='<?php echo $sgtacutoff?>'>
	<input type='hidden' name='uselogscale' value='<?php echo $uselogscale?>'>
	<td class='container' align='right'>
		Plot bias <input type='text' size='3' name='plotbias' value="<?printf('%.0f', $plotbias); ?>" onchange='OnSetPlotCfg(this.form);'>
	</td>
	<td class='container'>
		<input type=button value="Left" onClick="plotbiasupdown(this.form, -10)">
		<input type=button value="Right" onClick="plotbiasupdown(this.form, 10)">
	</td>
	</tr>
	<tr>
	<td class="container" align='right'>
		Data Scale <input type='text' size='3' name='scaleright' value='<?php echo $scaleright; ?>' onchange='OnSetPlotCfg(this.form);'>
	</td>
	<td class="container" align='left'>
		<input type='checkbox' <?php if($uselogscale!=0) echo " checked='true' "; ?> id='lscb' name='lscb' onclick='OnLogScale(this.form);'>
		Logarithmic scale
	</td>
	</tr>
	<tr>
	<td align='right' class='container'>
		Data Average <input size='3' type='text' name='dataavg' value="<?php echo $dataavg?>" onchange='OnSetPlotCfg(this.form);'>
	</td>
	</FORM>
	</tr>

	<tr>
	<FORM method='post'>
	<input type='hidden' name='ret' value='gva_tab4.php'>
	<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
	<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
	<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
	<input type='hidden' name='sgtastart' value='<?php echo $sgtastart?>'>
	<input type='hidden' name='sgtaend' value='<?php echo $sgtaend?>'>
	<input type='hidden' name='sgtacutin' value='<?php echo $sgtacutin?>'>
	<input type='hidden' name='sgtacutoff' value='<?php echo $sgtacutoff?>'>
	<input type='hidden' name='zoom' value='<?php echo $zoom?>'>
	<td class='container' align='right'>
		Depth scale
		<input type='text' <?php if($dscache_freeze>0) echo "disabled='true'"?> id='zoomtext' name='zoomtext' size='3' value='<?php echo $zoom; ?>' onchange="setzoom(this.form)">
	</td>
	<td class='container' align='left'>
		<input type="submit" <?php if($dscache_freeze>0) echo "disabled='true'"?> value="Zoom In" <?php if($zoom<=.5)echo " disabled='true' "; ?> onmouseup="setzoomto(this.form, <?php echo $zoomdec; ?>)">
		<input type="submit" <?php if($dscache_freeze>0) echo "disabled='true'"?> value="Zoom Out" <?php if($zoom>=$maxzoom)echo " disabled='true' "; ?> onmouseup="setzoomto(this.form, <?php echo $zoominc; ?>)">
	</td>
	</FORM>
	</tr>
	<tr><td> <br style='font-size: 2pt;'> </td></tr>
	<tr>
	<td align='right' class='container'>
		<FORM name='directinput' method='post' action='gva_tab4.php'>
		<input type='hidden' name='ret' value='gva_tab4.php'>
		<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
		<input type='hidden' name='editmode' value=''>
		<input type='hidden' name='setflag' value='1'>
		<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
		<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
		<input type='hidden' name='dsseltop' value='<?php echo $dsseltop?>'>
		<input type='hidden' name='dsselbot' value='<?php echo $dsselbot?>'>
		<input type='hidden' name='sgtastart' value='<?php echo $sgtastart?>'>
		<input type='hidden' name='sgtaend' value='<?php echo $sgtaend?>'>
		<input type='hidden' name='sgtacutin' value='<?php echo $sgtacutin?>'>
		<input type='hidden' name='sgtacutoff' value='<?php echo $sgtacutoff?>'>
		Depth
		<input size='6' type='text' name='depth' id='dbgdepth' value="<?printf("%.2f", $depth); ?>" onchange="directInput(this.form)">
		</FORM>
	</td>
	<td align='right' class='container'>
		<input type=button name=choice onClick="window.open('outputpicker.php?seldbname=<?php echo $seldbname?>&title=View%20Snapshot&program=welllogpdf.php&filename=/tmp/<?php echo $seldbname; ?>.snapshot.pdf&plotstart=<?php echo $plotstart; ?>&plotend=<?php echo $plotend; ?>&wlid=<?php echo $tableid; ?>','popuppage','width=200,height=220,left=500');" value="Snapshot">
	</td>
	</tr>
	<tr>
	<td>
		ProcessTime:<?printf("%.3f",$timelapse); ?>
	</td>
	<td class='container' align='right'>
	<!--<input type="submit" value="View Tables" onclick="window.open('viewtablespdf.php?seldbname=<?php echo $seldbname?>', 'View Tables', 'height=600,width=900,scrollbars=yes')">
	-->
	<input type="submit" value="View Tables" onclick="window.location='viewtablespdf.php?seldbname=<?php echo $seldbname?>'">
	</td>
	</tr>
	<tr>
	<td>
		<input size='3' readonly='true' type='text' name='dbgscrolltop' id='dbgscrolltop' value="">
		
	</td>
	</tr>
	</table>
	<br style='font-size: 2pt;'>
	<table class="settings">
	<tr>
	<th colspan='4' class='header'>Exports</th>
	</tr>
	<tr>
		<td>start depth(tvd)</td><td>end depth(tvd)</td>
	</tr>
	<form method='get' action='csv/tvdgamma.php'>
	<tr>
	
		<input type='hidden' value='<?php echo "$seldbname"; ?>' name='seldbname'>
		<td><input type='text' value='0' name='sdepth'></td>
		<td><input type='text' value='999999' name='edepth'></td>
	</tr>
	<tr>
		<td>increment</td><td></td>
	</tr>
	<tr>
		<td><input type='text' value='' name='incr'></td>
		<td><input type='submit' value='Gamma Export'></td>
	
	</tr>
	</form>
	<tr>
		<td>
			<FORM name='rawimportexport' method='get' action='csv/rawimportexport.php' target="_blank">
				<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
				<input type="hidden" name="tn" value="<?php echo "$tablename"; ?>">
				<input type='submit' value='Raw Import Export'>
			</form>
		</td>
	</tr>
	<tr>
		<td colspan=2>
		<FORM name='rawimportexport' method='get' action='export_tvdgamma_controltvd.php' target="_blank">
				<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
				<input type='submit' value='Control TVD Gamma Export'>
			</form>
		</td>
	</tr>
	</table>
</td>
<?php if($viewdspcnt>0) {
	$enable=" ";
	if($dscache_freeze>0)
	{
		$enable=" disabled='true' ";
	}
?>
<td class="container" align='left' style='vertical-align: middle;'>
	<table class="settings" style='width: 170;'>
	<FORM action='setdscache.php' method='post'>
	<tr><th colspan='3' class='header'>Shadow Sections Modeling</th></tr>
	<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
	<input type='hidden' name='ret' value='gva_tab4.php'>
	<input type='hidden' name='tablename' value='<?php echo $tablename?>'>
	<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
	<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
	<input type="hidden" name="viewdspcnt" value="<?php echo "$viewdspcnt"; ?>">
	<input type="hidden" name="endmd" value="<?php echo "$endmd"; ?>">
	<input type="hidden" name="dscache_md" value="<?php echo $endmd; ?>">
	<input type="hidden" name="dscache_plotstart" value="<?php echo "$plotstart"; ?>">
	<input type="hidden" name="dscache_plotend" value="<?php echo "$plotend"; ?>">
	<input type='hidden' name='dscache_freeze' value='<?php echo $dscache_freeze?>'>
	<input type="hidden" name="dscache_fault" value="<?php echo $dscache_fault?>">
	<input type="hidden" name="dsholdfault" value="<?php echo $dsholdfault?>">
	<input type='hidden' name='faultmod' value=''>
	<tr>
	<td class='header'>
		Dip
	</td><td class='header'>
		<input type="text" <?php echo $enable?> size="3" name="dscache_dip" value="<?php echo $dscache_dip?>" onchange='setdscache(this.form)'>
	</td><td class='header' style='text-align: left;'>
		<input type=button <?php echo $enable?> value="+" onClick="setdscache(this.form, 'dip', 1)">
		<input type=button <?php echo $enable?> value="-" onClick="setdscache(this.form, 'dip', -1)">
	</td>
	</tr> <tr>
	<td class='header'>
		Bias
	</td><td class='header'>
		<input type='text' <?php echo $enable?> size='3' name='dscache_bias' value="<?printf('%.0f', $dscache_bias); ?>" onchange='setdscache(this.form)'>
	</td><td class='header' style='text-align: left;'>
		<input type=button <?php echo $enable?> value="<" onClick="setdscache(this.form, 'bias', -10)">
		<input type=button <?php echo $enable?> value=">" onClick="setdscache(this.form, 'bias', 10)">
	</td>
	</tr> <tr>
	<td class='header'>
		Scale
	</td><td class='header'>
		<input type='text' <?php echo $enable?> size='3' name='dscache_scale' value="<?printf('%.2f', $dscache_scale); ?>" onchange='setdscache(this.form)'>
	</td><td class='header' style='text-align: left;'>
		<input type=button <?php echo $enable?> value="+" onClick="setdscache(this.form, 'scale', 0.1)">
		<input type=button <?php echo $enable?> value="-" onClick="setdscache(this.form, 'scale', -0.1)">
	</td>
	</tr>
	<tr><td colspan='4'>
	<input type='button' value='Reset Calculated Fault' onclick="setdscache(this.form,'reset')">
	</td></tr>
	<tr>
	<td colspan='2' class='header'>
		Freeze <input type='checkbox' name='freeze' value='<?php echo $dscache_freeze; ?>' <?php if($dscache_freeze==1)echo " checked='true' "; ?> onchange="setdscache(this.form, 'freeze')">
		<?php if($dscache_freeze==1) { ?><br>Hold Fault <input type='checkbox' name='holdfault' value='1'  <?php if($dsholdfault>0) echo " checked='true'"; ?> onchange="setdscache(this.form,'holdfault')"> <?php } ?>
	</td>
	</FORM>
	<td colspan='1' class='header'>
		<FORM id='savedscache' method='post' style='padding: 0 0; margin: 0 0;'>
		<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
		
		<input type='hidden' name='ret' value='gva_tab4.php'>
		<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
		<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
		<input type="hidden" name="viewdspcnt" value="<?php echo $viewdspcnt; ?>">
		<input type="hidden" name="dscache_dip" value="<?php echo $dscache_dip?>">
		<input type="hidden" name="dscache_bias" value="<?php echo $dscache_bias?>">
		<input type="hidden" name="dscache_scale" value="<?php echo $dscache_scale?>">
		<input type='hidden' name='dscache_freeze' value='<?php echo $dscache_freeze?>'>
		<input type="hidden" name="dscache_fault" value="<?php echo $dscache_fault?>">
		<input type="hidden" name="dsholdfault" value="<?php echo $dsholdfault?>">
		<input type="hidden" name="dscache_md" value="<?php echo "$dscache_md"; ?>">
		<input type="hidden" name="dscache_md" value="<?php echo "$dscache_md"; ?>">
		</FORM>
		<input type='submit' <?php if($dscache_freeze==0) echo " disabled='true '"; ?> value='Save Dip' onclick="savedscache(0)">
		<input type='submit' <?php if($dscache_freeze==0) echo " disabled='true '"; ?> value='Save Fault' onclick="savedscache(1)">
		
	</td>
	</tr>
	<tr>
	<td colspan='4' align='right' class='container'>
		<!--<br>
		Scroll distance
		-->
		<input size='3' readonly='true' type='hidden' name='dbgscrolldist' id='dbgscrolldist' value="">
		<br>
		Current Fault
		<input size='3' readonly='true' type='text' value="<?php echo $dbfault?>">
		<br>
		Calculated Fault
		<input size='3' readonly='true' type='text' name='dbgscrollfault' id='dbgscrollfault' value="<?php echo ($dbfault+$dscache_fault)?>">
	</td>
	</table>
</td>
<?php } ?>
</tr>
<tr>
<td colspan='3'>
	<center><small><small>
	&#169; 2010-2011 Supreme Source Energy Services, Inc.
	</small></small></center>
</td>
</tr>
</table>
</body>
</html>
<?php
$timelog[] = array(microtime(),'before tab5');
include_once('gva_tab5_funct.php');
$timelog[] = array(microtime(),'after tab5');
//echo '<pre>'; print_r($timelog); echo '</pre>';
$db->CloseDb();
?>
