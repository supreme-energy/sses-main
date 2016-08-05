<?php
require '../../vendor/autoload.php';

use OpenCloud\Rackspace;

$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, array(
    'username' => 'ssesus',
    'apiKey'   => '07e5f95ae5dd783fd66e1e1d90cf180e'
));
$id =$_REQUEST['id'];
$service = $client->computeService('cloudServersOpenStack', 'DFW');
$server = $service->server($id);
echo "{\"result\":\"".$server->progress."\"}"
?>
