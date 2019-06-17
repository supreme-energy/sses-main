<?
include("api_header.php");
$id = $_REQUEST['id'];
$field_names = array('md', 'inc', 'azm', 'tvd', 'pos', 'vs', 'ns', 'ew', 'ca', 'cd', 'dl', 'cl', 'bot', 'dip', 'fault', 'method');
$updates_array = array();
$method_updated = false;
$query = "select * from projections where id = ". $id;
$db->DoQuery($query);
$db->FetchRow();
$data = $db->FetchField('data');
$pos = null;
foreach($field_names as $field_name){	
    if(isset($_REQUEST[$field_name])){
		$value = $_REQUEST[$field_name];
		if($field_name != 'pos'){
		  array_push($updates_array, "$field_name = '$value'");
		}
		$$field_name = $value;
    } else {
        if($field_name != 'pos'){
            $$field_name = $db->FetchField($field_name);
        } 
    }
}
if(pos===null){
    if ($method == 6 || $method == 7) $pos = explode($data,',')[2];
    else if($method==8)  $pos = explode($data,',')[1];
    else $pos = 0;
}
if($method==0) $data="$md,0,0";
else if($method>=3 && $method<=5) $data="$md,$inc,$azm";
else if($method==6) $data="$tvd,$vs,$pos";
else if($method==7) $data="$tot,$vs,$pos";
else if($method==8) $data="$vs,$pos,$dip,$fault";
else $data="0,0,0";

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