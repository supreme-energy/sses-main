<?php
/*
 * Created on Jul 2, 2013
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 include_once 'dbio.class.php';
 if(isset($_REQUEST['dbcheck'])){
 	include_once 'dbupdate.inc.php';
 }
 
 
 include('session_handler.php');
 ?>
 <HTML>
<HEAD>
<TITLE>SGTA REPORTING</TITLE>
<LINK rel='stylesheet' type='text/css' href='projws.css'/>
<LINK rel='stylesheet' type='text/css' href='themes/blue/style.css'/>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />
  <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
  <script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
  <link rel='stylesheet' type='text/css' href='jquery.timepicker.css'/>
<script type="text/javascript" src='jquery.timepicker.js'></script>
<script type="text/javascript" src='js/jquery.tablesorter.js'></script>
<script>
	$(document).ready(function() {
    	height = (window.innerHeight > 0) ? window.innerHeight : screen.height;
		width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
		mwidth = $("#main").width()
		mheight =$("#main").height()
		ewidth  =$("#error_popup").width()
		eheight = $("#error_popup").height()
		marginw = (width - mwidth)/2
		marginh = (height - mheight)/2.3
		margine = (width - ewidth)/2
		$("#main").css('margin-left',marginw);
		
		$("#error_popup").css('margin-left',margine);
	});
</script>
<STYLE>
#layer1 {
	position: fixed;
	visibility: hidden;
	background-color: #eee;
	border: 1px solid #000;
	padding: 0 2;
}
#layer1 input {
	border: none;
	background-color: transparent;
	color: blue;
	padding: 0 0;
	margin: 0;
}
#close {
	float: left;
}
</STYLE>
</HEAD>

<?if($reload=="") echo "<BODY onload=''>";
else echo "<BODY onload=''>";
	include 'error_message.php';
?>
	
 <?switch($_REQUEST['cmd']){
 		case 'login':
 			include 'login.php';
 			break;
 		case 'logout':
 			$_SESSION['token']=null;
 			header('Location: index.php?cmd=login');
 			break;
 		case 'report_list':
 			include 'reports.php';
 			break;
 		case 'report_view':
 			include 'report_view.php';
 			break;
 		case 'cu':
 			$r = find_or_create_and_assign($_REQUEST);
 			break;
 		case 'cu_all':
 			unassociate_all($_REQUEST);
 			break;
 		case 'atj':
 			$r = associate($_REQUEST);
 			break;
 		case 'atjr':
 			$r = unassociate($_REQUEST);
 			break;
 		default:
 			header('Location: index.php?cmd=login');
 			break;
 	}
 ?>
 </BODY>
 </HTML>

<?
function report_list($params){
 	global $uid;
 	$dbu=new dbio();
	$dbu->OpenDb();
 	$jobas = $params['ja'];
 	$retar=array();
 	$query = "select * from user_tdbas where id = $jobas";
 	if($dbu->DoQuery($query)){
 		$row = $dbu->FetchRow();
 		$query = http_build_query(array('seldbname'=>$row['dbname']),'','&');
 		$url = 'http://'.$row['dbserver'].'/sses/json/reports_list.php?'.$query;
 		$up  = $row['dbserver_uname'].':'.$row['dbserver_pass'];
 		$process = curl_init($url);
		curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($process, CURLOPT_HEADER, 1);
		curl_setopt($process, CURLOPT_USERPWD, $up);
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		$return = curl_exec($process);
		curl_close($process);
		$pos = strpos($return,"\r\n\r\n");
		$resp = substr ($return,$pos);
 		return $resp;	
 	} else {
 		$retar['status']=0;
 		$retar['message']="Error finding user assocition";	
 	}
 	return json_encode($retar);		
 }
 
 function unassociate_all($params){
 	$dbu=new dbio();
	$dbu->OpenDb();
	$dbname=$params['dbname'];
	$dbserver=$params['dbserver'];
 	$del=  "delete from user_tdbas where dbname='$dbname' and dbserver='$dbserver'";
 	$dbu->DoQuery($del);
 }
 
 function unassociate($params){
	$dbu=new dbio();
	$dbu->OpenDb();
	$error=false;
	$uid='';
	$dbname='';
	$dbid='';
	$dbserver='';
	$retar = array();
	if(isset($params['user_id']) && $params['user_id']!=''){
		$uid = $params['user_id'];	
	} else {
		$error = true;
		$message.='user_id must be included and cannot be blank';
	}
	if(isset($params['dbname']) && $params['dbname']!=''){
		$dbname = $params['dbname'];
	} else {
		$error = true;
		$message.=' dbname must be included and cannot be blank.';
	}
	if(isset($params['dbid']) && $params['dbid']!=''){
		$dbid = $params['dbid'];
	} else {
		$error = true;
		$message.=' dbid must be included and cannot be blank.';
	} 	
	if(isset($params['dbserver']) && $params['dbserver']!=''){
		$dbserver = $params['dbserver'];
	} else {
		$error = true;
		$message.=' dbserver must be included and cannot be blank.';
	}
	$jobname=urldecode($params['jobname']);
	if(!$error){
		$del=  "delete from user_tdbas where user_id=$uid and dbid='$dbid' and dbname='$dbname' and dbserver='$dbserver'";
		$dbu->DoQuery($del);
		$retar['status']=1;
		$retar['message']='User to Job association removed.';
		return $retar;
	}else{
		$retar['status']=0;
		$retar['message']=$message;
		return $retar;	
	}
	
 }
 function associate($params){
	$dbu=new dbio();
	$dbu->OpenDb();
	$error=false;
	$uid='';
	$dbname='';
	$dbid='';
	$dbserver='';
	$retar = array();
	if(isset($params['user_id']) && $params['user_id']!=''){
		$uid = $params['user_id'];	
	} else {
		$error = true;
		$message.='user_id must be included and cannot be blank';
	}
	if(isset($params['dbname']) && $params['dbname']!=''){
		$dbname = $params['dbname'];
	} else {
		$error = true;
		$message.=' dbname must be included and cannot be blank.';
	}
	if(isset($params['dbid']) && $params['dbid']!=''){
		$dbid = $params['dbid'];
	} else {
		$error = true;
		$message.=' dbid must be included and cannot be blank.';
	} 	
	if(isset($params['dbserver']) && $params['dbserver']!=''){
		$dbserver = $params['dbserver'];
	} else {
		$error = true;
		$message.=' dbserver must be included and cannot be blank.';
	}
	$jobname=urldecode($params['jobname']);
	if(!$error){
		$check_for_existing= "select count(*) as cnt from user_tdbas where user_id=$uid and dbid='$dbid' and dbname='$dbname' and dbserver='$dbserver'";
		$dbu->DoQuery($check_for_existing);
		$row = $dbu->FetchRow();
		if($row['cnt']<=0){
			$insert = "insert into user_tdbas (user_id,dbid,dbname,dbserver,dbserver_uname,dbserver_pass,job_name) values ($uid,$dbid,'$dbname','$dbserver','reportreader','stoic121627','$jobname')";
			$dbu->DoQuery($insert);
		}
		$retar['status']=1;
		$retar['message']='User to Job association.';
		return $retar;
	}else{
		$retar['status']=0;
		$retar['message']=$message;
		return $retar;	
	}
	
 }
 
 function find_or_create_and_assign($params){
 	$result = create_user($params);
 	$params['user_id']=$result['status'];
 	if(isset($params['del'])){
 		unassociate($params);	
 	}else{
 		associate($params);
 	}
 }

 function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $count = mb_strlen($chars);

    for ($i = 0, $result = ''; $i < $length; $i++) {
        $index = rand(0, $count - 1);
        $result .= mb_substr($chars, $index, 1);
    }

    return $result;
}
 function clean_up_user($params){
 	
 }
 
 function sendmail($to,$message) {
	$smtp_from ="supreme2@sses.us";
	$subject = "SGTA Report User Created"; 
	$smtp_server = "localhost:25";
	$smtp_login = "sses";
	$smtp_password="sses";
	$email_attach="";
	
	$strcmd=sprintf("sendEmail -l /tmp/sendemail.log -f %s -t %s -u \"%s\" -s %s -a %s -xu %s -xp %s -m \"%s\"",
	$smtp_from, $to, $subject, $smtp_server, $email_attach, $smtp_login, $smtp_password, $message);
	$output=shell_exec($strcmd);
}

 function create_user($params){
 	$dbu=new dbio();
	$dbu->OpenDb();
	$error = false;
	$username='';
	$password='';
	$retar = array();
	
	$username = $params['username'];	
	
	if(isset($params['password']) && $params['password']!=''){
		$password = $params['password'];
	} else {
		$password = generatePassword();
		
	}
	if(!$error){
		$check_for_existing_uname = "select * from users where username='$username'";
		$dbu->DoQuery($check_for_existing_uname);
		$row = $dbu->FetchRow();
		if(!$row){
			$insert_user = "insert into users (username,password) values ('$username','$password')";
			$dbu->DoQuery($insert_user);
			$dbu->DoQuery($check_for_existing_uname);
			$row = $dbu->FetchRow();	
			$retar['status']=$row['id'];
			$retar['message']='user created';
			sendmail($username,"A new user has been created for use with SGTA reporting system.<br> This user will allow you real time access to the reports of any job SGTA is operating on.<br> Your username is $username<br> Your password is $password<br> The Reports server can be accessed at http://sgta.us");
			return $retar;
		} else {
			$retar['status']=$row['id'];
			$retar['message']='Username already exists please use another.';
			return $retar;	
		}
	}else{
		$retar['status']=0;
		$retar['message']=$message;
		return $retar;	
	}
	
	
 }
?>
