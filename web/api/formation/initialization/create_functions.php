<?php 
function initialize_formations($dbconn){
    $query = "select * from addforms where label='TOT' or label='BOT'";
    $dbconn->DoQuery($query);
    $tot_present=false;
    $bot_present=false;
    while($dbconn->FetchRow()){
        if($dbconn->FetchField('label')=='TOT'){
            $tot_present=true;
        }
        if($dbconn->FetchField('label')=='BOT'){
            $bot_present=false;
        }
    }
    if(!$tot_present){
        $query_tot = "insert into addforms(label,thickness) values('TOT', -15);";
        $dbconn->DoQuery($query_tot);
    }
    if(!$bot_present){
        $query_bot = "insert into addforms(label,thickness) values('BOT', 15);";
        $dbconn->DoQuery($query_bot);
    }
    
}
?>