 <?php
 include("api_header.php");
 $id = $_REQUEST['id'];
 $db->DoQuery("select * from addforms where id=$id"); 		
 $db->FetchRow(); 
 $results = array(
 		"id" => $db->FetchField("id"),
 		"label" => $db->FetchField("label"),
 		"color" => $db->FetchField("color"),
 		"bg_color" => $db->FetchField('bg_color'),
 		"bg_percent" => $db->FetchField('bg_percent'),
 		"pat_color"  => $db->FetchField('pat_color'),
 		"pat_num"    => $db->FetchField('pat_num'),
 		"show_line"  => $db->FetchField('show_line'),
 		"data" = array()
 );
 $db->DoQuery("SELECT * FROM addformsdata WHERE infoid=$id order by md");
 $data = array();
 while($db->FetchRow()) {
 	$tvd = $db->FetchField("tvd");
 	$tot = $db->FetchField("tot");
 	array_push($data,array(
 			"id" => $db->FetchField("id"),
 			"md" => $db->FetchField("md"),
 			"tvd" => $tvd,
 			"vs"  => $db->FetchField("vs"),
 			"tot" => $tot,
 			"bot" => $db->FetchField("bot"),
 			"fault" => $db->FetchField("fault"),
 			"thickness" => $db->FetchField("thickness"),
 			"pos" => printf("%.2f",$tot-$tvd)
 	)); 	
 }
 $results['data'] = $data;
 echo json_encode($results);
 ?>