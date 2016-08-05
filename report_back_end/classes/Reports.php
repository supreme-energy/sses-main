<?php
class Reports {
    
    function __construct($request) {
    	$this->db=new dbio(); 
    }
    
    function report_list($params,$cmd=null){
	 	session_start();
	 	$uid =$_SESSION['user_id'];
	 	$dbu=new dbio();
		$dbu->OpenDb();
	 	$jobas = $params['ja'];
	 	$retar=array();
	 	$query = "select * from user_tdbas where id = $jobas and user_id=$uid";
	 	if($dbu->DoQuery($query)){
	 		$row = $dbu->FetchRow();
	 		if($cmd){
	 			$query = http_build_query(array('seldbname'=>$row['dbname'],'cmd'=>$cmd),'','&');
	 		} else {
	 			$query = http_build_query(array('seldbname'=>$row['dbname']),'','&');
	 		}
	 		$up  = $row['dbserver_uname'].':'.$row['dbserver_pass'];
	 		$url = 'http://'.$up.'@'.$row['dbserver'].'/sses/json/reports_list.php?'.$query;
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
	 		return json_decode($resp);	
	 	} else {
	 		$retar['status']=0;
	 		$retar['message']="Error finding user assocition";	
	 	}
	 	return $retar;		
   }
   function get_report_png($params){
   		session_start();
 	 	$uid =$_SESSION['user_id'];
	 	$dbu=new dbio();
		$dbu->OpenDb();
	 	$jobas = $params['ja'];
	 	$report_id = $params['id'];
	 	$retar=array();
	 	$query = "select * from user_tdbas where id = $jobas and user_id=$uid";
	 	if($dbu->DoQuery($query)){
	 		$row = $dbu->FetchRow();
	 		$up  = $row['dbserver_uname'].':'.$row['dbserver_pass'];
	 		$query = http_build_query(array('seldbname'=>$row['dbname'],'id'=>$report_id,'png'=>1),'','&');
	 		$url = 'http://'.$up.'@'.$row['dbserver'].'/sses/report_download.php?'.$query;
	 		$process = curl_init($url);
			curl_setopt($process, CURLOPT_USERPWD, $up);
			curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
			$return = curl_exec($process);
			curl_close($process);
			$pos = strpos($return,"\r\n\r\n");
			$resp = substr ($return,$pos);
			$now = strtotime($row['created']);
	 		$retar['status']=1;
	 		$retar['filename']=sprintf("tmp/%s_%s.surveyplotlat.pdf", date('Y-m-d_H-i-s_T',$now), $row['dbname']);
	 		$retar['content']=$resp;
	 	} else {
	 		$retar['status']=0;
	 		$retar['message']="Error finding user assocition";	
	 	}
	 	return $retar;	  	
   }
   function get_report($params,$cmd=null){
 	 	session_start();
 	 	$uid =$_SESSION['user_id'];
	 	$dbu=new dbio();
		$dbu->OpenDb();
	 	$jobas = $params['ja'];
	 	$report_id = $params['id'];
	 	$retar=array();
	 	$query = "select * from user_tdbas where id = $jobas and user_id=$uid";
	 	if($dbu->DoQuery($query)){
	 		$row = $dbu->FetchRow();
	 		$up  = $row['dbserver_uname'].':'.$row['dbserver_pass'];
	 		$query = http_build_query(array('seldbname'=>$row['dbname'],'id'=>$report_id),'','&');
	 		$url = 'http://'.$up.'@'.$row['dbserver'].'/sses/report_download.php?'.$query;
	 		$process = curl_init($url);
			curl_setopt($process, CURLOPT_USERPWD, $up);
			curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
			$return = curl_exec($process);
			curl_close($process);
			$pos = strpos($return,"\r\n\r\n");
			$resp = substr ($return,$pos);
			$now = strtotime($row['created']);
	 		$retar['status']=1;
	 		$retar['filename']=sprintf("tmp/%s_%s.surveyplotlat.pdf", date('Y-m-d_H-i-s_T',$now), $row['dbname']);
	 		$retar['content']=$resp;
	 	} else {
	 		$retar['status']=0;
	 		$retar['message']="Error finding user assocition";	
	 	}
	 	return $retar;	  	
   }
   
   function get_job_list(){
   		session_start();
   		$uid =$_SESSION['user_id'];
   		$dbu=new dbio();
		$dbu->OpenDb();
		$query = "select * from user_tdbas where user_id=$uid";
		$retar = array();
		$dbu->DoQuery($query);
		while($row  = $dbu->FetchRow()){
			array_push($retar,$row);
		}
		return $retar;
   }
   
   function check_for_new(){
   	
   }
}
?>