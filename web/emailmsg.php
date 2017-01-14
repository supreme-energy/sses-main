<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Supreme Source Energy Services, Inc.
//	All rights reserved.
//	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once("dbio.class.php");
require_once 'sses-math.php';

function GetLRDistanceFromWellPlan(&$db,$themd,$theew,$thens)
{

	// get well plan points before and after the current survey (p1 and p2)

	$query = "select * from wellplan where md <= $themd order by md desc limit 1";
	$db->DoQuery($query);
	if($db->FetchRow())
	{
		$p1md = $db->FetchField("md");
		$p1ew = $db->FetchField("ew");
		$p1ns = $db->FetchField("ns");
	}
	else return 'N/A';
	//echo "<p>p1md=$p1md p1ew=$p1ew p1ns=$p1ns</p>";

	// get well plan points before and after the current survey

	$query = "select * from wellplan where md > $themd order by md asc limit 1";
	$db->DoQuery($query);
	if($db->FetchRow())
	{
		$p2md = $db->FetchField("md");
		$p2ew = $db->FetchField("ew");
		$p2ns = $db->FetchField("ns");
	}
	else return 'N/A';
	//echo "<p>p2md=$p2md p2ew=$p2ew p2ns=$p2ns</p>";

	// get the constants for the line between the two points

	$m = 0.0;
	$c = 0.0;
	GetLineEquation(floatval($p1ew),floatval($p2ew),floatval($p1ns),floatval($p2ns),$m,$c);
	//echo "<p>m=$m c=$c</p>";

	// get the distance from the current point to the line between the plan points

	$dfp = GetDistPointLine(floatval($theew),floatval($thens),$m,-1.0,$c);

	$thedfp = sprintf('%.2f',$dfp);

	$from = (object) array('x' => floatval($p1ew),'y' => floatval($p1ns));
	$to = (object) array('x' => floatval($p2ew),'y' => floatval($p2ns));
	$point = (object) array('x' => floatval($theew),'y' => floatval($thens));

	if(PointIsLeft($from,$to,$point)) $thedfp .= ' L';
	else $thedfp .= ' R';
	//echo "<p>thedfp=$thedfp</p>";

	return $thedfp;
}

$seldbname=$_GET['seldbname'];
$email_attach = (isset($_GET['email_attach']) ? $_GET['email_attach'] : '');
$program=$_GET['program'];
$filename=$_GET['filename'];
$plotstart = (isset($_GET['plotstart']) ? $_GET['plotstart'] : '');
$plotend = (isset($_GET['plotend']) ? $_GET['plotend'] : '');
$cutoff = (isset($_GET['cutoff']) ? $_GET['cutoff'] : '');

if(!isset($mintvd) or strlen($mintvd)<=0) $mintvd = (isset($_GET['mintvd']) ? $_GET['mintvd'] : '');
if(!isset($maxtvd) or strlen($maxtvd)<=0) $maxtvd = (isset($_GET['maxtvd']) ? $_GET['maxtvd'] : '');
if(!isset($minvs) or strlen($minvs)<=0) $minvs = (isset($_GET['minvs']) ? $_GET['minvs'] : '');
if(!isset($maxvs) or strlen($maxvs)<=0) $maxvs = (isset($_GET['maxvs']) ? $_GET['maxvs'] : '');
if(!isset($yscale) or strlen($yscale)<=0) $yscale = (isset($_GET['yscale']) ? $_GET['yscale'] : '');

$message=sprintf("Email report from %s\n", (isset($wellname) ? $wellname : ''));

$templestr = '';

$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("SELECT * FROM emaillist WHERE enabled>0;");
$select_las="<select id='las_sel' name='las_sel'>";
$select_r1="<select id='r1_sel' name='r1_sel'>";
$select_r2="<select id='r2_sel' name='r2_sel'>";
$active_las_email="";
$active_r1_email="";
$active_r2_email="";
while($db->FetchRow()) {
	$id=$db->FetchField("id");
	$c=$db->FetchField("email");
	$dname = $db->FetchField("name");
	$las=$db->FetchField("las_file");
	$r1 = $db->FetchField("report_1");
	$r2 = $db->FetchField("report_2");
	$select_las.="<option value='$id'>$dname - $c</option>";
	$select_r1.="<option value='$id'>$dname - $c</option>";
	$select_r2.="<option value='$id'>$dname - $c</option>";
	if($las==1){
		$active_las_email.="<div style='padding-left:20px;border-bottom:black 1px solid;'>$dname - $c - <a onclick='remove_element(this.parentElement,$id,\"las\")'>Remove</a></div>";
	}
	if($r1==1){
		$active_r1_email.="<div style='padding-left:20px;border-bottom:black 1px solid;'>$dname - $c - <a onclick='remove_element(this.parentElement,$id,\"r1\")'>Remove</a></div>";
	}
	if($r2==1){
		$active_r2_email.="<div style='padding-left:20px;border-bottom:black 1px solid;'>$dname - $c - <a onclick='remove_element(this.parentElement,$id,\"r2\")'>Remove</a></div>";
	}
}
$select_las.="</select>";
$select_r1.="</select>";
$select_r2.="</select>";
$db->DoQuery("select * from addforms;");
$totid =null;
$botid = null;
while($db->FetchRow()){
	
	if(trim($db->FetchField('label'))=='TOT'){
		$totid = $db->FetchField('id');
	}
	if(trim($db->FetchField('label'))=='BOT'){
		$botid = $db->FetchField('id');
	}
}
$query = "with s as (select dip from surveys where plan=0 order by md desc limit 3) select avg(dip) as davg from s";
$db->DoQuery($query);
$db->FetchRow();
$l3avg = sprintf("%+.2f",$db->FetchField("davg"));

$query = "with s as (select dip from surveys where plan=0 order by md desc limit 5) select avg(dip) as davg from s";
$db->DoQuery($query);
$db->FetchRow();
$l5avg = sprintf("%+.2f",$db->FetchField("davg"));

$query ="with s as (select dip from surveys where plan=0 order by md desc limit 8) select avg(dip) as davg from s";
$db->DoQuery($query);
$db->FetchRow();
$l8avg = sprintf("%+.2f",$db->FetchField("davg"));

// getting the template type

$db->DoQuery("select cvalue from adm_config where cname = 'msgtemplate' limit 1");
$currenttemplate = 'curved';
if($db->FetchRow()) $currenttemplate = $db->FetchField('cvalue');
else $db->DoQuery("insert into adm_config (cname,cvalue) values ('msgtemplate','curved')");

if(isset($_GET['msgtemplate']) and $currenttemplate != $_GET['msgtemplate'])
{
	$db->DoQuery("update adm_config set cvalue = '{$_GET['msgtemplate']}' where cname = 'msgtemplate'");
	$currenttemplate = $_GET['msgtemplate'];
}

$thetemplate = $currenttemplate;

// getting the lateral 1 or 2

$db->DoQuery("select cvalue from adm_config where cname = 'lateralsel' limit 1");
$lateralsel = '1';
if($db->FetchRow()) $lateralsel = $db->FetchField('cvalue');
else $db->DoQuery("insert into adm_config (cname,cvalue) values ('lateralsel','1')");

if(isset($_GET['lateralsel']) and $lateralsel != $_GET['lateralsel'])
{
 	$db->DoQuery("update adm_config set cvalue = '{$_GET['lateralsel']}' where cname = 'lateralsel'");
 	$lateralsel = $_GET['lateralsel'];
}

$query = "with s as (select dip from surveys where plan=0 order by md desc limit 3) select avg(dip) as davg from s";
$query = "select * from projections order by MD asc limit 1";
$db->DoQuery($query);
$db->FetchRow();
$paid = $db->FetchField('id');
$pavs = $db->FetchField("vs");
$patvd = sprintf("%.2f",$db->FetchField("tvd"));
$patcl = $db->FetchField("tot");
$pamd = $db->FetchField("md");
$painc = $db->FetchField("inc");
$paazm = $db->FetchField("azm");
$padl = $db->FetchField("dl");
$pacl = $db->FetchField("cl");
$patf = $db->FetchField("tf");
$patot='NF';
$pabot='NF';
if($totid){
	$query = "select tot from addformsdata where projid=$paid and infoid=$totid;"; 
	$db->DoQuery($query);
	$db->FetchRow();
	$patot =sprintf("%.2f", $db->FetchField("tot"));
}
if($botid){
	$query = "select tot from addformsdata where projid=$paid and infoid=$botid;";
	$db->DoQuery($query);
	$db->FetchRow();
	$pabot =sprintf("%.2f", $db->FetchField("tot"));
}

// get the values for the current survey

$query = "select * from surveys where plan = 0 order by md desc limit 1 ";
$db->DoQuery($query);
$db->FetchRow();
$curid = $db->FetchField('id');
$curvs = $db->FetchField("vs");
$curtvd = $db->FetchField("tvd");
$curtcl = $db->FetchField("tot");
$curmd = $db->FetchField("md");
$curinc = $db->FetchField("inc");
$curazm = $db->FetchField("azm");
$curdl = $db->FetchField("dl");
$curew = $db->FetchField("ew"); // current nort-south value
$curns = $db->FetchField("ns"); // current east-west value
$curtot='NF';
$curbot='NF';
if($totid){
	$query = "select tot from addformsdata where svyid=$curid and infoid=$totid;"; 
	$db->DoQuery($query);
	$db->FetchRow();
	$curtot =sprintf("%.2f", $db->FetchField("tot"));
}
if($botid){
	$query = "select tot from addformsdata where svyid=$curid and infoid=$botid;";
	$db->DoQuery($query);
	$db->FetchRow();
	$curbot =sprintf("%.2f", $db->FetchField("tot"));
}
$curdfp = GetLRDistanceFromWellPlan($db,$curmd,$curew,$curns);

// get the next values for the projected path

$query = "select * from surveys where plan = 1";
$db->DoQuery($query);
$db->FetchRow();
$bprjid = $db->FetchField('id');
$bprjvs = $db->FetchField("vs");
$bprjtvd = sprintf("%.2f",$db->FetchField("tvd"));
$bprjtcl = $db->FetchField("tot");
$bprjmd = $db->FetchField("md");
$bprjinc = $db->FetchField("inc");
$bprjazm = $db->FetchField("azm");
$bprjdl = $db->FetchField("dl");
$bprjew = $db->FetchField("ew");
$bprjns = $db->FetchField("ns");
$bprjtot='NF';
$bprjbot='NF';
if($totid){
	$query = "select tot from addformsdata where svyid=$bprjid and infoid=$totid;"; 
	$db->DoQuery($query);
	$db->FetchRow();
	$bprjtot =sprintf("%.2f", $db->FetchField("tot"));
}
if($botid){
	$query = "select tot from addformsdata where svyid=$bprjid and infoid=$botid;";
	$db->DoQuery($query);
	$db->FetchRow();
	$bprjbot =sprintf("%.2f", $db->FetchField("tot"));
}
$bprjdfp = GetLRDistanceFromWellPlan($db,$bprjmd,$bprjew,$bprjns);

$query = "select vslon,vsldip,vsland from wellinfo";
$db->DoQuery($query);
$db->FetchRow();
$vsland = $db->FetchField("vsland");
$vsldip = $db->FetchField("vsldip");
$vslon  = $db->FetchField("vslon");
include("readwellinfo.inc.php");
include("readappinfo.inc.php");
$db->DoQuery("SELECT smtp_message FROM emailinfo;");
if($db->FetchRow()) $message=$db->FetchField("smtp_message");
$savedcomments="PLACE COMMENTS HERE";
$query = "select emailcomments from wellinfo";
$db->DoQuery($query);
$db->FetchRow();
if($scom = $db->FetchField("emailcomments")){
	$savedcomments=$scom;
}
$db->CloseDb();
$lateraltemplatestr = $curvedtemplatestr = '<table cellspacing="0" cellpadding="5px" style="width:350px;border:0;background-color:transparent">
<tr>
<td>Operator</td>
<td>'.$opname.'</td>
</tr>
<tr>
<td>WellName</td>
<td>'.$wellname.'</td>
</tr>
<tr>
<td>Drig. Rig ID</td>
<td>'.$rigid.'</td>
</tr>
<tr>
<td>County</td>
<td>'.$county.'</td>
</tr>
<tr>
<td>Field</td>
<td>'.$field.'</td>
</tr>
</table>
<p>TOP COMMENT RIGHT HERE</p>
<table width="720px" cellspacing="0" cellpadding="5" style="border:0;background-color:transparent">
<tr>
<td colspan="4">';
$colora='#e6dcb1';
$colorb='#307040';
$curvedtemplatestr.='<h3><span style="text-decoration:underline;font-size:14px"><strong>SSES LANDING RECOMMENDATION</strong></span></h3>
</td>
</tr>';
if($lateralsel != '4')
{
$lateraltemplatestr.='<h3><span style="text-decoration:underline;font-size:14px"><strong>SSES RECOMMENDATION</strong></span></h3>
</td>
</tr>';
} else{
	$below_tot =$bprjtvd - $bprjtot ;
	$below_tot_text =($below_tot < 0 ? "below" : "above");
	$above_bot = $bprjtvd - $bprjbot ;
	$above_bot_text =($above_bot < 0 ? "below" : "above");
	$inc_bit = $bprjinc;
	$past_100 = 0;
	$past_500 = 0;
	$lateraltemplatestr.="<h3>" .
	"Currently $below_tot' $below_tot_text the TOT and $above_bot' $above_bot_text the BOT, current inc at bit is $inc_bit and the past 100’ of the formation is $past_100 and the past 500’ of the formation is $past_500</td>" .
	"</h3></td></tr></table>";
}
if($vslon){
	$bitpostcl =$bprjtcl-$bprjtvd;
	$ltvdvsl=($bprjvs - $vsland)*tan(($vsldip*(pi()/180)))+$bprjtvd+($bitpostcl);
	$curvedtemplatestr.='<tr>
	<td>Fixed VS Landing Target</td>
	<td style="background-color: '.$colora.';">'.sprintf("%.2f",$vsland).' VS</td>
	<td style="background-color: '.$colora.';" colspan="2">'.sprintf("%.2f",$ltvdvsl).' TVD</td>
	</tr>';
}
if($lateralsel != '4'){
	$lateraltemplatestr .= "<tr>\n";
	
		if($lateralsel == '3')
		{
			$lateraltemplatestr .= "<td>SLD to " . sprintf('%.2f',$pamd) . ":</td>\n" .
				"<td style='background-color: ".$colora.";'>" . sprintf("%.2f",$pacl) . " FT</td>\n" .
				"<td colspan='2' style='background-color: ".$colora.";'>$patf</td>\n";
		}
		else
		{
			$lateraltemplatestr .= "<td>Intersect PA1 at " . sprintf("%.2f",$pavs) . " VS:</td>\n" .
				"<td style='background-color: ".$colora.";'>" . sprintf("%.2f",$patvd). " TVD </td>\n" .
				"<td style='background-color: ".$colora.";'>" . sprintf("%.2f",$painc). "INC</td>\n" .
				"<td style='background-color: ".$colora.";'>" . sprintf("%.2f",$padl). "/100DL</td>\n";
		}
$lateraltemplatestr .= "</tr>\n";
}

if($lateralsel == '2')
{
	$sval1 = sprintf('%.2f',round(floatval($curtcl),2));
	$sval2 = sprintf('%+.2f',round(floatval($curvs),2));
	$sval3 = sprintf('%+.2f',round(floatval($projdip),2));
	$lateraltemplatestr .= "<tr><td colspan='4'>" .
		"Current Survey's TCL: {$sval1}TVD @ {$sval2}VS {$sval3}dip</td></tr>\n";
}
$lateraltemplatestr .= "<tr><td colspan='4' rowspan='2'>";

$curvedtemplatestr.='<tr>
<td>GEO Landing Target</td>
<td style="background-color: '.$colora.';">'.sprintf("%.2f",$pavs).' VS</td>
<td style="background-color:'.$colora.';" colspan="1">'.sprintf("%.2f",$patvd).' TVD</td>' .
		'<td style="background-color:'.$colora.';" colspan="1"><b>DOGLEG NEEDED&nbsp;: '.sprintf("%.2f",$padl).'</b></td>'.
'</tr>
<tr>
<td colspan="4" rowspan="2" style="padding:2px">';

$templestr.='<table style="border:0;border-collapse:collapse;background-color:none;width:100%" cellspacing="0" cellpadding="8">
<tr><td colspan=7></td><td colspan=4 style="text-align:center;padding:18px 0">(+Above/-Below)</td></tr>
<tr>
<td>&nbsp;</td>
<td style="text-align:center;vertical-align:bottom;padding:4px">MD</td>
<td style="text-align:center;vertical-align:bottom;padding:4px">INC</td>
<td style="text-align:center;vertical-align:bottom;padding:4px">AZM</td>
<td style="text-align:center;vertical-align:bottom;padding:4px">TVD</td>
<td style="text-align:center;vertical-align:bottom;padding:4px">VS</td>
<td style="text-align:center;vertical-align:bottom;padding:4px">DL</td>
<td style="text-align:center;vertical-align:bottom;padding:4px">DIST<br>FROM<br>TOT</td>
<td style="text-align:center;vertical-align:bottom;padding:4px">DIST<br>FROM<br>TCL</td>
<td style="text-align:center;vertical-align:bottom;padding:4px">DIST<br>FROM<br>BOT</td>
<td style="text-align:center;vertical-align:bottom;padding:4px">L/R<br>FROM<br>WP</td>
</tr>
<tr>
<td style="text-align:right;padding:5px">Current Survey</td>
<td style="text-align: center;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$curmd).'</td>
<td style="text-align: center;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$curinc).'</td>
<td style="text-align: center;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$curazm).'</td>
<td style="text-align: center;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$curtvd).'</td>
<td style="text-align: center;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$curvs).'</td>
<td style="text-align: center;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$curdl).'</td>
<td style="text-align: center;color:white;border-bottom:solid black 1px;border-top:solid black 1px;border-left:1px solid black; background-color: '.$colorb.';">'.sprintf("%.2f",$curtot-$curtvd,2).'</td>
<td style="text-align: center;color:white;border-bottom:solid black 1px;border-top:solid black 1px;border-left:1px solid black; background-color: '.$colorb.';">'.sprintf("%.2f",$curtcl-$curtvd,2).'</td>
<td style="text-align: center;color:white;border-bottom:solid black 1px;border-top:solid black 1px;border-left:1px solid black;border-right:solid black 1px; background-color: '.$colorb.';">'.sprintf("%.2f",$curbot-$curtvd,2).'</td>
<td style="text-align: center;color:white;border-bottom:solid black 1px;border-top:solid black 1px;border-left:1px solid black;border-right:solid black 1px; background-color: '.$colorb.';">'.$curdfp.'</td>
</tr>
<tr>
<td style="text-align: right;;padding:5px">Bitprj</td>
<td style="text-align: center;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$bprjmd).'</td>
<td style="text-align: center;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$bprjinc).'</td>
<td style="text-align: center;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$bprjazm).'</td>
<td style="text-align: center;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$bprjtvd).'</td>
<td style="text-align: center;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$bprjvs).'</td>
<td style="text-align: center;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$bprjdl).'</td>
<td style="text-align: center;color:white;border-bottom:solid black 1px;border-top:solid black 1px;border-left:1px solid black; background-color:  '.$colorb.';">'.sprintf("%.2f",$bprjtot-$bprjtvd).'</td>
<td style="text-align: center;color:white;border-bottom:solid black 1px;border-top:solid black 1px;border-left:1px solid black; background-color:  '.$colorb.';">'.sprintf("%.2f",$bprjtcl-$bprjtvd).'</td>
<td style="text-align: center;color:white;border-bottom:solid black 1px;border-top:solid black 1px;border-left:1px solid black;border-right:solid black 1px; background-color:  '.$colorb.';">'.sprintf("%.2f",$bprjbot-$bprjtvd).'</td>
<td style="text-align: center;color:white;border-bottom:solid black 1px;border-top:solid black 1px;border-left:1px solid black;border-right:solid black 1px; background-color:  '.$colorb.'" nowrap>'.$bprjdfp.'</td>
</tr>
<tr>';

$curvedtemplatestr.=$templestr;
if($lateralsel != '4'){
	$lateraltemplatestr.=$templestr;
}

$curvedtemplatestr.='<td style="text-align:right;padding:5px" nowrap>PA1-Geo Landing Target</td>';
if($lateralsel != '4'){
	
	$lateraltemplatestr.='<td style="text-align:right;padding:5px" nowrap>' . ($lateralsel == 3 ? 'SLD' : 'PA1-Geo') . ' Intersect</td>';
	
}
$templestr='<td style="text-align: center;border-bottom:solid black 1px;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$pamd).'</td>
<td style="text-align: center;border-bottom:solid black 1px;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$painc).'</td>
<td style="text-align: center;border-bottom:solid black 1px;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$paazm).'</td>
<td style="text-align: center;border-bottom:solid black 1px;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$patvd).'</td>
<td style="text-align: center;border-bottom:solid black 1px;border-top:solid black 1px;border-left:1px solid black;">'.sprintf("%.2f",$pavs).'</td>
<td style="text-align: center;border-bottom:solid black 1px;border-top:solid black 1px;border-left:1px solid black;">' .
	(($currenttemplate == 'lateral' and $lateralsel == 3) ? '-' : sprintf("%.2f",$padl)) . '</td>
<td style="text-align: center;color:white;border-bottom:solid black 1px;border-top:solid black 1px;border-left:1px solid black; background-color:  '.$colorb.';">'.sprintf("%.2f",$patot-$patvd).'</td>
<td style="text-align: center;color:white;border-bottom:solid black 1px;border-top:solid black 1px;border-left:1px solid black; background-color:  '.$colorb.';">'.sprintf("%.2f",$patcl-$patvd).'</td>
<td style="text-align: center;color:white;border-bottom:solid black 1px;border-top:solid black 1px;border-right:solid black 1px;border-left:1px solid black; background-color:  '.$colorb.';">'.sprintf("%.2f",$pabot-$patvd).'</td>
<td style="text-align: center;color:white;border-bottom:solid black 1px;border-top:solid black 1px;border-right:solid black 1px;border-left:1px solid black; background-color:  '.$colorb.';"></td>
</tr>';
$curvedtemplatestr.=$templestr;
$curvedtemplatestr.='<tr><td style="text-align:left;padding:5px" nowrap colspan=2></td></tr>';
$templestr.='</table>
</table>
';

$curvedtemplatestr.='</table></table>';
if($lateralsel != '4'){
	$lateraltemplatestr.=$templestr;
}

$lateraltemplatestr.='<table style="border:0;width:719px;background-color:transparent" cellspacing="0" cellpadding="5">
<tr>
<td colspan="2">
<h3><span style="text-decoration: underline;font-size:14px"><strong>STRUCTURAL MODELED FORMATION DIP:(+UP DIP/- DOWN DIP)</strong></span></h3>
</td>
</tr>
<tr>
<td style="text-align: right;width:150px;">Last 3 Survey Stations</td>
<td>'.$l3avg.' deg</td>
</tr>
<tr>
<td style="text-align: right;width:150px;">Last 5 Survey Stations</td>
<td>'.$l5avg.' deg</td>
</tr>
<tr>
<td style="text-align: right;width:150px;">Last 8 Survey Stations</td>
<td>'.$l8avg.' deg</td>
</tr>
</table>
';

$templestr='<h3><span style="text-decoration: underline;font-size:14px"><strong>COMMENTS:</strong></span></h3>
<p id="comments" style="font-size:12px">COMMENTS WILL GO HERE</p>';
$curvedtemplatestr.=$templestr;
$lateraltemplatestr.=$templestr;

if($currenttemplate=='lateral'){
	$currenttemplate=$lateraltemplatestr;
}else{
	$currenttemplate=$curvedtemplatestr;
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?echo "$wellname";?>-Email report<?echo "($seldbname)";?></title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<style>
body {
	margin:8px;
	font-size:8pt;
	color:black;
	background-color: #307040;
}
#maindiv {
	background-color: rgb(230,220,177);
	padding:0px 4px;
	border:1px solid black;
}
#maildiv {
	background-color:white;
	border:1px solid gray;
	height:280px;
	overflow:auto;
	margin-top:20px;
}
#formdiv {
	margin:10px 0;
}
h1 {
	font-size:2.2em;
}
table {
	-moz-border-radius: 10px 10px 10px 10px;
	border: 2px solid black;
	color: #000;
	text-align: left;
	padding: 2px;
	background-color: rgb(230,220,177);
	width: 800px;
}
.tempbut {
	height:28px;
}
#sellat {
	border:1px solid gray;
}
#sellat option {
	height:22px;
}
</style>
<script type="text/javascript" src="js/tinymce/js/tinymce/tinymce.min.js"></script>
<script>tinymce.init({selector:'textarea.mainbody',height:280,plugins:["table","code"],tools:"inserttable code"});</script>
<script>tinymce.init({selector:'textarea.comments',height:50,plugins:["code"],tools:"inserttable code"});</script>
<script>
var directo = function(v) {
	if(window.location.search.indexOf('msgtemplate')>=0) {
		if(window.location.search.indexOf('msgtemplate=lateral')>=0) {
			newsearch = window.location.search.replace("msgtemplate=lateral","msgtemplate="+v)
		} else {
			newsearch = window.location.search.replace("msgtemplate=curved","msgtemplate="+v)
		}
		window.location=window.location.pathname+newsearch
	} else {
		window.location=window.location+"&msgtemplate="+v;
	}
}
function changeScreenSize(w,h) {
	window.resizeTo( w,h )
}
</script>
</head>
<body onload="changeScreenSize(935,920)">
<div id='maindiv'>
<h1>Send report via email</h1>
<div><button class='tempbut' onclick="directo('curved')">Load Curve Template</button>&nbsp;&nbsp;
<button class='tempbut' onclick="directo('lateral')">Load Lateral Template</button><?php
if($thetemplate == 'lateral')
{
?>
&nbsp;&nbsp;
<select id='sellat'>
<option id='optlat1'<?php echo ($lateralsel == '1' ? ' selected' : '') ?>>Lateral 1</option>
<option id='optlat2'<?php echo ($lateralsel == '2' ? ' selected' : '') ?>>Lateral 2</option>
<option id='optlat3'<?php echo ($lateralsel == '3' ? ' selected' : '') ?>>Lateral 3</option>
<option id='optlat4'<?php echo ($lateralsel == '4' ? ' selected' : '') ?>>Lateral 4</option>
</select>
<?php
}
?></div>

<!--
<div id='maildiv'>
<?php echo $currenttemplate ?>
</div>
-->

<div id='formdiv'>
<form action='emailsend.php' method='post'>
<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
<input type='hidden' name='program' value='<?echo $program;?>'>
<input type='hidden' name='filename' value='<?echo $filename;?>'>
<input type='hidden' name='cutoff' value='<?echo $cutoff;?>'>
<input type='hidden' name='plotstart' value='<?echo $plotstart;?>'>
<input type='hidden' name='plotend' value='<?echo $plotend;?>'>
<input type='hidden' name='wlid' value='<?echo $wlid;?>'>
<input type='hidden' name='mintvd' value='<?echo $mintvd?>'>
<input type='hidden' name='maxtvd' value='<?echo $maxtvd?>'>
<input type='hidden' name='minvs' value='<?echo $minvs?>'>
<input type='hidden' name='maxvs' value='<?echo $maxvs?>'>
<input type='hidden' name='yscale' value='<?echo $yscale?>'>
<!--
<input type='hidden' name='message' value="<?php echo htmlentities($currenttemplate) ?>">
-->
<h2>EMAIL BODY:</h2>
<textarea class='mainbody' cols = '80' name="message"><?php echo $currenttemplate ?></textarea>
<h2>ENTER COMMENTS BELOW:</h2>
<textarea class='comments' cols = '80' name="comments"><?php echo $savedcomments ?></textarea>
<h1>Additional attachments</h1>
<div style='text-align:left;font-size:22px;'>
		<div>
		<div>LAS for current survey: <input type="checkbox" <?= $email_attach_las==1?"checked":""?> name="las" value="1" onchange='setfield("las",this)'></div>
		<div>
			<div id="las_contacts">
				<?=$active_las_email?>
			</div>
			<div><?=$select_las?> - <a onclick="add_to_las()">Add</a></div>
		</div>
		<div>1-inch GR report: <input type="checkbox" <?= $email_attach_r1==1?"checked":""?> name="inch1gr" value="gr1" onchange='setfield("r1",this)'></div>
		<div>
			<div id="r1_contacts"><?=$active_r1_email?></div>
			<div><?=$select_r1?> - <a onclick="add_to_r1()">Add</a></div>
		</div>
		<div>5-inch GR report: <input type="checkbox" <?= $email_attach_r2==1?"checked":""?> name="inch5gr" value="gr5" onchange='setfield("r2",this)'></div>
		<div>
			<div id="r2_contacts"><?=$active_r2_email?></div>
			<div><?=$select_r2?> - <a onclick="add_to_r2()">Add</a></div>
		</div>
		</div>
</div>
<div style='text-align:center;margin-top:7px'>
<input type='submit' name='submit' id='submit' value='Send Email'>
</div>
</form>
</div>

</div>

<script>
$(document).ready( function () {
	$('#optlat1').click(function() {
		if(window.location.search.indexOf('lateralsel')>=0) {
			if(window.location.search.indexOf('lateralsel=1')>=0) {
				return;
			} else if (window.location.search.indexOf('lateralsel=2')>=0) {
				newsearch = window.location.search.replace('lateralsel=2','lateralsel=1');
			} else {
				newsearch = window.location.search.replace('lateralsel=3','lateralsel=1');
			}
			window.location = window.location.pathname + newsearch;
		} else {
			window.location = window.location + '&lateralsel=1';
		}
	});
	$('#optlat2').click(function() {
		if(window.location.search.indexOf('lateralsel')>=0) {
			if(window.location.search.indexOf('lateralsel=2')>=0) {
				return;
			} else if (window.location.search.indexOf('lateralsel=1')>=0) {
				newsearch = window.location.search.replace('lateralsel=1','lateralsel=2');
			} else {
				newsearch = window.location.search.replace('lateralsel=3','lateralsel=2');
			}
			window.location = window.location.pathname + newsearch;
		} else {
			window.location = window.location + '&lateralsel=2';
		}
	});
	$('#optlat3').click(function() {
		if(window.location.search.indexOf('lateralsel')>=0) {
			if(window.location.search.indexOf('lateralsel=3')>=0) {
				return;
			} else if (window.location.search.indexOf('lateralsel=1')>=0) {
				newsearch = window.location.search.replace('lateralsel=1','lateralsel=3');
			} else {
				newsearch = window.location.search.replace('lateralsel=2','lateralsel=3');
			}
			window.location = window.location.pathname + newsearch;
		} else {
			window.location = window.location + '&lateralsel=3';
		}
	});
});
var selected_element;
var newdiv;
var seldbname="<?=$seldbname?>";
function remove_element(el,id,rv){
	selected_element=el;
	//el.remove()
	var script = document.createElement('script');
	script.src = '/sses/json/remove_from_email_report.php?seldbname='+seldbname+'&callback=afterremove&id='+id+'&rv='+rv;
	document.head.appendChild(script);
}

function setfield(n,inobj){
	v=inobj.checked?1:0;
	var script = document.createElement('script');
	script.src = '/sses/json/setfield.php?seldbname='+seldbname+'&callback=afteradd&id='+v+'&rv='+n;
	document.head.appendChild(script);
}
function add_to_las(){
	selected_element = document.getElementById("las_contacts");
	seled = document.getElementById("las_sel");
	val = seled.options[seled.selectedIndex].value
	newdiv = document.createElement('div');
	newdiv.innerHTML=seled.options[seled.selectedIndex].text+"- <a onclick='remove_element(this.parentElement,"+val+",\"las\")'>Remove</a>"
	newdiv.style.cssText="padding-left:20px;border-bottom:black 1px solid;";
	//el.appendChild(newdiv);
	var script = document.createElement('script');
	script.src = '/sses/json/add_to_email_report.php?seldbname='+seldbname+'&callback=afteradd&id='+val+'&rv=las';
	document.head.appendChild(script);
}
function add_to_r1(){
	selected_element = document.getElementById("r1_contacts");
	seled = document.getElementById("r1_sel");
	val = seled.options[seled.selectedIndex].value
	newdiv = document.createElement('div');
	newdiv.innerHTML=seled.options[seled.selectedIndex].text+"- <a onclick='remove_element(this.parentElement,"+val+",\"r1\")'>Remove</a>"
	newdiv.style.cssText="padding-left:20px;border-bottom:black 1px solid;";
	//el.appendChild(newdiv);
	var script = document.createElement('script');
	script.src = '/sses/json/add_to_email_report.php?seldbname='+seldbname+'&callback=afteradd&id='+val+'&rv=r1';
	document.head.appendChild(script);
}
function add_to_r2(){
	selected_element = document.getElementById("r2_contacts");
	seled = document.getElementById("r2_sel");
	val = seled.options[seled.selectedIndex].value
	newdiv = document.createElement('div');
	newdiv.innerHTML=seled.options[seled.selectedIndex].text+"- <a onclick='remove_element(this.parentElement,"+val+",\"r2\")'>Remove</a>"
	newdiv.style.cssText="padding-left:20px;border-bottom:black 1px solid;";
	//el.appendChild(newdiv);
	var script = document.createElement('script');
	script.src = '/sses/json/add_to_email_report.php?seldbname='+seldbname+'&callback=afteradd&id='+val+'&rv=r2';
	document.head.appendChild(script);
}

function afteradd(wasadded){
	if(wasadded=="added"){
		selected_element.appendChild(newdiv);
	}
}
function afterremove(wasremoved){
	if(wasremoved=="removed"){
		selected_element.remove()
	}
}
</script>
</body>
</html>
