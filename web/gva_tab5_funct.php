<?php
require_once("dbio.class.php");
include_once("generatepdfimageleft.php");

if(!isset($seldbname) or $seldbname == "") $seldbname = (isset($_GET['seldbname']) ? $_GET['seldbname'] : '');
if($seldbname == "") include("dberror.php");

$db=new dbio("sgta_index");
$db->error_print = true;
$db->OpenDb();
$db->DoQuery("SELECT * FROM dbindex ORDER BY id;");
while($db->FetchRow()) {
	$dbn = $db->FetchField("dbname");
	$dbreal = $db->FetchField("realname");
	if($seldbname == $dbn) $dbrealname = $dbreal;
} 
$db->CloseDb();

$db=new dbio("$seldbname");
$db->OpenDb();
include_once("readappinfo.inc.php");
include_once("readwellinfo.inc.php");
exec("./sses_gva -d $seldbname --justsurveys");
//exec("./sses_cc -d -p $seldbname  -w");
//exec("./sses_as -d $seldbname");
exec("./sses_af -d $seldbname");
$fn=sprintf("./tmp/%s_gva_tab5.png", $seldbname);
$fn2=sprintf("./tmp/%s_gva_tab5.png1.png", $seldbname); // name generated by sses_ps
$fn3=sprintf("./tmp/%s_gva_tab5.png2.png", $seldbname); // name generated by sses_ps
$fn4=sprintf("./tmp/%s_gva_tab5.png3.png", $seldbname); // name generated by sses_ps
$additionlgraphs = array();
$db->DoQuery("select * from edatalogs where single_plot=1");
while($db->FetchRow()){
	array_push($additionlgraphs,sprintf("./tmp/%s_gva_tab5.png%s.png", $seldbname,$db->FetchField("label")));
}
$fn5 = generatepdfimageleft('',300,700,null,null,$wb_show_forms);
if(file_exists($fn)) unlink($fn);
if(file_exists($fn2)) unlink($fn2);
if(file_exists($fn3)) unlink($fn3);
if(file_exists($fn4)) unlink($fn4);

$db->DoQuery("SELECT inc,md FROM surveys WHERE plan=0 ORDER BY md DESC LIMIT 1;");
$inc=10;
if($db->FetchRow()) $inc=$db->FetchField("inc");
$db->DoQuery("SELECT * FROM splotlist WHERE ptype='LAT' AND mtype='TVD';");
if($db->FetchRow()) {
	$inputa=$db->FetchField("inputa");
	$yscale=$db->FetchField("inputb");
	if($yscale<10)	$yscale=100;
	$mintvd=$db->FetchField("mintvd");
	$maxtvd=$db->FetchField("maxtvd");
	$minvs=$db->FetchField("minvs");
	$maxvs=$db->FetchField("maxvs");
}

// right here we take the rotation and slide data and place it in a file that
// can be read by gnuplot

$gnuplot_data_file = sprintf("./tmp/%s_rotslide.dat",$seldbname);
$gnuplot_data = array();
if(file_exists($gnuplot_data_file)) unlink($gnuplot_data_file);
$db->DoQuery('select * from rotslide order by rsid');
$first_time = true;
if($db->FetchNumRows())
{
	while($row = $db->FetchRow())
	{
		$rotstartvs = intval($row['rotstartvs']);
		$rotendvs = intval($row['rotendvs']);
		$slidestartvs = intval($row['slidestartvs']);
		$slideendvs = intval($row['slideendvs']);

		if($first_time and !$rotstartvs and $slidestartvs)
			$gnuplot_data[] = "$slidestartvs 0\n";

		if($rotstartvs)
		{
			$gnuplot_data[] = "$rotstartvs 0\n"; 
			$gnuplot_data[] = "$rotendvs 0\n"; 
		}
		elseif($slidestartvs)
		{
			$gnuplot_data[] = "$slidestartvs $yscale\n"; 
			$gnuplot_data[] = "$slideendvs $yscale\n"; 
		}

		$first_time = false;
	}
	if($slidestartvs) $gnuplot_data[] = "$slideendvs 0\n";
}
//echo '<pre>'; print_r($gnuplot_data); echo '</pre>';
file_put_contents($gnuplot_data_file,$gnuplot_data);

// done building the gnuplot rotation and slide data

$db->CloseDb();
unset($db);

if ($inc>80)	$cutoff=75;
else if ($inc>60)	$cutoff=30;
else $cutoff=0;

$retstr=array(); $retval=0;
if($mintvd == "") {
	$cmd = "./sses_ps -d $seldbname -p $projection -t lat -c $cutoff -o $fn -h 698 -w 1148";
	echo $cmd;
	exec($cmd);
}
else
{
	$height_mod=0;
	if(count($additionlgraphs)>0){
		$height_mod = -25+count($additionlgraphs)*75;
	}
	$height_f = 598 - $height_mod;
	$args=" -t lat";
	$args=$args." -nodata";
	$args=$args." -p $projection";
	$args=$args." -o $fn";
	// $args=$args." -h 698";
	$args=$args." -h $height_f";
	$args=$args." -w 1148";
	$args=$args." -c 0";
	if(strlen($mintvd))	$args=$args." -tvd1 $mintvd";
	if(strlen($maxtvd))	$args=$args." -tvd2 $maxtvd";
	if(strlen($minvs))	$args=$args." -vs1 $minvs";
	if(strlen($maxvs))	$args=$args." -vs2 $maxvs";
	if(strlen($yscale)) {
		$args=$args." -yscale $yscale";
	}
	$cmd = "./sses_ps -d $seldbname $args";
	echo $cmd;
	exec($cmd,$retstr,$retval);
//	echo "<p>retval = $retval</p>";
//	echo '<pre>gva_tab5_funct.php: retstr='; print_r($retstr); echo '</pre>';
}
?>
