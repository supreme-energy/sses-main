 <?php 
 include("../api_header.php");
 include("../request_presence_validation.php");
 $response = Array();
 list($request_fields, $errors) = request_presence_validation($request,
     Array(           
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
    $data="0,0,0";
    if($method==0) $data="$dmd,0,0";
    else if($method>=3 && $method<=5) $data="$dmd,$dinc,$dazm";
    else if($method==6) $data="$tvd,$vs,$tpos";
    else if($method==7) $data="$tot,$vs,$tpos";
    else if($method==8) $data="$vs,$tpos,$dip,$fault";
    $db->DoQuery("INSERT INTO projections (method, data, md, inc, azm, dip, fault, tvd, vs, tot, bot)
		VALUES ('$method','$data','$md','$inc','$azm','$dip','$fault','$tvd','$vs','$tot','$bot');");
    $db->DoQuery("SELECT * FROM projections order by id desc limit 1");
    exec ("./sses_af -d $seldbname");    
    $db->FetchRow();
    $svyid = $db->FetchField("id");
    $bot = $db->FetchField("tot")+$bot_thickness;
    $tot = $db->FetchField("tot")+$tot_thickness;
    $response = Array("status" => "success", "projection"=>
        Array(
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
            'data'=>$db->FetchField("data")
            )
    );
 } else {
     $response = Array("status"=>"failed", "errors" => $errors);
 }
 echo json_encode($response);
 ?>