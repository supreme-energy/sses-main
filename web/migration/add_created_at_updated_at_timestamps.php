
<?php
$timestamp_columns = Array('created_at', 'updated_at');
$timestamp_tables  = Array('surveys', 'projections', 'wellinfo', 'appinfo');
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
    $query = "CREATE OR REPLACE FUNCTION trigger_set_timestamp()"
             ." RETURNS TRIGGER AS $$ "
             ." BEGIN"
             ." NEW.updated_at = NOW();"
             ." RETURN NEW;"
             ." END;"
             ." $$ LANGUAGE plpgsql;";
    echo $query."\n";
    $db->DoQuery($query);
    
    foreach($timestamp_tables as $tt){
        foreach($timestamp_columns as $tc){
            if(!$db->ColumnExists($tt, $tc)) {
                $query = "alter table $tt add $tc TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP";
                echo $query."\n";
                $db->DoQuery($query);
            }
        }
        $query ="CREATE TRIGGER set_timestamp BEFORE UPDATE ON $tt FOR EACH ROW EXECUTE PROCEDURE trigger_set_timestamp();";
        echo $query."\n";
        $db->DoQuery($query);
    }
}


?>