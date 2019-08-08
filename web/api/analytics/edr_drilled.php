<?php 
include("../api_header.php");
include("analytics_connection.php");
$call_to = "http://104.197.12.235/api/edrdrilled-parameters/?job=$analytics_job_id";
$addparams = array('rig_time_lte', 'rig_time_gte', 'hole_depth_gte', 'hole_depth_lte');
foreach($addparams as $adp){
    if(isset($_REQUEST[$adp])){
        $call_to.="&".$adp."=".$_REQUEST[$adp];
    }
}
$process = curl_init($call_to);
curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($process, CURLOPT_TIMEOUT, 30);
curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
$return = curl_exec($process);
curl_close($process);
echo $return;
?>