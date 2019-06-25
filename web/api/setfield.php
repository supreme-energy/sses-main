<?
include("api_header.php");

function mapToDbTable($tablein){
	if($tablein == 'autorc'){
		return 'rigminder_connection';
	}
	return $tablein;
}

$response = array();
if($seldbname){
	$allowed_tables=array("wellinfo","appinfo", "autorc", "emailinfo", "witsml_details");
	if($_SERVER['Content-Type'] == 'application/json'){
	    $json_body = file_get_contents('php://input');
	    $obj = json_decode($json_body);
	    foreach($obj as $key => $value){
	        if(in_array($key, $allowed_tables)){
	            $table = mapToDbTable($table);
	            foreach($value as $field => $value){
	                $query = "update $table set $field = '$value'";
	                $result = $db->DoQuery($query);
	            }
	        }
	    }
	} else {
    	$table = $_REQUEST['table'];
    	if(in_array($table,$allowed_tables)){
    		$table = mapToDbTable($table);
    		$field = $_REQUEST['field'];
    		$value = $_REQUEST['value'];
    		$query = "update $table set $field='$value';";
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