<?
include("api_header.php");
$id = $_REQUEST['id'];
$field_names = array('md', 'inc', 'azm', 'tvd', 'vs', 'ns', 'ew', 'ca', 'cd', 'dl', 'cl', 'bot', 'dip', 'fault', 'method');
$updates_array = array();
foreach($field_names as $field_name){
	if(isset($_REQUEST[$field_name])){
		$value = $_REQUEST[$field_name];
		array_push($updates_array, "$field_name = '$value'");	
	}
}
if(count($updates_array) > 0 ){
	$query = "update projections set ". implode($updates_array, ',') . " where id=$id";
	$db->DoQuery($query);
}
exec("./sses_gva -d $seldbname ");
exec("./sses_cc -d $seldbname");
exec("./sses_cc -d $seldbname -p");
exec ("./sses_af -d $seldbname");
echo json_encode(array("status" => "Success", "message" => "operation completed"));
?>