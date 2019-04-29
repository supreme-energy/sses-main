<?
include("api_header.php");
$id = $_REQUEST['id'];
$field_names = array('md', 'inc', 'azm', 'tvd', 'vs', 'ns', 'ew', 'ca', 'cd', 'dl', 'cl', 'bot', 'dip', 'fault');
$updates_array = array();
foreach($field_names as $field_name){
	if(isset($_REQUEST[$field_name])){
		$value = $_REQUEST[$field_name];
		array_push($updates_array, "$field_name = '$value'");	
	}
}
$query = "update surveys set ". implode($updates_array, ',') . ' where id=$id'
echo $query;
?>