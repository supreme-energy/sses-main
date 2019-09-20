 <?php
 include("api_header.php");
 include("readappinfo.inc.php");
 include("readwellinfo.inc.php");

 $with_data = $_REQUEST['data'] == '1';
 $db->DoQuery('select * from addforms order by thickness');
 $results = array();
 while($db->FetchRow()) {
 	$id = $db->FetchField("id");
 	$query = "select count(*) from addformsdata where infoid=$id";
 	$db2=new dbio($seldbname);
 	$db2->OpenDb();
 	$db2->DoQuery($query);
 	$db2->FetchRow();
 	$result = array(
 			"id" => $id,
 			"label" => $db->FetchField("label"),
 			"color" => $db->FetchField("color"),
 			"bg_color" => $db->FetchField('bg_color'),
 			"bg_percent" => $db->FetchField('bg_percent'),
 			"pat_color"  => $db->FetchField('pat_color'),
 			"pat_num"    => $db->FetchField('pat_num'),
 			"show_line"  => $db->FetchField('show_line'),
 	    "interp_pattern_show" => $db->FetchField("interp_pattern_show"),
 	    "interp_line_show" => $db->FetchField("interp_line_show"),
 	    "interp_fill_show" => $db->FetchField("interp_fill_show"),
 	    "vert_pattern_show" => $db->FetchField("vert_pattern_show"),
 	    "vert_line_show" => $db->FetchField("vert_line_show"),
 	    "vert_fill_show" => $db->FetchField("vert_fill_show"),
 	        "db_count" => $db2->FetchField('count')
 	);
 	$db2->CloseDb();
 	if($with_data){
 		include("read_formation_include.php");
 		$result['actual_count'] = count($data);
 		$result['data'] = $data; 		
 	}
 	array_push($results, $result); 	
 }
 
 echo json_encode($results);
 ?>