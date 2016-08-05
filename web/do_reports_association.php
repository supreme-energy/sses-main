<?php
// Created on Jul 15, 2013
//
// To change the template for this generated file go to
// Window - Preferences - PHPeclipse - PHP - Code Templates
 require_once("dbio.class.php");
 $seldbname = $_REQUEST['seldbname'];
 $contact_id = $_REQUEST['id'];
 $del = isset($_REQUEST['del']);
 
 $dbu_idx = new dbio('sgta_index');
 $dbu_idx->OpenDb();
 $dbu = new dbio($seldbname);
 $dbu->OpenDb();
 
 $query = 'select * from server_info';
 $dbu_idx->DoQuery($query);
 $server_info = $dbu_idx->FetchRow();
 if($server_info['on_lan']){
 	$report_addr= $server_info['reports_lan'];
 	$my_addr = $server_info['lan_addr'];
 } else {
 	$report_addr= $server_info['reports_wan'];
 	$my_addr = $server_info['wan_addr'];
 }
 $query = 'select * from dbindex where dbname=\''.$seldbname.'\'';
 $dbu_idx->DoQuery($query);
 $server_name = $dbu_idx->FetchRow();
 $jobname = urlencode($server_name['realname']);
 $query2 = "select * from emaillist where id=$contact_id";
 $dbu->DoQuery($query2);
 $contact_info = $dbu->FetchRow();
 $username = $contact_info['email'];
 $dbid_ar= explode('_',$seldbname);
 $dbid= $dbid_ar[1];
 $ishttps='';
 if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    $ishttps="&ishttps=1";
 }
 if($del){
 	$call_to = "https://$report_addr/report_back_end/index.php?cmd=cu$ishttps&del=1&username=$username&jobname=$jobname&dbname=$seldbname&dbid=$dbid&dbserver=$my_addr";
 } else {
 	$call_to = "https://$report_addr/report_back_end/index.php?cmd=cu$ishttps&username=$username&jobname=$jobname&dbname=$seldbname&dbid=$dbid&dbserver=$my_addr";
 }
 $process = curl_init($call_to);
 curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
 curl_setopt($process, CURLOPT_HEADER, 1);
 curl_setopt($process, CURLOPT_USERPWD, $up);
 curl_setopt($process, CURLOPT_TIMEOUT, 30);
 curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
 $return = curl_exec($process);
 curl_close($process);
 header("Location: gva_tab1.php?seldbname=$seldbname&currtab=4");
?>
