<?php
function generatepdfimageleft($name_app='',$w=300,$h=700,$sd=null,$ed=null,$addforms=0){
	require_once("dbio.class.php");
	global $seldbname;
	$viewallds=0;
	$db=new dbio($seldbname);
	$db->OpenDb();
	$forcesel=1;
	include "readappinfo.inc.php";
	$logsw=""; if($uselogscale>0)	$logsw="-log";
	$fn = sprintf("./tmp/%s_pdfleft$name_app.png", $seldbname);
	if($lasttablename){
		$start_end_query = "select max(depth) as dmax,min(depth) as dmin from $lasttablename";
		$db->DoQuery($start_end_query);
		if ($db->FetchRow()) {
			$dmax=$db->FetchField("dmax");
			$dmin=$db->FetchField("dmin");
			$dmin_val = $dmin-$dmod;
			$dmax_val = $dmax+$dmod;
		}
		$dmin_val=$sd?$sd:$dmin_val;
		$dmax_val=$ed?$ed:$dmax_val;
		$tbl_vals =explode('_',$lasttablename);
		$wlid = $tbl_vals[1];
        $db->DoQuery("SELECT * FROM welllogs WHERE tablename='$lasttablename';");
if($db->FetchNumRows()<=0) {
	$db->DoQuery("SELECT * FROM welllogs ORDER BY startmd DESC LIMIT 1;");
}
if($db->fetchRow()){
$startmd=$db->FetchField('startmd');
$endmd=$db->FetchField('endmd');
}else{
 $startmd = $dmin_val;
 $endmd = $dmax_val;
}	
if($viewallds<=1) {
        $cutinMD=0;
        $cutoffMD=99999.0;
}
else {
        if($startmd<$sgtacutin) {
               
                $sgtastart = (isset($plotstart) ? $plotstart : '');
                $sgtaend = (isset($plotend) ? $plotend : '');
                $cutoffMD=$sgtacutoff=$endmd+$viewallds;
                $cutinMD=$sgtacutin=$startmd;
        }
        else if($endmd>$sgtacutoff) {
               
                $sgtastart = (isset($plotstart) ? $plotstart : '');
                $sgtaend = (isset($plotend) ? $plotend : '');
                $cutinMD=$sgtacutin=$startmd-$viewallds;
                $cutoffMD=$sgtacutoff=$endmd;
        }
        else if($forcesel<1 && $sgtastart!="" && $sgtaend!="" && $sgtacutin!="" && $sgtacutoff!="") {             
                $cutinMD=$sgtacutin;
                $cutoffMD=$sgtacutoff;
        }
        else {
               
                $sgtacutin=$cutinMD=$startmd-$viewallds;
                $sgtacutoff=$cutoffMD=$endmd;
                $sgtastart = (isset($plotstart) ? $plotstart : '');
                $sgtaend = (isset($plotend) ? $plotend : '');
        }
}
		if($addforms){
			$addformsstr = '-aforms';
		} else {
			$addformsstr='';
		}
		if($viewallds>0){
		$exccom= "./sses_gpd -d $seldbname " .
				" -w $w -h $h -s $dmin_val -e $dmax_val " .
				" -o $fn -cld -wld " .
				"-wlid $wlid " .
				"-ci $cutinMD " .
				"-co $cutoffMD " .
				"-avg $dataavg " .
				"-r $scaleright $logsw" .
				" $addformsstr";
		}else{
			$exccom = "./sses_gpd -d $seldbname" .
			"	-w $w -h $h -s $dmin_val -e $dmax_val " .
			"-o $fn -cld " .
			"-T $lasttablename " .
			"-avg $dataavg " .
			"-r $scaleright $logsw" .
			" $addformsstr";;
	
		}
		
		$retstr=array(); $retval=0;
		exec ($exccom,$retstr,$retval);
	}
	return $fn;
}
?>
