 <?php
include ("../api_header.php");
$id = $_REQUEST['id'];
$field_names = array(
    'fault',
    'dip',
    'secttot',
    'sectbot',
    'scalebias',
    'scalefactor'
);
$updates_array = array();
foreach ($field_names as $field_name) {
    if (isset($_REQUEST[$field_name])) {
        $value = $_REQUEST[$field_name];
        $query_field_name = $field_name;
        if ($field_name == 'unixtime_src') {
            $query_field_name = 'srcts';
        }
        array_push($updates_array, "$field_name = '$value'");
    }
}
if (count($updates_array) > 0) {
    $query = "update welllogs set " . implode($updates_array, ',') . " where id=$id";
    $db->DoQuery($query);
}
exec ("../../sses_gva -d $seldbname");
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