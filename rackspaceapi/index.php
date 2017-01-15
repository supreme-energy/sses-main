<?php

require '../../vendor/autoload.php';
require '../web/dbio.class.php';
use OpenCloud\Rackspace;

$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, array(
    'username' => 'ssesus',
    'apiKey'   => '07e5f95ae5dd783fd66e1e1d90cf180e'
));
$service = $client->computeService('cloudServersOpenStack', 'DFW');
$servers = $service->serverList(true);
$servers->populateAll();
$servers->rewind();
$flavors = $service->flavorList(false);
$rows_data = '';

do{
        if(!$servers->current()->name){continue;};
        if($servers->current()->name=='base' || $servers->current()->name=='dev'){
        	$actions ='no actions';
        } else {
        	if($servers->current()->progress<79){
        		$display_wait = "display:block;";
        		$display_actions= "display:none;";
        	} else {
        		$display_wait = "display:none;";
        		$display_actions= "display:block;";
        	}
        	$actions = "<span id='wait_for_complete_".$servers->current()->id."' style='$display_wait'>No actions available while building</span><div  style='$display_actions' id='action_buttons_".$servers->current()->id."'><button onclick=\"this.style.display='none';document.getElementById('configureform_".$servers->current()->id."').style.display='block'\">configure</button>" .
        			"<div id='configureform_".$servers->current()->id."' class='container' style='position:relative;top:0px;left:0px;border:1px solid black;background-color:white;display:none;'>" .
        					"Reset <input checked type='checkbox' name='resetserver' id='reset_".$servers->current()->id."'><br>" .
        					"Set Password(blank for no change)<input type='text' name='npass' id='npass_".$servers->current()->id."'><br>" .
        					"Send DNS <input checked type='checkbox' name='dns' id='dns_".$servers->current()->id."'><br>" .
        					"<button onclick=\"doconfigure('".$servers->current()->id."','".$servers->current()->ip()."','".$servers->current()->name."')\">Send Configure</button></div>";
        	$actions .= '<button onclick="deleteserver(\''.$servers->current()->id.'\',\''.$servers->current()->name.'\')">delete</button></div>';
        };
        if($servers->current()->progress<79){
        	$inner_status = array("<script>add_to_status('".$servers->current()->id."')</script><div id='status_".$servers->current()->id."'>building<span id='dots_1_".$servers->current()->id."'>...<span id='progress_".$servers->current()->id."'>".$servers->current()->progress."%</span></div>",$servers->current()->id);
        } else {
        	//lookup the local data check to see if dns configure request was made
        	$ip =$servers->current()->ip();  
			$db = new dbio("server_manager");
			$db->OpenDb();
			$sql = "select * from server_passes where ip ='$ip'";
			$db->DoQuery($sql);
			$include_script='';
			if($db->FetchRow()){
				if($db->FetchField('configured')==1){
					$unlinked_name="display:none";
					$linked_name = "display:inline";
					$please_config="display:none";
					$config_inprog="display:none";
				} else {
					$unlinked_name="display:inline";
					$linked_name = "display:none";
					$please_config="display:none";
					$config_inprog="display:inline";
					$include_script="<script>add_to_config('".$servers->current()->id."')</script>";
				}
			} else{
				if($servers->current()->name=='dev'||$servers->current()->name=='base'){
					$unlinked_name="display:none";
					$linked_name = "display:inline";
					$please_config="display:none";
					$config_inprog="display:none";
				}else{
					$unlinked_name="display:inline";
					$linked_name = "display:none";
					$please_config="display:inline";
					$config_inprog="display:none";
				}
			}
        	//otherwise display the please configure 
        	$inner_status = array("$include_script<span style='$unlinked_name' id='unlinked_name_".$servers->current()->id."'>".$servers->current()->name.".sgta.us</span>" .
        	"<a style='$linked_name' id='warning_free_link_".$servers->current()->id."' href='https://".$servers->current()->name.".sgta.us/sses' target='_blank'>".$servers->current()->name.".sgta.us</a>" .
        	"<span style='$config_inprog' id='still_config_".$servers->current()->id."'>&nbsp;(still configuring<span id='dots_2_".$servers->current()->id."'>...</span></div>)</span>" .
        	"<span style='$please_config' id='please_config_".$servers->current()->id."'>&nbsp;(please configure)</span>", 
        	"<a href='https://".$servers->current()->ip()."/sses' target='_blank'>".$servers->current()->ip()."</a>&nbsp;(certificate warning)",
        	$servers->current()->id);
        }
        $rows_data.="<tr><td class='info'><i>".$servers->current()->name."</i>" .
        		"<br>".implode('<br>',$inner_status)."</td>";
        $rows_data.="<td class='info'>".$actions."</td>";
        $rows_data.= "</tr>";
}while($servers->next());

?>


<HEAD><LINK rel='stylesheet' type='text/css' href='index.css' /></HEAD>
<HTML>
<BODY onload="setInterval(checkstatus,8000);setInterval(dots_running,1000);setInterval(config_running,1000);setInterval(checkconfig,8000);">
<script>
	var servers_to_check={};
	var servers_to_config_check={};
	function createnew(){
		document.getElementById('subnewbutton').disabled=true;
		sname = document.getElementById('newservername').value;
    	if(sname=='' ||  /[^a-zA-Z0-9]/.test(sname) ) {
       		alert('Input is not alphanumeric');
       		document.getElementById('subnewbutton').disabled=false;
    	}else {
    		flavorid = document.getElementById('serverperformance').value;
    		if(flavorid=='default'){
    			url='/createnew.php?id=d5f123f1-76bf-413e-8ae4-9c654c425df4&name='+sname
    		} else{
    			url='/createnew.php?id=d5f123f1-76bf-413e-8ae4-9c654c425df4&name='+sname+'&flavor='+flavorid
    		}
    		window.location=url;
    	}
	}
	
	function doconfigure(serverid,ip,name){
		npass = document.getElementById('npass_'+serverid).value
		reset = document.getElementById('reset_'+serverid).checked?1:'';
		dns = document.getElementById('dns_'+serverid).checked?1:'';
		url = '/configuredns.php?ip='+ip+'&name='+name+'&reset='+reset+'&dns='+dns+'&npass='+npass;
		window.location=url;
	}
	
	function deleteserver(serverid,name){
		var r = confirm("Are you sure?");
		if(r==true){
			window.location = '/deleteserver.php?id='+serverid+'&name='+name;
		}
	}
	function add_to_status(server_id){
		servers_to_check[server_id]=0;
	}
	function add_to_config(server_id){
		servers_to_config_check[server_id]=0;
	}
	function config_running(){
		for(i in servers_to_config_check){
			lastcheck = servers_to_config_check[i];
			if(lastcheck!=-1){
				if(document.getElementById('dots_2_'+i).style.display=='inline'||document.getElementById('dots_2_'+i).style.display=='block'){
						document.getElementById('dots_2_'+i).style.display='none'
					} else {
						document.getElementById('dots_2_'+i).style.display='inline';
					}
			}
		}
	}
	function dots_running(){
		
		for(i in servers_to_check){
			lastcheck = servers_to_check[i]
			if(lastcheck!=-1){
				if(document.getElementById('dots_1_'+i).style.display=='inline'||document.getElementById('dots_1_'+i).style.display=='block'){
						document.getElementById('dots_1_'+i).style.display='none'
					} else {
						document.getElementById('dots_1_'+i).style.display='inline';
					}
			}
		}
		
	}
	
	function checkconfig(){
		for(i in servers_to_config_check){
			lastcheck = servers_to_config_check[i];
			if(lastcheck!=-1){
				if(lastcheck >10){
					//make ajax call check
					console.log('checking status :'+i);
					xmlhttp=new XMLHttpRequest();
					xmlhttp.onreadystatechange = function(){
						if(xmlhttp.readyState==4){
							if(xmlhttp.status===500){
								window.location=window.location;
							}else{
								respobj = JSON.parse(xmlhttp.responseText)
								if(parseInt(respobj.result) >= 1){
									servers_to_config_check[i]=-1;
									window.location=window.location;
								} else {
									servers_to_config_check[i]=0;
								}
							}
						}
					}
					xmlhttp.open('GET','/configuredcheck.php?id='+i,true);
					xmlhttp.send();
				} else{
					lastcheck = servers_to_config_check[i]=lastcheck+8;
				}
			}
		}			
	}
	
	function checkstatus(){
		for(i in servers_to_check){
			lastcheck = servers_to_check[i];
			if(lastcheck!=-1){
				if(lastcheck >10){
					//make ajax call check
					
					xmlhttp=new XMLHttpRequest();
					xmlhttp.onreadystatechange = function(){
						if(xmlhttp.readyState==4){
							if(xmlhttp.status===500){
								window.location=window.location;
							}else{
								respobj = JSON.parse(xmlhttp.responseText)
								if(parseInt(respobj.result) >= 90){
									servers_to_check[i]=-1;
									window.location=window.location;
								} else {
									document.getElementById('progress_'+i).innerHTML=respobj.result+'%';
									servers_to_check[i]=0;
								}
							}
						}
					}
					xmlhttp.open('GET','/checkstatus.php?id='+i,true);
					xmlhttp.send();
				} else{
					lastcheck = servers_to_check[i]=lastcheck+8;
				}
			}
		}	
	}
</script>
<TABLE style='width:800px;'>
<TR>
<TD class='header'>
	<center>
	<H2 style='line-height: 1.0; font-style: italic; color: #040;'>Supreme Source Energy Services, Inc.</H2>
	<H1 style='line-height: 0.3;'>Server Manager</H1>
	<div style='text-align:right'><div>Server Name:<input id='newservername' name='newservername' type='input'></div>
		<div>Performance:<select id='serverperformance' name='serverperformance'>
			<option value='default'>Use Base(2GB Performance)</option>
			<?php
				foreach($flavors as $flavor){
					if(strpos($flavor->name,'Standard')!==false
					|| strpos($flavor->name,'120 GB')!==false
					|| strpos($flavor->name,'90 GB')!==false
					|| strpos($flavor->name,'60 GB')!==false )continue;
					?>
					<option value='<?php echo $flavor->id?>'><?php echo $flavor->name?></option>
				<?}
			?>
		</select></div>	
		<div><button id='subnewbutton' onclick="createnew()">Create New</button></div></div>
	</center>
</TD>
<!--
<TD class='logo'>
	<img src="logo.gif" width="100" height="100">
</TD>
-->
</TR>
<TR>
<TD class='container'>
	<table>
	<tr>
	<th><h3>Server</h3></th>
	<th><h3>Actions</h3></th>
	</tr>
	<?php echo $rows_data ?>
	</table>
</TD>

</TR>
</TABLE>
&#169; 2010-2011 Supreme Source Energy Services, Inc.
</BODY>
</HTML>

