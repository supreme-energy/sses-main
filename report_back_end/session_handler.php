<?
include_once 'dbio.class.php';
session_start();
 switch($_REQUEST['cmd']){
 		case 'logout':
 		case 'report_list':
 		case 'report_count':
 			session_verify();
 			break;
 		case 'login':
 			if(session_verify(false)){
 				header('Location: index.php?cmd=report_list');
 			}
 		default:
 			break;
 	}

function session_verify($redirect=true){
	$token = $_SESSION['token'];
	$find_token="select user_id from tokens where token = '$token'";
 	$dbu=new dbio();
	$dbu->OpenDb();
	$dbu->DoQuery($find_token);
	if($row=$dbu->FetchRow()){
		$_SESSION['user_id']=$row['user_id'];
		return true;
	} else {
		if($redirect){
			header("Location: index.php?cmd=login");
		}
		return false;
	}
}

function generate_token($uid){
 	$retar = array();
 	$token = uniqid('tok');
 	$insert_token = "insert into tokens (user_id,token) values ($uid,'$token')";
 	
 	$dbu=new dbio();
	$dbu->OpenDb();
	$dbu->DoQuery($insert_token);
	$dbu->CloseDb();
	
 	$retar['status']=1;
	$retar['token']=$token;
	$retar['message']='';
	$_SESSION['token']=$token;
	return $retar;
 }

function login($params){
	$dbu=new dbio();
	$dbu->OpenDb();
	$error=false;
	$username='';
	$password='';
	$retar = array();
	if(isset($params['username']) && $params['username']!=''){
		$username = $params['username'];	
	} else {
		$error = true;
		$message.='username must be included and cannot be blank';
	}
	if(isset($params['password']) && $params['password']!=''){
		$password = $params['password'];
	} else {
		$error = true;
		$message.=' password must be included and cannot be blank.';
	}
	if(!$error){
		$validate_user = "select * from users where username='$username' and password='$password'";
		$dbu->DoQuery($validate_user);
		$row = $dbu->FetchRow();
		if($row){
			$uid = $row['id'];
			$find_token = "select * from tokens where user_id=$uid";
			$dbu->DoQuery($find_token);
			$row2 = $dbu->FetchRow();
			if($row2){
				$retar['status']=1;
				$retar['token']=$row2['token'];
				$retar['message']='';
				$_SESSION['token']=$row2['token'];
				return $retar;
			} else {
				return generate_token($uid);
			}
		} else {
			$retar['status']=0;
			$retar['message']='Invalid login information';
			return $retar;				
		}
	}else{
		$retar['status']=0;
		$retar['message']=$message;
		return $retar;	
	}	
}
?>