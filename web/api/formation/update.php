 <?php
include ("../api_header.php");
include ("../shared_functions/json_post_reader.php");//queryReader(request, allowed) and jsonPostReader(json_body, allowed)
$field_names = array(
    'label',
    'color',
    'bg_color',
    'bg_percent',
    'pat_color',
    'pat_num',
    'show_line'
);
$updates_array = array();
if(strpos($_SERVER['CONTENT_TYPE'],'application/json') !== false){    
    list($updates_array, $id) = jsonPostReader(file_get_contents('php://input'), $field_names);
} else {
    list($updates_array, $id) = queryReader($_REQUEST, $field_names);
}
if (count($updates_array) > 0) {
    $query = "update addforms set " . implode($updates_array, ',') . " where id=$id";
    $db->DoQuery($query);
}
exec ("../../sses_af -d $seldbname");
$db->DoQuery("select * from addforms where id=$id");
$db->FetchRow();
$results = array(
    "id" => $db->FetchField("id"),
    "label" => $db->FetchField("label"),
    "color" => $db->FetchField("color"),
    "bg_color" => $db->FetchField('bg_color'),
    "bg_percent" => $db->FetchField('bg_percent'),
    "pat_color"  => $db->FetchField('pat_color'),
    "pat_num"    => $db->FetchField('pat_num'),
    "show_line"  => $db->FetchField('show_line'),
    "data" => array()
);
include('../read_formation_include.php');
$results['data'] = $data;
echo json_encode($results);
?>