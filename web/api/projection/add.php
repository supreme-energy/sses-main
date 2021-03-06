 <?php 
 include("../api_header.php");
 include("../shared_functions/request_presence_validation.php");
 $response = array();
 list($request_fields, $errors) = presenceValidation($_REQUEST,
     array(           
           'method', 
           'md',
           'inc',
           'azm',
           'dip',
           'fault',
           'tvd',
           'vs',
           'tot',
           'bot',
           'pmd',
           'pinc',
           'pazm',
           'ptvd',
           'pca',
           'pcd'
         ));
 $proj_dip_query = "select projdip from wellinfo limit 1";
 $db->DoQuery($proj_dip_query);
 $db->FetchRow();
 $proj_dip = $db->FetchField("projdip");
 if(count($errors)<=0){
     $db->DoQuery("select * from addforms");
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
    extract($request_fields);
    $vs_start = $vs - 200;
    $vs_end   = $vs + 100;
    $wellplan_query = "select * from wellplan where vs >= $vs_start and vs <= $vs_end";
    $db->DoQuery($wellplan_query);
    $best_row = null;
    $last_diff = 1000000;
    while($cur_row = $db->FetchRow()){
        $cur_diff = abs($cur_row['vs']- $vs);
        if($cur_diff < $last_diff){
            $last_diff = $cur_diff;
            $best_row = $cur_row;
        }
    }
    if($best_row){
        $inc = $best_row['inc'];
        $azm = $best_row['azm'];
        $md  = $best_row['md'];
        $method = 3;
        
    }
    $data="0,0,0";
    $dmd=$md-$pmd;
    $dinc=$inc-$pinc;
    $dazm=$azm-$pazm;
    $dtvd=$tvd-$ptvd;
    
    if($inc >= 83 && $inc <= 97){              
        $dip = $best_row['inc']- 90;
    } else {
        $dip = $proj_dip;
    }
    if($method==0) $data="$dmd,0,0";
    else if($method>=3 && $method<=5) $data="$dmd,$dinc,$dazm";
    else if($method==6) $data="$tvd,$vs,$tpos";
    else if($method==7) $data="$tot,$vs,$tpos";
    else if($method==8) $data="$vs,$tpos,$dip,$fault";
    $db->DoQuery("INSERT INTO projections (method, data, md, inc, azm, dip, fault, tvd, vs, tot, bot)
		VALUES ('$method','$data','$md','$inc','$azm','$dip','$fault','$tvd','$vs','$tot','$bot');");
    exec("../../sses_gva -d $seldbname ");
    exec("../../sses_cc -d $seldbname");
    exec("../../sses_cc -d $seldbname -p");
    exec ("../../sses_af -d $seldbname");    
    
    $db->DoQuery("SELECT * FROM projections order by id desc limit 1");
    $db->FetchRow();
    $svyid = $db->FetchField("id");
    $bot = $db->FetchField("tot")+$bot_thickness;
    $tot = $db->FetchField("tot")+$tot_thickness;
    $tf = $db->FetchField("tf");
    $direction = substr($tf, -1);
    $tf_numeric = floatval(substr($tf, 0 , -1));
    $tf_final = number_format($tf_numeric, 3);
    $response = array("status" => "success", "projection"=>
        array(
            'id' => $db->FetchField("id") ,
            'md' => sprintf("%.2f",$db->FetchField("md")) ,
            'inc' =>sprintf("%.2f", $db->FetchField("inc")),
            'azm' =>sprintf("%.2f", $db->FetchField("azm")),
            'tvd' =>$db->FetchField("tvd"),
            'vs' =>sprintf("%.2f", $db->FetchField("vs")),
            'ns' =>sprintf("%.2f", $db->FetchField("ns")),
            'ew' =>sprintf("%.2f", $db->FetchField("ew")),
            'ca' =>sprintf("%.2f", $db->FetchField("ca")),
            'cd' =>sprintf("%.2f", $db->FetchField("cd")),
            'dl' =>sprintf("%.2f", $db->FetchField("dl")),
            'cl' =>sprintf("%.2f", $db->FetchField("cl")),
            'tcl'=>$db->FetchField("tot"),
            'tot'=>sprintf("%.2f",$tot),
            'bot'=>sprintf("%.2f",$bot),
            'dip'=>sprintf("%.2f", $db->FetchField("dip")),
            'fault'=>sprintf("%.2f", $db->FetchField("fault")),
            'method'=>$db->FetchField("method"),
            'data'=>$db->FetchField("data"),            
            'tf'  =>$tf_final,
            'tfdir' => $direction,
            'ptype'=>$db->FetchField("ptype")
            )
    );
 } else {
     $response = array("status"=>"failed", "errors" => $errors);
 }
 echo json_encode($response);
 ?>