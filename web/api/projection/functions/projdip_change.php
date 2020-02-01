<?php
function projdipChange($db, $projdip){
    $query = "select id from projections order by md asc limit 3";
    $db->DoQuery($query);
    $updates = array();
    while($row = $db->FetchRow()){
        array_push($updates, "update projections set dip=$projdip where id=".$row['id'].";");
    }
    foreach($updates as $q){
        $db->DoQuery($q);
    }    
}
?>