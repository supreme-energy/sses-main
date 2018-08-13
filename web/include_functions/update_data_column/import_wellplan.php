<?php 
	function import_wellplan($db, $table, $fields_for_import, $values, $min_size_cnt){
		if(count($values)==0) return;
		$query = "delete from ".pg_escape_string($table);
		$db->DoQuery($query);
		$insert_keys = "(".implode(",",$fields_for_import).")";
		$insert_portion_arr = Array();
		
		for($i = 0 ; $i < $min_size_cnt-1; $i++){
			$row_values_arr = Array();
			foreach($fields_for_import as $ffi){
				array_push($row_values_arr, $values[$ffi][$i]);
			}
			array_push($insert_portion_arr,"(".implode(",",$row_values_arr).")");
		}
		$query = "insert into ".pg_escape_string($table)." ".$insert_keys." values ". implode(",", $insert_portion_arr);
		echo $query;
		$db->DoQuery($query);
	}
?>