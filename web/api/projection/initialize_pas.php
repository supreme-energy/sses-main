<?php 
function initializeFirstTimePas($db, $db2){    
    $bit_depth_query = "select * from surveys order by md asc limit 1";
    $db->DoQuery($bit_depth_query);
    $last_survey_or_bit = $db->FetchRow();
    $last_depth = $db->FetchField('md');
    
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
            if($curwp_md > $last_depth){
                addProjection($prev_row, $row, $db2);
                $pa_count++;
                $step = 'pa2';
                $prev_row = $row;
            }
        }else if($step == 'pa2'){
            if ($curwp_inc <= 35 && $curwp_inc >= 25){
                addProjection($prev_row, $row, $db2);
                $pa_count++;
                $step = 'pa3';
                $prev_row = $row;
            }            
        }else if($step == 'pa3'){
            if ($curwp_inc <= 65 && $curwp_inc >= 55){
                addProjection($prev_row, $row, $db2);
                $pa_count++;
                $step = 'pa4';
                $prev_row = $row;
            }  
        }else if($step == 'pa4'){
            if ($curwp_inc <= 98 && $curwp_inc >= 82){
                addProjection($prev_row, $row, $db2);
                $pa_count++;
                $step = 'pa5';
                $last_vs = $vs;
                $prev_row = $row;
            }  
        } else {
            if(($curwp_md - $prev_row['md']) > 200){
                addProjection($prev_row, $row, $db2);
                $prev_row = $row;
                $last_vs = $vs;
                $pa_count++;
            }
        }
        if($pa_count > 7){
            break;
        }
    }
}

function addProjection($prev_row, $well_plan_row, $db2){    
    $db2->DoQuery("select * from addforms");
    $totid =null;
    $botid = null;
    $bot_thickness = 0;
    $tot_thickness = 0;
    while($db->FetchRow()){
        if(trim($db->FetchField('label'))=='TOT'){
            $tot_thickness = $db->FetchField("thickness");
            $totid = $db->FetchField('id');
        }
        if(trim($db->FetchField('label'))=='BOT'){
            $bot_thickness = $db->FetchField("thickness");
            $botid = $db->FetchField('id');
        }
    }
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
    $dip = 0;
    $azm = $well_plan_row['azm'];
    $inc = $well_plan_row['inc'];    
    $md = $well_plan_row['md'];
    
    $db->DoQuery("INSERT INTO projections (method, data, md, inc, azm, dip, fault, tvd, vs, tot, bot)
		VALUES ('$method','$data','$md','$inc','$azm','$dip','$fault','$tvd','$vs','$tot','$bot');");                
}
