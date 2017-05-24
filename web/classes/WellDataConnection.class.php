<?php

class WellDataConnection extends WitsmlData {
		public $witsversion='1.3.1.1';
		public $soapv=SOAP_1_1;
		public $endpoint = "https://witsml.welldata.net/witsml/wmls.asmx";
		public $username = "geoserviceswitsml";
		public $password = "flodrift20";
		
		function __construct($request,$witsv='1.3.1.1',$soapv=SOAP_1_1) {
    		$this->db_name = $request['seldbname'];
    		$this->raw_request = $request;
    		$this->get_witsml_data();
    		$this->db=new dbio("{$this->db_name}");
    		$this->db2=new dbio("{$this->db_name}");
    	}
    function retrieve_fromstore($body,$type){
    	$this->construct_query($body,$type);
    	$this->prepare_soap();
//		echo "<pre>body=" . htmlspecialchars($body) . "</p>";
		echo "<p>witsml_query=" . $this->witsml_query . "</p>";
    	$this->resp = $this->client->WMLS_GetFromStore($type,$this->witsml_query,'None','');
    	echo "\nresponse:".$this->client->__getLastResponse ()."\n\n<br><br>";
    	echo "\nrequest:".$this->client->__getLastRequest ()."\n\n<br><br>";
//		echo "<pre>resp="; print_r($this->resp); echo "</pre>";
//		echo "<p>resp['XMLout']=" . htmlspecialchars($this->resp['XMLout']) . "</p>";
    	return $this->resp;
    }
    function prepare_soap(){
    	if(!$this->client){
    		$this->client = new SoapClient(
    			$this->endpoint.'?WSDL',
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