<?php 
	function import_welllog($db,$table,$fields_for_import, $values, $min_size_cnt, $survey_data){
		if(count($values)==0) return;
		$query = "select * from welllogs";
		$db->DoQuery($query);
		//$del_query_arr = Array();
		//while($db->FetchRow()){
		//	$table_name = $db->FetchField("tablename");
		//	array_push($del_query_arr,"drop table ".$table_name);
		//}
	}
?>