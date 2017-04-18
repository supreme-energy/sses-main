<?php
/*
 * Created on May 20, 2014
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require '../web/dbio.class.php';
 	require '../../vendor/autoload.php';

	use OpenCloud\Rackspace;

	$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, array(
	    'username' => 'ssesus',
	    'apiKey'   => '07e5f95ae5dd783fd66e1e1d90cf180e'
	));
	$id =$_REQUEST['id'];
	$service = $client->computeService('cloudServersOpenStack', 'DFW');
	$server = $service->server($id);
	
 	if($server->name){
	 	$name =$server->name;
	 	$url = "https://$name.sgta.us/sses";
	 	$ip = $server->ip();
	 	$db = new dbio("server_manager");
		$db->OpenDb();
		$sql = "select * from server_passes where ip ='$ip'";
		$db->DoQuery($sql);
		if($db->FetchRow()){
			$id = $db->FetchField('id');
			$password = $db->FetchField('password');
		 	$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_USERPWD,"subsurfacegeosteering:$password");
			curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
			curl_setopt($ch, CURLOPT_NOBODY, true);
		    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
		    curl_exec($ch);
		    $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		    curl_close($ch);
		    if (200==$retcode || 401==$retcode) {
		       $sql = "update server_passes set configured = 1 where id = $id";
		       $db->DoQuery($sql);
		       echo '{"result":1,"retcode":"'.$retcode.'"}';
		    } else {
		       echo '{"result":0,"retcode":"'.$retcode.'"}';
		    }
		}
 	}
?>
