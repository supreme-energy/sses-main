<?php /*
	Written by: Richard Gonsuron
	Copyright: 2009, Digital Oil Tools
	All rights reserved.
	NOTICE: This file is solely owned by Digital Oil Tools You may NOT modify, copy,
	or distribute this file in any manner without written permission of Digital Oil Tools
*/ ?>
<?
require_once("dbio.class.php");
$badshit = array("'", "%%");
$currtab=$_POST['currtab'];
$seldbname=$_POST['seldbname'];
$connectiontype = $_POST['connection_type'];
$tablename=$_POST['connection_dbname'];
$addr=$_POST['connection_addr'];
$uname=$_POST['connection_uname'];
$pass=$_POST['connection_pass'];
$aisd = $_POST['acsd'];
$grmnemonic= (isset($_POST['gr_import_mnemonic'])&& $_POST['gr_import_mnemonic']!='')?$_POST['gr_import_mnemonic']:'GR';
$enable_alarm = (isset($_POST['importalarmenable'])&&$_POST['importalarmenable']!='')?$_POST['importalarmenable']:0;
$alarm = (isset($_POST['importalarm'])&&$_POST['importalarm']!='')?$_POST['importalarm']:'';
if(!$aisd || $aisd <0){
	$aisd = 0;
}

$db=new dbio($seldbname);
$db->OpenDb();
require_once  "readwellinfo.inc.php";
$db->DoQuery("select count(*) as cnt from rigminder_connection");
$row = $db->FetchRow();
if($row['cnt']>0){
$db->DoQuery("BEGIN TRANSACTION;");
$query="update rigminder_connection set host='$addr',username='$uname',password='$pass',dbname='$tablename',aisd='$aisd',connection_type='$connectiontype';";
$db->DoQuery($query);
$result=$db->DoQuery("COMMIT;");
}else {
	$query = "insert into rigminder_connection (host,username,password,dbname,aisd,connection_type) values ('$addr','$uname','$pass','$tablename','$aisd','$connectiontype')";
	$db->DoQuery($query);
}
$query= "update appinfo set auto_gr_mnemonic='$grmnemonic',import_alarm_enabled=$enable_alarm,import_alarm='$alarm'";
$db->DoQuery($query);
$query = "select * from witsml_details";
$db->DoQuery($query);
$row = $db->FetchRow();
$welluid = $row['wellid'];
$boreuid = $row['boreid'];
$logid   = $row['logid'];
$db->CloseDb();

?>
<HTML>
<HEAD>
<title>configure auto rig connection</title>
<link rel="stylesheet" type="text/css" href="gva_tab7.css" />
<link rel="stylesheet" type="text/css" href="waitdlg.css" />
</HEAD>
<BODY onload='getWells()'>
<script>
	welluid = "<?echo $welluid?>";
	boreuid = "<?echo $boreuid?>";
	logid   = "<?echo $logid?>";
	function selectByAPI(){
		pageObj = document.getElementById('uidWell');
		wellAPInumb = "<?echo $wellid;?>";
		found=false;
		for( i in pageObj.options){
			sel = pageObj.options[i]
			if(sel){
				if(sel.text && sel.text.indexOf(wellAPInumb)>=0){
					pageObj.value= sel.value
					found=true;
					break;
				}
			}
		}
		if(!found){
			alert("No well found with api #"+wellAPInumb+". Please verify you are using the correct well API. Verify that you are using the correct Polaris connection details and verify that Polaris has properly setup the well on their end.");
		}
	}
	
	function getWells(){
			lpageObj = document.getElementById('uidLog');
			while(lpageObj.options.length>0){
					lpageObj.options.remove(0);
			}
			wbpageObj = 	document.getElementById('uidWellbore');
			while(wbpageObj.options.length>0){
					wbpageObj.options.remove(0);
			}
			xmlhttp=new XMLHttpRequest();
			xmlhttp.open('GET','/sses/json/list_witswells.php?seldbname=<?=$seldbname?>',true);
			xmlhttp.onreadystatechange=function(){
				if(xmlhttp.readyState==4 && xmlhttp.status==200){
					obj = JSON.parse(xmlhttp.responseText);
					if( Object.prototype.toString.call( obj.well ) !== '[object Array]' ) {
    					obj.well=[obj.well];
					}
					pageObj = document.getElementById('uidWell');
					for(i in obj.well){
						opt = document.createElement('option');
						opt.value = obj.well[i]['@attributes'].uid
						opt.text = obj.well[i].name+"-"+obj.well[i].numAPI
						pageObj.appendChild(opt);
					}
					if(welluid&& pageObj.value!=welluid){
						pageObj.value=welluid;
						getWellbores();
					}
				}
			}
			xmlhttp.send();
			
	}
	function getWellbores(){
			lpageObj = document.getElementById('uidLog');
			while(lpageObj.options.length>0){
					lpageObj.options.remove(0);
			}
			wbpageObj = 	document.getElementById('uidWellbore');
			while(wbpageObj.options.length>0){
					wbpageObj.options.remove(0);
			}
			wellObj = document.getElementById('uidWell');
			selval = wellObj.options[wellObj.selectedIndex].value;
			xmlhttp=new XMLHttpRequest();
			xmlhttp.open('GET','/sses/json/list_witswellbores.php?seldbname=<?=$seldbname?>&uidWell='+selval,true);
			xmlhttp.onreadystatechange=function(){
				if(xmlhttp.readyState==4 && xmlhttp.status==200){
					obj = JSON.parse(xmlhttp.responseText);
					console.log(obj);
					if( Object.prototype.toString.call( obj.wellbore ) !== '[object Array]' ) {
    					obj.wellbore=[obj.wellbore];
					}
					pageObj = document.getElementById('uidWellbore');
					for(i in obj.wellbore){
						found=false;
						fidx=0
						fuid = obj.wellbore[i]['@attributes'].uid
						for(j in pageObj.options){
							if(pageObj.options[j].value== fuid){
								found=true
								fidx = j
								break;
							}
						}
						if(found){
							if(selval==obj.wellbore[i]['@attributes'].uidWell){
								pageObj.options.remove(fidx)
							}
							continue;
						}
						if(selval==obj.wellbore[i]['@attributes'].uidWell){
							opt = document.createElement('option');
							opt.value = obj.wellbore[i]['@attributes'].uid
							opt.text = obj.wellbore[i].name+" - "+obj.wellbore[i]['@attributes'].uid
							pageObj.appendChild(opt);
						}
					}
					if(boreuid!=''&& pageObj.value!=boreuid){
						pageObj.value= boreuid;
					}
					pageObj.disabled=false;
					getLogs();
				}
			}
			xmlhttp.send();
	}
	function getLogs(){
			wellObj = document.getElementById('uidWell');
			selval = wellObj.options[wellObj.selectedIndex].value;
			wellbore = document.getElementById('uidWellbore');
			try{
				boreselval = wellbore.options[wellbore.selectedIndex].value;
			}catch(e){
				pageObj = document.getElementById('uidLog');
				while(pageObj.options.length>0){
					pageObj.options.remove(0);
				}
				return;
			}
			xmlhttp=new XMLHttpRequest();
			xmlhttp.open('GET','/sses/json/list_witslogs.php?seldbname=<?=$seldbname?>&uidWell='+selval+'&uidWellbore='+boreselval,true);
			xmlhttp.onreadystatechange=function(){
				if(xmlhttp.readyState==4 && xmlhttp.status==200){
					obj = JSON.parse(xmlhttp.responseText);
					console.log(obj);
					if( Object.prototype.toString.call( obj.log ) !== '[object Array]' ) {
    					obj.log=[obj.log];
					}
					pageObj = document.getElementById('uidLog');
					if( Object.prototype.toString.call( obj.log ) !== '[object Array]' ) {
    					obj.log=[obj.log];
					}
					for(i in obj.log){
						if(obj.log[i] && obj.log[i]['@attributes'].uid){
							opt = document.createElement('option');
							opt.value = obj.log[i]['@attributes'].uid
							opt.text = obj.log[i].name+" - "+obj.log[i]['@attributes'].uid
							pageObj.appendChild(opt);
						}
					}
					if(logid!='' && pageObj.value!=logid){
						pageObj.value=logid;
						logid=''
						boreuid=''
						welluid=''
					}
					pageObj.disabled=false;
				}
			}
			xmlhttp.send();		
	}
	function wellboreChange(){
		getLogs();
	}
	function wellChange(){
		wellObj = document.getElementById('uidWell');
		wellboreObj = document.getElementById('uidWellbore');
		welllog = document.getElementById('uidLog');
		selectedVal = wellObj.options[wellObj.selectedIndex].value;
		if(selectedVal=='0'){
			while(wellboreObj.options.length>0){
				wellboreObj.remove(0);
			}
			while(welllog.options.length>0){
				welllog.remove(0);
			}
		}else{
			getWellbores();
			wellboreObj.disabled=false;
		}
	}
	function saveandclose(){
		wellObj = document.getElementById('uidWell');
		selval = wellObj.options[wellObj.selectedIndex].value;
		wellbore = document.getElementById('uidWellbore');
		boreselval = wellbore.options[wellbore.selectedIndex].value;
		logObj = document.getElementById('uidLog');
		logselval = logObj.options[logObj.selectedIndex].value
		xmlhttp=new XMLHttpRequest();
		xmlhttp.open('GET','/sses/witsml_details_save.php?seldbname=<?=$seldbname?>&welluid='+selval+'&wellboreuid='+boreselval+'&loguid='+logselval,true);
		xmlhttp.onreadystatechange=function(){
				if(xmlhttp.readyState==4 && xmlhttp.status==200){
					window.close()
				}
		}
		xmlhttp.send();		
	}
</script>
<table class='container' width=750>
	<tr><td>Choose a well:</td><td><select name='uidWell' id='uidWell' onchange="wellChange()"><option value='0'>choose a well</option></select><button onclick="selectByAPI()">select by API #</button></td></tr>

	<tr><td>Choose a wellbore:</td><td><select disabled name='uidWellbore' id='uidWellbore' onchange="wellboreChange()"></select></td></tr>

	<tr><td>Choose a log:</td><td><select disabled name='uidLog' id='uidLog' onchange="logChange()"></td></tr>
	<tr><td colspan='2' align='right'><button onclick="saveandclose()">save &amp; close</button></td></tr>
</table>
</body>
</html>