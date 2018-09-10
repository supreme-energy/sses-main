<?php 
	#include function files
	include "include_functions/update_data_column/import_wellplan.php";
	include "include_functions/update_data_column/import_controllog.php";
	include "include_functions/update_data_column/import_welllog.php";
	
	$column_data_map = Array("wellplan" => "md|inc|azm",
			"controllog" => "md|value|tvd|vs",
			"welllog" => "md|value");
	$field = $_REQUEST['field'];
	$filename = $_REQUEST['filename'];
	$start_col = $_REQUEST['col_start'];
	$start_row = $_REQUEST['row_start'];
	$table = $_REQUEST['table'];
	
	if(!isset($seldbname) or $seldbname == '') $seldbname = (isset($_REQUEST['seldbname']) ? $_REQUEST['seldbname'] : '');
	$fields_for_import = explode( '|' , $column_data_map[$table]);	
	$db=new dbio($seldbname);
	$db->OpenDb();
	$query = "select count(*) as cnt from file_values_map where source_table ='". pg_escape_string($table)."' and source_column='".pg_escape_string ($field)."'";
	$db->DoQuery($query);
	$row = $db->FetchRow();
	if($db->FetchField('cnt')>0){
		$query = "update file_values_map set imported_file_name='". pg_escape_string($filename)."'".
				 ", source_table='". pg_escape_string($table)."'".
				 ", source_column='".pg_escape_string ($field)."'".
				 ", value_type='NA'".
				 ", columns='".pg_escape_string ($start_col)."'".
				 ",rows='".pg_escape_string ($start_row)."'".
				 "where source_table='".pg_escape_string ($table)."'".
				 "and source_column='".pg_escape_string ($field)."'";
	} else{
		$query = "insert into file_values_map (imported_file_name,source_table, source_column, value_type, columns, rows) values (".
			"'". pg_escape_string($filename)."',".
			"'". pg_escape_string($table)."',".
			"'". pg_escape_string($field)."',".
			"'NA',".
			"'". pg_escape_string($start_col)."',".
			"'". pg_escape_string($start_row)."'".
			")";
	}
	$result = $db->DoQuery($query);
	$source_colar = Array();
	foreach($fields_for_import as $ffi){
		array_push($source_colar, "source_column = '".$ffi."'");
	}
	$source_colstr = "(".implode(" or ", $source_colar).")";
	$query = "select count(*) from file_values_map where source_table='".pg_escape_string($table)."' and ".$source_colstr;
	$result = $db->DoQuery($query);
	$row = $db->FetchRow();
	$values = Array();
	$min_size_cnt = 50000;
	if($db->FetchField('count') == count($fields_for_import)){				
		foreach($fields_for_import as $ffi){
			$these_values = array();
			$query = "select * from file_values_map where source_table='". pg_escape_string($table)."' and source_column = '".pg_escape_string ($ffi)."'";
			$db->DoQuery($query);
			$data_row = $db->FetchRow();
			$col = $data_row['columns'];
			$row = $data_row['rows'];
			$filename = $data_row['imported_file_name'];
			echo $filename;
			$filehandle = fopen("import_files/$seldbname/".$filename, "r");
			$lncnt = 0;
			$spliton = (strpos($filename, ".csv") > 0) ? "/[,]/" : "/[\s]+/";
			echo $spliton;
			while (($line = fgets($filehandle)) !== false) {
				if($lncnt < $row){
					$lncnt++;
					continue;
				} else{
					$line_arr = preg_split($spliton, (" ".$line));
					array_push($these_values, $line_arr[$col]);
				}
				$lncnt++;
			}
			if(count($these_values)< $min_size_cnt){
				$min_size_cnt=count($these_values);
			}
			fclose($filehandle);
			$values[$ffi] = $these_values;
		}
	}
	if($table == 'wellplan'){
		import_wellplan($db, $table, $fields_for_import, $values, $min_size_cnt);
	} else if($table == 'controllog'){
		import_controllog($db,$table,$fields_for_import, $values, $min_size_cnt);	
	} else if($table == 'welllog'){
		$survey_for_this = Array('md'=>0, 'inc' => 0, 'azm' => 0);
		import_welllog($db,$table,$fields_for_import, $values, $min_size_cnt, $survey_for_this);
	}

	//$query = "update wellinfo set $field = '$value';";
	$db->CloseDb();
	
	
	
	
?>