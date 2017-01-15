<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Supreme Source Energy Services, Inc.
//	All rights reserved.
//	NOTICE: This file is solely owned by Supreme Source Energy Services, Inc. You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Supreme Source Energy Services, Inc.

require_once("dbio.class.php");
$ret=$_GET['ret'];

$seldbname = $_REQUEST['seldbname'];
$db=new dbio($seldbname);
$db->OpenDb();

include("readappinfo.inc.php");

$db->DoQuery("select autodipconfig from wellinfo");
$db->FetchRow();
$init_config = $db->FetchField('autodipconfig');

$db->DoQuery("select cvalue from adm_config where cname = 'autoapply' limit 1");
$autoapply = '';
if($db->FetchRow()) $autoapply = $db->FetchField('cvalue');
else $db->DoQuery("insert into adm_config (cname) values ('autoapply')");

$db->DoQuery("select cvalue from adm_config where cname = 'manualdip' limit 1");
$manualdip = '';
if($db->FetchRow()) $manualdip = $db->FetchField('cvalue');
else $db->DoQuery("insert into adm_config (cname) values ('manualdip')");

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

$db->DoQuery("SELECT * FROM wellplan WHERE hide=0 ORDER BY md desc");
$num=$db->FetchNumRows(); 
$i = 0;
$control_options=array();
while($i < $num) {
	$db->FetchRow();
	if($i==0){$i++;continue;}
	$md=sprintf("%.2f", $db->FetchField("md"));
	$nsraw = $db->FetchField("ns");
	$ewraw = $db->FetchField("ew");
	$closure = atan2($ewraw,$nsraw);
	$regazm = deg2rad($stregdipazm);
	$tregdip=sprintf("%.2f",atan(tan(deg2rad($cldip))*cos($regazm - $closure))*(180/pi()));
	array_push($control_options,"<option value='$i|$tregdip'>$md($tregdip)</option>");
	$i++;
}
$db->DoQuery("SELECT * FROM surveys WHERE hide=0 and plan=0 ORDER BY md desc");
$num=$db->FetchNumRows()-1; 
$survey_options=array();
$average_options=array();
$i=0;
while($num > 0 ) {
	$db->FetchRow();
	if($num==0){$num--;continue;}
	$md=sprintf("%.2f", $db->FetchField("md"));
	$dip=sprintf("%.2f",$db->FetchField("dip"));
	$nsraw = $db->FetchField("ns");
	$ewraw = $db->FetchField("ew");
	$closure = atan2($ewraw,$nsraw);
	$regazm = deg2rad($stregdipazm);
	$tregdip=sprintf("%.2f",atan(tan(deg2rad($cldip))*cos($regazm - $closure))*(180/pi()));
	array_push($survey_options,"<option value='$i|$tregdip'>$md($tregdip)</option>");
	array_push($average_options,"<option value='$i|$dip'>$md($dip)</option>");
	$num--;
	$i++;
}
?>
<!DOCTYPE HTML>
<html>

<head>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>

<title>Auto Dip Calculator</title>
<link rel='stylesheet' type='text/css' href='projavgdip.css'/>

<script language='javascript'>
curdivid=0;
init_config= '<?=$init_config?>';

function brJsonRunData(json_data)
{
	$.getJSON('rundat.php',json_data,function(data) {
		if(data.res == 'ERR') alert('ERROR: ' + data.msg);
	}).fail(function( jqxhr, textStatus, error ) {
		alert('Request Failed: ' + textStatus + ", " + error);
	});
}

<!-- load from configuration parameters -->

function loadFromConfig()
{
	objs = JSON.parse(init_config);
	for(i in objs){
		ndivid = add_selection_row();
		obj = objs[i];
		if(obj.type=='man'){
			el = document.getElementById('manin'+ndivid)
			el.value=obj.avgval;
		}else{
			selel = document.getElementById('sel'+ndivid)
			selel.value=obj.type;
			row_calc_type_value_change(ndivid,true);
			sel1el = document.getElementById('selstart'+ndivid)
			for(i in sel1el.options){
				sel1eloptval = sel1el.options[i].value
				if(sel1eloptval){
				sel1eloptid  = sel1eloptval.split('|')[0]
				if(parseFloat(sel1eloptid)==parseFloat(obj.startidx)){
					sel1el.value=sel1eloptval;
					break;
				} }
			}
			sel2el = document.getElementById('selend'+ndivid).value = obj.avgsize;
			calcOnRangeDip(ndivid);
			calculate_dip()
		}
	}
	document.getElementById('autodip').value = document.getElementById('calcdip').value;
}

<!-- save configuration parameters -->

function save_config()
{
	objs = jsonify();
	stringfied = JSON.stringify(objs);
	init_config=stringfied;
	xmlhttp=new XMLHttpRequest();
	xmlhttp.open('POST','/sses/setfield.php',true);
	xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	xmlhttp.onreadystatechange=function(){}
  	xmlhttp.send("seldbname=<?=$seldbname?>&table=wellinfo&field=autodipconfig&value="+stringfied);

	var mval = $('#autodip').val();
	brJsonRunData({'sdb':'<?php echo $seldbname ?>','a':'setconf','n':'manualdip','v':mval});
}

<!-- make a json string of object information -->

function jsonify()
{
	cnt=1;
	val=0;
	had_val_cnt=0;
	avg=0;
	objs = [];
	while(cnt <= curdivid)
	{
		el = document.getElementById('manin'+cnt)
		if(el)
		{
			f = parseFloat(el.value)
			if(!isNaN(f))
			{
				eltype = document.getElementById('type'+cnt)
				
				obj = {"type":eltype.value}
				obj.id=cnt;
				if(obj.type=='man'){
					obj.avgval = f;
				} else {
					obj.avgval='calculated';
					obj.startidx = document.getElementById('selstart'+cnt).value;
					obj.avgsize  = document.getElementById('selend'+cnt).value;
				}
				objs.push(obj);
			} 
		}
		cnt++;
	}
	return objs;
}

<!-- figure out the calculated dip -->

function calculate_dip(){
	cnt=1;
	val=0;
	had_val_cnt=0;
	avg=0;
	while(cnt <= curdivid){
		el = document.getElementById('manin'+cnt)
		if(el){
			f = parseFloat(el.value)
			if(!isNaN(f)){
				had_val_cnt++;
				val+=f;
			} 
		}
		cnt++;
	}
	if(had_val_cnt>0){
		avg = val/had_val_cnt;
	} 
	document.getElementById('calcdip').value = avg;
}
function add_selection_row(){
	curdivid++;
	div = document.createElement('div');
	div.setAttribute('id',"inputdiv"+curdivid);
	div.setAttribute('style',"border-bottom:solid 1px black");
	div.innerHTML=create_manual_input(curdivid);
	mel = document.getElementById('selection_elements');
	mel.insertBefore(div,document.getElementById('addrow'));
	return curdivid;
}
function row_calc_type_value_change(inid,skipcalc){
	skipcalc = skipcalc?skipcalc:false;
	selel = document.getElementById('sel'+inid);
	divel = document.getElementById('inputdiv'+inid);
	/* console.log(selel.value); */
	if(selel.value=='man'){
		divel.innerHTML=create_manual_input(inid);
		if(!skipcalc){
			calculate_dip()
		}
	}else if(selel.value=='ad'){
		divel.innerHTML=create_average_dip(inid);
		if(!skipcalc){
			calcOnRangeDip(inid);
			calculate_dip()
		}
	}else if(selel.value=='ardc'){
		divel.innerHTML=create_real_dip_closure(inid);
		if(!skipcalc){
			calcOnRangeDip(inid);
			calculate_dip()
		}	
	}else if(selel.value=='acdc'){
		divel.innerHTML=create_control_dip_closure(inid);
		if(!skipcalc){
			calcOnRangeDip(inid);
			calculate_dip()
		}
	}
	if(!skipcalc){
		save_config()
	}
}
function remove_selection_row(el){
	mel = document.getElementById('selection_elements');
	mel.removeChild(el);
	calculate_dip();	
	save_config();
}

function calcOnRangeDip(inid){
	sv = document.getElementById('selstart'+inid);
	ev = document.getElementById('selend'+inid);
	numtoavg = ev.value
	startat = sv.value.split('|');
	startat_id= parseInt(startat[0]);
	start_avging=false;
	cnt=0;
	avgtot=0;
	for(i in sv.options){
		if(sv.options[i].value){
			lar = sv.options[i].value.split('|');
			lid = parseInt(lar[0]);
			lval = parseFloat(lar[1]);
			if(!start_avging){
				if(startat_id==lid){
					start_avging =true
				}
			} else {
				avgtot+=lval;
				cnt++;
			}
			if(cnt >= numtoavg){
				break;
			}
		}
	}
	document.getElementById("manin"+inid).value = avgtot/cnt;
	calculate_dip();
}

function get_calc_select(inid,seltype){
	man_sel=seltype=='man'?'selected':'';
	ad_sel=seltype=='ad'?'selected':'';
	ardc_sel=seltype=='ardc'?'selected':'';
	acdc_sel=seltype=='acdc'?'selected':'';
	
	return "<select id='sel"+inid+"' onchange=\"row_calc_type_value_change("+inid+")\"><option value='man' "+man_sel+">Manual input</option><option value='ad' "+ad_sel+">Average Dip</option><option value='acdc' "+acdc_sel+">Average Control Dip-Closure</option><option value='ardc' "+ardc_sel+">Average Real Dip-Closure</option></select>";
}

function create_manual_input(inid)
{
	addin="";
	return "<input id='type"+inid+"' name='type' type='hidden' value='man'>"+get_calc_select(inid,'man')+"<input onchange=\"calculate_dip();save_config();\" id='manin"+inid+"' type='text'>&nbsp;<span class='hpoint' onclick=\"remove_selection_row(this.parentElement)\">X</span>";
}

function create_average_dip(inid){
	options_sray = "<select onchange='calcOnRangeDip(" + inid + ");save_config();' id='selstart" + inid + "'>" +
		"<?=implode("",$average_options)?>" + "</select>";
	options_eray = "<select onchange=\"calcOnRangeDip("+inid+");save_config();\" id='selend"+inid+"'>"+"<?php
	for($i=2; $i<count($average_options); $i++) {
		echo "<option value=$i>$i</option>";
	} ?>"+"</select>";
	addin ="<input id='type"+ inid +"' name='type' type='hidden' value='ad'>&nbsp;start with" +
		options_sray + " average back " + options_eray;
	return get_calc_select(inid,'ad') + addin + "&nbsp;<input onchange='calculate_dip()' id='manin" + inid +
		"' type='text' disabled>&nbsp;<span class='hpoint' onclick='remove_selection_row(this.parentElement)'>X</span>";
}

function create_real_dip_closure(inid){
	options_sray = "<select onchange=\"calcOnRangeDip("+inid+");save_config();\"  id='selstart"+inid+"'>"+"<?=implode("",$survey_options)?>"+"</select>";
	options_eray = "<select onchange=\"calcOnRangeDip("+inid+");save_config();\" id='selend"+inid+"'> "+"<?for($i=2;$i<count($survey_options);$i++){echo "<option value=$i>$i</option>";}?>"+"</select>";
	addin ="<input id='type"+inid+"' name='type' type='hidden' value='ardc'>&nbsp;start with"+options_sray+" average back "+ options_eray;
	return get_calc_select(inid,'ardc')+addin+"&nbsp;<input onchange=\"calculate_dip()\" id='manin"+inid+"' type='text' disabled>&nbsp;<span class='hpoint' onclick=\"remove_selection_row(this.parentElement)\">X</span>";
}
function create_control_dip_closure(inid){
	console.log('create control dip closure');
	options_sray = "<select onchange=\"calcOnRangeDip("+inid+");save_config();\" id='selstart"+inid+"'>"+"<?=implode("",$control_options)?>"+"</select>";
	options_eray = "<select onchange=\"calcOnRangeDip("+inid+");save_config();\" id='selend"+inid+"'>"+"<?for($i=2;$i<count($control_options);$i++){echo "<option value=$i>$i</option>";}?>"+"</select>";
	addin ="<input id='type"+inid+"' name='type' type='hidden' value='acdc'>&nbsp;start with"+options_sray+" average back "+ options_eray;
	fret = get_calc_select(inid,'acdc')
	fret+=addin
	fret+="&nbsp;<input onchange=\"calculate_dip()\" id='manin"+inid+"' type='text' disabled>&nbsp;<span class='hpoint' onclick=\"remove_selection_row(this.parentElement)\">X</span>";
	return fret;
}

function apply_dip() {
	if(window.opener && !window.opener.closed) {
		window.opener.document.getElementById('sectdip_parent').value =
			document.getElementById('autodip').value;
		window.opener.setdscfg();
	} else {
		alert("Dip cannot be applied. SGTA window has been closed or is not accessible.")
	}

	// save the value for manual auto dip

	var mval = $('#autodip').val();
	brJsonRunData({'sdb':'<?php echo $seldbname ?>','a':'setconf','n':'manualdip','v':mval});
}

function close_dip()
{
	var mval = $('#autodip').val();
	var json_data = {'sdb':'<?php echo $seldbname ?>','a':'setconf','n':'manualdip','v':mval};

	$.getJSON('rundat.php',json_data,function(data) {
		if(data.res == 'ERR') alert('ERROR: ' + data.msg);
		else window.close();
	}).fail(function(jqxhr,textStatus,error) {
		alert('Request Failed: ' + textStatus + ", " + error);
	});
}
</script>

</head>

<body>

<?php
//echo '<pre>control_options = '; print_r($control_options); echo '</pre>';
//echo '<pre>survey_options = '; print_r($survey_options); echo '</pre>';
//echo '<pre>average_options = '; print_r($average_options); echo '</pre>';
?>

<div id='selection_elements' class='tabcontainer' style='width:680px;margin:10px auto;padding:0'>
<div id='addrow' class='hpoint' style='padding-top:5px' onclick='add_selection_row()'>add additional row</div>
<div>calculated dip:<input type='text' id='calcdip' name='calcdip'><button onclick="document.getElementById('autodip').value=document.getElementById('calcdip').value;save_config();">use calc dip</button></div>
<div>apply this dip:<input type='text' id='autodip' name='autodip'></div>

<div style='float:right'><button style='margin-left:20px' onclick='apply_dip()'>apply</button><button onclick='close_dip()'>close</button></div>

<table style='border:1px solid gray;float:right;border-collapse:collapse;margin-top:3px;'>
<tr>
<td style='valign:middle;font-weight:bolder'>Auto Apply:</td>
<td style='valign:middle;padding:5px 0 5px 15px'><input type='checkbox' id='autoapplyman' /></td>
<td style='valign:middle'>Manual Dip</td>
<td style='valign:middle;padding:5px 0 5px 15px'><input type='checkbox' id='autoapplycalc' /></td>
<td style='valign:middle'>Calculated Dip</td>
</tr>
</table>

<div style='clear:both'></div>

<div style='padding-top:10px'><small>&#169; 2010-2011 Supreme Source Energy Services, Inc.</small></div>
</div>

<script>
$(document).ready(function() {

	// load from the last configuration

	loadFromConfig();

	// now check the auto apply

	var autoapply = '<?php echo $autoapply ?>';

	if(autoapply == 'Calculated') $('#autoapplycalc').attr('checked',true);
	else if(autoapply == 'Manual') $('#autoapplyman').attr('checked',true);

	$('#autoapplyman').change(function() {
		var state = '';
		if($(this).is(':checked')) {
			$('#autoapplycalc').attr('checked',false);
			state = 'Manual';
		}
		brJsonRunData({'sdb':'<?php echo $seldbname ?>','a':'setauta','s':state});
		return;
	});

	$('#autoapplycalc').change(function() {
		var state = '';
		if($(this).is(':checked')) {
			$('#autoapplyman').attr('checked',false);
			var state = 'Calculated';
		}
		brJsonRunData({'sdb':'<?php echo $seldbname ?>','a':'setauta','s':state});
		return;
	});

	// if the auto apply is for manual then update the manual field

	if(autoapply == 'Manual') $('#autodip').val('<?php echo $manualdip ?>');
});
</script>

</body>
</html>
<?php $db->CloseDb(); ?>
