<?php
//	Created on Jan 5, 2013
//
//	To change the template for this generated file go to
//	Window - Preferences - PHPeclipse - PHP - Code Templates

require_once("dbio.class.php");
require_once("classes/WitsmlData.class.php");
$seldbname = $_REQUEST['seldbname'];
$db=new dbio($seldbname);
$db->OpenDb();
include("readwellinfo.inc.php");
include("readappinfo.inc.php");
$db->CloseDb();
$autoimport_configured=false;
if($autorc_type=='rigminder'){
	require_once('classes/RigMinderConnection.php');
	$autoimport_configured=true;
	$obj= new RigMinderConnection($_REQUEST);
	if($obj->is_connected){
		$next = $obj->load_next_survey(false);
		if($next['next_survey']){
			$new_sdisp = "display:block";
			$count_disp= "display:none";
		} else {
			$new_sdisp = "display:none";
			$count_disp= "display:none";
		}

		if($next['cleanup_occured']===true){
			$cleanup_msg = $next['cmes'];
		}else if($next['cleanup_occured']){
			if($next['cmes']){
				$next['cmes'].="<br><br>";
			}
			$cleanup_msg = "An automatic survey clean up has occured. {$next['cmes']} If you want to review what data " .
				"has been removed please <a onclick='load_del_group({$next['cleanup_occured']})' style='cursor:pointer'>" .
				"click here.<script>reload_parent()</script>";
		} else {
			$cleanup_msg = "No clean up has occured";
		}
	}
 }elseif($autorc_type=='polaris'){
 	require_once('classes/PolarisConnection.class.php');
 	$autoimport_configured=true;
 	$query="select * from witsml_details";
 	$db->OpenDb();
 	$db->DoQuery($query);
 	$row = $db->FetchRow();
 	if($row){
 		$query= "update witsml_details set endpoint='$autorc_host',username='$autorc_username',password='$autorc_password'";
 	} else {
 		$query = "insert into witsml_details (endpoint,username,password) values ('$autorc_host','$autorc_username','$autorc_password')";
 	}
 	$db->DoQuery($query);
 	$query = "select * from witsml_details";
	$db->DoQuery($query);
	$row = $db->fetchRow();
	$db->CloseDb();
	if(!$row['wellid'] || !$row['boreid'] || !$row['logid']){
		$next = array("next_survey"=>false,"md"=>'',"inc"=>'',"azm"=>'',"msg"=>'Connection not configured. Please configure the connection selecting a well, a well bore and a log.');;
	} else {
		$obj = new PolarisConnection($_REQUEST);
		$obj->uidWell=$row['wellid'];
		$obj->uidWellBore=$row['boreid'];
		//$obj->logid=$row['logid'];
		$next = $obj->load_next_survey($do_load);
	}
 	
	if($next['next_survey']){
		$new_sdisp = "display:block";
		$count_disp= "display:none";
	} else {
		$new_sdisp = "display:none";
		$count_disp= "display:none";
	}
	if($next['cleanup_occured']===true){
		$cleanup_msg = $next['cmes']." Please <a style='cursor:pointer' onclick=\"load_survey(false,true)\">click here if you want to cleanup</a>.";
	}else if($next['cleanup_occured']){
		if($next['cmes']){
				$next['cmes'].="<br><br>";
			}
		$cleanup_msg = "An automatic survey clean up has occured.".$next['cmes']." If you want to review what data has been removed please <a onclick='load_del_group(".$next['cleanup_occured'].")' style='cursor:pointer'>click here.<script>reload_parent()</script>";
	} else {
		$cleanup_msg = "No clean up has occured";
	}
} else if($autorc_type=='digidrill'){
require_once('classes/PolarisConnection.class.php');
 	$autoimport_configured=true;
 	$query="select * from witsml_details";
 	$db->OpenDb();
 	$db->DoQuery($query);
 	$row = $db->FetchRow();
 	if($row){
 		$query= "update witsml_details set endpoint='$autorc_host',username='$autorc_username',password='$autorc_password'";
 	} else {
 		$query = "insert into witsml_details (endpoint,username,password) values ('$autorc_host','$autorc_username','$autorc_password')";
 	}
 	$db->DoQuery($query);
 	$query = "select * from witsml_details";
	$db->DoQuery($query);
	$row = $db->fetchRow();
	$db->CloseDb();
	if(!$row['wellid'] || !$row['boreid'] || !$row['logid']){
		$next = array("next_survey"=>false,"md"=>'',"inc"=>'',"azm"=>'',"msg"=>'Connection not configured. Please configure the connection selecting a well, a well bore and a log.');;
	} else {
		$obj = new PolarisConnection($_REQUEST);
		$obj->uidWell=$row['wellid'];
		$obj->uidWellBore=$row['boreid'];
		//$obj->logid=$row['logid'];
		$next = $obj->load_next_survey($do_load);
	}
 	
	if($next['next_survey']){
		$new_sdisp = "display:block";
		$count_disp= "display:none";
	} else {
		$new_sdisp = "display:none";
		$count_disp= "display:none";
	}
	if($next['cleanup_occured']===true){
		$cleanup_msg = $next['cmes']." Please <a style='cursor:pointer' onclick=\"load_survey(false,true)\">click here if you want to cleanup</a>.";
	}else if($next['cleanup_occured']){
		if($next['cmes']){
				$next['cmes'].="<br><br>";
			}
		$cleanup_msg = "An automatic survey clean up has occured.".$next['cmes']." If you want to review what data has been removed please <a onclick='load_del_group(".$next['cleanup_occured'].")' style='cursor:pointer'>click here.<script>reload_parent()</script>";
	} else {
		$cleanup_msg = "No clean up has occured";
	}
} else if($autorc_type=='welldata'){
require_once('classes/PolarisConnection.class.php');
 	$autoimport_configured=true;
 	$query="select * from witsml_details";
 	$db->OpenDb();
 	$db->DoQuery($query);
 	$row = $db->FetchRow();
 	if($row){
 		$query= "update witsml_details set endpoint='$autorc_host',username='$autorc_username',password='$autorc_password'";
 	} else {
 		$query = "insert into witsml_details (endpoint,username,password) values ('$autorc_host','$autorc_username','$autorc_password')";
 	}
 	$db->DoQuery($query);
 	$query = "select * from witsml_details";
	$db->DoQuery($query);
	$row = $db->fetchRow();
	$db->CloseDb();
	if(!$row['wellid'] || !$row['boreid'] || !$row['logid']){
		$next = array("next_survey"=>false,"md"=>'',"inc"=>'',"azm"=>'',"msg"=>'Connection not configured. Please configure the connection selecting a well, a well bore and a log.');;
	} else {
		$obj = new PolarisConnection($_REQUEST);
		$obj->uidWell=$row['wellid'];
		$obj->uidWellBore=$row['boreid'];
		//$obj->logid=$row['logid'];
		$next = $obj->load_next_survey($do_load);
	}
 	
	if($next['next_survey']){
		$new_sdisp = "display:block";
		$count_disp= "display:none";
	} else {
		$new_sdisp = "display:none";
		$count_disp= "display:none";
	}
	if($next['cleanup_occured']===true){
		$cleanup_msg = $next['cmes']." Please <a style='cursor:pointer' onclick=\"load_survey(false,true)\">click here if you want to cleanup</a>.";
	}else if($next['cleanup_occured']){
		if($next['cmes']){
				$next['cmes'].="<br><br>";
			}
		$cleanup_msg = "An automatic survey clean up has occured.".$next['cmes']." If you want to review what data has been removed please <a onclick='load_del_group(".$next['cleanup_occured'].")' style='cursor:pointer'>click here.<script>reload_parent()</script>";
	} else {
		$cleanup_msg = "No clean up has occured";
	}
}
?>
<!doctype html>
<html>
<head>
<title>AutoImporter</title>
<LINK rel='stylesheet' type='text/css' href='gva_tab3.css'/>
<style type='text/css'>
.x-mask {
	z-index: 100;
	position: absolute;
	top: 0;
	left: 0;
	filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=50 );
	opacity: 0.5;
	width: 100%;
	height: 100%;
	zoom: 1;
	background: #cccccc
}

.x-mask-msg {
	z-index: 20001;
	position: absolute;
	top: 0;
	left: 0;
	padding: 2px;
	border: 1px solid;
	border-color: #d0d0d0;
	background-image: none;
	background-color: #e0e0e0
}

.x-mask-msg div {
	padding: 5px 10px 5px 25px;
	background-image:
		url('../../resources/themes/images/gray/grid/loading.gif');
	background-repeat: no-repeat;
	background-position: 5px center;
	cursor: wait;
	border: 1px solid #b3b3b3;
	background-color: #eeeeee;
	color: #222222;
	font: normal 11px tahoma, arial, verdana, sans-serif
}
</style>
<script type="text/javascript" src='ext-all.js'></script>
</head>
<body>
<?if($autoimport_configured){?>
<script>
	var nocheck=false;
	var intveral_id=0;
	var audioplay = null;
	var myMask = new Ext.LoadMask(Ext.getBody(), {msg:"Loading. Please wait..."});
	var callMask=function(flip){if(flip){myMask.show()}else{myMask.hide()}}
	if(window.location.href.indexOf('gva_tab')>=0){
		localStorage.setItem('loc',window.location.href);
	}
	alarm_play = function(){
		alarm_file = "/sses/alarm_sounds/<?echo $import_alarm?>";
		do_play = <?echo $import_alarm_enabled?>;
		if(do_play==1){
			el = document.getElementById('alarmstop');
			el.style.display="inline";
			audioplay = new Audio(alarm_file)
			audioplay.play()
		}
	}
	
	alarm_stop = function(){
		if(audioplay){
			el = document.getElementById('alarmstop');
			el.style.display="none";
			audioplay.pause();
		}
	}
	
	new_survey_check = function(time_pass){
		if(!nocheck){
			document.getElementById('load_countdown').style.display='block'
			document.getElementById('new_survey_section').style.display='none'
			passage = time_pass?time_pass:0
			timetocheck = parseInt(localStorage.getItem('ttc'))
			if(!timetocheck || passage==-1){
				timetocheck=60
				if(passage==-1){
					passage=0
				}
			}
			timetocheck=timetocheck-passage
			if(timetocheck<=0){
				timetocheck=60
				survey_check();	
			}else{ 
				if(!intveral_id){
					intveral_id=setInterval("new_survey_check(1)",1100)
				}
				document.getElementById('time_till_check').innerHTML=timetocheck
			}
			
			localStorage.setItem('ttc',timetocheck)
		}else{
			if(intveral_id){
				try{
					clearInterval(intveral_id);
				}catch(e){}
			}
		}
	}
	survey_check=function(){
		nocheck=true;
		if(intveral_id){
			try{
				clearInterval(intveral_id);
			}catch(e){}
		}
		Ext.Ajax.request({
			url: 'json/new_survey_check.php',
			params:{seldbname:'<?echo $_REQUEST['seldbname']?>'},
			success:function(resp){
				try{
					json = Ext.decode(resp.responseText)
				} catch(e) {
					json={};
					json.msg='An error has occured with the response from the server. Please contact support or <a onclick="window.location.reload()" style="cursor:pointer">start over</a>';
				}
				nocheck=false;
				intveral_id=0;
				if(json.md){
					alarm_play()
					document.getElementById('load_countdown').style.display='none'
					document.getElementById('new_survey_section').style.display='block'
					document.getElementById('new_smd').innerHTML=json.md
					document.getElementById('new_sinc').innerHTML=json.inc
					document.getElementById('new_sazm').innerHTML=json.azm
					if(json.cleanup_occurred){
						if(json.cmes){
							json.cmes+="<br><br>";
						}
						document.getElementById('cleanup_msg').innerHTML = "An automatic survey clean up has occured."+json.cmes+" If you want to review what data has been removed please <a onclick='load_del_group("+json.cleanup_occured+")' style='cursor:pointer'>click here."
					} else {
						document.getElementById('cleanup_msg').innerHTML= "No clean up has occured";
					}
				} else {
					if(!json.msg){
						new_survey_check(-1);
					} else {
						document.getElementById('new_survey_section').style.display='none'
						document.getElementById('load_countdown').style.display='none'
						document.getElementById('autoload_msg').style.display='block'
						document.getElementById('autoload_msg').innerHTML=json.msg
					}
				}
			}
		})
	}
	reload_parent=function(){
		callMask(true);
		if(window.opener && !window.opener.closed){
//			window.opener.callMask(true);
			window.opener.location.reload()
		}
	
	}
	load_survey=function(doload,docleanup){		
		doload = doload?doload:false;
		docleanup=docleanup?docleanup:false;
		myMask.show();
		callMask(true);
//		if(window.opener && !window.opener.closed){
//			window.opener.callMask(true);
//		}
		Ext.Ajax.request({
			url: 'json/new_survey_check.php',
			params:{seldbname:'<?echo $_REQUEST['seldbname']?>',load:doload,cleanup:docleanup},
			failure:function(){
				alert("an unknown error has occured");
				myMask.hide();
				if(window.opener && !window.opener.closed) {
						window.opener.location.reload();
				}
			},
			success:function(resp){
				try{
					json = Ext.decode(resp.responseText)
				}catch(e){
					doload =false;
					docleanup=false;
					reload_parent();
					myMask.hide();
					document.getElementById('cleanup_msg').innerHTML='An error has occured with the response from the server. Please contact support or <a onclick="window.location.reload()" style="cursor:pointer">start over</a>';
				}
				if(doload && !docleanup){
					nocheck=false;
					new_survey_check(-1);
					if(window.opener && !window.opener.closed) {
						window.opener.location.reload();
					}
					if(json.cleanup_occured){
					
						if(parseInt(json.cleanup_occured)==NaN){
							if(json.cmes){
								json.cmes+="<br><br>";
							}
							document.getElementById('cleanup_msg').innerHTML = json.cmes+" Please <a style='cursor:pointer' onclick=\"load_survey(false,true)\">click here if you want to cleanup</a>.";
						} else{
							document.getElementById('cleanup_msg').innerHTML = json;
						}
					} else {
						document.getElementById('cleanup_msg').innerHTML= "No clean up has occured";
					}
				} else if(docleanup && !doload){
					
					if(window.opener && !window.opener.closed) {
						window.opener.location.reload();
					}
					if(json.cleanup_occured){
					
						if(parseInt(json.cleanup_occured)!=NaN){
							if(json.cmes){
								json.cmes+="<br><br>";
							}
							document.getElementById('cleanup_msg').innerHTML = "An automatic survey clean up has occured."+json.cmes+" If you want to review what data has been removed please <a onclick='load_del_group("+json.cleanup_occured+")' style='cursor:pointer'>click here."
						} else{
							document.getElementById('cleanup_msg').innerHTML = json;
						}
					} else {
						document.getElementById('cleanup_msg').innerHTML= "No clean up has occured";
					}
				}
				myMask.hide()
			}
		})
		
	}
	load_del_group=function(grpid){
		window.open('cleanedsurveysview.php?seldbname=<?echo $seldbname?>&grpid='+grpid,'_blank','width=680,height=500,left=250,location=no,menubar=no,status:no,toolbar=no');
	}
</script>

<div style="position:fixed;top:10px;right:10px;border:solid black 1px;background-color:white;padding:10px 10px 10px 10px;">
	<div id='autoload_msg' style='display:none'></div>
	<div id='load_countdown' style="<?echo $count_disp?>">checking for next survey in <span id='time_till_check'></span> seconds. Skip timer and <a style='cursor:pointer' onclick='survey_check()'>check now</a></div>
	<div id='new_survey_section' style="<?echo $new_sdisp?>">New survey available with MD:<span id='new_smd'><?echo $next['md']?></span>, INC:<span id='new_sinc'><?echo $next['inc']?></span> and AZM:<span id='new_sazm'><?echo $next['azm']?></span>. <a onclick="load_survey(true,false)" style='cursor:pointer'>Click here to import</a></div>
	<?if(!$next['next_survey']){?>
		<script>new_survey_check(0)</script>
	<?}?>
</div>
<div style="position:fixed;top:100px;right:25px;"><input onclick="alarm_stop()" style="display:none" type='button' id='alarmstop' value="Stop Alarm">&nbsp;<input type=button name=choice value="Clean Up History" \
		onclick="window.open('cleanedsurveysview.php?seldbname=<?echo $seldbname?>','_blank','width=680,height=240,left=250,location=no,menubar=no,resizable=no,status:no,toolbar=no');"></div>
<div style="position:fixed;top:120px;right:10px;border:solid black 1px;background-color:white;padding:10px 10px 10px 10px;">
	<div id='cleanup_msg'><?echo $cleanup_msg?></div>
</div>
<?}else{?>
	<div>Auto import not configured</div>
<?}?>
</body>
</HTML>
