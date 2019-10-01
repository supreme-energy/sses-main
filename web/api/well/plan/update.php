<?php
include("../../api_header.php");
include ("../../shared_functions/json_post_reader.php");
$field_names = array(
    'md',
    'inc',
    'azm',
    'tvd',
    'ns',
    'ew',
    'vs'
);

list($updates_array, $id) = jsonPostReader(file_get_contents('php://input'), $field_names);
if (count($updates_array) > 0) {
    $query = "update wellplan set " . implode($updates_array, ',') . " where id=$id";
    $db->DoQuery($query);
}

$output = shell_exec("../../../sses_cc -d $seldbname -w");
// $output = shell_exec("./sses_cc -d $seldbname -w -i ./tmp/wellplan.dat");
header("Location: ../../wellplan.php?seldbname=$seldbname");
?>