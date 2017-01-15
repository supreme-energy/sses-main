<?php
	require '../../vendor/autoload.php';
	require '../web/dbio.class.php';
	use OpenCloud\Rackspace;
	$ip = $_REQUEST['ip'];
	$name = $_REQUEST['name'];
	$reset = isset($_REQUEST['reset'])?$_REQUEST['reset']:'';
	$configuredns = isset($_REQUEST['dns'])?$_REQUEST['dns']:false;
	$npass = isset($_REQUEST['npass'])?$_REQUEST['npass']:'';
	if($ip && $name && $configuredns){
		$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, array(
   		 'username' => 'ssesus',
    	 'apiKey'   => '07e5f95ae5dd783fd66e1e1d90cf180e'
		));
		$service = $client->dnsService();
		$domain = $service->domain(4195853);
		$anamer = $name.".sgta.us";
		$aRecord = $domain->record();
		$response = $aRecord->create(array(
			'type' => 'A',
			'name' => $anamer,
			'data' => $ip,
			'ttl'  => 3600
		));
	}
	$db = new dbio("server_manager");
	$db->OpenDb();
	$sql = "select * from server_passes where ip ='$ip'";
	
	$db->DoQuery($sql);
	if($db->FetchRow()){
		$password = $db->FetchField("password");
		$passid = $db->FetchField("id");
		if($npass){
			$sql = "update server_passes set password = '$npass' where id=$passid";
			$db->DoQuery($sql);
		}
	} else {
		$password = "sgtageo1984";
		if($npass){
			$sql = "insert into server_passes (ip,password,configured) values('$ip','$npass',0)";
			$db->DoQuery($sql);	
		} else {
			$sql = "insert into server_passes (ip,password,configured) values('$ip','$password',0)";
			$db->DoQuery($sql);
		}
	}
	if($ip){
		$url = "https://".$ip."/sses/configureme.php?reset=$reset&npass=$npass&ip=$ip"; 
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_USERPWD,"subsurfacegeosteering:$password");
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
		curl_exec($ch);
	}
	header('Location: /');
?>