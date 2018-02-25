<?php
//	Written by: Richard Gonsuron
//	Copyright: 2009, Digital Oil Tools
//	All rights reserved.
//	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
//	or distribute this file in any manner without written permission of Digital Oil Tools
require_once("dbio.class.php");

$seldbname=$_REQUEST['seldbname'];
$ret=$_REQUEST['ret'];
$db=new dbio($seldbname);
$db->OpenDb();

$query = "Select * from import_config";
$db->DoQuery($query);
$import_config = array();
while($row = $db->FetchRow()){
	array_push($import_config,$row);
}
?>
<!doctype html>
<head>
<link rel="stylesheet" type="text/css" href="gva_styles.css" />.
<link rel='stylesheet' type='text/css' href='projws.css'/>
<link rel='stylesheet' type='text/css' href='themes/blue/style.css'/>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />
<script src="https://code.jquery.com/jquery-1.9.1.js"></script>
<script src="https://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
<link rel='stylesheet' type='text/css' href='jquery.timepicker.css'/>
<script type="text/javascript" src='jquery.timepicker.js'></script>
<script type="text/javascript" src='js/jquery.tablesorter.js'></script>
</head>
<body>



<TABLE class='container'>
<tr>
<td>
	<A class='menu' href='#' onclick='window.close()'>Close</A>
	<h1>LAS File To Import:</h1>
</td>
</tr>
<tr>
<td class="container" align='left'>
	<form method="post" action='/sses/upload_raw_file.php' enctype="multipart/form-data" id='fileform'>
	<input type='hidden' name='ret' value='<?echo $ret;?>'>
	<input type='hidden' name='seldbname' value='<?echo $seldbname;?>'>
	<input type='hidden' name='JSON' value='true'>
	<input type="file" name="userfile" size="90" id='userfile'>
	</form>
</td>
</tr>
<tr>
<td class="container" align='right'>
	<input type="submit" value="Import" onclick="importWellLogSet()">
</td>
</tr>

<tr><td>
	<div id='content_loaded_action_area'>
		<div id='not_configured' <?php echo (count($import_config)>0 ? 'style="display:none;"':'')?>>
			<div>Header data is not configured. Please configure the header data.</div>
			<div>This is the header data we have found within your LAS file. Please assign the proper data columns</div>
			<div id='header_assigment'>
			</div>
		</div>
		<div id='configuration_mismatch'></div>
		<div id='properly_configured'></div>
	</div>
</td></tr>
<tr>
<td>
	<center><small><small>&#169; 2010-2011 Digital Oil Tools</small></small></center>
</td>
</tr>
</table>



<script language="javascript">
var surveys  = [];
var isConfigged = <?php echo count($import_config)>0 ? 'true':'false';?>;
var configuredElementCount = <?php echo count($import_config)?>;
var headerConfig = {};
var seldbname = '<?php echo $_REQUEST['seldbname']?>';
<?php if(count($import_config)>0){
	foreach($import_config as $config){
	?>
		headerConfig['<?php echo $config['field_value']?>'] = {index: <?php echo $config['field_column_index']?>, value: '<?php echo $config['field_name']?>'};	
<?php }
}?>
function readLasFile(evt) {
	var xhr = new XMLHttpRequest();	
	xhr.open('POST', '/sses/upload_raw_file.php', true);
	var file = this.files[0];	
	var fd = new FormData()
	fd.append("userfile", file);
    xhr.onreadystatechange = function () {
  	  	  if(xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
    	    parseLasFile(xhr.responseText)
    	  }
    	};
	xhr.send(fd);
  }

document.getElementById('userfile').addEventListener('change', readLasFile, false);

var file_data = ''

function parseLasFile(filecontents){
	var split1 = filecontents.split('~Curve Information')
	var split2 = split1[1].split("~ASCII LOG DATA SECTION")
	var header_data = split2[0]
	file_data = split2[1].split("\n")
	if(validateConfiguration(header_data)){			
		extractDataSets(file_data)
	}
}

function validateHeadersPositions(header_data){
	return true
}

function validateConfiguration(header_data){
	if(isConfigged){
	  return validateHeadersPositions(header_data)
	}
	if(header_data.indexOf('#-') > -1){ 
		header_fields = header_data.split('#')[1].split(/[ \t]+/)
	} else {
		header_array_fields = header_data.split("\n")
		header_fields = []
		for(var i=0; i< header_array_fields.length; i++){
			var row = header_array_fields[i]
			var header = row.trim().split(" ")[0]
			if(header.trim()!=""){
				header_fields.push(header)
			}
		}
	}
		
	html='<table><form id="column_assignment_form" onsubmit="return false;">'
	html+='<tr><td colspan="2" style="text-align:right"><button onclick="assignDataSetConfigs()">assign</button></td></tr>'
	var trimed_hdr_cnt = 0
	for(var i=0; i< header_fields.length; i++){
		if(header_fields[i].trim()!=""){
			html+='<tr><td>'+header_fields[i]+'</td><td><select class="index_assigment" name="select_idx_'+(i-trimed_hdr_cnt)+'" id="hf_index_'+header_fields[i]+'"><option>-</option><option value="md">MD</option><option value="gr">Gamma</option><option value="tvd">TVD</option><option value="vs">VS</option><option value="inc">INC</option><option value="azm">AZM</option><option value="add_data">Additional Data</option></select></td></tr>'
		} else {
			trimed_hdr_cnt++	
		}
	}
	html+='<tr><td colspan="2" style="text-align:right"><button onclick="assignDataSetConfigs()">assign</button></td></tr>'
	html+='</form></table>'
	document.getElementById('header_assigment').innerHTML = html
	
}

function assignDataSetConfigs(){
	var form = document.getElementById('column_assignment_form')
	var elements = document.getElementsByClassName('index_assigment')
	var didAssign = false
	var add_data_cnt = 0
	for(var i = 0; i < elements.length; i++){
		element = elements[i]
		field_name = element.id.split('_')[2]
		field_idx  = element.name.split('_')[2]
		field_val  = element.options[ element.selectedIndex ].value
		if(field_val != '-'){
			if(field_val=='add_data'){
				field_val+= "_"+field_name
			}
			didAssign = true
			assignDataSetConfig(field_idx, field_name, field_val)
		}		
	}
	if(didAssign){
		document.getElementById('not_configured').style.display='none'
		extractDataSets(file_data)
	} 
}

function assignDataSetConfig(column_index, name, value){
	headerConfig[value]= {index: column_index, name: name}
	var xhr = new XMLHttpRequest();
	xhr.open('GET', '/sses/json.php?path=json/sgta_modeling/update_import_config.php&seldbname=<?php echo $seldbname ?>&field='+name+'&value='+value+'&col_idx='+column_index);
	xhr.send();
}

function submitDataCheck(){
	var xhr = new XMLHttpRequest();	
	xhr.open('post', '/sses/welllogupload.php', true);
    xhr.setRequestHeader("Content-Type","multipart/form-data");
	xhr.send(new FormData(document.getElementById('fileform')));
}

function importSurverys(){
	for(var i = 0; i < surveys.length; i++){
		var xhr = new XMLHttpRequest();
		var formdata = new FormData();
		survey = surveys[i]
		formdata.append('json', 'true')
		formdata.append('seldbname', seldbname)
		formdata.append('md', survey.md)
		formdata.append('inc', survey.inc)
		formdata.append('azm', survey.azm)
		if(i != surveys.length-1){	
			formdata.append('nocalc', 'true')
		}
		xhr.open('post', '/sses/surveyadd.php', true);
		xhr.send(formdata)
	}
	
}

function importAdditionalData(welllogset){
	
}

function importWellLogSet(){
	var xhr = new XMLHttpRequest();
	xhr.open("POST", "/sses/json.php?path=json/sgta_modeling/import_well_log.php&seldbname=<?php echo $seldbname ?>");
	xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
	xhr.send(JSON.stringify(surveys));
}

function extractDataSets(data){
	var last_inc = 0;
	var last_azm = 0;
	var surveycnt = 0;	
	var azms = []
	var tvds = []
	var vss  = []
	var depths = []
	var ropes  = []
	var gases   = []
	var gammas = []
	
	for(var i = 1; i < data.length; i++){		
		var row = data[i].split(/[ \t]+/)
		var depth = row[headerConfig['md'].index]
		var gamma = row[headerConfig['gr'].index]
		var tvd = row[headerConfig['tvd'].index]
		var vs  = row[headerConfig['vs'].index]
		var inc = row[headerConfig['inc'].index]
		var azm = row[headerConfig['azm'].index]
		var rop = row[6]
		var gas = row[7]
		vss.push(vs)
		tvds.push(tvd)
		depths.push(depth)
		gammas.push(gamma)
		gases.push(gas)
		ropes.push(rop)
		azms.push(azm)
		var survey_data_row = false
		if(inc > -9999){
			survey_data_row = true
		}

		if( last_inc != inc && survey_data_row){
			surveys.push( {
					tvd: tvd,
					md: depth,
					inc: inc,
					azm: azm,
					vs: vs,
					azms : azms.slice(0),
					tvds : tvds.slice(0),
					vss  : vss.slice(0),
					depth: depths.slice(0),
					gamma: gammas.slice(0),
					gas: gases.slice(0),
					rop: ropes.slice(0)						
				})
			azms = []
			tvds = []
			vss  = []
			depths = []
			gammas = []
			gases  = []
			ropes  = []
		}
		last_inc = inc
		last_azm = azm
	}
	displayDetectedSurveys()
}

function displayDetectedSurveys(){
	var html = "<table class='tablesorter'><tr><td>Depth</td><td>Inc</td><td>Azm</td><td>TVD</td><td>Vs</td></tr>"
	for(var i = surveys.length-1; i >= 0; i--){
		var survey = surveys[i];
		html+= "<tr><td class='surveys'>"+survey.md
		html+= "</td><td class='surveys'>"+survey.inc
		html+= "</td><td class='surveys'>"+survey.azm
		html+= "</td><td class='surveys'>"+survey.tvd
		html+= "</td><td class='surveys'>"+survey.vs
		html+="</td></tr>"
		
	}
	html+="</table>"	
	document.getElementById('properly_configured').innerHTML = html
}
function show_alert(rowform)
{
	// var r=confirm("Ready to import LAS file?");
	// if (r==true)
  	// {
		t = 'welllogupload.php';
		t = encodeURI (t); // encode URL
		rowform.action = t;
		rowform.submit(); // submit form using javascript
		return true;
  	// }
	// rowform.userfile.value="";
	// document.location=rowform.ret.value;
	// return ray.ajax();
}
var ray={
ajax:function(st) { this.show('load'); },
show:function(el) { this.getID(el).style.display=''; },
getID:function(el) { return document.getElementById(el); }
}
</script>

</body>
</html>
