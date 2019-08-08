<?php 
    $db->DoQuery('select analytics_address, analytics_job_id from wellinfo limit 1');
    $db->FetchRow();
    $analytics_job_address = $db->FetchField('analytics_address');
    $analytics_job_id = $db->FetchField('analytics_job_id');
?>