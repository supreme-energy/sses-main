<?
include("api_header.php");

exec("./sses_gva -d $seldbname ");
exec("./sses_cc -d $seldbname");
exec("./sses_cc -d $seldbname -p");
exec ("./sses_af -d $seldbname");
echo json_encode(array("status" => "Success", "message" => "operation completed"));
?>