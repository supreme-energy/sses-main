<?
include("api_header.php");
include("shared_functions/pterm_change.php");

function mapToDbTable($tablein){
	if($tablein == 'autorc'){
		return 'rigminder_connection';
	}
	return $tablein;
}

function mapToDbTableField($tablein, $fieldname){
    if($tablein == 'witsml_details'){
        if($fieldname == 'welluid'){
            return 'wellid';
        }
        if($fieldname == 'boreuid'){
            return 'boreid';
        }
    }
    return $fieldname;
}

$response = array();
if($seldbname){
	$allowed_tables=array("wellinfo","appinfo", "autorc", "emailinfo", "witsml_details");
	
	if(strpos($_SERVER['CONTENT_TYPE'],'application/json') !== false){
	    $json_body = file_get_contents('php://input');
	    $obj = json_decode($json_body);
	    foreach($obj as $key => $value){
	        if(in_array($key, $allowed_tables)){
	            $table = mapToDbTable($key);
	            foreach($value as $field => $value){
	                $field_adjusted = mapToDbTableField($table, $field);
	                $query = "update $table set $field_adjusted = '$value'";	                
	                $result = $db->DoQuery($query);
	                if($table == 'wellinfo' && $field == 'tot'){
	                    $query = "update controllogs set tot = '$value'";
	                    $result = $db->DoQuery($query);
	                }
	            }
	        }
	    }
	    $response = array("status"=>"success", "message" => "operation successful");
	} else {
    	$table = $_REQUEST['table'];
    	if(in_array($table,$allowed_tables)){
    		$table = mapToDbTable($table);
    		$field = $_REQUEST['field'];
    		$value = $_REQUEST['value'];
    		$field_adjusted = mapToDbTableField($table, $field);
    		$query = "update $table set $field_adjusted='$value';";
    		$result = $db->DoQuery($query);		
    		$response = array("status"=>"success", "message" => "operation successful");
    	} else {
    		$response = array("status"=>"failed", "message"=>"operation not allowed");
    	}
	}
} else {
	$response = array("status"=>"failed", "message"=>"seldbname not found");
}
echo json_encode($response);
exec("./sses_gva -d $seldbname ");
exec("./sses_cc -d $seldbname");
exec("./sses_cc -d $seldbname -p");
exec ("./sses_af -d $seldbname");
?>