<?php 
function ptermChange($pterm_val, $db){
    if($pterm_val=='bp'){
        $query = "select * from projections";
        $db->DoQuery($query);
        $queries = array();
        while($db->FetchRow()){
            if($db->FetchField('method')!=6){
                continue;
            }
            $id = $db->FetchField('id');
            $tvd = $db->FetchField('tvd');
            $vs = $db->FetchField('vs');
            $tot = $db->FetchField('tot');
            $tpos = $tot - $tvd;
            $data="$tvd,$vs,$tpos";
            array_push($queries, "update projections set method=6, data='$data' where id=$id");
        }
        foreach($queries as $query){            
            $db->DoQuery($query);
        }
    }
}

?>