 <?php
include ("api_header.php");
$tablename = $_REQUEST['tablename'];
$results = array();
if (isset($tablename) && ! empty($tablename)) {
    $db->DoQuery("SELECT * FROM controllogs where tablename='$tablename'");
    $db->FetchRow();
    $id = $db->FetchField("id");
    $tablename = $db->FetchField("tablename");
    $results = array(
        "id" => $id,
        "tablename" => $tablename,
        "startmd" => $db->FetchField('startmd'),
        "endmd" => $db->FetchField('endmd'),
        "tot" => $db->FetchField("tot"),
        "bot" => $db->FetchField("tot"),
        "azm" => $db->FetchField("amz")
    );

    include ("read_controllog_include.php");
    $result['data'] = $data;   
} else {
    $results = array(
        "status" => "Failed",
        "message" => "missing required parameter tablename"
    );
}

echo json_encode($results);
?>