 <?php
include ("../api_header.php");
include ("../shared_functions/json_post_reader.php");//queryReader(request, allowed) and jsonPostReader(json_body, allowed)
$field_names = array(
    'fault',
    'dip',
    'sectdip',
    'secttot',
    'sectbot',
    'scalebias',
    'scalefactor'
);
$updates_array = array();
if(strpos($_SERVER['CONTENT_TYPE'],'application/json') !== false){    
    list($updates_array, $id) = jsonPostReader(file_get_contents('php://input'), $field_names);
} else {
    list($updates_array, $id) = queryReader($_REQUEST, $field_names);
}
if (count($updates_array) > 0) {
    $query = "update welllogs set " . implode($updates_array, ',') . " where id=$id";
    $db->DoQuery($query);
}
exec ("../../sses_gva -d $seldbname");
exec("../../sses_cc -d $seldbname");
exec("../../sses_cc -d $seldbname -p");
exec ("../../sses_af -d $seldbname");

$db->DoQuery("SELECT * FROM welllogs where id='$id'");
$db->FetchRow();
$tablename = $db->FetchField('tablename');
$results = array(
    "id" => $id,
    "tablename" => $tablename,
    "realname" => $db->FetchField('realname'),
    "startmd" => $db->FetchField('startmd'),
    "endmd" => $db->FetchField('endmd'),
    "starttvd" => $db->FetchField('starttvd'),
    "endtvd" => $db->FetchField('endtvd'),
    "startvs" => $db->FetchField('startvs'),
    "endvs" => $db->FetchField('endvs'),
    "startdepth" => $db->FetchField('startdepth'),
    "enddepth" => $db->FetchField('enddepth'),
    "fault" => $db->FetchField('fault'),
    "sectdip" => $db->FetchField('dip'),
    "secttot" => $db->FetchField('tot'),
    "sectbot" => $db->FetchField('bot'),
    "scalebias" => $db->FetchField('scalebias'),
    "scalefactor" => $db->FetchField('scalefactor')
);
include ("../read_welllog_include.php");
$results['data'] = $data;
echo json_encode(array(
    "status" => "Success",
    "welllog" => $results
));
?>