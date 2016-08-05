<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Supreme Source Energy Services, Inc.
	All rights reserved.
	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.
*/ ?>
<?php
include('php_gnuplot.php');
$seldbname=$_GET['seldbname'];
$cutoff=$_GET['cutoff']; if($cutoff==""||$cutoff==null)	$cutoff=10;
$xaxis=$_GET['xaxis'];
$yaxis=$_GET['yaxis'];
$zaxis=$_GET['zaxis'];
$yscale = $_REQUEST['yscale'];
$orign_x = $_REQUEST['ox'];
$orign_y = $_REQUEST['oy'];
$orign_o_x = $_REQUEST['ox'];
$orign_o_y = $_REQUEST['oy'];
if(!$orign_o_x){
	$orign_o_x=0;
}
if(!$orign_o_y){
	$orign_o_y=0;
}
if(!$yscale) $yscale = 100;
$yscale_mod_x = $yscale/100.0;
$yscale_mod_y = $yscale/100.0;
if(!$orign_x) $orign_x=(-0.05)*($yscale_mod_x*$yscale_mod_x);
else $orign_x = ($orign_x - 0.05)*($yscale_mod_x*$yscale_mod_x);
if(!$orign_y) $orign_y=(-0.05)*($yscale_mod_x*$yscale_mod_x);
else $orign_y = ($orign_y - 0.05)*($yscale_mod_x*$yscale_mod_x);

if(strlen($xaxis)<=0) $xaxis = 80.0;
if(strlen($yaxis)<=0) $yaxis = 0.0;
if(strlen($zaxis)<=0) $zaxis = 290.0;
if($xaxis<0)	$xaxis+=360.0;
if($xaxis>360)	$xaxis-=360.0;
if($xaxis==360)	$xaxis=0.0;

if($zaxis<0)	$zaxis+=360.0;
if($zaxis>360)	$zaxis-=360.0;
if($zaxis==360)	$zaxis=0.0;

require_once("dbio.class.php");
$db=new dbio($seldbname);
$db->OpenDb();
$query = "update wellinfo set zoom3d=$yscale,originh3d=$orign_o_x,originv3d=$orign_o_y,xaxis=$xaxis,zaxis=$zaxis";
$db->DoQuery($query);
include('readwellinfo.inc.php');


$db2=new dbio($seldbname);
$db2->OpenDb();
$db2->DoQuery("select * from addforms;");
$totid =null;
$totcolor='#ffffff';
$botid = null;
$botcolor='#ffffff';
$addforms=array();
while($db2->FetchRow()){
	array_push($addforms,array('label'=>trim($db2->FetchField('label')),
		'id'=>$db2->FetchField('id'),
		'color'=> $db2->FetchField('color')
	));
}
//print_r($addforms);
$p = new GNUPlot();
$p->width=1148;
$p->height=598;
$outfile="./tmp/surveyplot3d.png";
exec("rm $outfile");

$mintvd=$_REQUEST['mintvd'];
$maxtvd=$_REQUEST['maxtvd'];
$maxvs = $_REQUEST['maxvs'];
$minvs = $_REQUEST['minvs'];
$maxew=-99999;
$minew=99999;
$maxns=-99999;
$minns=99999;

$wellplan=new PGData();
$wellplan2=new PGData();
$wellplan->legend="Well Plan";
$db->DoQuery("SELECT * FROM wellplan WHERE plan=0 where tvd > $mintvd and tvd < $maxtvd and vs>$minvs and vs <$maxvs ORDER BY md ASC");
$num=$db->FetchNumRows();
for($i=0; $i<$num;$i++) {
	$db->FetchRow();
	$md=$db->FetchField("md");
	$ns=$db->FetchField("ns");
	$ew=$db->FetchField("ew");
	$tvd=$db->FetchField("tvd");
	$hide=$db->FetchField("hide");
	//if($tvd>$maxtvd)	$maxtvd=$tvd;
	//if($tvd<$mintvd)	$mintvd=$tvd;
	if($ns>$maxns)	$maxns=$ns;
	if($ew>$maxew)	$maxew=$ew;
	if($ns<$minns)	$minns=$ns;
	if($ew<$minew)	$minew=$ew;
	if($hide==0)
		$wellplan2->addDataEntry( array($ew, $ns, $tvd) );
	$wellplan->addDataEntry( array($ew, $ns, $tvd) );
}

$totbotdata=new PGData();
$data = new PGData();
$data->legend="Surveys";
$db->DoQuery("SELECT * FROM surveys where plan=0 and  tvd > $mintvd and tvd < $maxtvd and vs>$minvs and vs <$maxvs ORDER BY md ASC");
$num=$db->FetchNumRows();
for($i=0; $i<$num;$i++) {
	$db->FetchRow();
	$id = $db->FetchField('id');
	$md=$db->FetchField("md");
	$inc=$db->FetchField("inc");
	$azm=$db->FetchField("azm");
	$tvd=$db->FetchField("tvd");
	$vs=$db->FetchField("vs");
	$vs=$db->FetchField("vs");
	$ca=$db->FetchField("ca");
	$cd=$db->FetchField("cd");
	$ns=$db->FetchField("ns");
	$ew=$db->FetchField("ew");
	$tot = $db->FetchField('tot');
	$fault = $db->FetchField("fault");
	$addarray=array();
	$lastarray=array();
	if(count($addforms)>0){
		foreach($addforms as $add){
			$totid = $add['id'];
			$query = "select tot from addformsdata where svyid=$id and infoid=$totid";
			
			$db2->DoQuery($query);
			$db2->FetchRow();
			array_push($addarray,sprintf("%.2f",$db2->FetchField("tot")));
			array_push($lastarray,sprintf("%.2f",$db2->FetchField("tot")));
		}
	}
	array_push($addarray,sprintf("%.2f",$tot+$fault));
	array_unshift($addarray,$ns);
	array_unshift($addarray,$ew);
	array_unshift($lastarray,$ns);
	array_unshift($lastarray,$ew);
	//if($tvd>$maxtvd)	$maxtvd=$tvd;
	//if($tvd<$mintvd)	$mintvd=$tvd;
	if($ns>$maxns)	$maxns=$ns;
	if($ew>$maxew)	$maxew=$ew;
	if($ns<$minns)	$minns=$ns;
	if($ew<$minew)	$minew=$ew;

	
	$totbotdata->addDataEntry($addarray);
	

	$data->addDataEntry( array($ew, $ns, $tvd) );
}

$projdata = new PGData();
$projdata->addDataEntry($lastarray);
$projstations = new PGData();
$db->DoQuery("Select * from projections where tvd >$mintvd and tvd <$maxtvd and vs >$minvs and vs <$maxvs order by md asc");
while($db->FetchRow()){
	$id = $db->FetchField('id');
	$md=$db->FetchField("md");
	$inc=$db->FetchField("inc");
	$azm=$db->FetchField("azm");
	$tvd=$db->FetchField("tvd");
	$vs=$db->FetchField("vs");
	$vs=$db->FetchField("vs");
	$ca=$db->FetchField("ca");
	$cd=$db->FetchField("cd");
	$ns=$db->FetchField("ns");
	$ew=$db->FetchField("ew");
	$tot = $db->FetchField('tot');
	$fault = $db->FetchField("fault");
	$addarray=array();
	if(count($addforms)>0){
		foreach($addforms as $add){
			$totid = $add['id'];
			$query = "select tot from addformsdata where projid=$id and infoid=$totid";
			
			$db2->DoQuery($query);
			$db2->FetchRow();
			array_push($addarray,sprintf("%.2f",$db2->FetchField("tot")));
		}
	}
	//array_unshift($addarray,sprintf("%.2f",$tvd));
	array_unshift($addarray,$ns);
	array_unshift($addarray,$ew);	
	if($ns>$maxns)	$maxns=$ns;
	if($ew>$maxew)	$maxew=$ew;
	if($ns<$minns)	$minns=$ns;
	if($ew<$minew)	$minew=$ew;
	$projdata->addDataEntry($addarray);
	$projstations->addDataEntry(array($ew, $ns, $tvd) );
}
$projstation= new PGData();
$db->DoQuery("SELECT * FROM surveys where plan=1 and  tvd > $mintvd and tvd < $maxtvd and vs>$minvs and vs <$maxvs ORDER BY md ASC");
while($db->FetchRow()){
	$id = $db->FetchField('id');
	$md=$db->FetchField("md");
	$inc=$db->FetchField("inc");
	$azm=$db->FetchField("azm");
	$tvd=$db->FetchField("tvd");
	$vs=$db->FetchField("vs");
	$vs=$db->FetchField("vs");
	$ca=$db->FetchField("ca");
	$cd=$db->FetchField("cd");
	$ns=$db->FetchField("ns");
	$ew=$db->FetchField("ew");
	$tot = $db->FetchField('tot');
	$fault = $db->FetchField("fault");
	$addarray=array();
	array_unshift($addarray,sprintf("%.2f",$tvd));
	array_unshift($addarray,$ns);
	array_unshift($addarray,$ew);	
	if($ns>$maxns)	$maxns=$ns;
	if($ew>$maxew)	$maxew=$ew;
	if($ns<$minns)	$minns=$ns;
	if($ew<$minew)	$minew=$ew;
	$projstation->addDataEntry($addarray);
}
$db->DoQuery("Select * from projections where tvd >$mintvd and tvd <$maxtvd and vs >$minvs and vs <$maxvs order by md asc limit 1");
while($db->FetchRow()){
	$tvd=$db->FetchField("tvd");
	$ns=$db->FetchField("ns");
	$ew=$db->FetchField("ew");
	$addarray=array();
	array_unshift($addarray,sprintf("%.2f",$tvd));
	array_unshift($addarray,$ns);
	array_unshift($addarray,$ew);	
	$projstation->addDataEntry($addarray);
}

$db->CloseDb();
$rightsign = $leftsign = $topsign = $botsign = 1;
if($minew<0) $leftsign=-1;
if($maxew<0) $rightsign=-1;
if($minns<0) $botsign=-1;
if($maxns<0) $topsign=-1;
$minew=abs($minew);
$maxew=abs($maxew);
$minns=abs($minns);
$maxns=abs($maxns);
$minew=((int)($minew-($minew%1)-1) * $leftsign);
$maxew=((int)($maxew-($maxew%1)-1) * $rightsign);
$minns=((int)($minns-($minns%1)-1) * $botsign);
$maxns=((int)($maxns-($maxns%1)-1) * $topsign);

$left=$minew;
$right=$maxew;
$top=$maxns;
$bottom=$minns;

$p->setRange('x', $left, $right);
$p->setRange('y', $bottom, $top);
$p->setRange('z',$maxtvd,$mintvd);

// $p->set("xrange [$left:$right] noreverse nowriteback");
// $p->set("yrange [$bottom:$top] noreverse nowriteback");
// $p->set("zrange [$maxtvd:$mintvd] noreverse nowriteback");

// $p->setSize( 1.15, 1.15 );
// $p->set("origin 0, -.7");
// $za=sin(deg2rad($zaxis))*.05;
// $p->set("origin $za, 0.0");
// $p->set("xyplane at $maxtvd");

if($zaxis<=180.0)	$za=$zaxis;
else $za=360.0-$zaxis;

if ($za>=0.0 && $za<10)	{$zf=1.15;
	$orign_x+=-0.05;
	$orign_y+=-0.06;
}
if ($za>=10.0 && $za<20){	
	$zf=1.2;
	$orign_x+=-0.05;
	$orign_y+=-0.06;
}
if ($za>=20.0 && $za<30)	$zf=1.1;
if ($za>=30.0 && $za<=60)	$zf=1;
if ($za>60.0 && $za<=70)	$zf=1.1;
if ($za>70.0 && $za<=80)	$zf=1.2;
if ($za>80.0 && $za<100)	$zf=1.4;
if ($za>=100.0 && $za<110)	$zf=1.2;
if ($za>=110.0 && $za<120)	$zf=1.1;
if ($za>=120.0 && $za<=150)	$zf=1.0;
if ($za>150.0 && $za<=160)	$zf=1.1;
if ($za>160.0 && $za<=170)	$zf=1.2;
if ($za>170.0 && $za<=180)	$zf=1.4;
 $p->set("view $xaxis, $zaxis, $zf, $zf");
//$p->set("view $xaxis, $zaxis, 1, 1");
$p->set("key off");

$p->set('lmargin 0');
$p->set('origin '.$orign_x.','.$orign_y.'');
$p->set('size '.$yscale_mod_x.','.$yscale_mod_y.'');
$p->set("style line 1 lt 2 linecolor rgb '#000000' lw .5 ");	// wellplan
$p->set('grid xtics ytics ztics linestyle 1');
$p->set("style line 2 lt 2 linecolor rgb 'black' lw 2 pt 2 ps 1.05 "); // survey
$strtlp=99;
$lpcnt = 0;
foreach($addforms as $add){
	$styletxt = "style line ".($lpcnt+$strtlp)." lt 2 linecolor rgb '#".$add['color']."' lw 2 pt 0 ps 1.00 ";
	$p->set($styletxt);
	$lpcnt++;
}
	// tot
$p->set("style line 4 lt 2 linecolor rgb '#".$botcolor."' lw 2 pt 2 ps 1.00 ");	// bot
$p->set("style line 7 lt 2 linecolor rgb '#ff7000' lw 2 pt 6 ps 1.5 ");	// wellplan points
$p->set("style line 13 lt 2 linecolor rgb '#ff7000' lw 2 pt 6 ps 1.5 ");	// wellplan points
$p->set( "style line 12 lt 26 lc rgb '#d00000' lw 2 pt 6 ps 1.7");//projection formations
$p->set("style line 11 lt 2 lc rgb '#d05050' lw 3 ");// projection line
$p->set("style line 8 lt 2 linecolor rgb 'red' lw 2 pt 2 ps .75 ");	// survey points
$p->set("style line 9 lt 2 linecolor rgb '#".$colorwp."' lw 2 pt 2 ps 1.00 ");
$p->set("style line 10 lt 2 linecolor rgb '#".$colortot."' lw 2 pt 2 ps 1.00 ");
// $p->set("style fill   pattern 2 border");
// $p->set ("style data lines");
// $p->splotData("'silver.dat' u 1:2:3 w filledcu,       '' u 1:2 lt -1 notitle, '' u 1:3 lt -1 notitle");

if($num > 0) {
	if(count($data->DataList)>1) {
		$p->splotData( $data, 'lines', '1:2:3 ls 2' );
		$p->splotData( $data, 'points', '1:2:3 ls 8' );
	}
	if(count($wellplan->DataList)>1)
		$p->splotData( $wellplan, 'lines', '1:2:3 ls 9' );
	
	if(count($totbotdata->DataList)>1) {
		$lpcnt=0;
		foreach($addforms as $add){
			$accesstxt =  '1:2:'.(3+$lpcnt).' ls '.($lpcnt+$strtlp) ;
			$p->splotData( $totbotdata, 'lines',$accesstxt);	
			$lpcnt++;
		}
		$accesstxt =  '1:2:'.(3+$lpcnt).' ls 10';
		$p->splotData( $totbotdata, 'lines',$accesstxt);	
		//$p->splotData( $totbotdata, 'lines', '1:2:4 ls 4' );
		//$p->plotData( $totbotdata, 'filledcurve', '1:3:4 ls 3' );
	}
	
	
	if(count($projdata->DataList)>1){
		$p->splotData($projstations,'points','1:2:3 ls 7');
		$p->splotData($projstations,'line','1:2:3 ls 11');
		$lpcnt=0;
		foreach($addforms as $add){
			$accesstxt =  '1:2:'.(3+$lpcnt).' ls 12';
			$p->splotData( $projdata, 'lines',$accesstxt);	
			$lpcnt++;
		}
	}
	if(count($projstation->DataList)>1){
		$p->splotData( $projstation, 'points', '1:2:3 ls 13' );
	}
}
$p->set("set object 1 rectangle from screen 0,0 to screen 1,1 fillcolor rgb '#DCDCA0' behind");
$p->export("$outfile");
$p->close();
?>
<head><title><?echo $wellname?> 3D Plot</title></head>
<SCRIPT language="javascript">
function OnSubmitForm(rowform)
{
	t = 'surveyplot3d.php';
	t = encodeURI (t); // encode URL
	rowform.action = t;
	rowform.submit(); // submit form using javascript
	return true;
}
</SCRIPT>
<link rel='stylesheet' type='text/css' href='surveyplot.css' />

<table class='plot'>
<tr>
<td>
	<table>
	<tr>
	<td class='topalign' colspan='2' align='center'>
		Rotate:<br>
		<FORM ACTION="surveyplot3d.php" NAME="newplot" METHOD="get">
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='cutoff' value='<?echo $cutoff?>'>
		<input type='hidden' name='mintvd' value='<?echo $mintvd?>'>
		<input type='hidden' name='maxtvd' value='<?echo $maxtvd?>'>
		<input type='hidden' name='minvs' value='<?echo $minvs?>'>
		<input type='hidden' name='maxvs' value='<?echo $maxvs?>'>
		<input type='hidden' name='yscale' value='<?echo $yscale?>'>
		<input type='hidden' name='oy' value = '<?echo $orign_o_x?>'>
		<input type='hidden' name='ox' value = '<?echo $orign_o_y?>'>
		X-Axis
		<br>
		<INPUT TYPE="text" VALUE="<?echo($xaxis);?>" NAME="xaxis" SIZE="3" ONCHANGE="OnSubmitForm(this.form)">
		<br>
		Z-Axis
		<br>
		<INPUT TYPE="text" VALUE="<?echo($zaxis);?>" NAME="zaxis" SIZE="3" ONCHANGE="OnSubmitForm(this.form)">
		</FORM>
	</td>
	</tr>

	<tr>
	<td colspan='2' align='center'>
		<FORM action='surveyplot3d.php' method='get'>
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='cutoff' value='<?echo $cutoff?>'>
				<input type='hidden' name='mintvd' value='<?echo $mintvd?>'>
		<input type='hidden' name='maxtvd' value='<?echo $maxtvd?>'>
		<input type='hidden' name='minvs' value='<?echo $minvs?>'>
		<input type='hidden' name='maxvs' value='<?echo $maxvs?>'>
		<input type='hidden' name='yscale' value='<?echo $yscale?>'>
		<INPUT type='hidden' name='zaxis' value='<?echo $zaxis?>'>
		<INPUT type='hidden' name='xaxis' value='<?echo $xaxis+10?>'>
				<input type='hidden' name='oy' value = '<?echo $orign_o_y?>'>
		<input type='hidden' name='ox' value = '<?echo $orign_o_x?>'>
		<INPUT TYPE="submit" VALUE="Up" NAME="xrot" <?if($xaxis>=180) echo"disabled='true'"?>>
		</FORM>
	</td>
	</tr>
	<tr>
	<td>
		<FORM action='surveyplot3d.php' method='get'>
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='cutoff' value='<?echo $cutoff?>'>
		<input type='hidden' name='mintvd' value='<?echo $mintvd?>'>
		<input type='hidden' name='maxtvd' value='<?echo $maxtvd?>'>
		<input type='hidden' name='minvs' value='<?echo $minvs?>'>
		<input type='hidden' name='maxvs' value='<?echo $maxvs?>'>
		<input type='hidden' name='yscale' value='<?echo $yscale?>'>
		<INPUT type='hidden' name='zaxis' value='<?echo $zaxis-10?>'>
		<INPUT type='hidden' name='xaxis' value='<?echo $xaxis?>'>
				<input type='hidden' name='oy' value = '<?echo $orign_o_y?>'>
		<input type='hidden' name='ox' value = '<?echo $orign_o_x?>'>
		<INPUT TYPE="submit" VALUE="Left" NAME="zrot">
		</FORM>
	</td>
	<td>
		<FORM action='surveyplot3d.php' method='get'>
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='cutoff' value='<?echo $cutoff?>'>
				<input type='hidden' name='mintvd' value='<?echo $mintvd?>'>
		<input type='hidden' name='maxtvd' value='<?echo $maxtvd?>'>
		<input type='hidden' name='minvs' value='<?echo $minvs?>'>
		<input type='hidden' name='maxvs' value='<?echo $maxvs?>'>
		<input type='hidden' name='yscale' value='<?echo $yscale?>'>
		<INPUT type='hidden' name='zaxis' value='<?echo $zaxis+10?>'>
		<INPUT type='hidden' name='xaxis' value='<?echo $xaxis?>'>
				<input type='hidden' name='oy' value = '<?echo $orign_o_y?>'>
		<input type='hidden' name='ox' value = '<?echo $orign_o_x?>'>
		<INPUT TYPE="submit" VALUE="Right" NAME="zrot">
		</FORM>
	</td>
	</tr>
	<tr>
	<td colspan=2 align='center'>
		<FORM action='surveyplot3d.php' method='get'>
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='cutoff' value='<?echo $cutoff?>'>
				<input type='hidden' name='mintvd' value='<?echo $mintvd?>'>
		<input type='hidden' name='maxtvd' value='<?echo $maxtvd?>'>
		<input type='hidden' name='minvs' value='<?echo $minvs?>'>
		<input type='hidden' name='maxvs' value='<?echo $maxvs?>'>
		<input type='hidden' name='yscale' value='<?echo $yscale?>'>
		<INPUT type='hidden' name='zaxis' value='<?echo $zaxis?>'>
		<INPUT type='hidden' name='xaxis' value='<?echo $xaxis-10?>'>
				<input type='hidden' name='oy' value = '<?echo $orign_o_y?>'>
		<input type='hidden' name='ox' value = '<?echo $orign_o_x?>'>
		<INPUT TYPE="submit" VALUE="Down" NAME="xrot" <?if($xaxis<=0) echo"disabled='true'"?>>
		</FORM>
	</td>
	</tr>
	<tr>
	<td class='topalign' colspan=2 align='center'>
		Origin:<br>
		<FORM ACTION="surveyplot3d.php" NAME="newplot" METHOD="get">
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='cutoff' value='<?echo $cutoff?>'>
		<input type='hidden' name='mintvd' value='<?echo $mintvd?>'>
		<input type='hidden' name='maxtvd' value='<?echo $maxtvd?>'>
		<input type='hidden' name='minvs' value='<?echo $minvs?>'>
		<input type='hidden' name='maxvs' value='<?echo $maxvs?>'>
		<input type='hidden' name='yscale' value='<?echo $yscale?>'>
		<INPUT type='hidden' name='zaxis' value='<?echo $zaxis?>'>
		<INPUT type='hidden' name='xaxis' value='<?echo $xaxis?>'>
		Horizontal
		<br>
		<INPUT TYPE="text" VALUE="<?echo($orign_o_x);?>" NAME="ox" SIZE="3" ONCHANGE="OnSubmitForm(this.form)">
		<br>
		Vertical
		<br>
		<INPUT TYPE="text" VALUE="<?echo($orign_o_y);?>" NAME="oy" SIZE="3" ONCHANGE="OnSubmitForm(this.form)">
		</FORM>
	</td>
	</tr>

	<tr>
	<td colspan='2' align='center'>
		<FORM action='surveyplot3d.php' method='get'>
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='cutoff' value='<?echo $cutoff?>'>
		<input type='hidden' name='mintvd' value='<?echo $mintvd?>'>
		<input type='hidden' name='maxtvd' value='<?echo $maxtvd?>'>
		<input type='hidden' name='minvs' value='<?echo $minvs?>'>
		<input type='hidden' name='maxvs' value='<?echo $maxvs?>'>
		<input type='hidden' name='yscale' value='<?echo $yscale?>'>
		<INPUT type='hidden' name='zaxis' value='<?echo $zaxis?>'>
		<INPUT type='hidden' name='xaxis' value='<?echo $xaxis?>'>
		<input type='hidden' name='oy' value = '<?echo $orign_o_y+0.01?>'>
		<input type='hidden' name='ox' value = '<?echo $orign_o_x?>'>
		<INPUT TYPE="submit" VALUE="Up" NAME="xup">
		</FORM>
	</td>
	</tr>
	<tr>
	<td>
		<FORM action='surveyplot3d.php' method='get'>
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='cutoff' value='<?echo $cutoff?>'>
		<input type='hidden' name='mintvd' value='<?echo $mintvd?>'>
		<input type='hidden' name='maxtvd' value='<?echo $maxtvd?>'>
		<input type='hidden' name='minvs' value='<?echo $minvs?>'>
		<input type='hidden' name='maxvs' value='<?echo $maxvs?>'>
		<input type='hidden' name='yscale' value='<?echo $yscale?>'>
		<INPUT type='hidden' name='zaxis' value='<?echo $zaxis?>'>
		<INPUT type='hidden' name='xaxis' value='<?echo $xaxis?>'>
		<input type='hidden' name='oy' value = '<?echo $orign_o_y?>'>
		<input type='hidden' name='ox' value = '<?echo $orign_o_x-0.01?>'>
		<INPUT TYPE="submit" VALUE="Left" NAME="zrot">
		</FORM>
	</td>
	<td>
		<FORM action='surveyplot3d.php' method='get'>
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='cutoff' value='<?echo $cutoff?>'>
				<input type='hidden' name='mintvd' value='<?echo $mintvd?>'>
		<input type='hidden' name='maxtvd' value='<?echo $maxtvd?>'>
		<input type='hidden' name='minvs' value='<?echo $minvs?>'>
		<input type='hidden' name='maxvs' value='<?echo $maxvs?>'>
		<input type='hidden' name='yscale' value='<?echo $yscale?>'>
		<INPUT type='hidden' name='zaxis' value='<?echo $zaxis?>'>
		<INPUT type='hidden' name='xaxis' value='<?echo $xaxis?>'>
		<input type='hidden' name='oy' value = '<?echo $orign_o_y?>'>
		<input type='hidden' name='ox' value = '<?echo $orign_o_x+0.01?>'>
		<INPUT TYPE="submit" VALUE="Right" NAME="zrot">
		</FORM>
	</td>
	</tr>
	<tr>
	<td colspan=2 align='center'>
		<FORM action='surveyplot3d.php' method='get'>
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='cutoff' value='<?echo $cutoff?>'>
				<input type='hidden' name='mintvd' value='<?echo $mintvd?>'>
		<input type='hidden' name='maxtvd' value='<?echo $maxtvd?>'>
		<input type='hidden' name='minvs' value='<?echo $minvs?>'>
		<input type='hidden' name='maxvs' value='<?echo $maxvs?>'>
		<input type='hidden' name='yscale' value='<?echo $yscale?>'>
		<INPUT type='hidden' name='zaxis' value='<?echo $zaxis?>'>
		<INPUT type='hidden' name='xaxis' value='<?echo $xaxis?>'>
		<input type='hidden' name='oy' value = '<?echo $orign_o_y-0.01?>'>
		<input type='hidden' name='ox' value = '<?echo $orign_o_x?>'>
		<INPUT TYPE="submit" VALUE="Down" NAME="xrot">
		</FORM>
	</td>
	</tr>
	<tr>
		<td colspan=2 align='center'>
		Zoom:<br>
		<FORM ACTION="surveyplot3d.php" NAME="newplot" METHOD="get">
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='cutoff' value='<?echo $cutoff?>'>
		<input type='hidden' name='mintvd' value='<?echo $mintvd?>'>
		<input type='hidden' name='maxtvd' value='<?echo $maxtvd?>'>
		<input type='hidden' name='minvs' value='<?echo $minvs?>'>
		<input type='hidden' name='maxvs' value='<?echo $maxvs?>'>
		
		<INPUT type='hidden' name='zaxis' value='<?echo $zaxis?>'>
		<INPUT type='hidden' name='xaxis' value='<?echo $xaxis?>'>
		<input type='hidden' name='oy' value = '<?echo $orign_o_y?>'>
		<input type='hidden' name='ox' value = '<?echo $orign_o_x?>'>
		<INPUT TYPE="text" VALUE="<?echo($yscale);?>" NAME="yscale" SIZE="3" ONCHANGE="OnSubmitForm(this.form)">
		</FORM>
		</td>
	</tr>	
	<tr>
	<td>
		<FORM action='surveyplot3d.php' method='get'>
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='cutoff' value='<?echo $cutoff?>'>
		<input type='hidden' name='mintvd' value='<?echo $mintvd?>'>
		<input type='hidden' name='maxtvd' value='<?echo $maxtvd?>'>
		<input type='hidden' name='minvs' value='<?echo $minvs?>'>
		<input type='hidden' name='maxvs' value='<?echo $maxvs?>'>
		<input type='hidden' name='yscale' value='<?echo $yscale+10?>'>
		<INPUT type='hidden' name='zaxis' value='<?echo $zaxis?>'>
		<INPUT type='hidden' name='xaxis' value='<?echo $xaxis?>'>
		<input type='hidden' name='oy' value = '<?echo $orign_o_y?>'>
		<input type='hidden' name='ox' value = '<?echo $orign_o_x?>'>
		<INPUT TYPE="submit" VALUE="In" NAME="zrot">
		</FORM>
	</td>
	<td>
		<FORM action='surveyplot3d.php' method='get'>
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='cutoff' value='<?echo $cutoff?>'>
				<input type='hidden' name='mintvd' value='<?echo $mintvd?>'>
		<input type='hidden' name='maxtvd' value='<?echo $maxtvd?>'>
		<input type='hidden' name='minvs' value='<?echo $minvs?>'>
		<input type='hidden' name='maxvs' value='<?echo $maxvs?>'>
		<input type='hidden' name='yscale' value='<?echo $yscale-10?>'>
		<INPUT type='hidden' name='zaxis' value='<?echo $zaxis?>'>
		<INPUT type='hidden' name='xaxis' value='<?echo $xaxis?>'>
		<input type='hidden' name='oy' value = '<?echo $orign_o_y?>'>
		<input type='hidden' name='ox' value = '<?echo $orign_o_x?>'>
		<INPUT TYPE="submit" VALUE="Out" NAME="zrot">
		</FORM>
	</td>
	</tr>
	</table>
</td>
<td >
	<table>
		<tr>
			<td>
				<table>
					<tr>
						<td>
	<table class='buttons'>
	<FORM action='surveyplot3d.php' method='get'>
	<INPUT id='seldbname' type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='cutoff' value='<?echo $cutoff?>'>
		<input type='hidden' name='minvs' value='<?echo $minvs?>'>
		<input type='hidden' name='maxvs' value='<?echo $maxvs?>'>
		<input type='hidden' name='yscale' value='<?echo $yscale?>'>
		<INPUT type='hidden' name='zaxis' value='<?echo $zaxis?>'>
		<INPUT type='hidden' name='xaxis' value='<?echo $xaxis?>'>
		<input type='hidden' name='oy' value = '<?echo $orign_o_y?>'>
		<input type='hidden' name='ox' value = '<?echo $orign_o_x?>'>
	<td> Min TVD: <input size='6' type='text' name='mintvd' value='<?echo $mintvd?>' onchange="OnSubmitForm(this.form)"> </td>
	<td> Max TVD: <input size='6' type='text' name='maxtvd' value='<?echo $maxtvd?>' onchange="OnSubmitForm(this.form)"> </td>
	</form>
	</table>
</td>
<td>
	<table class='buttons'>
	<FORM action='surveyplot3d.php' method='get'>
	<INPUT id='seldbname' type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname?>'>
		<INPUT type='hidden' name='cutoff' value='<?echo $cutoff?>'>
		<input type='hidden' name='mintvd' value='<?echo $mintvd?>'>
		<input type='hidden' name='maxtvd' value='<?echo $maxtvd?>'>
		<input type='hidden' name='minvs' value='<?echo $minvs?>'>
		<input type='hidden' name='maxvs' value='<?echo $maxvs?>'>
		<input type='hidden' name='yscale' value='<?echo $yscale?>'>
		<INPUT type='hidden' name='zaxis' value='<?echo $zaxis?>'>
		<INPUT type='hidden' name='xaxis' value='<?echo $xaxis?>'>
		<input type='hidden' name='oy' value = '<?echo $orign_o_y?>'>
		<input type='hidden' name='ox' value = '<?echo $orign_o_x?>'>
	<td> Min VS: <input size='6' type='text' name='minvs' value='<?echo $minvs?>' onchange="OnSubmitForm(this.form)"> </td>
	<td> Max VS: <input size='6' type='text' name='maxvs' value='<?echo $maxvs?>' onchange="OnSubmitForm(this.form)"> </td>
	</form>
	</table>
</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
		<td class='plot'>
		<?
		if(file_exists("$outfile") && filesize("$outfile")>0)
			echo "<img src='$outfile'>";
		else
			echo "<br><br><h1>No Data For Plot</h1>";
		?>
		</td>
		</tr>
	</table>
</tr>
</table>
