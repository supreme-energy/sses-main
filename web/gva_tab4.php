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
	//echo ($com." -w $pwidth -h $pheight -o $fn");
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
<script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
<title><?php echo "$dbrealname"; ?>-SGTA Editor<?php echo " ($seldbname)"; ?></title>
<script language='javascript' type='text/javascript' src='waitdlg.js'></script>
<script language='javascript'>

</script>
</head>
<?php
echo "<body>";
$timelog[] = array(microtime(),'right after the body');
$maintab=3;
include 'apptabs.inc.php';
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
	
	<div style='padding-top:5px;text-align:center;width:80px;'><button id='deletedatsetbutton'  title='Delete Data Set'><i class="fas fa-trash-alt" style='color:red'></i></button></div>
	

	
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

	<!-- <table class="settings">
	<tr><th colspan='5' class='header'>Data Sections - <?php echo $wellname; ?></th></tr>
	<tr>
	<td>
		<table style='font-size: 1em; width: 100%;'>
		<tr>
		<td>
			<INPUT type="submit" value="First" onclick="firstDataSet()">
		</td>
		<td>
			<INPUT type="submit" value="Prev" onclick='prevDataSet()'>
		</td>
		<td>
			<INPUT type="submit" value="Next" onclick='nextDataSet()'>
		</td>
		<td>
			<INPUT type="submit" value="Last" onclick='lastDataSet()'>
		</td>
		<td align='right'>
			<INPUT type="submit" value="Import" ONCLICK="OnLasImport()">
		</td>
		</tr>

		<tr>
		<td colspan='5'>
			
		</td>
		</tr>

		<tr>
		<td colspan='3' class="container" align='right'>
			Go to dataset at  MD
		</td>
		</tr>

		<tr><td colspan='5'>plotstart=<?php echo $plotstart ?> plotend=<?php echo $plotend ?></td></tr>

		<tr>
		<td colspan='5'>
			<input type="radio" name='viewallds' value='<?php
				if($viewallds<=1) echo "500"; else echo $viewallds; ?>' <?php
				if($viewallds>1) echo " checked='true';"?> onclick="viewPreviousXMD()">View previous 
			<input type='text' size='7' id='viewallprevval' name='viewallds' value='<?php
				if($viewallds<=1) echo "500"; else echo $viewallds; ?>' <?php
				if($viewallds<=1) echo "readonly='true'"; ?> onchange="viewPreviousXMD()"> MD<br>
			<input type="radio" name='viewallds' value='1' <?php
				if($viewallds==1) echo " checked='true'"?> onclick="viewAll(true)">View All Datasets<br>
			<input type="radio" name='viewallds' value='0' <?php
				if($viewallds==0) echo " checked='true'"?> onclick="viewOnlySelected()">View Only Selected
			<br> <br>
			Show shadow of last 
			<input type='text' size='3' id='shadow_view_cnt' name='viewdspcnt' value='<?php echo $viewdspcnt?>' onchange="showShadow(this)">
			modeled datasets
			
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
	<tr> <th colspan='3' class='header'>Data Modeling  </th> </tr>
	<tr>
	<td colspan='5'>
		<table class='header' style='width: 100%;'>
		<tr>
		<td class='header'>On Scroll</td><td colspan=2><div style='float:left;padding-right:10px'><button onclick='scrollMode="zoom"; mouseWheelZoomOn(true);'>Zoom</button></div><div style='float:left;padding-right:10px'><button onclick='scrollMode="fault";mouseWheelZoomOn(false);' >Fault</button></div><div style='float:left;padding-right:10px'><button onclick='scrollMode="dip";mouseWheelZoomOn(false);' >Dip</button></div><div style='clear:both'></div></div></td>
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
		Depth Range
		<input type='text' <?php if($dscache_freeze>0) echo "disabled='true'"?> id='zoomtext' name='zoomtext' size='3' value='<?php echo $zoom; ?>' onchange="setzoom(this.form)">
	</td>
	<td class='container' align='left'>

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

<td class="container" align='left' style='vertical-align: middle;'>
	<table class="settings" style='width: 270;<?php echo $viewdspcnt> 0 ? '' : 'display:none' ?>;' id='dropshadow_container'>
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
	</table> -->
</td>
<!-- drop shadow end -->
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
$timelog[] = array(microtime(),'before tab5');
include_once('gva_tab5_funct.php');
$timelog[] = array(microtime(),'after tab5');
//echo '<pre>'; print_r($timelog); echo '</pre>';
//$db->CloseDb();
?>
