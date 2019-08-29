<?php 
$tables  = Array('wellinfo');
$columns = Array(Array('draftcolor', 'text'), Array('selectedsurveycolor', 'text'));
require_once("../dbio.class.php");
$db=new dbio("sgta_index");
$db->OpenDb();
echo "\ndbupdate: Checking database for updates...\n";
$query = "select * from dbindex";
$db->DoQuery($query);
$dbnames = Array('sgta_template');
while($db->FetchRow()){
    array_push($dbnames, $db->FetchField('dbname'));
}
$db->CloseDb();
foreach($dbnames as $dbname){
    $db=new dbio($dbname);
    $db->OpenDb();
    foreach($tables as $tt){
        foreach($columns as $tc){
            $col_name = $tc[0];
            $col_type = $tc[1];
            $query = "alter table $tt add $col_name $col_type";
            echo $dbname.":".$query. "\n";
            $db->DoQuery($query);
        }
    }
}
?>