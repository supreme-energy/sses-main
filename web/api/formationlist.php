 <?php
 include("api_header.php");
 include("readappinfo.inc.php");
 include("readwellinfo.inc.php");
 exec ("../sses_af -d $seldbname");
 $with_data = $_REQUEST['data'] == '1';
 $db->DoQuery('select * from addforms order by thickness');
 $results = array();
 while($db->FetchRow()) {
 	$id = $db->FetchField("id");	
 	$result = array(
 			"id" => $id,
 			"label" => $db->FetchField("label"),
 			"color" => $db->FetchField("color"),
 			"bg_color" => $db->FetchField('bg_color'),
 			"bg_percent" => $db->FetchField('bg_percent'),
 			"pat_color"  => $db->FetchField('pat_color'),
 			"pat_num"    => $db->FetchField('pat_num'),
 			"show_line"  => $db->FetchField('show_line')
 	);
 	if($with_data){
 		include("read_formation_include.php");
 		$result['data'] = $data;
 	}
 	array_push($results, $result); 	
 }
 
 echo json_encode($results);
 ?>