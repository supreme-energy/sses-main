<?php

class WitsmlData {
	public $witsversion='1.3.1.1';
	public $soap_v=SOAP_1_1;
    
    function __construct($request,$witsv='1.3.1.1',$soapv=SOAP_1_1) {
    	$this->db_name = $request['seldbname'];
    	$this->raw_request = $request;
    	$this->get_witsml_data();
    	$this->db=new dbio("{$this->db_name}");
    	$this->db2=new dbio("{$this->db_name}");
    }
    
    function get_witsml_data($field='endpoint'){
    	$this->client=null;
    	$db = new dbio($this->db_name);
		$db->OpenDb();
		$db->DoQuery("SELECT * FROM witsml_details order by id desc limit 1");
		if($row=$db->FetchRow()){
			$this->endpoint=$row['endpoint'];
			$this->send_data =$row['send_data'];
			$this->username=$row['username'];
			$this->password=$row['password'];
			$this->well = $row['wellid'];
			$this->wellbore = $row['boreid'];
			$this->logid = $row['logid'];
			$this->trajectory = $row['trajid'];
		}else{
			$this->endpoint='';
			$this->send_data=false;
			$this->username='';
			$this->password='';
			$this->well = '';
			$this->wellbore = '';
			$this->trajectory = '';
		}
		$db->DoQuery("select * from rigminder_connection");
		
		if($row=$db->FetchRow()){
				$this->aisd = $row['aisd'];
				if($this->endpoint==''){
					$this->endpoint =$row['host'];
					$this->username = $row['username'];
					$this->password = $row['password'];
				}
		} else {
				$this->aisd=0;
		}
		$db->DoQuery("select auto_gr_mnemonic from appinfo;");
		if($row=$db->FetchRow()){
			$this->grmnemonic=$row['auto_gr_mnemonic'];
		} else {
			$this->grmnemonic="GR";
		}
		$db->CloseDb();
		return $this->$field;
    }
    
	function get_well_bore_id($well_id){
		if(!$this->wellbore){
		$well_id=trim($well_id);
		$body = "<wellbore uidWell=\"$well_id\" uid=\"\"><name/><nameWell/><numGovt/></wellbore>";
		$resp = $this->retrieve_fromstore($body,'wellbore');
		$xmlresp = $resp['XMLout'];
		$wellbores = new SimpleXMLElement($xmlresp);
			foreach($wellbores->wellbore as $wellbore){
				$this->wellbore=$wellbore['uid'];
				$query = "update witsml_details set boreid='$this->wellbore'";
				$db = new dbio("$this->db_name");
				$db->OpenDb();
				$db->DoQuery($query);
				$db->CloseDb();
				return $this->wellbore;
			}
		}else{
			return $this->wellbore;
		}
		return null;
		
	}
    function get_well_id_from_name($name){
    		if(!$this->well){
	    		$body = "<well uid=''><name>$name</name><numGovt/></well>";
	    		$resp = $this->retrieve_fromstore($body,'well');
	    		$xmlresp = $resp['XMLout'];
	    		$wells = new SimpleXMLElement($xmlresp);
				//print $xmlresp;
	    		foreach($wells->well as $well){
	    			if(trim((string)$well->name)==trim($name)){
	    				$this->well = $well['uid'];
	    				$query = "update witsml_details set wellid='$this->well'";
						$db = new dbio("$this->db_name");
						$db->OpenDb();
						$db->DoQuery($query);
						$db->CloseDb();
	    				return $well['uid'];
	    			}
	    		}
    		} else {
    			return $this->well;
    		}
    		return null;
    		
    }
    function get_traj_uid(){
    	$this->trajectory_action = 'update';
    	if(!$this->trajectory){
			$this->trajectory_action = 'add';
			$this->trajectory = uniqid('SSES_TRAJECTORY_');
			$query = "update witsml_details set trajid='$this->trajectory'";
			$db = new dbio("$this->db_name");
			$db->OpenDb();
			$db->DoQuery($query);
			$db->CloseDb();
			
    	}
    	return $this->trajectory;
    }
    function get_obj_uid($type){
		$db = new dbio("$this->db_name");
		$db->OpenDb();
		$db->DoQuery("SELECT uid FROM witsml_log WHERE type='$type' order by id desc limit 1");
		if($row = $db->FetchRow()){
			$uid = $row['uid'];
		} else {
			$uid = uniqid($this->db_name);
		}
		$db->CloseDb();
		$this->$type=$uid;    	
    }
    
    function construct_witsml($body,$type){
    	 $types = $type.'s';
    	 $system_name="Subsurface Geological Tracking Analysis";
 		 $now = gmdate("Y-m-d\TH:i:s\Z");
 		 $this->witsml="<$types xmlns=\"http://www.witsml.org/schemas/1series\"" .
 		 		" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"" .
 		 		" xsi:schemaLocation=\"http://www.witsml.org/schemas/1series" .
 		 		"  ../xsd_schemas/obj_$type.xsd\" version=\"".$this->witsversion."\">" .
 		 		"<documentInfo>" .
 		 		"<documentName>well</documentName>" .
 		 		"<fileCreationInformation>" .
 		 		"<fileCreationDate>$now</fileCreationDate>" .
 		 		"<fileCreator>$system_name</fileCreator>" .
 		 		"</fileCreationInformation>" .
 		 		"</documentInfo> $body</$types>";
    }
    
    function construct_query($body,$type){
    	$types=$type.'s';
    	$this->witsml_query="<$types xmlns=\"http://www.witsml.org/schemas/131\" version=\"".$this->witsversion."\">$body</$types>";
    	
    }
    function getCap(){
    	$this->prepare_soap();
    	$this->resp = $this->client->WMLS_GetCap();
    	echo $this->client->__getLastRequest();
    	return $this->resp;
    }
    function retrieve_fromstore($body,$type){
    	$this->construct_query($body,$type);
    	$this->prepare_soap();
		//echo "<pre>body=" . htmlspecialchars($body) . "</p>";
		//echo "<p>witsml_query=" . htmlspecialchars($this->witsml_query) . "</p>";
    	$this->resp = $this->client->WMLS_GetFromStore($type,$this->witsml_query,'None','');
    	//echo "REQUEST:\n" . $this->client->__getLastRequest() . "\n";
		//echo "<pre>resp="; print_r($this->resp); echo "</pre>";
		//echo "<p>resp['XMLout']=" . htmlspecialchars($this->resp['XMLout']) . "</p>";
    	return $this->resp;
    }
    function send($body,$type){
    	$action = $type."_action";
    	if($this->$action=='add'){
    		return $this->send_request($body,$type);
    	}else{
    		return $this->send_update($body,$type);
    	}
    }
    function send_update($body,$type){
    	$this->construct_witsml($body,$type);
    	$this->prepare_soap();
    	$this->resp = $this->client->WMLS_UpdateInStore($type,$this->witsml,'None','');
    	$witsmlstr = $this->witsml;
    	$uid=$this->$type;
    	$db = new dbio("$this->db_name");
		$db->OpenDb();
		$db->DoQuery("insert into witsml_log (type,witsml,uid) values ('$type','$witsmlstr','$uid');");
		$db->CloseDb();
		return $this->resp;    	
    }
    function send_request($body,$type){
    	$this->construct_witsml($body,$type);
    	$this->prepare_soap();
    	$this->resp = $this->client->WMLS_AddtoStore($type,$this->witsml,'None','');
    	$witsmlstr = $this->witsml;
    	$uid = $this->$type;
    	$db = new dbio("$this->db_name");
		$db->OpenDb();
		$db->DoQuery("insert into witsml_log (type,witsml,uid) values ('$type','$witsmlstr','$uid');");
		$db->CloseDb();
    	return $this->resp;
    }
    
    function active(){
    	return $this->send_data;
    }
    
    function prepare_soap(){
    	if(!$this->client){
    		$this->client = new SoapClient(
    			dirname(dirname(__FILE__)).'/soap/WMLS.WSDL',
    			array(
    				'soap_version'=>$this->soap_v,
    				'location'=>$this->endpoint,
    				'login'=>$this->username,
    				'password'=>$this->password,
    				'exceptions'=>0,
    				'trace'=>1
    			)
    		);
    	}
    }
    
}
?>
