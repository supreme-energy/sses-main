<?php 
    function analyticsJobId(){
        global $db, $seldbname, $wellname, $analytics_job_id;
        if(!$analytics_job_id){
            $call_to = "http://104.197.12.235/api/jobs";
            $process = curl_init($call_to);
            curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,
                "name=&sses_id=$seldbname");
            curl_setopt($process, CURLOPT_TIMEOUT, 30);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
            $result = curl_exec($process);
            curl_close($process);
            $json = json_decode($result, true);
            $analytics_job_id = $json['id'];
        }
        return $analytics_job_id;
    }
    function createAnalyticsWell(){
        global $db, $analytics_well_connected;
        global $autorc_host, $autorc_password, $autorc_username;
        $analytics_job_id = analyticsJobId();
        
        if(!$analytics_well_connected){
            $query = "select * from witsml_details";
            $db->DoQuery($query);
            $row = $db->FetchRow();
            $welluid = $row['wellid'];
            $boreuid = $row['boreid'];            
            $call_to = "http://104.197.12.235/api/jobs";
            $process = curl_init($call_to);
            curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,
                "job=$analytics_job_id&data_frequency=10&well_name=$seldbname&uid=$welluid&uidWellbore=$boreuid&url=$autorc_host&username=$autorc_username&password=$autorc_password");
            curl_setopt($process, CURLOPT_TIMEOUT, 30);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
            $result = curl_exec($process);
            curl_close($process);
            $json = json_decode($result, true);
            $query = "update wellinfo set analytics_job_id = $analytics_job_id, analytics_well_connected = true";
            $db->DoQuery($query);
        }
       
        
    }
?>