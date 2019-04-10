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
 		"data" => array()
 );
 include('read_formation_include.php');
 $results['data'] = $data;
 echo json_encode($results);
 ?>