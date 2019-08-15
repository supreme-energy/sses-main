<?php 
include ("../api_header.php");
include ("../shared_functions/json_post_reader.php");//queryReader(request, allowed) and jsonPostReader(json_body, allowed)
include ("./shared.php");
$field_names = array(
    'label',
    'color',
    'logscale',
    'enabled',
    'single_plot'
);
$updates_array = array();
$id = 0;
if(strpos($_SERVER['CONTENT_TYPE'],'application/json') !== false){
    list($updates_array, $id) = jsonPostReader(file_get_contents('php://input'), $field_names);
} else {
    list($updates_array, $id) = queryReader($_REQUEST, $field_names);
}
if ($id == 0) echo json_encode(array("status"=>"failed", "message" => "id is required")); 
if (count($updates_array) > 0) {
    $query = "update edatalogs set " . implode($updates_array, ',') . " where id=$id";
    $db->DoQuery($query);
}
exec ("../../sses_af -d $seldbname");
echo addDataJson($id, false);
?>
