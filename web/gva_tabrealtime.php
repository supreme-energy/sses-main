<?php
//	Written by: John Arnold from Richard Gonsuron's modules
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Digital Oil Tools

error_reporting(E_ALL);
ini_set('display_errors', '1');
$streaming_realtime=1;
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
					$set = array('md'=>$db3->FetchField('md'),
						'tvd'=>$db3->FetchField('tvd'),
						'vs'=>$db3->FetchField('vs'),
						'value'=>$db3->FetchField('value'),
						'depth'=>$db3->FetchField('depth'));
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
//$s = (isset($_POST['editmode']) ? $_POST['editmode'] : ''); if($s != '') $editmode = $s;
//$s = (isset($_POST['depth']) ? $_POST['depth'] : ''); if($s != '') $depth = $s;
$editmode = (isset($_POST['editmode']) ? $_POST['editmode'] : (isset($editmode) ? $editmode : ''));
$depth = (isset($_POST['depth']) ? $_POST['depth'] : (isset($depth) ? $depth : ''));
$setflag = (isset($_POST['setflag']) ? $_POST['setflag'] : '');
$ret="gva_tab8.php";
if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_GET['seldbname']) ? $_GET['seldbname'] : '');
if(!isset($noshowxy) or $noshowxy == '') $noshowxy = (isset($_GET['noshowxy']) ? $_GET['noshowxy'] : '');
$sortdir = (isset($_GET['sortdir']) ? $_GET['sortdir'] : '');
if($seldbname == '') $seldbname = (isset($_POST['seldbname']) ? $_POST['seldbname'] : '');
if($seldbname == '') include("dberror.php");

	
// print_r($HTTP_POST_FILES);

// open and read from index database

$db= new dbio("sgta_index");
$db->OpenDb();
$db->DoQuery("SELECT * FROM dbindex ORDER BY id;");
while($db->FetchRow()) {
	$dbids=$db->FetchField("id");
	$dbn=$db->FetchField("dbname");
	$dbreal=$db->FetchField("realname");
	if($seldbname==$dbn) $dbrealname=$dbreal;
} 
$db->CloseDb();
unset($db);

// open selected database

$db= new dbio($seldbname);
$db->OpenDb();
	try{
	require 'HTTP/Upload.php';
	$upload = new http_upload('en');
	$file = $upload->getFiles('userfile');
	if (PEAR::isError($file)) {
		
	} else{
		if ($file->isValid()) {
			$file->setName('uniq');
			$dest_dir = './tmp/';
			$dest_name = $file->moveTo($dest_dir);
			if (PEAR::isError($dest_name)) {
				die ($dest_name->getMessage());
			}
			$filename=sprintf("$dest_dir%s", $file->getProp('name'));
			$temp=tmpfile();
			$infile=fopen("$filename", "r");
			if(!$infile)	die("<pre>File not found: $filename\n</pre>");
			do{
				$line=fgets($infile,1024);
				if($line==FALSE) 
					die("End of file looking for curve section \n");
			} while(stristr($line,"~Curve Information Section")==FALSE);
			$ar_cols=array();
			
			do {
				$line=fgets($infile);
				
				$ar = explode(":",$line);
				if(count($ar)>1){
					
					$ar2 = explode(" ",trim($ar[1]));
					$idxval_a = array_slice($ar2,0,1);
					$idxval=$idxval_a[0];
					$value_name = implode("",array_slice($ar2,1,count($ar2)));
					echo $value_name." : ".$idxval."<br>/n";
					
					$ar_cols[$value_name]=($idxval-1);
				}
			
				if($line==FALSE) 
					die("End of file looking for ~A data section\n");
			} while(stristr($line, "~A")==FALSE);
		//	print_r($ar_cols);
			while($line=fgets($infile)) {
				$line=trim($line);
				$line=preg_replace( '/\s+/', ',', $line );
				fputs($temp, "$line\n");
			}
			fclose($infile);
			fseek($temp,0);
			$sql_pa_ks = "select md,tvd,vs,plan from surveys order by id desc limit 2;";
			$db->DoQuery($sql_pa_ks);
			$pa=$db->FetchRow();
			$ks=$db->FetchRow();
			$mdks = $ks['md'];
			$vsks = $ks['vs'];
			$tvdks=$ks['tvd'];
			$mdpa = $pa['md'];
			$vspa = $pa['vs'];
			$tvdpa = $pa['tvd'];
			while (($data = fgetcsv($temp, 5000, ",")) !== FALSE) {
				
				$md=$data[$ar_cols["Depth"]];
				$val=$data[$ar_cols["Gamma"]];
				$tvd=(($md-$mdks)*($tvdpa-$tvdks)/($mdpa-$mdks))+$tvdks;
				$vs=(($md-$mdks)*($vspa-$vsks)/($mdpa-$mdks))+$vsks;
				if($md=="")	$md=0;
				if($tvd=="")	$tvd=0;
				if($vs=="")	$vs=0;
				if($value=="")	$value=0;
				$sql = "INSERT INTO  ghost_data (md,value,tvd,vs,depth) VALUES ($md,$val,$tvd,$vs,$md);";
				echo $sql."<br>";
				$result=$db->DoQuery($sql);
			}
		} elseif ($file->isMissing()) {
			
		} elseif ($file->isError()) {
			echo '<pre>';
			echo $file->errorMsg() . "\n";
			echo '</pre>';
			
		}
			
	
	}
	}catch(Exception $er){}
include 'cleanoujia.php';
include 'readwellinfo.inc.php';
include 'readappinfo.inc.php';

$timelog[] = array(microtime(),'before search');
// search for closest matching dataset
if($editmode == 'search')
{
	$db->DoQuery("SELECT tablename FROM welllogs WHERE startdepth<$depth AND enddepth>$depth;");
	if($db->FetchRow()) $tablename=$db->FetchField("tablename");
	else
	{
		$db->DoQuery("SELECT tablename FROM welllogs WHERE startdepth>$depth ORDER BY startmd DESC LIMIT 1;");
		if($db->FetchRow()) $tablename=$db->FetchField("tablename");
		else
		{
			$db->DoQuery("SELECT tablename FROM welllogs WHERE startdepth<$depth ORDER BY startmd DESC LIMIT 1;");
			if($db->FetchRow()) $tablename=$db->FetchField("tablename");
		}
	}
	$editmode='';
}	// perform edits requested
else if(($editmode == 'align' || $editmode == 'trim') && $tablename != '')
{
	$db->DoQuery("SELECT * FROM welllogs WHERE tablename='$tablename'");
	if ($db->FetchRow())
	{
		$sectfault=$db->FetchField('fault');
		if($editmode == "align")
		{
			SetAlign($tablename, $depth, $sectfault);
			$editmode = "redraw";
		}
		if($editmode == "trim")
		{
			SetTrim($tablename, $depth, $sectfault);
			$editmode = "redraw";
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
	if($editmode == 'redraw' || $dir == 'first')
	{
		exec ("./sses_gva -d $seldbname -s $gvastart");
		$db->DoQuery("SELECT * FROM welllogs WHERE tablename='$tablename';");
		if($db->FetchRow())
		{
			$startdepth=$db->FetchField('startdepth');
			$enddepth=$db->FetchField('enddepth');
			$secttot=$db->FetchField('tot');
			$sectbot=$db->FetchField('bot');
			$startdepth=(int)($startdepth);
			$enddepth=(int)($enddepth);
		}
	}
}
else
{
	// echo "no table selected";
	$tableid=-1;
	$startdepth='0.0';
	$enddepth='0.0';
	$startmd='0.0';
	$endmd='0.0';
	$starttvd='0.0';
	$endtvd='0.0';
	$startvs='0.0';
	$endvs='0.0';
	$secttot='0.0';
	$sectbot='0.0';
	$sectfault='';
	$sectdip='';
	$bias='';
	$realname='';
	$factor='';
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
	else if(isset($forcesel) && $forcesel<1 && $sgtastart!="" && $sgtaend!="" && $sgtacutin!="" && $sgtacutoff!="") {
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

if($viewdspcnt > 0){
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

$fn=sprintf("./tmp/%s_gva_realtime.png", $seldbname);
$fnsnap=sprintf("./tmp/%s_gva_realtimesnap.png", $seldbname);
$snaph=900;
$snapw=600;
$logsw=""; if($uselogscale>0)	$logsw="-log";
if($sgta_show_forms){
	$addformsstr = '-aforms';
} else {
	$addformsstr='';
}

$timelog[] = array(microtime(),'before generating editor image');

$result = array();

if($viewallds>0) {

	// $retstr=array(); $retval=0;
	$com = "./sses_gpd -d $seldbname " .
			"-s $plotstart -e $plotend " .
			"-cld -wld -wlid $tableid " .
			"-ci $cutinMD -co $cutoffMD " .
			"-avg $dataavg -r $scaleright $logsw $addformsstr";
	exec ($com." -w $pwidth -h $pheight -o $fn",$result);
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
	exec ($com." -w $pwidth -h $pheight -o $fn",$result);
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
$faultmod = isset($_POST['faultmod']) and $_POST['faultmod'] != '' ? $_POST['faultmod'] : 0;
$timelog[] = array(microtime(),'fault log is ' . $faultmod . ' dspoffset is ' . $dspoffset);
if(!isset($cmd)) $cmd = '';
if($viewdspcnt>0) {
	$fn2=sprintf("./tmp/%s_gva_realtime1.png", $seldbname);

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
	$timelog[] = array(microtime(),$cmd);
	$db->DoQuery("SELECT fault FROM welllogs WHERE endmd<=$d ORDER BY endmd DESC LIMIT $viewdspcnt;");
	while($db->FetchRow()){$dbfault=$db->FetchField('fault');} // fetch the first fault value
}

$editmode="";
$random=md5(uniqid(rand(),1));
$timestop=microtime_float();
$timelapse=$timestop-$timestart;
$timelog[] = array(microtime(),'right before html, timelapse = ' . $timelapse);

// get the display value of the SGTA main section and that of the different sub-sections

$db->DoQuery("select cvalue from adm_config where cname = 'show_sgta_graph' limit 1");
$show_sgta_graph = 'Yes';
if($db->FetchRow()) $show_sgta_graph = $db->FetchField('cvalue');
else $db->DoQuery("insert into adm_config (cname,cvalue) values ('show_sgta_graph','Yes')");

$db->DoQuery("select cvalue from adm_config where cname = 'show_sgta_forms' limit 1");
$show_sgta_forms = 'Yes';
if($db->FetchRow()) $show_sgta_forms = $db->FetchField('cvalue');
else $db->DoQuery("insert into adm_config (cname,cvalue) values ('show_sgta_forms','Yes')");

$db->DoQuery("select cvalue from adm_config where cname = 'show_data_modeling' limit 1");
$show_data_modeling = 'Yes';
if($db->FetchRow()) $show_data_modeling = $db->FetchField('cvalue');
else $db->DoQuery("insert into adm_config (cname,cvalue) values ('show_data_modeling','Yes')");

$db->DoQuery("select cvalue from adm_config where cname = 'show_shadow_section' limit 1");
$show_shadow_section = 'Yes';
if($db->FetchRow()) $show_shadow_section = $db->FetchField('cvalue');
else $db->DoQuery("insert into adm_config (cname,cvalue) values ('show_shadow_section','Yes')");

$db->DoQuery("select cvalue from adm_config where cname = 'show_exports' limit 1");
$show_exports = 'Yes';
if($db->FetchRow()) $show_exports = $db->FetchField('cvalue');
else $db->DoQuery("insert into adm_config (cname,cvalue) values ('show_exports','Yes')");

$db->DoQuery("select cvalue from adm_config where cname = 'show_view_scaling' limit 1");
$show_view_scaling = 'Yes';
if($db->FetchRow()) $show_view_scaling = $db->FetchField('cvalue');
else $db->DoQuery("insert into adm_config (cname,cvalue) values ('show_view_scaling','Yes')");

$db->DoQuery("select cvalue from adm_config where cname = 'show_plot_right_to_left' limit 1");
$show_plot_right_to_left = 'Yes';
if($db->FetchRow()) $show_plot_right_to_left = $db->FetchField('cvalue');
else $db->DoQuery("insert into adm_config (cname,cvalue) values ('show_plot_right_to_left','Yes')");
?>
<!doctype html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="gva_tab8.css" />
<link rel="stylesheet" type="text/css" href="waitdlg.css" />
<title><?php echo $dbrealname ?>-SGTA Editor<?php echo " ($seldbname)"; ?></title>
<style>
#clickimage[src=''] {
	display:none;
}
</style>
<script src='//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js'></script>
<script language='javascript' type='text/javascript' src='waitdlg.js'></script>
<script language='javascript' type='text/javascript' src='gva_tab8.js?v=1.5'></script>
<script>
var current_rt_depth = 0;
var stream_dp_count=0;
var current_rt_id = 0;
var my_dbfault = <?php echo $dbfault ?>;
var my_dbname = '<?php echo $seldbname ?>';

function save_dip(el) {
    id = el.id.split("_")[1]
	window.location="surveyfaultdipupdate.php?seldbname=<?php echo $seldbname
	?>&ret=<?php echo urlencode($ret) ?>&action=dip&value="+el.value+"&id="+id
}

function save_fault(el) {
	id = el.id.split("_")[1]
	window.location="surveyfaultdipupdate.php?seldbname=<?php echo $seldbname
	?>&ret=<?php echo urlencode($ret) ?>&action=fault&value="+el.value+"&id="+id
}

function brJsonRunData(json_data)
{
    $.getJSON('rundat.php',json_data,function(data) {
		if(data.res == 'ERR') alert('ERROR: ' + data.msg);
	}).fail(function( jqxhr, textStatus, error ) {
		alert('Request Failed: ' + textStatus + ", " + error);
	});
}

function streamPolling(){
	if($('#pause-stream').is(":visible")){	
		 $.getJSON('/sses/json/getghostgamma.php',
		 "seldbname=<?=$seldbname?>&depth="+current_rt_id,
		function(data) {
			if(data.res == 'ERR') alert('ERROR: ' + data.msg);
			$.each( data, function( key, val ) {
	    		current_rt_id=val.id
	    		stream_dp_count=stream_dp_count+1;
	    		$("#streaming-data-table tr:first").after('<tr class="surveys"><td class="surveys">'+val.tvd+'</td><td class="surveys">'+val.md+'</td><td class="surveys">'+val.value+'</td></tr>');
	 		 });
	 		 $("#streaming-data-count").text(stream_dp_count);
		}).fail(function( jqxhr, textStatus, error ) {
			alert('Request Failed: ' + textStatus + ", " + error);
		});
	}
	//setTimeout(streamPolling,10000);
}
setTimeout(streamPolling, 1000);
$(document).ready(function() {
	
	$('#pause-stream').click(function(){
		$('#pause-stream').hide()
		$('#play-stream').show()
		$.getJSON("/sses/json/rt_stream_pause.php","seldbname=<?php echo $seldbname; ?>",function(data){});
	})
	$('#play-stream').click(function(){
		$('#play-stream').hide()
		$('#pause-stream').show()
		$('#disable-ghost').hide()
		$('#enable-ghost').show()
		$('#streaming-data').show()
		$.getJSON("/sses/json/rt_disable_ghost.php","seldbname=<?php echo $seldbname; ?>",function(data){});
		$.getJSON("/sses/json/rt_stream_play.php","seldbname=<?php echo $seldbname; ?>",function(data){});
	})
	$('#enable-ghost').click(function(){
		$('#streaming-data').hide()
		$('#pause-stream').hide()
		$('#play-stream').show()
		$('#enable-ghost').hide()
		$('#disable-ghost').show()	
		$.getJSON("/sses/json/rt_stream_pause.php","seldbname=<?php echo $seldbname; ?>",function(data){});
		$.getJSON("/sses/json/rt_enable_ghost.php","seldbname=<?php echo $seldbname; ?>",function(data){});
	})
	$('#disable-ghost').click(function(){
		$('#streaming-data').show()
		$('#disable-ghost').hide()	
		$('#enable-ghost').show()
		$.getJSON("/sses/json/rt_disable_ghost.php","seldbname=<?php echo $seldbname; ?>",function(data){});	
	})
	$('#hide-whole-graph').click(function () {
		$('#the-whole-graph').fadeOut();
		$('#blank-whole-graph').fadeIn();
		$('#whole-graph-td').width('30px');
		brJsonRunData({'sdb':my_dbname,'a':'setconf','n':'show_sgta_graph','v':'No'});
	});
	$('#show-whole-graph').click(function () {
		$('#blank-whole-graph').fadeOut();
		$('#the-whole-graph').fadeIn();
		$('#whole-graph-td').width('740px');
		brJsonRunData({'sdb':my_dbname,'a':'setconf','n':'show_sgta_graph','v':'Yes'});
	});
	$('#hide-sgta-forms').click(function () {
		$('#the-sgta-forms').fadeOut();
		$('#blank-sgta-forms').fadeIn();
		$('#sgta-forms-td').width('30px');
		brJsonRunData({'sdb':my_dbname,'a':'setconf','n':'show_sgta_forms','v':'No'});
	});
	$('#show-sgta-forms').click(function () {
		$('#blank-sgta-forms').fadeOut();
		$('#the-sgta-forms').fadeIn();
		$('#sgta-forms-td').width('330px');
		brJsonRunData({'sdb':my_dbname,'a':'setconf','n':'show_sgta_forms','v':'Yes'});
	});
	$('#toggle-data-modeling').click(function() {
		if($('#data-modeling-body').is(":visible"))
			brJsonRunData({'sdb':my_dbname,'a':'setconf','n':'show_data_modeling','v':'No'});
		else brJsonRunData({'sdb':my_dbname,'a':'setconf','n':'show_data_modeling','v':'Yes'});
		$('#data-modeling-body').fadeToggle();
	});
	$('#toggle-view-scaling').click(function() {
		if($('#view-scaling-body').is(":visible"))
			brJsonRunData({'sdb':my_dbname,'a':'setconf','n':'show_view_scaling','v':'No'});
		else brJsonRunData({'sdb':my_dbname,'a':'setconf','n':'show_view_scaling','v':'Yes'});
		$('#view-scaling-body').fadeToggle();
	});
	$('#toggle-exports').click(function() {
		if($('#exports-body').is(":visible"))
			brJsonRunData({'sdb':my_dbname,'a':'setconf','n':'show_exports','v':'No'});
		else brJsonRunData({'sdb':my_dbname,'a':'setconf','n':'show_exports','v':'Yes'});
		$('#exports-body').fadeToggle();
	});
	$('#toggle-shadow-section').click(function() {
		if($('#shadow-section-body').is(":visible"))
			brJsonRunData({'sdb':my_dbname,'a':'setconf','n':'show_shadow_section','v':'No'});
		else brJsonRunData({'sdb':my_dbname,'a':'setconf','n':'show_shadow_section','v':'Yes'});
		$('#shadow-section-body').fadeToggle();
	});
	$('#plot-direction').click(function() {
		if($('#lateral-plot').css('direction') == 'rtl') {
			$('#lateral-plot').css('direction','ltr');
			$('#plot-direction').text('Show Right to Left');
			brJsonRunData({'sdb':my_dbname,'a':'setconf','n':'show_plot_right_to_left','v':'No'});
		}
		else {
			$('#lateral-plot').css('direction','rtl');
			$('#plot-direction').text('Show Left to Right');
			brJsonRunData({'sdb':my_dbname,'a':'setconf','n':'show_plot_right_to_left','v':'Yes'});
		}

	
	});
<?php
	if($show_sgta_graph == 'Yes') echo "	$('#blank-whole-graph').hide();\n";
	else
	{
		echo "	$('#the-whole-graph').hide()\n";
		echo "	$('#whole-graph-td').width('30px');\n";
	}

	if($show_sgta_forms == 'Yes') echo "	$('#blank-sgta-forms').hide();\n";
	else
	{
		echo "	$('#the-sgta-forms').hide()\n";
		echo "	$('#sgta-forms-td').width('30px');\n";
	}

	if($show_data_modeling == 'No') echo "	$('#data-modeling-body').hide();\n";
	if($show_shadow_section == 'No') echo "	$('#shadow-section-body').hide();\n";
	if($show_view_scaling == 'No') echo "	$('#view-scaling-body').hide();\n";
	if($show_exports == 'No') echo "	$('#exports-body').hide();\n";
?>
});
</script>
</head>
<body onload="<?php echo "Init({$viewrotds},{$scrolltop},{$scrollleft})" ?>">
<?php
//echo "<div>com = $com -w $pwidth -h $pheight -o $fn</div>\n";
//echo "<pre>"; print_r($result); echo "</pre>\n";
$timelog[] = array(microtime(),'right after the body');
$maintab=8;
include 'apptabs.inc.php';
include 'waitdlg.html';
?>
<table class='surveys'>
<tr> 
<th colspan='12' style='text-align:center'>Survey Data</th>
<th colspan='4' style='text-align:center'>Target Tracker Section</th>
</tr>
<tr> 
<th class='surveys'>Svy</th>
<th class='surveys'>Depth</th>
<th class='surveys'>Inc</th>
<th class='surveys'>Azm</th>
<th class='surveys'>TVD</th>
<th class='surveys'>VS</th>
<th class='surveys'>NS</th>
<th class='surveys'>EW</th>
<?php
if($showxy == 1)
{
	echo "<th class='surveys'>Northing</th>";
	echo "<th class='surveys'>Easting</th>";
}
else
{
	echo "<th class='surveys'>CD</th>";
	echo "<th class='surveys'>CA</th>";
}
?>
<th class='surveys'>DL</th>
<th class='surveys'>CL</th>
<th class='surveys'>TF</th>
<th class='rot'>TCL</th>
<th class='rot'>Pos-TCL</th>
<th class='rot'>TOT</th>
<th class='rot'>BOT</th>
<th class='rot'>Dip</th>
<th class='rot'>Fault</th>
</tr>
<?
//if($surveysort == 'DESC') PrintProjections();
//PrintSurveys();
//if($surveysort == 'ASC') PrintProjections();
?>
</table>


<div class='tabcontainer'>

	<!-- start of top table -->

	<table style='table-layout:fixed;width:100%;border-collapse:collapse'>
	<tr>
	<td id='whole-graph-td' style='vertical-align:top;width:740px'>

	<div id='blank-whole-graph'>
		<div id='show-whole-graph'>+/-</div>
		<div id='blank-whole-graph-title'>SGTA Graph</div>
	</div>

	<div id='the-whole-graph'>
		<div id='the-graph-scale' style='padding-left:22px;width:705px'>
			<div> 
				<div style='width:50%;float:left;text-align:left'>
					<?php if($viewrotds==0) echo '0'; else echo '.'; ?>
				</div>
				<div style='width:50%;float:left;text-align:right'>
					<?php if($viewrotds==0) echo $scaleright; else echo '.'; ?> <span id='hide-whole-graph'>+/-</span>
				</div>
				<div style='clear:both'></div>
			</div>
		</div>

		<!-- start of 'the-graph' div -->

		<div id='the-graph'>

			<div style='width:16px;min-height:100px;float:left'><?php
				if($viewrotds==1) echo $scaleright; else echo ' '; ?></div>

			<!-- start of graph div -->

			<div style='float:left;height:700px;background-color:white;border:1px solid gray;text-align:left'>

				<!-- start of pointform form (using old code) -->

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
	if(isset($fn2) and $fn2 != '') echo ";background: url('{$fn2}?{$random}') no-repeat";
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
		<img id='clickimage' src='<?php
if($tableid > 0 and $endmd > 0 and $fn != '') echo "{$fn}?{$random}";
?>' onmouseup='showvalue(event, this)' onclick='getpoint(event, this)'><?php
if(!($tableid > 0 and $endmd > 0 and $fn != '')) {
	echo "			<p style='padding-left:20px;color:#6666ff'>No well log data found</p>\n";
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
	</form> <!-- end of pointform (using old code) --> 

			</div> <!-- end of graph div -->

			<div style='clear:both'></div>

		</div> <!-- end of the-graph div -->

	</div> <!-- end of the-whole-graph div -->

	</td>

	<td id='sgta-forms-td' style='vertical-align:top;width:330px;white-space:nowrap'>

	<div id='blank-sgta-forms'>
		<div id='show-sgta-forms'>+/-</div>
		<div id='blank-sgta-forms-title'>Graph Controls</div>
	</div>
	
	<!-- start of the-sgta-forms div -->
	
	<div id='the-sgta-forms'>
	<div id='hide-sgta-forms'>+/-</div>
	<div id="sgta-rt-controls-div">
	<div class='settings'>
		<table>
			<tr><td>
					<div id="pause-stream" style="float:left">
					<button>
					<div><img width=18 height=18 src="/sses/imgs/pause.png"></div>
					<div>Pause</div>
					</button> 
					</div>
					<div id="play-stream" style="display:none;float:left">
					<button>
					<div><img width=18 height=18 src="/sses/imgs/play.png"></div>
					<div>Play</div>
					</button> 
					</div></td><td>
					<div  id="enable-ghost" style="float:left;position:relative;cursor:pointer">
					<button>
					<div style="position:relative;"><img width=18 height=18 src="/sses/imgs/ghost.png"></div>
					<div>Enable Ghost</div> 
					</div>
					</button>
					<div  id="disable-ghost" style="display:none;float:left;position:relative;cursor:pointer">
					<button>
					<div style="position:relative;"><img width=18 height=18 src="/sses/imgs/ghost.png"></div>
					<div style="position:absolute;top:2px;left:40px;"><img width=18 height=18 src="/sses/imgs/cancel.png"></div>
					<div>Disable Ghost</div> 
					</div>
					</button>
					
			</td></tr>
			<tr><td><div>Manual Gamma Import</div>
				<div>
						<FORM method="post" enctype="multipart/form-data">
		<b>File to import from:</b>
		<br>
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
		<INPUT type="file" name="userfile" size="70">
		<INPUT type="submit" value="Import">
		</form>
				</div>
				
			</td></tr>
			<tr><td>data points:</td><td><div id="streaming-data-count"></div></td></tr>
			<tr id="streaming-data"><td colspan=2><div style="height:400px;overflow:scroll;width:300px">
				<table id="streaming-data-table" style="width:280px;"  class='surveys'>
					<tr class='surveys'><th class='surveys'>TVD</th><th class='surveys'>MD</th><th class='surveys'>GAMMA</th></tr>
				</table>
			</div></td></tr>
		</table>
	
	</div>
	<br>
	</div>
	
	<!-- start of data sections div -->

	<div class='settings'>
	<div class='header fbold' style='padding:2px'>Data Sections - <?php echo $wellname ?></div>

	<!-- start of data sections top buttons -->

	<div>

	<div class='left_20 tcenter'>
		<form action='changeds.php' method='post'>
		<input type='hidden' name='ret' value='gva_tab8.php'>
		<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
		<input type='hidden' name='tablename' value='<?php echo $tablename?>'>
		<input type='hidden' name='startmd' value='<?php echo $startmd?>'>
		<input type='hidden' name='endmd' value='<?php echo $endmd?>'>
		<input type='hidden' name='zoom' value='<?php echo $zoom; ?>'>
		<input type='hidden' name='dir' value='first'>
		<input type="submit" value="First">
		</form>
	</div>
	<div class='left_20 tcenter'>
		<form action='changeds.php' method='post'>
		<input type='hidden' name='ret' value='gva_tab8.php'>
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
		<input type="submit" value="Prev" onclick='SetScroll(this.form)'>
		</form>
	</div>
	<div class='left_20 tcenter'>
		<form action='changeds.php' method='post'>
		<input type='hidden' name='ret' value='gva_tab8.php'>
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
		<input type="submit" value="Next" onclick='SetScroll(this.form)'>
		</form>
	</div>
	<div class='left_20 tcenter'>
		<form action='changeds.php' method='post'>
		<input type='hidden' name='ret' value='gva_tab8.php'>
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
		<input type="submit" value="Last" onclick='SetScroll(this.form);'>
		</form>
	</div>
	<div class='left_20 tcenter'>
		<form method="post">
		<input type='hidden' name='ret' value='gva_tab8.php'>
		<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
		<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
		<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
		<input type='hidden' name='zoom' value='<?php echo $zoom; ?>'>
		<input type="submit" value="Import" ONCLICK="OnLasImport(this.form)">
		</form>
	</div>
	<div style='clear:both'></div>
	</div> <!-- end of data sections top buttons -->

	<!-- start of file section -->

	<div>
		<div class='tcenter fbold' style='padding:2px 0'>
			File: <?php echo " $realname ($tablename)"; ?>
		</div>
		<div>
			<div class='left_25 tright fbold'>MD:</div>
			<div class='left_30 tright'> <?php printf("%9.2f", $startmd) ?></div>
			<div class='left_10 tcenter fbold'>to</div>
			<div class='left_25 tright'> <?php printf("%9.2f", $endmd) ?></div>
			<div style='clear:both'></div>
		</div>
		<div>
			<div class='left_25 tright fbold'>TVD:</div>
			<div class='left_30 tright'> <?php printf("%9.2f", $starttvd) ?></div>
			<div class='left_10 tcenter fbold'>to</div>
			<div class='left_25 tright'> <?php printf("%9.2f", $endtvd) ?></div>
			<div style='clear:both'></div>
		</div>
		<div>
			<div class='left_25 tright fbold'>VS:</div>
			<div class='left_30 tright'> <?php printf("%9.2f", $startvs) ?></div>
			<div class='left_10 tcenter fbold'>to</div>
			<div class='left_25 tright'> <?php printf("%9.2f", $endvs) ?></div>
			<div style='clear:both'></div>
		</div>
		<div>
			<div class='left_25 tright fbold'>TOT/BOT:</div>
			<div class='left_30 tright'> <?php printf("%9.2f", $secttot) ?></div>
			<div class='left_10 tcenter fbold'>to</div>
			<div class='left_25 tright'> <?php printf("%9.2f", $sectbot) ?></div>
			<div style='clear:both'></div>
		</div>
		<div class='tcenter' style='padding:4px 0'>
			<form action='changeds.php' method='post'>
			<input type='hidden' name='ret' value='gva_tab8.php'>
			<input type="hidden" name='seldbname' value='<?php echo $seldbname ?>'>
			<input type='hidden' name='dir' value='directmd'>
			Go to dataset at <input type='text' size='4' name='startmd' value=''> MD
			</form>
		</div>
		<div class='tleft' style='padding:2px 0 2px 20px'>
			<form method='post'>
			<input type='hidden' name='ret' value='gva_tab8.php'>
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
				if($viewallds>1) echo " checked='true'" ?> onclick="OnViewDS(this.form)">View previous 
			<input type='text' size='7' name='viewallds' value='<?php
				if($viewallds<=1) echo "500"; else echo $viewallds; ?>' <?php
				if($viewallds<=1) echo "readonly='true'"; ?> onchange="OnViewDS(this.form)"> MD
			<br>
			<input type="radio" name='viewallds' value='1' <?php
				if($viewallds==1) echo " checked='true'" ?> onclick='OnViewDS(this.form)'>View All Datasets<br>
			<input type="radio" name='viewallds' value='0' <?php
				if($viewallds==0) echo " checked='true'" ?> onclick='OnViewDS(this.form)'>View Only Selected
			</form>
		</div>
		<form method='post'>
		<input type='hidden' name='ret' value='gva_tab8.php'>
		<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
		<input type='hidden' name='tablename' value='<?php echo $tablename?>'>
		<input type='hidden' name='scrolltop' value='<?php echo $scrolltop; ?>'>
		<input type='hidden' name='scrollleft' value='<?php echo $scrollleft; ?>'>
		<input type='hidden' name='viewrotds' value='<?php echo $viewrotds?>'>
		<input type='hidden' name='viewallds' value='<?php echo $viewallds?>'>
		<input type='hidden' name='viewdspcnt' value='<?php echo $viewdspcnt?>'>
		<div class='tleft' style='padding:2px 0 2px 20px'>
			<input type="checkbox" name='viewrot' <?php
				if($viewrotds>=1) echo " checked='true' "; ?> value="<?php echo $viewrotds; ?>" onclick="OnRotateDS(this.form)">Rotate Dataset
		</div>
		</form>
	</div> <!-- end of file section div -->

	<!-- start of csv file import div -->

	<div class='tcenter' style='padding:2px 0;border-top:1px solid black'>
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
	</div> <!-- end of csv file import div -->

	</div> <!-- end of data sections div -->

	<!-- start of data modeling div -->

	<div class='settings' style='margin-top:5px'>
	<div class='header fbold' style='padding:2px'>
		<div class='left_50'>Data Modeling</div>
		<div id='toggle-data-modeling' class='left_50 tright'>+/-</div>
		<div style='clear:both'></div>
	</div>

	<div id='data-modeling-body'>
		<form method='post' id='dipform'>
		<input type='hidden' name='ret' value='gva_tab8.php'>
		<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
		<input type='hidden' name='tablename' value='<?php echo $tablename?>'>
		<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
		<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>

		<div>
			<div class='left_25 tright' style='padding-top:4px'>Fault:</div>
			<div class='left_20 tcenter'><input type="text" size="4" name="sectfault" value="<?php
				echo $sectfault?>" onchange='setdscfg(this.form)'></div>
			<div class='left_50 tleft'>
				<input type='button' value="+" onClick="faultupdown(this.form, 1)">
				<input type='button' value="-" onClick="faultupdown(this.form, -1)">
			</div>
			<div style='clear:both'></div>
		</div>

		<div>
			<div class='left_25 tright' style='padding-top:4px'>Dip:</div>
			<div class='left_20 tcenter'><input id='sectdip_parent' type="text" size="4" name="sectdip" value="<?php
				echo $sectdip ?>" onchange='setdscfg(this.form)'></div>
			<div class='left_50 tleft'>
				<input type=button value="+" onClick="dipupdown(this.form, 1)">
				<input type=button value="-" onClick="dipupdown(this.form, -1)">
				<input type=button value='Auto Dip' onClick="window.open('sgtamodeling_autodip.php?seldbname=<?php
					echo $seldbname ?>','sgtaavgpopup','width=700,height=400,left=200,scrollbars=yes');">
			</div>
			<div style='clear:both'></div>
		</div>

		<div>
			<div class='left_25 tright' style='padding-top:4px'>Bias:</div>
			<div class='left_20 tcenter'><input type='text' size='4' name='bias' value="<?php
				printf('%.0f', $bias); ?>" onchange='setdscfg(this.form)'></div>
			<div class='left_50 tleft'>
				<input type=button value="Left" onClick="biasupdown(this.form, -10)">
				<input type=button value="Right" onClick="biasupdown(this.form, 10)">
			</div>
			<div style='clear:both'></div>
		</div>

		<div>
			<div class='left_25 tright' style='padding-top:4px'>Scale:</div>
			<div class='left_20 tcenter'><input type='text' size='4' name='factor' value="<?php
				printf('%.2f', $factor); ?>" onchange='setdscfg(this.form)'></div>
			<div class='left_50 tleft'>
				<input type=button value="+" onClick="scaleupdown(this.form, 0.1)">
				<input type=button value="-" onClick="scaleupdown(this.form, -.1)">
			</div>
			<div style='clear:both'></div>
		</div>

		</form>

		<div style='margin-top:4px'>
			<div class='left_33 tcenter'>
				<input type="submit" id='btntrim' value="Trim Section" onclick="setTrimMode()">
			</div>
			<div class='left_33 tcenter'>
				<input type="submit" id="btnalign" value="Align Section" onclick="setAlignMode()">
			</div>
			<div class='left_33 tcenter'>
				<form name='deleteds' method='post'>
				<input type='hidden' name='ret' value='gva_tab8.php'>
				<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
				<input type='hidden' name='tn' value='<?php echo $tablename?>'>
				<input type='hidden' name='depth' value='<?php echo $startmd?>'>
				<input type='hidden' name='editmode' value='search'>
				<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
				<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
				<input type='hidden' name='sgtastart' value='<?php echo $sgtastart?>'>
				<input type='hidden' name='sgtaend' value='<?php echo $sgtaend?>'>
				<input type='hidden' name='sgtacutin' value='<?php echo $sgtacutin?>'>
				<input type='hidden' name='sgtacutoff' value='<?php echo $sgtacutoff?>'>
				<input type="submit" value="DELETE DS" onclick="OnDeleteDS()">
				</form>
			</div>
			<div style='clear:both'></div>
		</div>

	</div> <!-- end of data modeling body -->

	</div> <!-- end of data modeling div -->

	<!-- start of shadow section div -->

	<div class='settings' style='margin-top:5px'>
	<div class='header fbold' style='padding:2px'>
		<div class='left_60'>Shadow Section Modeling</div>
		<div id='toggle-shadow-section' class='left_40 tright'>+/-</div>
		<div style='clear:both'></div>
	</div>

	<div id='shadow-section-body'>

	<form method='post'>
	<input type='hidden' name='ret' value='gva_tab8.php'>
	<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
	<input type='hidden' name='tablename' value='<?php echo $tablename?>'>
	<input type='hidden' name='scrolltop' value='<?php echo $scrolltop; ?>'>
	<input type='hidden' name='scrollleft' value='<?php echo $scrollleft; ?>'>
	<input type='hidden' name='viewrotds' value='<?php echo $viewrotds?>'>
	<input type='hidden' name='viewallds' value='<?php echo $viewallds?>'>
	<div class='tcenter' style='padding:2px 0'>
		Show shadow of last <input type='text' size='3' name='viewdspcnt' value='<?php
			echo $viewdspcnt?>' onchange="OnViewDS(this.form)"> modeled datasets
	</div>
	</form>
 
<?php
if($viewdspcnt > 0)
{
	$enable=" ";
	if($dscache_freeze > 0)
	{
		$enable=" disabled='true' ";
	}
?>

	<form action='setdscache.php' method='post'>

	<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
	<input type='hidden' name='ret' value='gva_tab8.php'>
	<input type='hidden' name='tablename' value='<?php echo $tablename?>'>
	<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
	<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
	<input type="hidden" name="endmd" value="<?php echo "$endmd"; ?>">
	<input type="hidden" name="dscache_md" value="<?php echo $endmd; ?>">
	<input type="hidden" name="dscache_plotstart" value="<?php echo "$plotstart"; ?>">
	<input type="hidden" name="dscache_plotend" value="<?php echo "$plotend"; ?>">
	<input type='hidden' name='dscache_freeze' value='<?php echo $dscache_freeze?>'>
	<input type="hidden" name='dscache_fault' value="<?php echo $dscache_fault?>">
	<input type="hidden" name='dsholdfault' value="<?php echo $dsholdfault?>">
	<input type='hidden' name='faultmod' value=''>

	<div class='left_55'>
	<div style='border:1px solid gray;margin:2px;padding:2px'>

	<div>
		<div class='left_50 tright'>Dip:
			<input type="text" size="3" name="dscache_dip" value="<?php echo $dscache_dip ?>" onchange='setdscache(this.form)'></div>
		<div class='left_50 tleft'><div style='padding-left:10px'>
			<input type=button <?php echo $enable?> value="+" onClick="setdscache(this.form, 'dip', 1)">
			<input type=button <?php echo $enable?> value="-" onClick="setdscache(this.form, 'dip', -1)">
		</div></div>
		<div style='clear:both'></div>
	</div>

	<div>
		<div class='left_50 tright'>Bias: <input type='text' size='3' name='dscache_bias' value="<?php
			printf('%.0f', $dscache_bias); ?>" onchange='setdscache(this.form)'></div>
		<div class='left_50 tleft'><div style='padding-left:10px'>
			<input type=button value="<" onClick="setdscache(this.form, 'bias', -10)">
			<input type=button value=">" onClick="setdscache(this.form, 'bias', 10)">
		</div></div>
		<div style='clear:both'></div>
	</div>

	<div>
		<div class='left_50 tright'>Scale: <input type='text' size='3' name='dscache_scale' value="<?php
			printf('%.2f', $dscache_scale); ?>" onchange='setdscache(this.form)'></div>
		<div class='left_50 tleft'><div style='padding-left:10px'>
			<input type=button value="+" onClick="setdscache(this.form, 'scale', 0.1)">
			<input type=button value="-" onClick="setdscache(this.form, 'scale', -0.1)">
		</div></div>
		<div style='clear:both'></div>
	</div>

	</div>
	</div>

	<div class='left_45 tcenter'>
		<input type='button' value='Reset Calc Fault' onclick="setdscache(this.form,'reset')">
		<div style='padding:3px 0'>
			<div class='left_60 tright' style='padding-top:3px'>Freeze:</div>
			<div class='left_33 tleft'>&nbsp;<input type='checkbox' name='freeze' value='<?php echo
			$dscache_freeze; ?>' <?php
				if($dscache_freeze == 1) echo " checked='true' "; ?> onchange="setdscache(this.form,'freeze')"></div>
			<div style='clear:both'></div>
		</div>
		<?php if($dscache_freeze==1) { ?>
		<div style='padding:3px 0'>
			<div class='left_60 tright' style='padding-top:3px'>Hold Fault:</div>
			<div class='left_33 tleft'>&nbsp;<input type='checkbox' name='holdfault' value='1' <?php
			if($dsholdfault > 0) echo " checked='true'"; ?> onchange="setdscache(this.form,'holdfault')"></div>
			<div style='clear:both'></div>
		</div>
		<?php } ?>
	</div>

	<div style='clear:both'></div>

	</form>

	<form id='savedscache' method='post' style='padding: 0 0; margin: 0 0;'>
	<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
	<input type='hidden' name='ret' value='gva_tab8.php'>
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
	</form>

	<div class='tcenter'>
		<div class='left_50 tcenter'><input type='submit' <?php
			if($dscache_freeze == 0) echo " disabled='true '" ?> value='Save Dip' onclick="savedscache(0)"></div>
		<div class='left_50 tcenter'><input type='submit' <?php
			if($dscache_freeze == 0) echo " disabled='true '" ?> value='Save Fault' onclick="savedscache(1)"></div>
		<div style='clear:both'></div>
	</div>

	<div class='tcenter'>
		<input size='3' readonly='true' type='hidden' name='dbgscrolldist' id='dbgscrolldist' value="">
		<div class='left_50 tcenter'>Current Fault: <input size='5' readonly='true' type='text' value="<?php echo $dbfault ?>"></div>
		<div class='left_50 tcenter'>Calculated Fault:
			<input size='5' readonly='true' type='text' name='dbgscrollfault' id='dbgscrollfault' value="<?php
			echo ($dbfault+$dscache_fault) ?>"></div>
		<div style='clear:both'></div>
	</div>
<?php
}
?>

	</div> <!-- end of shadow section body -->

	</div> <!-- end of shadow section div -->

	<!-- start of view scaling div -->

	<div class='settings' style='margin-top:5px'>
	<div class='header fbold' style='padding:2px'>
		<div class='left_50'>View Scaling</div>
		<div id='toggle-view-scaling' class='left_50 tright'>+/-</div>
		<div style='clear:both'></div>
	</div>

	<div id='view-scaling-body'>

	<form action='setplotcfg.php' method='post'>
	<input type='hidden' name='ret' value='gva_tab8.php'>
	<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
	<input type='hidden' name='tablename' value='<?php echo $tablename?>'>
	<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
	<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
	<input type='hidden' name='sgtastart' value='<?php echo $sgtastart?>'>
	<input type='hidden' name='sgtaend' value='<?php echo $sgtaend?>'>
	<input type='hidden' name='sgtacutin' value='<?php echo $sgtacutin?>'>
	<input type='hidden' name='sgtacutoff' value='<?php echo $sgtacutoff?>'>
	<input type='hidden' name='uselogscale' value='<?php echo $uselogscale?>'>

	<div>
		<div class='left_40 tright'>Plot bias: <input type='text' size='3' name='plotbias' value="<?php
			printf('%.0f', $plotbias); ?>" onchange='OnSetPlotCfg(this.form);'></div>
		<div class='left_55 tleft'><div style='padding-left:10px'>
			<input type=button value="Left" onClick="plotbiasupdown(this.form, -10)">
			<input type=button value="Right" onClick="plotbiasupdown(this.form, 10)">
		</div></div>
		<div style='clear:both'></div>
	</div>

	<div>
		<div class='left_40 tright'>Data Scale: <input type='text' size='3' name='scaleright' value='<?php
			echo $scaleright; ?>' onchange='OnSetPlotCfg(this.form);'></div>
		<div class='left_55 tleft'><div style='padding-left:10px'><input type='checkbox' <?php
			if($uselogscale!=0) echo " checked='true' "; ?> id='lscb' name='lscb' onclick='OnLogScale(this.form);'> Logarithmic Scale
		</div></div>
		<div style='clear:both'></div>
	</div>
				
	<div>
		<div class='left_40 tright'>Data Average: <input size='3' type='text' name='dataavg' value="<?php
			echo $dataavg?>" onchange='OnSetPlotCfg(this.form);'></div>
		<div style='clear:both'></div>
	</div>

	</form>

	<form method='post'>
	<input type='hidden' name='ret' value='gva_tab8.php'>
	<input type='hidden' name='scrolltop' value='<?php echo $scrolltop?>'>
	<input type='hidden' name='scrollleft' value='<?php echo $scrollleft?>'>
	<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
	<input type='hidden' name='sgtastart' value='<?php echo $sgtastart?>'>
	<input type='hidden' name='sgtaend' value='<?php echo $sgtaend?>'>
	<input type='hidden' name='sgtacutin' value='<?php echo $sgtacutin?>'>
	<input type='hidden' name='sgtacutoff' value='<?php echo $sgtacutoff?>'>
	<input type='hidden' name='zoom' value='<?php echo $zoom?>'>
	<div>
		<div class='left_40 tright'>Depth Scale: <input type='text' <?php if($dscache_freeze>0) echo "disabled='true'"; ?>
			id='zoomtext' name='zoomtext' size='3' value='<?php echo $zoom; ?>' onchange="setzoom(this.form)"></div>
		<div class='left_55 tleft'><div style='padding-left:10px'>
			<input type="submit" <?php if($dscache_freeze>0) echo "disabled='true'"?> value="Zoom In"
				<?php if($zoom<=.5)echo " disabled='true' "; ?> onmouseup="setzoomto(this.form, <?php echo $zoomdec; ?>)">
			<input type="submit" <?php if($dscache_freeze>0) echo "disabled='true'"?> value="Zoom Out"
				<?php if($zoom>=$maxzoom)echo " disabled='true' "; ?> onmouseup="setzoomto(this.form, <?php echo $zoominc; ?>)">
		</div></div>
		<div style='clear:both'></div>
	</div>
	</form>

	<div>
		<div class='left_40 tright'>
		<form name='directinput' method='post' action='gva_tab8.php'>
		<input type='hidden' name='ret' value='gva_tab8.php'>
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
		Depth: <input size='6' type='text' name='depth' id='dbgdepth' value="<?php
			printf("%.2f", $depth); ?>" onchange="directInput(this.form)">
		</form>
		</div>
		<div class='left_55 tleft'><div style='padding-left:10px'>
			<input type='button' name='choice' onClick="window.open('outputpicker.php?seldbname=<?php
				echo $seldbname ?>&title=View%20Snapshot&program=welllogpdf.php&filename=/tmp/<?php
				echo $seldbname ?>.snapshot.pdf&plotstart=<?php echo $plotstart ?>&plotend=<?php
				echo $plotend ?>&wlid=<?php echo $tableid ?>','popuppage','width=200,height=220,left=500');" value="Snapshot">
		</div></div>
		<div style='clear:both'></div>
	</div>

	<div>
		<div class='left_40 tright' style='padding-top:4px'>ProcessTime: <?printf("%.3f",$timelapse) ?></div>
		<div class='left_30 tleft'><div style='padding-left:10px'>
			<input type="submit" value="View Tables" onclick="window.location='viewtablespdf.php?seldbname=<?php echo $seldbname?>'">
		</div></div>
		<div class='left_30 tright'><input size='3' readonly='true' type='text' name='dbgscrolltop' id='dbgscrolltop' value=""></div>
		<div style='clear:both'></div>
	</div>

	</div> <!-- end of view scaling body -->

	</div> <!-- end of view scaling div -->

	<!-- start of exports div -->

	<div class='settings' style='margin-top:5px'>
	<div class='header fbold' style='padding:2px'>
		<div class='left_50'>Exports</div>
		<div id='toggle-exports' class='left_50 tright'>+/-</div>
		<div style='clear:both'></div>
	</div>

	<div id='exports-body'>

	<form method='get' action='csv/tvdgamma.php'>
	<input type='hidden' value='<?php echo $seldbname ?>' name='seldbname'>

	<div>
		<div class='left_50 tright'>Start Depth(tvd): <input type='text' size='6' value='0' name='sdepth'></div>
		<div class='left_50 tright'>End Depth(tvd): <input type='text' size='6' value='999999' name='edepth'></div>
		<div style='clear:both'></div>
	</div>

	<div>
		<div class='left_50 tright'>Increment: <input type='text' size='6' value='' name='incr'></div>
		<div class='left_50 tcenter'><input type='submit' value='Gamma Export'></div>
		<div style='clear:both'></div>
	</div>
	</form>

	<div class='tleft' style='padding:4px'>
		<form name='rawimportexport' method='get' action='csv/rawimportexport.php' target="_blank">
		<input type="hidden" name="seldbname" value="<?php echo "$seldbname"; ?>">
		<input type="hidden" name="tn" value="<?php echo "$tablename"; ?>">
		<input type='submit' value='Raw Import Export'>
		</form>
	</div>

	</div> <!-- end of exports body -->

	</div> <!-- end of exports div -->

	</div> <!-- end of the-sgta-forms div -->

<?php
	$db->CloseDb();
?>

	</td>
	<td style='vertical-align:top'>

<?php
	// this include rebuilds the wellbore and gamma plots using sses_ps

	include 'gva_tab5_funct.php';

	$db = new dbio($seldbname);
	$db->OpenDb();
?>

	<!-- start of the wellbore div

	<div style='overflow-x:scroll'> -->

	<!-- start of the wellbore plot buttons -->

	<div>
		<div class='buttons' style='float:left;margin:2px'>
			<div style='float:left'>
				<input type=button name=choice onClick="window.open('splotconfig.php?seldbname=<?php echo $seldbname ?>&title=Survey%20Plot','popuppage','width=450,height=260,left=250');" value='Plot Surveys'>
			</div>
			<div style='float:left;padding-left:3px'>
				<form id='avgdipform' name='avgdipform' method='get' style='margin:0'>
				<input type='hidden' name='seldbname' value='<?php echo $seldbname ?>'>
				<input type='hidden' name='dip' value='0'>
				<input type='hidden' name='ret' value='gva_tab8.php'>
				<input type='button' value='Average Dip' onclick="return OnAvgDip('<?php echo $seldbname ?>')">
				</form>
			</div>
			<div style='float:left;padding-left:3px'>
				<input type='button' name='choice' onClick="window.open('annotations.php?seldbname=<?php echo $seldbname ?>','annotations','width=1050,height=600,left=250');" value='Annotations'>
			</div>
			<div style='clear:both'></div>
		</div>

		<form action='splotconfigd.php' method='post'>

		<div class='buttons' style='float:left;margin:2px;height:21px'>
			<div style='float:left;padding-top:3px'>SGTA DMod:</div>
			<div style='float:left;margin-left:4px'><input size='5' type='text' name='sgtadmod' value='<?php
				echo $dmod?>' onchange="OnSubmit(this.form)" /></div>
			<div style='clear:both'></div>
		</div>

		<div class='buttons' style='float:left;margin:2px;height:21px'>
			<input id='seldbname' type='hidden' name='seldbname' value='<?php echo $seldbname?>'>
		    <input id='ret' type='hidden' name='ret' value='gva_tab8.php'>
		    <input id='ptype' type='hidden' name='ptype' value='LAT'>
		    <input id='mtype' type='hidden' name='mtype' value='TVD'>
		    <input id='inputa' type='hidden' name='inputa' value='<?php echo $inputa?>'>
			<div style='float:left;padding-top:3px'>Min TVD:</div>
			<div style='float:left;margin-left:4px'><input size='6' type='text' name='mintvd' value='<?php
				echo $mintvd ?>' onchange="OnSubmit(this.form)" /></div>
			<div style='float:left;padding-top:3px;margin-left:4px'>Max TVD:</div>
			<div style='float:left;margin-left:4px'><input size='6' type='text' name='maxtvd' value='<?php
				echo $maxtvd ?>' onchange="OnSubmit(this.form)" /></div>
			<div style='clear:both'></div>
		</div>

		<div class='buttons' style='float:left;margin:2px;height:21px'>
			<input id='seldbname' type='hidden' name='seldbname' value='<?php echo $seldbname?>'>
			<input id='ret' type='hidden' name='ret' value='gva_tab8.php'>
			<input id='ptype' type='hidden' name='ptype' value='LAT'>
			<input id='mtype' type='hidden' name='mtype' value='TVD'>
			<input id='inputa' type='hidden' name='inputa' value='<?php echo $inputa?>'>
			<div style='float:left;padding-top:3px'>Min VS:</div>
			<div style='float:left;margin-left:4px'><input size='6' type='text' name='minvs' value='<?php
				echo $minvs?>' onchange="OnSubmit(this.form)" /></div>
			<div style='float:left;padding-top:3px;margin-left:4px'>Max VS:</div>
			<div style='float:left;margin-left:4px'><input size='6' type='text' name='maxvs' value='<?php
				echo $maxvs?>' onchange="OnSubmit(this.form)" /></div>
			<div style='clear:both'></div>
		</div>

		<div class='buttons' style='float:left;margin:2px;height:21px'>
			<input id='seldbname' type='hidden' name='seldbname' value='<?echo $seldbname?>'>
			<input id='ret' type='hidden' name='ret' value='gva_tab8.php'>
			<input id='ptype' type='hidden' name='ptype' value='LAT'>
			<input id='mtype' type='hidden' name='mtype' value='TVD'>
			<input id='inputa' type='hidden' name='inputa' value='<?echo $inputa?>'>
			<div style='float:left;padding-top:3px'><?php
				echo $uselogscale ? 'Resistivity' : 'Gamma' ?> Scale:</div>
			<div style='float:left;margin-left:4px'><input size='2' type='text' name='inputb' value='<?php
				echo $yscale ?>' onchange="OnSubmit(this.form)" /></div>
			<div style='clear:both'></div>
		</div>

		</form>

		<div class='buttons' style='float:left;margin:2px;height:21px'>
			<button id='plot-direction' style='font-size:8pt'>Show <?php
			echo ($show_plot_right_to_left == 'Yes' ? 'Left to Right' : 'Right to Left') ?></button>
		</div>

		<div style='clear:both'></div>
	</div> <!-- end of the wellbore plot buttons -->

	<div id='lateral-plot' style='overflow-x:auto;<?php
		echo ($show_plot_right_to_left == 'Yes' ? 'direction:rtl;' : '') ?>border:1px solid gray;padding-top:2px'>

		<!-- start of the lateral plot div -->
		<div>
		<img src='<?php echo $fn ?>' />
		</div> <!-- end of the lateral plot div -->

		<!-- start of the gamma plot div -->
		<div>
		<img src='<?php echo $fn4 ?>' />
		</div> <!-- end of the gamma plot div -->

	</div>

	<!-- </div> end of the wellbore plots div -->

	</td>
	</tr>

	</table> <!-- end of top table -->
