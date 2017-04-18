<?
 require_once("../dbio.class.php");
 header("Access-Control-Allow-Origin: *");
 header('Content-type: application/json');
 $seldbname = $_REQUEST['seldbname'];
 if(!$seldbname ||$seldbname==''){
 	$resp = json_encode(array('status'=>'0','message'=>'no database selected'));
 } else{
 	if($_REQUEST['cmd']=='get_count'){
 		$query = "select count(*) as cnt from reports where approved=1";
 		$cnt = 0;
 		$dbu=new dbio($seldbname);
 		if(!$dbu->OpenDb()){
			$status= 0;
			$message='Could not connect to job database';
		}
		$dbu->DoQuery($query);
		$row = $dbu->FetchRow();
 		$resp = json_encode(array("status"=>$status,'count'=>$row['cnt'],'message'=>$msg));
 	} else {
	 	$results= array();
	 	$dbu=new dbio($seldbname);
	 	$status=1;
	 	$message='';
		if(!$dbu->OpenDb()){
			$status= 0;
			$message='Could not connect to job database';
		}
	 	$query = "select * from reports where approved=1 order by id desc";
	 	$dbu->DoQuery($query);
	 	while($row=$dbu->FetchRow()){
	 		array_push($results,$row);
	 	}
	 	$resp = json_encode(array('status'=>$status,'results'=>$results,'message'=>$message));
 	}
 }
 
 echo $resp;
?>