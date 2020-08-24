<?
include("api_header.php");
$id = $_REQUEST['id'];
$field_names = array('md', 'inc', 'azm', 'tvd', 'vs', 'ns', 'ew', 'ca', 'cd', 'dl', 'cl', 'bot', 'dip', 'fault', 'created_at', 'unixtime_src');
$updates_array = array();
$welllog_updates = array();
$update_wellogifexsists = false;
foreach($field_names as $field_name){
	if(isset($_REQUEST[$field_name])){
		$value = $_REQUEST[$field_name];
		$query_field_name = $field_name;
		if($field_name == 'unixtime_src'){
			$query_field_name = 'srcts';
		}
		array_push($updates_array, "$field_name = '$value'");
		if($field_name == 'dip' || $field_name == 'fault'){
		    $update_wellogifexsists = true;
		    array_push($welllog_updates, "$field_name = '$value'");
		}
	}
}
if(count($updates_array) > 0 ){
	$query = "update surveys set ". implode($updates_array, ',') . " where id=$id";
	$db->DoQuery($query);
    $query = "select md from surveys where id=$id";
    $db->DoQuery($query);
    $row = $db->FetchRow();
    $lmd = $row['md'];
	$query = "select id from welllogs where startmd > $lmd and endmd <= $lmd limit 1";
	$db->DoQuery($query);
	$row = $db->FetchRow();
	$wellogid = $row['id'];
	$query = "update welllogs set ".implode($welllog_updates, ',')." where id=$id";
	$db->DoQuery($query);
}
exec("../sses_gva -d $seldbname ");
exec("../sses_cc -d $seldbname");
exec("../sses_cc -d $seldbname -p");
exec ("../sses_af -d $seldbname");
echo json_encode(array("status" => "Success", "message" => "operation completed"));
?>