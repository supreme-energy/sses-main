<?php 
$tables  = Array('addforms');
$columns = Array(Array('interp_pattern_show', 'boolean'), Array('interp_line_show', 'boolean'), Array('interp_fill_show',  'boolean'), Array('vert_pattern_show', 'boolean'), Array('vert_line_show', 'boolean'), Array('vert_fill_show',  'boolean'));
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
            $query = "alter table $tt alter column $col_name set default true";
            $db->DoQuery($query);
            $query = "update $tt set $col_name = true";
            $db->DoQuery($query);
        }
    }
}
?>