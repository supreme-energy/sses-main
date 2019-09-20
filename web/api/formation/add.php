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
    'show_line',
    'thickness',
    'interp_pattern_show',
    'interp_line_show',
    'interp_fill_show',
    'vert_pattern_show',
    'vert_line_show',
    'vert_fill_show'
);
$updates_array = array();
$values_array  = array();
if(strpos($_SERVER['CONTENT_TYPE'],'application/json') !== false){    
    list($updates_array, $values_array) = jsonPostReadCreate(file_get_contents('php://input'), $field_names);
} else {
    list($updates_array, $id) = queryReader($_REQUEST, $field_names);
}
if (count($updates_array) > 0) {
    $thickness = $updates_array['thickness'];
    unset($updates_array['thickness']);
    unset($values_array['thickness']);
    $query = "insert into addforms (".implode($values_array, ",").") values (" . implode($updates_array, ',') . ");";
    $db->DoQuery($query);
    $query = "select * from addforms order by id desc limit 1";
    $db->DoQuery($query);
    $db->FetchRow();
    $id = $db->FetchField("id");
    $db->DoQuery($query);
    exec ("../../sses_af -d $seldbname");
    if($thickness){
        $query = "update addformsdata set thickness = ". $thickness . " where infoid=$id";        
        $db->DoQuery($query);
    }  
}
exec ("../../sses_af -d $seldbname");
$db->DoQuery("select * from addforms where id=$id");

$results = array(
    "id" => $db->FetchField("id"),
    "label" => $db->FetchField("label"),
    "color" => $db->FetchField("color"),
    "bg_color" => $db->FetchField('bg_color'),
    "bg_percent" => $db->FetchField('bg_percent'),
    "pat_color"  => $db->FetchField('pat_color'),
    "pat_num"    => $db->FetchField('pat_num'),
    "show_line"  => $db->FetchField('show_line'),
    "interp_pattern_show" => $db->FetchField("interp_pattern_show"),
    "interp_line_show" => $db->FetchField("interp_line_show"),
    "interp_fill_show" => $db->FetchField("interp_fill_show"),
    "vert_pattern_show" => $db->FetchField("vert_pattern_show"),
    "vert_line_show" => $db->FetchField("vert_line_show"),
    "vert_fill_show" => $db->FetchField("vert_fill_show"),
    "data" => array()
);
include('../read_formation_include.php');
$results['data'] = $data;
echo json_encode($results);
?>