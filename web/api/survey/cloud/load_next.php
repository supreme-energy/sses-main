<?php 
include './shared.php';
// find the last depth of data already imported
$db->DoQuery("SELECT endmd,scalebias,scalefactor FROM welllogs ORDER BY endmd DESC LIMIT 1;");
$lastbias=0;
$lastscale=1.0;
$initialize_pas = false;
if($db->FetchRow()) {
    $lastendmd = $db->FetchField("endmd");
    $lastbias = $db->FetchField("scalebias");
    $lastscale = $db->FetchField("scalefactor");
}
if(!isset($lastendmd)){
    $initialize_pas = true;
}
echo cloudSurveyCheck(true, false);
if($initialize_pas){
    include_once("../../projection/initialize_pas.php");
    $db2=new dbio($seldbname);
    $db2->OpenDb();
    initializeFirstTimePas($db, $db2);
    exec("../../../sses_cc -d $seldbname");
    exec("../../../sses_gva -d $seldbname");
    exec("../../../sses_cc -d $seldbname -p");
    exec ("../../../sses_af -d $seldbname");
    reMethodProjections($db, $db2);
}
exec("../../../sses_cc -d $seldbname");
exec("../../../sses_gva -d $seldbname");
exec("../../../sses_cc -d $seldbname -p");
exec ("../../../sses_af -d $seldbname");
?>