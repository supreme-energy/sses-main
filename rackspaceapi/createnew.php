<?php
require '../../vendor/autoload.php';

use OpenCloud\Rackspace;

$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, array(
    'username' => 'ssesus',
    'apiKey'   => '07e5f95ae5dd783fd66e1e1d90cf180e'
));
$id =$_REQUEST['id'];
$name = $_REQUEST['name'];
$flavorid = (isset($_REQUEST['flavor'])&&$_REQUEST['flavor']!='')?$_REQUEST['flavor']:false;
$service = $client->computeService('cloudServersOpenStack', 'DFW');
$images = $service->imageList(true,array('server'=>$id,
    'status' => OpenCloud\Common\Constants\State::ACTIVE));
$image = $images->current();
$server = $service->server($id);
$servern = $service->server();
if($flavorid===false){
	$flavorid=$server->flavor->id;
}
$flavor = $service->flavor($flavorid);
try {
    $response = $servern->create(array(
        'name'     => $name,
        'image'    => $image,
        'flavor'   => $flavor
    ));
    //$dnsservice = $client->dnsService();
	//$domain = $dnsservice->domain();
	//$aRecord = $domain->record(array(
	//		'type' => 'A',
	//		'name' => $name.'.sgta.us',
	//		'data' => $servern->ip(),
	//		'ttl'  => 3600
	//));
	//$domain->addRecord($aRecord);
} catch (\Guzzle\Http\Exception\BadResponseException $e) {

    // No! Something failed. Let's find out:
    $responseBody = (string) $e->getResponse()->getBody();
    $statusCode   = $e->getResponse()->getStatusCode();
    $headers      = $e->getResponse()->getHeaderLines();

    echo sprintf('Status: %s\nBody: %s\nHeaders: %s', $statusCode, $responseBody, implode(', ', $headers));
}
header('Location: /');
?>