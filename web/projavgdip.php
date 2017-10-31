<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
session_start();
$user_level=$_SESSION['userlevel'];
require_once("dbio.class.php");
$seldbname=$_GET['seldbname'];
$username=$_GET['username'];
$setcalc = isset($_REQUEST['calcdipset'])?$_REQUEST['calcdipset']:'ad';
$ret=$_GET['ret'];
$db=new dbio($seldbname);
$db->OpenDb();
include("readappinfo.inc.php");
if($sgta_off){
	include("readsurveys.inc.php");
	$svy_cnt=$svy_cnt-1;
	$sgta_off_section="<div>
	apply to surveys <input size=3 type='text' value='0' name='svy_start'>(0-$svy_cnt) through <input size=3 type='text' value='$svy_cnt' name='svy_end'>(0-$svy_cnt)<br>
	A dip of <input size=5 type='text' value='' id='svy_dip' name='svy_dip'>
</div>";
	$sv_strg = 'Set Dips';
} else {
	$sgta_off_section='';
	$sv_strg = 'Set Projected Dips';
}
$db=new dbio($seldbname);
$db->OpenDb();
$db->DoQuery("SELECT * FROM controllogs ORDER BY tablename");
if ($db->FetchRow()) {
	$tablename=$db->FetchField("tablename");
	$startmd=$db->FetchField("startmd");
	$endmd=$db->FetchField("endmd");
	$cltot=$db->FetchField("tot");
	$clbot=$db->FetchField("bot");
	$cldip=$db->FetchField("dip");
	$stregdipazm=$db->FetchField("azm");
}
$db->DoQuery("SELECT * FROM wellplan WHERE hide=0 ORDER BY md ASC");
$num=$db->FetchNumRows(); 
$i = 0;
$control_options=array();
while($i < $num){
	$db->FetchRow();
	if($i==0){$i++;continue;}
	$md=sprintf("%.2f", $db->FetchField("md"));
	$nsraw = $db->FetchField("ns");
	$ewraw = $db->FetchField("ew");
	$closure = atan2($ewraw,$nsraw);
	$regazm = deg2rad($stregdipazm);
	$tregdip=sprintf("%.2f",atan(tan(deg2rad($cldip))*cos($regazm - $closure))*(180/pi()));
	array_push($control_options,"<option value='$i|$tregdip'>$i - $md</option>");
	$i++;
}
$db->DoQuery("SELECT * FROM surveys WHERE hide=0 and plan=0 ORDER BY md desc");
$num=$db->FetchNumRows()-1; 
$survey_options=array();
while($num > 0 ){
	$db->FetchRow();
	if($num==0){$num--;continue;}
	$md=sprintf("%.2f", $db->FetchField("md"));
	$nsraw = $db->FetchField("ns");
	$ewraw = $db->FetchField("ew");
	$closure = atan2($ewraw,$nsraw);
	$regazm = deg2rad($stregdipazm);
	$tregdip=sprintf("%.2f",atan(tan(deg2rad($cldip))*cos($regazm - $closure))*(180/pi()));
	array_push($survey_options,"<option value='$num|$tregdip'>$num - $md</option>");
	$num--;
}
?>
<HTML>
<HEAD>
<TITLE>Average Dip Calculator</TITLE>
<LINK rel='stylesheet' type='text/css' href='projavgdip.css'/>
</HEAD>
<SCRIPT language="javascript">
function recalc() {
	var count=parseInt(document.getElementById("svycnt").value);
	var total=parseInt(document.getElementById("totalsvys").value);
	var dip=0.0;
	for(i=0; i<count && i<total; i++)
		dip+=parseFloat(document.getElementById("dip"+i).value);
	dip/=i;
	document.getElementById("dip").value=dip.toFixed(2);
	try{
		document.getElementById("svy_dip").value=dip.toFixed(2);
	}catch(e){}
	if(count>total) document.getElementById("svycnt").value=total;
	document.getElementById("svycnt").select();
}
function onclose()
{
	window.close();
}
function selectAvgDisplay(selin){
	document.getElementById('ad').style.display='none';
	document.getElementById('acdc').style.display='none';
	document.getElementById('ardc').style.display='none';
	document.getElementById(selin).style.display='inline';
	if(selin=='acdc'){
		calcControlDipClosure();
	}
	if(selin=='ardc'){
		calcSurveyDipClosure();
	}
	if(selin=='ad'){
		recalc();
	}
	
}
function calcControlDipClosure(){
	sv = document.getElementById('acdc_start');
	ev = document.getElementById('acdc_end');
	sv_ar=sv.value.split('|');
	sv_id = parseInt(sv_ar[0])
	ev_ar =ev.value.split('|');
	ev_id = parseInt(ev_ar[0])
	avg_val = 0;
	avg_cnt = 0;
	i=0;
	if(sv_id < ev_id){
		fid =sv_id;
		eid = ev_id;
	} else {
		fid = ev_id;
		eid = sv_id;

	}

	while(true){					
			lar = sv.options[i].value.split('|')
			lid = parseInt(lar[0]);
			lval = parseFloat(lar[1]);
			if(avg_cnt >= 1 || lid==fid || lid==eid){
				avg_cnt++;
				avg_val+=lval;
				if(lid==eid){
					break;
				}
			}
			i++;
		}
	document.getElementById('acdc_dip').value = avg_val/avg_cnt;
}
function calcSurveyDipClosure(){
	sv = document.getElementById('ardc_start');
	ev = document.getElementById('ardc_end');
	sv_ar=sv.value.split('|');
	sv_id = parseInt(sv_ar[0])
	ev_ar =ev.value.split('|');
	ev_id = parseInt(ev_ar[0])
	avg_val = 0;
	avg_cnt = 0;
	i=0;
	if(sv_id > ev_id){
		fid =sv_id;
		eid = ev_id;
	} else {
		fid = ev_id;
		eid = sv_id;

	}
	while(true){			
		
			lar = sv.options[i].value.split('|')
		
			lid = parseInt(lar[0]);
			lval = parseFloat(lar[1]);
			if(avg_cnt >= 1 || lid==fid || lid==eid){
				avg_cnt++;
				avg_val+=lval;
				if(lid==eid){
					break;
				}
			}
			i++;
		}
	document.getElementById('ardc_dip').value = avg_val/avg_cnt;
}
function onprojsetdips()
{
	val = document.getElementById('calcavg').value
	var rowform=document.getElementById("dipform"+val);
	t = 'projsetdips.php';
	t = encodeURI (t);
	rowform.action = t;
	rowform.submit();

//	if(window.opener && !window.opener.closed) {
//		window.opener.location.reload();
//	}
//	window.location.reload();
//	window.close();
	return true;
}
</SCRIPT>
<BODY onload='selectAvgDisplay("<?echo $setcalc?>")'>
<CENTER>
<TABLE class='tabcontainer' style='width=300px'>
<?
$db->DoQuery("SELECT * FROM surveys WHERE plan<=0 ORDER BY md DESC;");
$svycnt=0;
$totalsvys=0;
while($db->FetchRow()) {
	$dip=$db->FetchField("dip");
	echo "<INPUT type='hidden' id='dip$svycnt' value='$dip'>\n";
	$svycnt++;
	$totalsvys++;
}
// exclude the last read (tie-in) which has no dip
$svycnt--; $totalsvys--;
?>

<TR><TD><select id='calcavg' onchange="selectAvgDisplay(this.value);">
						<option value='ad' <?echo $setcalc=='ad'?'selected':''?>>Average Dip</option>
						<option value='acdc' <?echo $setcalc=='acdc'?'selected':''?>>Average Control Dip-Closure</option>
						<option value='ardc' <?echo $setcalc=='ardc'?'selected':''?>>Average Real Dip-Closure</option>
					</select></TD></TR>


<TR>
<td>
	<FORM id='dipformad' name='dipform' method='post'>
	<div id='ad'>
		<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
		<INPUT type='hidden' name='username' value='<?echo $username;?>'>
		<INPUT type='hidden' name='ret' value='<?echo $ret;?>'>
		<Input type='hidden' name='calcdipset' value='ad'>
		<div> Total Surveys: <input type='text' size='4' readonly='true' id='totalsvys' value='<?echo $totalsvys;?>'> </div>
		<div> Surveys To Average: <input type='text' <? if ($user_level > 1) echo 'readonly=true'; ?> size='4' id='svycnt' value='<?echo $svycnt;?>' onchange='recalc();'> </div>
		<div>Average Dip:<input type='text' <? if ($user_level > 1) echo 'readonly=true'; ?> size='4' id='dip' name='dip' value='0'></div>
		<div><?echo $sgta_off_section ?></div>
	</div>
	</FORM>
	<FORM id='dipformacdc' name='dipform' method='post' >
	<div id='acdc'  style='display:none;'>
	
	<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
	<INPUT type='hidden' name='username' value='<?echo $username;?>'>
	<INPUT type='hidden' name='ret' value='<?echo $ret;?>'>
	<Input type='hidden' name='calcdipset' value='acdc'>
	<div>Start Survey: <select name='vals' id='acdc_start' onchange="calcControlDipClosure()"><?echo implode('',$control_options)?> </select></div>
	<div>End Survey: <select  name='vale' id='acdc_end' onchange="calcControlDipClosure()"><?echo implode('',$control_options)?> </select></div>
	<div>Average Dip: <input type='text' <? if ($user_level > 1) echo 'readonly=true'; ?> size='14' id='acdc_dip' name='dip' value='0'> </div>
	<div><?echo $sgta_off_section ?></div>
	</div>
	</FORM>

	<FORM id='dipformardc' name='dipform' method='post'>
	<div id='ardc' style='display:none;'>
	
	<INPUT type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
	<INPUT type='hidden' name='username' value='<?echo $username;?>'>
	<INPUT type='hidden' name='ret' value='<?echo $ret;?>'>
	<Input type='hidden' name='calcdipset' value='ardc'>
	<div> Start Survey:<select name='vals' id='ardc_start' onchange="calcSurveyDipClosure()"><?echo implode('',$survey_options)?> </select></div>
	<div>End Survey:<select name='vale' id='ardc_end' onchange="calcSurveyDipClosure()"><?echo implode('',$survey_options)?> </select></div>
	<div>Average Dip:<input type='text' <? if ($user_level > 1) echo 'readonly=true'; ?> size='14' id='ardc_dip' name='dip' value='0'> </div>
	<div><?echo $sgta_off_section ?></div>
	</div>
	</FORM>

</td>
</tr>
<tr>
<TD >
	<INPUT type='submit' value='Close' onclick='onclose();'>
	<INPUT type='submit' <? if ($user_level > 1) echo 'disabled=true'; ?> value="<?php echo $sv_strg?>" onclick='onprojsetdips();'>
</TD>
</TR>
<tr>
<td >
	<br>
	<small>
	<small>
	&#169; 2010-2011 Digital Oil Tools
	</small>
	</small>
</td>
</tr>
</TABLE>
</CENTER>
</BODY>
</HTML>
<? $db->CloseDb(); ?>
