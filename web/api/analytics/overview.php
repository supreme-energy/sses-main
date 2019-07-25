<?php 
include("../api_header.php");

$jobid = 1;
$call_to = "http://104.197.12.235/api/welloverview/?job=$jobid";
$process = curl_init($call_to);
curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($process, CURLOPT_TIMEOUT, 30);
curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
$return = curl_exec($process);
curl_close($process);
echo $return;
?>