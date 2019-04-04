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
} else {
	$response = array("status"=>"failed", "message"=>"seldbname not found");
}
echo json_encode($response);
?>