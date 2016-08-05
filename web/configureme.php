<?php
require_once("dbio.class.php");
/*
 * Created on May 15, 2014
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 // this file will contain all the code for configuring a newly spun up server
 $npass = isset($_REQUEST['npass'])?$_REQUEST['npass']:false;
 $reset = isset($_REQUEST['reset'])?$_REQUEST['reset']:false;
 $ip = isset($_REQUEST['ip'])?$_REQUEST['ip']:false;
 if($npass){
 	$cryptedpass = crypt($npass,base64_encode($npass));
 	$handle = fopen("/var/www/html/htpasswd",'r');
 	if($handle){
 		while(($buffer = fgets($handle,4096))!==false){
 			echo $buffer;
 			if(strpos($buffer,'subsurfacegeosteering')===false){
 				$newfiletxt .= $buffer;
 			} else {
 				$newfiletxt .= 'subsurfacegeosteering:'.$cryptedpass;
 			}
 		}
 		if (!feof($handle)) {
       	 	echo "Error: unexpected fgets() fail\n";
    	}
    	fclose($handle);
  		file_put_contents('/var/www/html/htpasswd',$newfiletxt);
 	}
 } 
 if($reset && $ip){
 	$db=new dbio("sgta_index");
	$db->OpenDb();
	$dbstoclean= array();
	$sql  = "select * from dbindex";
	$db->DoQuery($sql);
	while($db->FetchRow()){
		$dbname = $db->FetchField('dbname');
		$db->DoQuery("DROP DATABASE IF EXISTS \"$dbname\";");
	}
	$sql = "delete from dbindex";
	$db->DoQuery($sql);
	$sql = "update server_info set wan_addr='$ip',lan_addr='',reports_lan=''";
	$db->DoQuery($sql);
 }
?>
