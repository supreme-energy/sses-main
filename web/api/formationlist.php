 <?php
 include("api_header.php");
 include("readappinfo.inc.php");
 include("readwellinfo.inc.php");
 exec ("../sses_af -d $seldbname");
 $db->DoQuery('select * from addforms order by thickness');
 $results = array();
 while($db->FetchRow()) {
 	array_push($results, array(
 			"id" => $db->FetchField("id"),
 			"label" => $db->FetchField("label"),
 			"color" => $db->FetchField("color"),
 			"bg_color" => $db->FetchField('bg_color'),
 			"bg_percent" => $db->FetchField('bg_percent'),
 			"pat_color"  => $db->FetchField('pat_color'),
 			"pat_num"    => $db->FetchField('pat_num'),
 			"show_line"  => $db->FetchField('show_line')
 	));
 }
 
 echo json_encode($results);
 ?>