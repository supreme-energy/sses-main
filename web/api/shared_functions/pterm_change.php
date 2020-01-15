<?php 
function ptermChange($pterm_val, $db){
    if($pterm_val=='bp'){
        $query = "select * from projections";
        $db->DoQuery($query);
        $queries = array();
        while($db->FetchRow()){
            if($db->FetchField('method')==6){
                continue;
            }
            $pmethod = $db->FetchField('method');
            $pdata   = $db->FetchField('data');
            $id = $db->FetchField('id');
            $tvd = $db->FetchField('tvd');
            $vs = $db->FetchField('vs');
            $tot = $db->FetchField('tot');
            $tpos = $tot - $tvd;
            $data="$tvd,$vs,$tpos";
            array_push($queries, "update projections set method=6,prev_method=$pmethod, data='$data', prev_data='$pdata' where id=$id");
        }
        foreach($queries as $query){            
            $db->DoQuery($query);
        }
    }
}

function ptermRevert($db){
    $query = "select * from surveys where plan = 1";
    $db->DoQuery($query);
    $prev_project = $db->FetchRow();
    $query = "select * from projections order by md asc";
    $db->DoQuery($query);
    $queries = array();    
    while($cur_proj = $db->FetchRow()){
        
        $pmethod = $db->FetchField('prev_method');
        $cur_method = $db->FetchField("method");
        $cur_data = $db->FetchField("data");
        if($cur_method==$pmethod){
            continue;
        }
        if($pmethod == null || $pmethod == '' || $pmethod == '3'){
            continue;
        }
        $method = $pmethod;
        $tvd = $db->FetchField('tvd');
        $vs  = $db->FetchField('vs');
        $dip = $db->FetchField('dip');
        $fault = $db->FetchField('fault');
        $dmd=$cur_proj['md']-$prev_project['md'];
        $dinc=$cur_proj['inc']-$prev_project['inc'];
        $dazm=$cur_proj['azm']-$prev_project['azm'];
        $tot = $db->FetchField('tot');
        $pos = $tot - $tvd;
        if($method==0) $data="$dmd,0,0";
        else if($method>=3 && $method<=5) $data="$dmd,$dinc,$dazm";
        else if($method==6) $data="$tvd,$vs,$pos";
        else if($method==7) $data="$tot,$vs,$pos";
        else if($method==8) $data="$vs,$pos,$dip,$fault";
        else $data="0,0,0";        
        $id = $db->FetchField('id');

        array_push($queries, "update projections set method=$method,prev_method=$cur_method, data='$data', prev_data='$cur_data' where id=$id");
        $prev_project = $cur_proj;
    }
    foreach($queries as $query){
        $db->DoQuery($query);
    }
}
?>