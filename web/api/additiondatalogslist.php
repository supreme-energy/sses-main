 <?php
 include("api_header.php");
 include("readappinfo.inc.php");
 include("readwellinfo.inc.php");
 exec ("../sses_af -d $seldbname");
 $with_data = $_REQUEST['data'] == '1';
 $db->DoQuery('select * from edatalogs order by id');
 $results = array();
 while($db->FetchRow()) {
 	$id = $db->FetchField("id");	
 	$tablename = $db->FetchField("tablename");
 	$result = array(
 			"id" => $id,
 	        "tablename" => $tablename,
 			"label" => $db->FetchField("label"),
 			"color" => $db->FetchField("color"),
 			"scalelo" => $db->FetchField('scalelo'),
 			"scalehi" => $db->FetchField('scalehi'),
 			"logscale"  => $db->FetchField('logscale'),
 			"enabled"    => $db->FetchField('enabled'),
 			"color"  => $db->FetchField('color'),
 	        "single_plot" => $db->FetchField('single_plot')
 	);
 	if($with_data){
 		include("read_edata_log.include.php");
 		$result['data'] = $data;
 	}
 	array_push($results, $result); 	
 }
 
 echo json_encode($results);
 ?>