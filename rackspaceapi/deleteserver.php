<?php
require '../../vendor/autoload.php';
require '../web/dbio.class.php';
	
use OpenCloud\Rackspace;

$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, array(
    'username' => 'ssesus',
    'apiKey'   => '07e5f95ae5dd783fd66e1e1d90cf180e'
));
$id =$_REQUEST['id'];
$name = $_REQUEST['name'];
$service = $client->computeService('cloudServersOpenStack', 'DFW');
try{
	$server = $service->server($id);
	if($server->ip()){
		$ip =$server->ip();  
		$db = new dbio("server_manager");
		$db->OpenDb();
		$sql = "select * from server_passes where ip ='$ip'";
		$db->DoQuery($sql);
		if($db->FetchRow()){
			
			$password = $db->FetchField("password");
			$passid = $db->FetchField("id");
			if(!$password){
				$password = 'sgtageo1984';
			}
		} else {
			$password = 'sgtageo1984';
		}
		$url = "https://".$server->ip()."/sses/deleteme.php?ip=".$server->ip()."&name=".$server->name; 
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_USERPWD,"subsurfacegeosteering:$password");
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
		curl_exec($ch);
		curl_close($ch);
		$sql = "delete from server_passes where id=$passid";
		$db->DoQuery($sql);
	}
	$server->delete();
}catch(Exception $e){
	echo 'exception caught';
}
$service = $client->dnsService();
$domain = $service->domain(4195853);
$records = $domain->recordList();
foreach($records as $record){
	echo $record->name;
	if($record->name==$name.".sgta.us"){
		$record->delete();
		break;
	}
}

header('Location: /');

?>