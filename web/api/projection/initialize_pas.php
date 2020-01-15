<?php 
function initializeFirstTimePas($db, $db2){    
    $bit_depth_query = "select * from surveys order by md asc limit 1";
    $db->DoQuery($bit_depth_query);
    $last_survey_or_bit = $db->FetchRow();
    $last_depth = $db->FetchField('md');
    $proj_dip_query = "select projdip from wellinfo limit 1";
    $db->DoQuery($proj_dip_query);
    $db->FetchRow();
    $proj_dip = $db->FetchField("projdip");
    $well_plan_query = "select * from wellplan order by md ASC";
    $db->DoQuery($well_plan_query);
    $step = 'pa1';
    
    $pa_count = 0;
    $prev_row = $last_survey_or_bit;
    
    while($row = $db->FetchRow()){
        $curwp_md = $db->FetchField('md');
        $curwp_inc = $db->FetchField('inc');
        $vs = $db->FetchField('vs');
        if($step == 'pa1'){
            if($curwp_md > $last_depth+100){
                addProjection($prev_row, $row, $db2, $proj_dip);
                $pa_count++;
                $step = 'pa2';
                $prev_row = $row;
            }
        }else if($step == 'pa2'){
            if ($curwp_inc <= 35 && $curwp_inc >= 25){
                addProjection($prev_row, $row, $db2, $proj_dip);
                $pa_count++;
                $step = 'pa3';
                $prev_row = $row;
            }            
        }else if($step == 'pa3'){
            if ($curwp_inc <= 65 && $curwp_inc >= 55){
                addProjection($prev_row, $row, $db2, $proj_dip);
                $pa_count++;
                $step = 'pa4';
                $prev_row = $row;
            }  
        }else if($step == 'pa3_5'){
            if ($curwp_inc <= 81 && $curwp_inc >= 66 ){
                addProjection($prev_row, $row, $db2, $proj_dip);
                $pa_count++;
                $step = 'pa4';
                $prev_row = $row;
            }
        }else if($step == 'pa4'){
            if ($curwp_inc <= 98 && $curwp_inc >= 82){
                addProjection($prev_row, $row, $db2, $proj_dip);
                $pa_count++;
                $step = 'pa5';
                $last_vs = $vs;
                $prev_row = $row;
            }  
        } else {
            if(($curwp_md - $prev_row['md']) > 200){
                $dip = $curwp_inc - 90;
                addProjection($prev_row, $row, $db2, $dip);
                $prev_row = $row;
                $last_vs = $vs;
                $pa_count++;
            }
        }
        if($pa_count > 8){
            break;
        }
    }
}

function reMethodProjections($db, $db2){
    $query = "select (tot-tvd) as bprjtops from projections where inc > 70 order by md asc limit 1";
    $db->DoQuery($query);
    $pl_proj = $db->FetchRow();
    $query = "select (max(tvd) - min(tot))/3 as autodec from projections where inc > 70";
    $db->DoQuery($query);    
    $ap_data_row = $db->FetchRow();
    
    if($ap_data_row){
        $autopos_dec = ceil(floatval($ap_data_row['autodec']));
    } else {
        $autopos_dec = 5;
    }
    $query = "select * from projections where inc > 70 order by md asc offset 1";
    $db->DoQuery($query);
    while($row = $db->FetchRow()){
        if(floatval($posi) < 0){
            $pos = floatval($posi) + $autopos_dec;
            if($pos > 0){
                $pos = 0;
            }
        } else {
            $pos = floatval($posi) - $autopos_dec;
            if($pos < 0 ){
                $pos = 0;
            }
        }
        
        $posi = $pos;
        $rowid = $row['id'];
        $vs = $row['vs'];        
        $dip = $row['dip'];
        $fault = $row['fault'];
        $data="$vs,$pos,$dip,$fault";
        $method = 8;
        $sql = "update projections set data='$data', method=$method where id=$rowid";
        $db2->DoQuery($sql);
    }
    $sql = "update wellinfo set autoposdec = $autopos_dec";
    $db->DoQuery($sql);
}
function addProjection($prev_row, $well_plan_row, $db2 , $dip){    
    $dmd = $well_plan_row['md'] - $prev_row['md'];
    $dinc = $well_plan_row['inc'] - $prev_row['inc'];
    $dazm = $well_plan_row['azm'] - $prev_row['azm'];
    $method = '3';
    $data="$dmd,$dinc,$dazm";
    $tot=0;
    $bot=0;
    $vs = $well_plan_row['vs'];
    $tvd = $well_plan_row['tvd'];
    $fault = 0;
    $azm = $well_plan_row['azm'];
    $inc = $well_plan_row['inc'];    
    $md = $well_plan_row['md'];
    
    $db2->DoQuery("INSERT INTO projections (method, data, md, inc, azm, dip, fault, tvd, vs, tot, bot)
		VALUES ('$method','$data','$md','$inc','$azm','$dip','$fault','$tvd','$vs','$tot','$bot');");                
}
