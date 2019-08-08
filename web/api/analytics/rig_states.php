<?php 
include("../api_header.php");
include("analytics_connection.php");
$call_to = "http://104.197.12.235/api/rig-states/?job=$analytics_job_id";
$process = curl_init($call_to);
curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($process, CURLOPT_TIMEOUT, 30);
curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
$return = curl_exec($process);
curl_close($process);
echo $return;
?>