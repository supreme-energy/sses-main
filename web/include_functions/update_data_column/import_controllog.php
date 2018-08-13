<?php 
	function import_controllog($db,$table,$fields_for_import, $values, $min_size_cnt){
		if(count($values)==0) return;
		$query = "select * from controllogs";
		$db->DoQuery($query);
		$del_query_arr = Array();
		while($db->FetchRow()){
			$table_name = $db->FetchField("tablename");
			array_push($del_query_arr,"drop table ".$table_name);
		}
		$query = implode(";", $del_query_arr);
		$db->DoQuery($query);
		$query = "delete from controllogs";
		$db->DoQuery($query);
		$db->DoQuery("INSERT INTO controllogs (tablename) VALUES ('xxxxxx');");
		$db->DoQuery("SELECT * FROM controllogs WHERE tablename='xxxxxx';");
		$id="";
		if($db->FetchRow())
			$id = $db->FetchField("id");
		
		if($id!="") {
			$tablename="cld_$id";
			echo "\n\n";
			echo $tablename;
			$query="CREATE TABLE \"$tablename\" (id serial not null primary key, md float, tvd float, vs float, value float, hide smallint not null default 0);";
			echo $query;
			$db->DoQuery($query);
			$query="UPDATE controllogs SET tablename='$tablename' WHERE id='$id';";
			$db->DoQuery($query);
		}
		$startmd=$startvs=$starttvd=99999;
		$endmd=$endvs=$endtvd=-99999;
		$insert_keys = "(".implode(",",$fields_for_import).")";
		$insert_portion_arr = Array();
		
		for($i = 0 ; $i < $min_size_cnt-1; $i++){
			$row_values_arr = Array();
			foreach($fields_for_import as $ffi){
				array_push($row_values_arr, $values[$ffi][$i]);
			}
			array_push($insert_portion_arr,"(".implode(",",$row_values_arr).")");
		}
		$query = "insert into ".pg_escape_string($tablename)." ".$insert_keys." values ". implode(",", $insert_portion_arr);
		$db->DoQuery($query);
		$db->DoQuery("select min(md) as startmd ,max(md) as endmd from ".$tablename);
		$row = $db->FetchRow();
		$startmd = $row['startmd'];
		$endmd = $row['endmd'];
		$db->DoQuery("UPDATE controllogs SET startmd=$startmd,endmd=$endmd WHERE id=$id;");
	}
?>