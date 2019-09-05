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
        "realname" =>  $db->FetchField('realname'),
        "startmd" => $db->FetchField('startmd'),
        "endmd" => $db->FetchField('endmd'),
        "tot" => $db->FetchField("tot"),
        "bot" => $db->FetchField("bot"),
        "dip" => $db->FetchField("dip"),
        "azm" => $db->FetchField("azm")
    );

    include ("read_controllog_include.php");
    $results['data'] = $data;   
} else {
    $results = array(
        "status" => "Failed",
        "message" => "missing required parameter tablename"
    );
}

echo json_encode($results);
?>