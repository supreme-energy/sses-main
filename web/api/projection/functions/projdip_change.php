<?php
function projdipChange($db, $projdip){
    $query = "select id from projections order by md asc limit 3";
    $db->DoQuery($query);
    $updates = "";
    while($row = $db->FetchRow()){
        $updates .= "update projections set dip=$projdip where id=".$row['id'].";";
    }
    $db->DoQuery($updates);
}
?>