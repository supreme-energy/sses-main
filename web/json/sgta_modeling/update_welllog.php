<?php 
	$request_body = file_get_contents('php://input');
	$data = json_decode($request_body);
	foreach($data as $k=>$d){
		foreach($d as $values){
		  $query = "update $k set depth=".$values->depth." where id=".$values->id;		  
		}
		
	}
?>